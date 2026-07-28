<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::where('status', 'active')->get();

        // Default accounts (NIK matches docs in AGENTS.md)
        User::firstOrCreate(['nik' => '26050001'], [
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        $firstStore = $stores->first();
        if ($firstStore) {
            User::firstOrCreate(['nik' => '26050002'], [
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'store_id' => $firstStore->id,
            ]);

            User::firstOrCreate(['nik' => '26050005'], [
                'name' => 'Kasir',
                'email' => 'kasir@test.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'store_id' => $firstStore->id,
            ]);
        }

        $admins = [
            'Budi Santoso', 'Siti Rahayu', 'Dewi Lestari',
            'Agus Prasetyo', 'Rina Susanti', 'Hendra Wijaya',
            'Tan Mei Ling', 'Fajar Nugroho', 'Putri Ayu',
            'Rizky Ramadhan', 'Linda Kusuma', 'Eko Saputra',
            'Yoga Aditya', 'Nur Halimah', 'Dimas Kurniawan',
        ];

        $kasirs = [
            'Andi Pratama', 'Maya Sari', 'Doni Firmansyah', 'Nur Aini',
            'Reza Mahendra', 'Fitri Handayani', 'Bayu Setiawan',
            'Ayu Lestari', 'Firman Hidayat', 'Ratna Dewi', 'Galih Permana',
            'Indah Wulandari', 'Arif Budiman', 'Sari Melati', 'Tari Anggraini',
            'Roni Saputra', 'Wati Susilawati', 'Hadi Purnomo', 'Lina Marlina',
            'Joko Widodo', 'Nita Permata', 'Rudi Hartono', 'Dewi Sartika',
            'Irfan Hakim', 'Mega Putri', 'Eko Prasetyo', 'Rini Astuti',
            'Dian Saputra', 'Lina Sari', 'Bambang Irawan', 'Sinta Dewi',
            'Agus Setiawan', 'Putri Rahayu', 'Fajar Hidayat', 'Nur Azizah',
        ];

        $adminIdx = 0;
        $kasirIdx = 0;

        foreach ($stores as $store) {
            for ($i = 0; $i < 3; $i++) {
                $name = $admins[$adminIdx++];
                $email = strtolower(str_replace(' ', '.', $name)).'@test.com';
                User::firstOrCreate(['email' => $email], [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'store_id' => $store->id,
                ]);
            }

            for ($i = 0; $i < 7; $i++) {
                $name = $kasirs[$kasirIdx++];
                $email = strtolower(str_replace(' ', '.', $name)).'@test.com';
                User::firstOrCreate(['email' => $email], [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'kasir',
                    'store_id' => $store->id,
                ]);
            }
        }
    }
}
