<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionsSeeder extends Seeder
{
    public function run(): void
    {
        $makanan = Category::where('name', 'Makanan')->first();
        $minuman = Category::where('name', 'Minuman')->first();
        $snack = Category::where('name', 'Snack')->first();
        $nasiGoreng = Product::where('name', 'Nasi Goreng')->first();
        $esTeh = Product::where('name', 'Es Teh')->first();
        $kopiHitam = Product::where('name', 'Kopi Hitam')->first();
        $kerupuk = Product::where('name', 'Kerupuk')->first();
        $kentangGoreng = Product::where('name', 'Kentang Goreng')->first();
        $mieGoreng = Product::where('name', 'Mie Goreng')->first();
        $bakso = Product::where('name', 'Bakso')->first();
        $ayamGoreng = Product::where('name', 'Ayam Goreng')->first();

        $promos = [
            [
                'name' => 'Diskon Member Spesial',
                'type' => 'member',
                'description' => 'Diskon khusus member 10% (max Rp10.000)',
                'discount_percentage' => 10,
                'max_discount_amount' => 10000,
                'priority' => 10,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Diskon 15% All Item',
                'type' => 'percentage',
                'description' => 'Diskon 15% untuk minimal belanja Rp50.000',
                'discount_percentage' => 15,
                'min_purchase_amount' => 50000,
                'max_discount_amount' => 30000,
                'priority' => 5,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Potongan Rp5.000',
                'type' => 'nominal',
                'description' => 'Potongan Rp5.000 untuk minimal belanja Rp30.000',
                'discount_nominal' => 5000,
                'min_purchase_amount' => 30000,
                'priority' => 4,
                'is_active' => true,
                'stackable' => true,
            ],
            [
                'name' => 'Diskon Makanan 20%',
                'type' => 'category',
                'description' => 'Diskon 20% untuk semua produk kategori Makanan',
                'category_id' => $makanan->id,
                'discount_percentage' => 20,
                'max_discount_amount' => 15000,
                'priority' => 3,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Es Teh Lebih Murah',
                'type' => 'product',
                'description' => 'Potongan Rp2.000 untuk Es Teh',
                'product_id' => $esTeh->id,
                'discount_nominal' => 2000,
                'priority' => 2,
                'is_active' => true,
                'stackable' => true,
            ],
            [
                'name' => 'Beli 1 Nasi Goreng Gratis 1 Es Teh',
                'type' => 'buy_x_get_y',
                'description' => 'Beli 1 Nasi Goreng dapat 1 Es Teh gratis',
                'buy_product_id' => $nasiGoreng->id,
                'get_product_id' => $esTeh->id,
                'buy_quantity' => 1,
                'get_quantity' => 1,
                'priority' => 7,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Paket Nasi + Minum',
                'type' => 'bundle',
                'description' => 'Nasi Goreng + Es Teh + Kerupuk hanya Rp30.000',
                'products' => [$nasiGoreng->id, $esTeh->id, $kerupuk->id],
                'bundle_price' => 30000,
                'priority' => 8,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Diskon Belanja Besar',
                'type' => 'min_purchase',
                'description' => 'Potongan Rp10.000 untuk minimal belanja Rp100.000',
                'discount_nominal' => 10000,
                'min_purchase_amount' => 100000,
                'priority' => 6,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Happy Hour',
                'type' => 'time_based',
                'description' => 'Diskon 10% setiap Senin-Jumat jam 14:00-16:00',
                'discount_percentage' => 10,
                'max_discount_amount' => 15000,
                'day_of_week' => '1,2,3,4,5',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'priority' => 3,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Diskon Tier Member',
                'type' => 'tiered',
                'description' => 'Gold 10%, Silver 5%, Bronze 2%',
                'tiers' => [
                    ['tier' => 'gold', 'discount_percentage' => 10],
                    ['tier' => 'silver', 'discount_percentage' => 5],
                    ['tier' => 'bronze', 'discount_percentage' => 2],
                ],
                'priority' => 9,
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'name' => 'Dapatkan Voucher Belanja',
                'type' => 'voucher',
                'description' => 'Dapatkan voucher Rp5.000 untuk minimal belanja Rp75.000',
                'discount_nominal' => 5000,
                'voucher_threshold' => 75000,
                'priority' => 1,
                'is_active' => true,
                'stackable' => false,
            ],
        ];

        foreach ($promos as $data) {
            Promotion::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
