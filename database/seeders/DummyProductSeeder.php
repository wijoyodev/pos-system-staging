<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DummyProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            Product::create([
                'sku' => 'DMY-'.rand(100, 999).'-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'barcode' => '899'.str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'name' => 'Dummy Product '.$i,
                'selling_price' => rand(10, 500) * 1000,
                'cost_price' => rand(5, 400) * 1000,
                'stock' => rand(10, 200),
                'threshold' => 5,
            ]);
        }
    }
}
