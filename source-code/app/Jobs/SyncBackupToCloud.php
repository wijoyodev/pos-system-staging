<?php

namespace App\Jobs;

use App\Models\Backup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncBackupToCloud implements ShouldQueue
{
    use Queueable;

    public $backupId;

    public function __construct($backupId)
    {
        $this->backupId = $backupId;
    }

    public function handle(): void
    {
        $backup = Backup::find($this->backupId);
        if (! $backup) {
            return;
        }

        try {
            $localPath = storage_path('app/'.$backup->path);

            if (! file_exists($localPath)) {
                $backup->update(['status' => 'failed', 'notes' => 'File tidak ditemukan di lokal']);

                return;
            }

            $cloudPath = 'backups/'.$backup->filename;

            Storage::disk('s3')->writeStream($cloudPath, fopen($localPath, 'r'));

            $backup->update([
                'status' => 'synced',
                'notes' => 'Tersinkronisasi ke cloud (S3)',
            ]);

            Log::info('Backup synced to cloud: '.$backup->filename);
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'notes' => $e->getMessage(),
            ]);

            Log::error('Backup sync failed: '.$e->getMessage());
        }
    }
}
