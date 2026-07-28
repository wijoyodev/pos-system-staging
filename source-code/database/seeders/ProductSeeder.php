<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $makanan = Category::firstOrCreate(['name' => 'Makanan']);
        $minuman = Category::firstOrCreate(['name' => 'Minuman']);
        $snack = Category::firstOrCreate(['name' => 'Snack']);

        $suppliers = Supplier::all();
        $supplierMap = [];
        foreach ($suppliers as $s) {
            $supplierMap[$s->name] = $s->id;
        }

        $products = [
            ['name' => 'Nasi Goreng', 'cat' => $makanan->id, 'price' => 25000, 'cost' => 15000, 'stock' => 50, 'threshold' => 10, 'supplier' => 'PT. Sumber Pangan Jaya', 'barcode' => '8901234567890', 'unit' => 'Porsi', 'expired' => null],
            ['name' => 'Mie Goreng', 'cat' => $makanan->id, 'price' => 20000, 'cost' => 12000, 'stock' => 50, 'threshold' => 10, 'supplier' => 'PT. Sumber Pangan Jaya', 'barcode' => '8901234567891', 'unit' => 'Porsi', 'expired' => null],
            ['name' => 'Ayam Goreng', 'cat' => $makanan->id, 'price' => 22000, 'cost' => 13000, 'stock' => 30, 'threshold' => 5, 'supplier' => 'CV. Daging Segar Nusantara', 'barcode' => '8901234567892', 'unit' => 'Potong', 'expired' => now()->addDays(7)->toDateString()],
            ['name' => 'Sate Ayam', 'cat' => $makanan->id, 'price' => 30000, 'cost' => 18000, 'stock' => 25, 'threshold' => 5, 'supplier' => 'CV. Daging Segar Nusantara', 'barcode' => '8901234567893', 'unit' => 'Porsi', 'expired' => now()->addDays(3)->toDateString()],
            ['name' => 'Bakso', 'cat' => $makanan->id, 'price' => 18000, 'cost' => 10000, 'stock' => 40, 'threshold' => 10, 'supplier' => 'CV. Daging Segar Nusantara', 'barcode' => '8901234567894', 'unit' => 'Porsi', 'expired' => now()->addDays(14)->toDateString()],
            ['name' => 'Es Teh', 'cat' => $minuman->id, 'price' => 5000, 'cost' => 2000, 'stock' => 100, 'threshold' => 20, 'supplier' => 'CV. Berkah Minuman Sejahtera', 'barcode' => '8901234567895', 'unit' => 'Gelas', 'expired' => null],
            ['name' => 'Es Kopi', 'cat' => $minuman->id, 'price' => 12000, 'cost' => 6000, 'stock' => 50, 'threshold' => 10, 'supplier' => 'CV. Berkah Minuman Sejahtera', 'barcode' => '8901234567896', 'unit' => 'Gelas', 'expired' => null],
            ['name' => 'Kopi Hitam', 'cat' => $minuman->id, 'price' => 10000, 'cost' => 5000, 'stock' => 50, 'threshold' => 10, 'supplier' => 'CV. Berkah Minuman Sejahtera', 'barcode' => '8901234567897', 'unit' => 'Gelas', 'expired' => null],
            ['name' => 'Teh Hangat', 'cat' => $minuman->id, 'price' => 6000, 'cost' => 2500, 'stock' => 80, 'threshold' => 15, 'supplier' => 'CV. Berkah Minuman Sejahtera', 'barcode' => '8901234567898', 'unit' => 'Gelas', 'expired' => null],
            ['name' => 'Jus Alpukat', 'cat' => $minuman->id, 'price' => 15000, 'cost' => 8000, 'stock' => 30, 'threshold' => 5, 'supplier' => 'PT. Fresh Produce Indo', 'barcode' => '8901234567899', 'unit' => 'Gelas', 'expired' => now()->addDays(2)->toDateString()],
            ['name' => 'Jus Mangga', 'cat' => $minuman->id, 'price' => 12000, 'cost' => 6000, 'stock' => 30, 'threshold' => 5, 'supplier' => 'PT. Fresh Produce Indo', 'barcode' => '8901234567900', 'unit' => 'Gelas', 'expired' => now()->addDays(2)->toDateString()],
            ['name' => 'Kerupuk', 'cat' => $snack->id, 'price' => 3000, 'cost' => 1500, 'stock' => 100, 'threshold' => 20, 'supplier' => 'UD. Maju Snack Indonesia', 'barcode' => '8901234567901', 'unit' => 'Pcs', 'expired' => now()->addMonths(6)->toDateString()],
            ['name' => 'Kentang Goreng', 'cat' => $snack->id, 'price' => 10000, 'cost' => 5000, 'stock' => 40, 'threshold' => 10, 'supplier' => 'UD. Maju Snack Indonesia', 'barcode' => '8901234567902', 'unit' => 'Porsi', 'expired' => null],
            ['name' => 'Cireng', 'cat' => $snack->id, 'price' => 8000, 'cost' => 4000, 'stock' => 35, 'threshold' => 8, 'supplier' => 'UD. Maju Snack Indonesia', 'barcode' => '8901234567903', 'unit' => 'Pcs', 'expired' => now()->addMonths(3)->toDateString()],
            ['name' => 'Pisang Goreng', 'cat' => $snack->id, 'price' => 8000, 'cost' => 4000, 'stock' => 30, 'threshold' => 8, 'supplier' => 'PT. Fresh Produce Indo', 'barcode' => '8901234567904', 'unit' => 'Pcs', 'expired' => null],
        ];

        $stores = Store::where('status', 'active')->get();

        foreach ($products as $p) {
            $supplierId = $supplierMap[$p['supplier']] ?? null;
            $profitPct = $p['cost'] > 0 ? round((($p['price'] - $p['cost']) / $p['cost']) * 100) : 0;

            $product = Product::firstOrCreate(
                ['name' => $p['name']],
                [
                    'category_id' => $p['cat'],
                    'selling_price' => $p['price'],
                    'cost_price' => $p['cost'],
                    'profit_percentage' => $profitPct,
                    'tax_amount' => 1,
                    'threshold' => $p['threshold'],
                    'unit' => $p['unit'],
                    'expired_date' => $p['expired'],
                    'sku' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                    'barcode' => $p['barcode'],
                    'primary_supplier_id' => $supplierId,
                ]
            );

            foreach ($stores as $store) {
                StockProduct::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $store->id],
                    ['quantity' => $p['stock']]
                );
            }
        }
    }
}
