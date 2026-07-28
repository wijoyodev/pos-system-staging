<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\History;
use App\Models\HistoryItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class HistorySeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::where('status', 'active')->get();
        $cashiers = User::where('role', 'kasir')->get();
        $products = Product::all()->keyBy('name');
        $customers = Customer::all();

        $productPrice = function ($name) use ($products) {
            return $products[$name]->selling_price;
        };

        $invoiceBase = 'INV-'.now()->format('Ymd').'-';

        $transactions = [];

        $txData = [
            // Store 1: Jakarta 1
            [
                'store' => 'Toko Jakarta 1',
                'cashier_idx' => 0,
                'invoice_suffix' => '0001',
                'customer_idx' => null,
                'payment_method' => 'tunai',
                'amount_received' => 100000,
                'items' => [
                    ['product' => 'Nasi Goreng', 'qty' => 2],
                    ['product' => 'Es Teh', 'qty' => 2],
                    ['product' => 'Kerupuk', 'qty' => 1],
                ],
                'promo_discount' => 0,
                'tier_discount' => 0,
                'voucher_discount' => 0,
                'points_discount' => 0,
                'points_earned' => 0,
                'points_used' => 0,
            ],
            [
                'store' => 'Toko Jakarta 1',
                'cashier_idx' => 1,
                'invoice_suffix' => '0002',
                'customer_idx' => 0, // Ahmad Fauzi (gold)
                'payment_method' => 'qris',
                'amount_received' => 75000,
                'items' => [
                    ['product' => 'Sate Ayam', 'qty' => 1],
                    ['product' => 'Jus Alpukat', 'qty' => 1],
                    ['product' => 'Kentang Goreng', 'qty' => 2],
                ],
                'promo_discount' => 3000,
                'tier_discount' => 5000,
                'voucher_discount' => 0,
                'points_discount' => 10000,
                'points_earned' => 600,
                'points_used' => 10000,
            ],
            [
                'store' => 'Toko Jakarta 1',
                'cashier_idx' => 2,
                'invoice_suffix' => '0003',
                'customer_idx' => null,
                'payment_method' => 'debit',
                'amount_received' => 55000,
                'items' => [
                    ['product' => 'Ayam Goreng', 'qty' => 1],
                    ['product' => 'Nasi Goreng', 'qty' => 1],
                    ['product' => 'Es Teh', 'qty' => 1],
                ],
                'promo_discount' => 0,
                'tier_discount' => 0,
                'voucher_discount' => 5000,
                'points_discount' => 0,
                'points_earned' => 0,
                'points_used' => 0,
            ],
            [
                'store' => 'Toko Jakarta 1',
                'cashier_idx' => 0,
                'invoice_suffix' => '0004',
                'customer_idx' => 4, // Bambang Suprapto (silver)
                'payment_method' => 'tunai',
                'amount_received' => 200000,
                'items' => [
                    ['product' => 'Nasi Goreng', 'qty' => 3],
                    ['product' => 'Mie Goreng', 'qty' => 2],
                    ['product' => 'Ayam Goreng', 'qty' => 2],
                    ['product' => 'Es Kopi', 'qty' => 3],
                ],
                'promo_discount' => 15000,
                'tier_discount' => 4000,
                'voucher_discount' => 0,
                'points_discount' => 5000,
                'points_earned' => 1500,
                'points_used' => 5000,
            ],

            // Store 2: Jakarta 2
            [
                'store' => 'Toko Jakarta 2',
                'cashier_idx' => 3,
                'invoice_suffix' => '0005',
                'customer_idx' => 1, // Dewi Sartika (gold)
                'payment_method' => 'qris',
                'amount_received' => 120000,
                'items' => [
                    ['product' => 'Bakso', 'qty' => 4],
                    ['product' => 'Es Teh', 'qty' => 4],
                ],
                'promo_discount' => 8000,
                'tier_discount' => 6000,
                'voucher_discount' => 10000,
                'points_discount' => 0,
                'points_earned' => 800,
                'points_used' => 0,
            ],
            [
                'store' => 'Toko Jakarta 2',
                'cashier_idx' => 4,
                'invoice_suffix' => '0006',
                'customer_idx' => 5, // Sari Dewi (silver)
                'payment_method' => 'tunai',
                'amount_received' => 50000,
                'items' => [
                    ['product' => 'Kopi Hitam', 'qty' => 2],
                    ['product' => 'Pisang Goreng', 'qty' => 2],
                    ['product' => 'Cireng', 'qty' => 1],
                ],
                'promo_discount' => 0,
                'tier_discount' => 1500,
                'voucher_discount' => 0,
                'points_discount' => 0,
                'points_earned' => 300,
                'points_used' => 0,
            ],
            [
                'store' => 'Toko Jakarta 2',
                'cashier_idx' => 3,
                'invoice_suffix' => '0007',
                'customer_idx' => null,
                'payment_method' => 'debit',
                'amount_received' => 35000,
                'items' => [
                    ['product' => 'Teh Hangat', 'qty' => 1],
                    ['product' => 'Kentang Goreng', 'qty' => 1],
                    ['product' => 'Cireng', 'qty' => 2],
                ],
                'promo_discount' => 0,
                'tier_discount' => 0,
                'voucher_discount' => 0,
                'points_discount' => 0,
                'points_earned' => 0,
                'points_used' => 0,
            ],

            // Store 3: Bandung 1
            [
                'store' => 'Toko Bandung 1',
                'cashier_idx' => 6,
                'invoice_suffix' => '0008',
                'customer_idx' => 2, // Hendra Wijaya (gold)
                'payment_method' => 'tunai',
                'amount_received' => 300000,
                'items' => [
                    ['product' => 'Sate Ayam', 'qty' => 3],
                    ['product' => 'Nasi Goreng', 'qty' => 3],
                    ['product' => 'Jus Mangga', 'qty' => 3],
                    ['product' => 'Kentang Goreng', 'qty' => 3],
                ],
                'promo_discount' => 25000,
                'tier_discount' => 10000,
                'voucher_discount' => 15000,
                'points_discount' => 20000,
                'points_earned' => 2000,
                'points_used' => 20000,
            ],
            [
                'store' => 'Toko Bandung 1',
                'cashier_idx' => 7,
                'invoice_suffix' => '0009',
                'customer_idx' => 8, // Agus Salim (bronze)
                'payment_method' => 'qris',
                'amount_received' => 30000,
                'items' => [
                    ['product' => 'Mie Goreng', 'qty' => 1],
                    ['product' => 'Es Teh', 'qty' => 1],
                ],
                'promo_discount' => 0,
                'tier_discount' => 500,
                'voucher_discount' => 0,
                'points_discount' => 0,
                'points_earned' => 200,
                'points_used' => 0,
            ],
            [
                'store' => 'Toko Bandung 1',
                'cashier_idx' => 6,
                'invoice_suffix' => '0010',
                'customer_idx' => null,
                'payment_method' => 'tunai',
                'amount_received' => 25000,
                'items' => [
                    ['product' => 'Bakso', 'qty' => 1],
                    ['product' => 'Es Kopi', 'qty' => 1],
                ],
                'promo_discount' => 0,
                'tier_discount' => 0,
                'voucher_discount' => 0,
                'points_discount' => 0,
                'points_earned' => 0,
                'points_used' => 0,
            ],

            // Store 4: Surabaya 1
            [
                'store' => 'Toko Surabaya 1',
                'cashier_idx' => 9,
                'invoice_suffix' => '0011',
                'customer_idx' => 3, // Rina Marlina (gold)
                'payment_method' => 'debit',
                'amount_received' => 150000,
                'items' => [
                    ['product' => 'Ayam Goreng', 'qty' => 2],
                    ['product' => 'Jus Alpukat', 'qty' => 2],
                    ['product' => 'Kerupuk', 'qty' => 2],
                    ['product' => 'Pisang Goreng', 'qty' => 1],
                ],
                'promo_discount' => 12000,
                'tier_discount' => 8000,
                'voucher_discount' => 10000,
                'points_discount' => 15000,
                'points_earned' => 1000,
                'points_used' => 15000,
            ],
            [
                'store' => 'Toko Surabaya 1',
                'cashier_idx' => 10,
                'invoice_suffix' => '0012',
                'customer_idx' => 9, // Putri Ayu (bronze)
                'payment_method' => 'tunai',
                'amount_received' => 40000,
                'items' => [
                    ['product' => 'Kopi Hitam', 'qty' => 1],
                    ['product' => 'Teh Hangat', 'qty' => 2],
                    ['product' => 'Cireng', 'qty' => 1],
                ],
                'promo_discount' => 0,
                'tier_discount' => 600,
                'voucher_discount' => 0,
                'points_discount' => 0,
                'points_earned' => 200,
                'points_used' => 0,
            ],
            [
                'store' => 'Toko Surabaya 1',
                'cashier_idx' => 9,
                'invoice_suffix' => '0013',
                'customer_idx' => null,
                'payment_method' => 'qris',
                'amount_received' => 55000,
                'items' => [
                    ['product' => 'Mie Goreng', 'qty' => 1],
                    ['product' => 'Bakso', 'qty' => 1],
                    ['product' => 'Jus Mangga', 'qty' => 1],
                ],
                'promo_discount' => 5000,
                'tier_discount' => 0,
                'voucher_discount' => 0,
                'points_discount' => 0,
                'points_earned' => 0,
                'points_used' => 0,
            ],
        ];

        foreach ($txData as $tx) {
            $store = $stores->firstWhere('name', $tx['store']);
            $cashier = $cashiers->get($tx['cashier_idx']);
            $customer = $tx['customer_idx'] !== null ? $customers->get($tx['customer_idx']) : null;

            $subtotal = 0;
            foreach ($tx['items'] as $item) {
                $subtotal += $productPrice($item['product']) * $item['qty'];
            }

            $totalDiscount = $tx['promo_discount'] + $tx['tier_discount'] + $tx['voucher_discount'] + $tx['points_discount'];
            $totalAmount = $subtotal - $totalDiscount;
            $change = $tx['amount_received'] - $totalAmount;

            $invoiceId = $invoiceBase.$tx['invoice_suffix'];
            $history = History::firstOrCreate(
                ['invoice_id' => $invoiceId],
                [
                    'store_id' => $store->id,
                    'user_id' => $cashier->id,
                    'cashier_name' => $cashier->name,
                    'customer_id' => $customer?->id,
                    'subtotal' => $subtotal,
                    'tax' => 0,
                    'service' => 0,
                    'promo_discount' => $tx['promo_discount'],
                    'tier_discount' => $tx['tier_discount'],
                    'voucher_discount' => $tx['voucher_discount'],
                    'points_discount' => $tx['points_discount'],
                    'payment_method' => $tx['payment_method'],
                    'total_amount' => $totalAmount,
                    'amount_received' => $tx['amount_received'],
                    'change_amount' => $change,
                    'points_earned' => $tx['points_earned'],
                    'points_used' => $tx['points_used'],
                    'points_redeemed' => $tx['points_discount'],
                    'status' => 'completed',
                    'created_at' => now()->subHours(rand(1, 48)),
                    'updated_at' => now()->subHours(rand(1, 48)),
                ]
            );

            foreach ($tx['items'] as $item) {
                $price = $productPrice($item['product']);
                HistoryItem::firstOrCreate(
                    ['history_id' => $history->id, 'product_id' => $products[$item['product']]->id],
                    [
                        'quantity' => $item['qty'],
                        'price' => $price,
                        'discount' => 0,
                        'discount_description' => null,
                        'created_at' => $history->created_at,
                        'updated_at' => $history->updated_at,
                    ]
                );
            }
        }
    }
}
