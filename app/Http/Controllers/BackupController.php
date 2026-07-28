<?php

namespace App\Http\Controllers;

use App\Jobs\SyncBackupToCloud;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected function backupDir(): string
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    public function index()
    {
        $backupDir = $this->backupDir();
        $files = glob($backupDir.'/*.{sql,sqlite}', GLOB_BRACE);

        $backups = collect($files)
            ->map(fn ($f) => [
                'name' => basename($f),
                'path' => $f,
                'size' => filesize($f),
                'date' => filemtime($f),
            ])
            ->sortByDesc('date')
            ->values();

        $connection = config('database.default');
        $dbPath = database_path('database.sqlite');
        $dbSize = file_exists($dbPath) ? filesize($dbPath) : 0;

        $storageUsed = collect($files)->sum('filesize');

        $backupRecords = Backup::latest()->get();

        return view('pages.backup', [
            'title' => 'Backup Database',
            'backups' => $backups,
            'backupRecords' => $backupRecords,
            'dbSize' => $dbSize,
            'dbPath' => $dbPath,
            'connection' => $connection,
            'storageUsed' => $storageUsed,
        ]);
    }

    public function create()
    {
        try {
            $connection = config('database.default');
            $timestamp = now()->format('Y-m-d_His');
            $filename = "backup_{$timestamp}";

            $backupDir = storage_path('app/backups');
            if (! is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            if ($connection === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (! file_exists($dbPath)) {
                    return back()->withErrors(['error' => 'File database tidak ditemukan di: '.$dbPath]);
                }
                $ext = '.sqlite';
                $dest = "{$backupDir}/{$filename}{$ext}";
                copy($dbPath, $dest);
            } else {
                $dump = $this->mysqlDump();
                if (! $dump) {
                    $dump = $this->phpDump();
                }
                if (! $dump) {
                    return back()->withErrors(['error' => 'Gagal membuat backup. mysqldump tidak tersedia.']);
                }
                $ext = '.sql';
                $dest = "{$backupDir}/{$filename}{$ext}";
                file_put_contents($dest, $dump);
            }

            $fullFilename = $filename.$ext;

            $record = Backup::create([
                'filename' => $fullFilename,
                'disk' => 'local',
                'path' => 'backups/'.$fullFilename,
                'size' => filesize($dest),
                'checksum' => hash_file('sha256', $dest),
                'status' => 'pending',
                'notes' => 'Dibuat manual',
            ]);

            SyncBackupToCloud::dispatch($record->id);

            Log::info('Database backup created: '.$fullFilename);

            return redirect('/backup')->with('success', 'Backup database berhasil dibuat ('.number_format(filesize($dest) / 1024, 1).' KB). Sinkronisasi cloud sedang diproses.');
        } catch (\Exception $e) {
            Log::error('Backup failed: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal membuat backup: '.$e->getMessage()]);
        }
    }

    public function download($filename)
    {
        $path = $this->backupDir().'/'.basename($filename);

        if (! file_exists($path)) {
            return redirect('/backup')->withErrors(['error' => 'File backup tidak ditemukan.']);
        }

        return response()->download($path, $filename);
    }

    public function destroy($filename)
    {
        try {
            $path = $this->backupDir().'/'.basename($filename);

            if (! file_exists($path)) {
                return back()->withErrors(['error' => 'File backup tidak ditemukan.']);
            }

            unlink($path);

            Backup::where('filename', basename($filename))->delete();

            Log::info('Backup deleted: '.$filename);

            return redirect('/backup')->with('success', 'Backup berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Delete backup failed: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal menghapus backup.']);
        }
    }

    public function restore(Request $request)
    {
        try {
            $request->validate(['file' => 'required|file|mimes:sql,sqlite,db|max:512000']);

            $connection = config('database.default');
            $file = $request->file('file');

            if ($connection === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                copy($file->getRealPath(), $dbPath);
            } else {
                $sql = file_get_contents($file->getRealPath());
                DB::unprepared($sql);
            }

            Log::info('Database restored from: '.$file->getClientOriginalName());

            return redirect('/backup')->with('success', 'Database berhasil direstore.');
        } catch (\Exception $e) {
            Log::error('Restore failed: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal merestore database: '.$e->getMessage()]);
        }
    }

    public function restoreCloud($backupId)
    {
        try {
            $backup = Backup::findOrFail($backupId);

            if (! $backup->isSynced()) {
                return back()->withErrors(['error' => 'Backup belum tersinkronisasi ke cloud.']);
            }

            $localPath = $this->backupDir().'/'.$backup->filename;

            $cloudPath = 'backups/'.$backup->filename;

            if (! Storage::disk('s3')->exists($cloudPath)) {
                return back()->withErrors(['error' => 'File backup tidak ditemukan di cloud.']);
            }

            $content = Storage::disk('s3')->read($cloudPath);
            file_put_contents($localPath, $content);

            $connection = config('database.default');

            if ($connection === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                copy($localPath, $dbPath);
            } else {
                DB::unprepared($content);
            }

            Log::info('Database restored from cloud backup: '.$backup->filename);

            return redirect('/backup')->with('success', 'Database berhasil direstore dari cloud.');
        } catch (\Exception $e) {
            Log::error('Restore from cloud failed: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal merestore dari cloud: '.$e->getMessage()]);
        }
    }

    public function retrySync($backupId)
    {
        try {
            $backup = Backup::findOrFail($backupId);

            $backup->update(['status' => 'pending', 'notes' => 'Dicoba ulang']);

            SyncBackupToCloud::dispatch($backup->id);

            return redirect('/backup')->with('success', 'Sinkronisasi cloud dicoba ulang untuk "'.$backup->filename.'".');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal: '.$e->getMessage()]);
        }
    }

    protected function mysqlDump(): ?string
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db)
        );

        $output = null;
        $code = null;
        exec($cmd, $output, $code);

        return $code === 0 ? implode("\n", $output) : null;
    }

    protected function phpDump(): string
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $key = "Tables_in_{$dbName}";
        $sql = '-- Generated by POS Backup at '.now()."\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $create = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= $create[0]->{'Create Table'}.";\n\n";

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = collect((array) $row)->map(fn ($v) => is_null($v) ? 'NULL' : "'".addslashes($v)."'")->implode(', ');
                $sql .= "INSERT INTO `{$tableName}` VALUES ({$values});\n";
            }
            $sql .= "\n";
        }

        return $sql;
    }
}
