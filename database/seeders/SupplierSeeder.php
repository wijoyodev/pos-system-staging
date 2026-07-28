<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT. Sumber Pangan Jaya',
                'contact_name' => 'Budi Santoso',
                'phone' => '021-5551234',
                'email' => 'info@sumberpangan.co.id',
                'address' => 'Jl. Industri Raya No. 45, Jakarta Utara',
            ],
            [
                'name' => 'CV. Berkah Minuman Sejahtera',
                'contact_name' => 'Siti Rahayu',
                'phone' => '021-5552345',
                'email' => 'order@berkahminuman.com',
                'address' => 'Jl. Raya Bekasi Km. 12, Jakarta Timur',
            ],
            [
                'name' => 'UD. Maju Snack Indonesia',
                'contact_name' => 'Hendra Wijaya',
                'phone' => '021-5553456',
                'email' => 'sales@majusnack.id',
                'address' => 'Jl. Mangga Dua Raya No. 88, Jakarta Utara',
            ],
            [
                'name' => 'PT. Fresh Produce Indo',
                'contact_name' => 'Dewi Lestari',
                'phone' => '021-5554567',
                'email' => 'supply@freshproduce.co.id',
                'address' => 'Jl. Pasar Minggu No. 22, Jakarta Selatan',
            ],
            [
                'name' => 'CV. Daging Segar Nusantara',
                'contact_name' => 'Agus Prasetyo',
                'phone' => '021-5555678',
                'email' => 'order@dagingsegar.com',
                'address' => 'Jl. Kelapa Gading Blvd. No. 15, Jakarta Utara',
            ],
            [
                'name' => 'PT. Bumbu Dapur Makmur',
                'contact_name' => 'Rina Susanti',
                'phone' => '021-5556789',
                'email' => 'info@bumbudapur.co.id',
                'address' => 'Jl. Tanah Abang No. 33, Jakarta Pusat',
            ],
            [
                'name' => 'UD. Es Batu & Cold Chain',
                'contact_name' => 'Tan Mei Ling',
                'phone' => '021-5557890',
                'email' => 'order@esbatucold.com',
                'address' => 'Jl. Ancol Barat No. 7, Jakarta Utara',
            ],
            [
                'name' => 'PT. Packaging Solution',
                'contact_name' => 'Fajar Nugroho',
                'phone' => '021-5558901',
                'email' => 'sales@packaging-solution.id',
                'address' => 'Jl. Gatot Subroto Kav. 50, Jakarta Selatan',
            ],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['name' => $s['name']], $s);
        }
    }
}
