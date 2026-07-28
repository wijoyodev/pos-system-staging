<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateUserNik extends Command
{
    protected $signature = 'users:generate-nik';

    public function handle(): int
    {
        $users = User::all();
        foreach ($users as $user) {
            $year = $user->created_at->format('y');
            $month = $user->created_at->format('m');
            $prefix = $year.$month;

            $lastUser = User::where('nik', 'like', $prefix.'%')
                ->where('id', '<=', $user->id)
                ->orderBy('nik', 'desc')
                ->first();

            if ($lastUser && $lastUser->id !== $user->id) {
                $lastNumber = (int) substr($lastUser->nik, -4);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $count = User::where('nik', 'like', $prefix.'%')->count();
                $newNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }

            $user->nik = $prefix.$newNumber;
            $user->save();
            $this->line("User {$user->name}: {$user->nik}");
        }

        $this->info('NIK generated for all users!');

        return 0;
    }
}
