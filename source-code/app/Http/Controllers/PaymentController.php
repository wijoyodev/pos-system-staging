<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Customer;
use App\Models\History;
use App\Models\HistoryItem;
use App\Models\PendingOrder;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\StockProduct;
use App\Models\StoreSetting;
use App\Models\Voucher;
use App\Services\PromotionEngine;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        // Block payment access if already clerek today
        $hasClosed = Closing::where('user_id', $user->id)
            ->whereDate('closing_date', $today)
            ->where('status', '!=', 'rejected')
            ->exists();

        if ($hasClosed) {
            return redirect('/')->with('error', 'Akses pembayaran ditutup karena Anda sudah melakukan clerek.');
        }

        $cart = $request->session()->get('cart', []);
        $storeId = auth()->user()->store_id;
        $userId = auth()->id();
        $userRole = auth()->user()->role;

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        // Note: Tax sudah termasuk dalam harga produk (selling_price sudah include VAT)
        // Hapus perhitungan pajak terpisah
        $serviceRate = floatval(StoreSetting::getVal('service_charge', $storeId, '0'));

        $tax = 0;
        $service = $subtotal * ($serviceRate / 100);
        $total = $subtotal + $service;

        $customerId = $request->session()->get('customer_id');

        // Get available promos
        $availablePromos = collect([]);
        $promoDiscount = 0;
        $appliedPromos = [];

        try {
            $engine = new PromotionEngine(
                collect($cart),
                $subtotal,
                $storeId,
                $userId,
                $userRole,
                $customerId
            );
            $availablePromos = $engine->getAvailablePromotions();

            // Apply promos
            $engine->applyAutoPromos();
            $promoDiscount = $engine->getTotalDiscount();
            $appliedPromos = $engine->getAppliedPromos();
        } catch (\Exception $e) {
            \Log::error('PromotionEngine Error: '.$e->getMessage());
        }

        $total = max(0, $total - $promoDiscount);

        $customer = null;
        $tierDiscount = 0;

        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $tierDiscount = $total * $customer->tier_discount;
                $total -= $tierDiscount;
            }
        }

        $totalQty = 0;
        foreach ($cart as $item) {
            $totalQty += $item['quantity'];
        }

        return view('pages/payment', [
            'title' => 'Payment',
            'cart' => $cart,
            'subtotal' => $subtotal,
            'total_qty' => $totalQty,
            'tax' => $tax,
            'service' => $service,
            'total' => $total,
            'promo_discount' => $promoDiscount,
            'tier_discount' => $tierDiscount,
            'applied_promos' => $appliedPromos,
            'available_promos' => $availablePromos,
            'currency_code' => Setting::getVal('currency_code', 'id-ID'),
            'invoice_id' => 'INV-'.date('Y').'-'.rand(1000, 9999),
            'customer' => $customer,
            'loyalty_point_value' => floatval(StoreSetting::getVal('loyalty_point_value', $storeId, '1000')),
            'loyalty_min_redeem' => intval(StoreSetting::getVal('loyalty_min_redeem', $storeId, '10')),
            'loyalty_points_per_rupiah' => intval(StoreSetting::getVal('loyalty_points_per_rupiah', $storeId, '10000')),
        ]);
    }

    public function checkout(Request $request)
    {
        $cart = json_decode($request->cart, true);
        $storeId = auth()->user()->store_id;

        // Validate stock per store
        foreach ($cart as $item) {
            $product = Product::find($item['id']);
            if ($product && $storeId) {
                $stock = $product->getStockForStore($storeId);

                // Check recipe if it's a finished good
                $recipe = $product->recipe;
                if ($recipe && $recipe->items->isNotEmpty()) {
                    foreach ($recipe->items as $recipeItem) {
                        $rawProduct = Product::find($recipeItem->product_id);
                        if ($rawProduct) {
                            $rawStock = $rawProduct->getStockForStore($storeId);
                            if ($rawStock < ($recipeItem->quantity * $item['quantity'])) {
                                return response()->json(['error' => "Stok {$rawProduct->name} tidak mencukupi"], 400);
                            }
                        }
                    }
                } else {
                    if ($stock < $item['quantity']) {
                        return response()->json(['error' => "Stok {$product->name} tidak mencukupi"], 400);
                    }
                }
            }
        }

        $request->session()->put('cart', $cart);

        if ($request->customer_id) {
            $request->session()->put('customer_id', $request->customer_id);
        }

        return redirect()->route('payment.index');
    }

    public function checkVoucher(Request $request)
    {
        $request->validate(['code' => 'required']);

        $storeId = auth()->user()->store_id;
        $code = strtoupper($request->code);

        // First check promotions table for voucher type with code
        $voucherPromo = Promotion::where('type', 'voucher')
            ->where('code', $code)
            ->where('is_active', true)
            ->where(function ($q) use ($storeId) {
                $q->whereNull('store_id')->orWhere('store_id', $storeId);
            })
            ->first();

        if ($voucherPromo) {
            // Check min purchase
            if ($voucherPromo->min_purchase_amount) {
                $cart = $request->session()->get('cart', []);
                $subtotal = 0;
                foreach ($cart as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }

                if ($subtotal < $voucherPromo->min_purchase_amount) {
                    return response()->json([
                        'valid' => false,
                        'message' => 'Minimal belanja Rp'.number_format($voucherPromo->min_purchase_amount, 0, ',', '.').' untuk menggunakan voucher ini.',
                    ]);
                }
            }

            // Check usage limit
            if ($voucherPromo->usage_limit && $voucherPromo->usage_count >= $voucherPromo->usage_limit) {
                return response()->json(['valid' => false, 'message' => 'Voucher sudah mencapai batas penggunaan.']);
            }

            // Hitung diskon: nominal dulu, fallback ke persentase
            $cart = $request->session()->get('cart', []);
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $discount = $voucherPromo->discount_nominal;
            if (! $discount && $voucherPromo->discount_percentage) {
                $discount = $subtotal * ($voucherPromo->discount_percentage / 100);
                if ($voucherPromo->max_discount_amount && $discount > $voucherPromo->max_discount_amount) {
                    $discount = $voucherPromo->max_discount_amount;
                }
            }

            return response()->json([
                'valid' => true,
                'discount' => (int) $discount,
                'message' => 'Voucher berhasil digunakan!',
            ]);
        }

        // Fallback to old voucher system
        $voucher = Voucher::where('code', $request->code)->where('is_used', false)->first();

        if (! $voucher) {
            return response()->json(['valid' => false, 'message' => 'Voucher tidak valid atau sudah digunakan.']);
        }

        $cart = $request->session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $voucherMinRedeem = 75000;
        if ($subtotal < $voucherMinRedeem) {
            return response()->json(['valid' => false, 'message' => 'Minimal belanja Rp'.number_format($voucherMinRedeem, 0, ',', '.').' untuk menggunakan voucher ini.']);
        }

        return response()->json([
            'valid' => true,
            'discount' => $voucher->discount_amount,
            'message' => 'Voucher berhasil digunakan!',
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required',
            'amount_received' => 'required|numeric',
            'invoice_id' => 'required',
        ]);

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect('/');
        }

        $paymentData = $this->parsePaymentData($request);
        $totalPaid = collect($paymentData)->sum('amount');

        $user = auth()->user();
        $today = now()->format('Y-m-d');

        // Final check before processing payment
        $hasClosed = Closing::where('user_id', $user->id)
            ->whereDate('closing_date', $today)
            ->where('status', '!=', 'rejected')
            ->exists();

        if ($hasClosed) {
            return response()->json(['error' => 'Akses ditutup. Anda sudah melakukan clerek hari ini.'], 403);
        }

        $storeId = auth()->user()->store_id;
        $userId = auth()->id();
        $userRole = auth()->user()->role;

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        // Tax sudah termasuk dalam harga produk
        $tax = 0;
        $serviceRate = floatval(StoreSetting::getVal('service_charge', $storeId, '0'));

        $service = $subtotal * ($serviceRate / 100);
        $total = $subtotal + $service;

        $customerId = $request->session()->get('customer_id');

        // Apply Promotion Engine
        $promoDiscount = 0;
        $appliedPromos = [];

        try {
            $engine = new PromotionEngine(
                collect($cart),
                $subtotal,
                $storeId,
                $userId,
                $userRole,
                $customerId
            );

            // Get available promos for debugging
            $available = $engine->getAvailablePromotions();

            $engine->applyAutoPromos();
            $promoDiscount = $engine->getTotalDiscount();
            $appliedPromos = $engine->getAppliedPromos();

            // Log for debugging
            \Log::info('Promo Checkout Debug', [
                'cart_items' => collect($cart)->pluck('id')->toArray(),
                'subtotal' => $subtotal,
                'available_promos_count' => $available->count(),
                'available_promos' => $available->pluck('name', 'type')->toArray(),
                'promo_discount' => $promoDiscount,
                'applied_promos' => $appliedPromos,
            ]);
        } catch (\Exception $e) {
            \Log::error('Promo Engine Error: '.$e->getMessage());
        }

        $total = max(0, $total - $promoDiscount);

        \Log::info('Payment Final Total', [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service' => $service,
            'promo_discount' => $promoDiscount,
            'total_before_promo' => $subtotal + $tax + $service,
            'final_total' => $total,
        ]);

        $voucher = null;
        $pointsDiscount = 0;
        $tierDiscount = 0;
        $customer = null;
        $maxRedeemable = 0;

        // Tier Discount (before voucher — same order as index())
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $tierDiscount = $total * $customer->tier_discount;
                $total -= $tierDiscount;
                $total = max(0, $total);
            }
        }

        // Handle Voucher
        if ($request->voucher_code) {
            $voucherCode = strtoupper($request->voucher_code);
            $voucherPromo = Promotion::where('type', 'voucher')
                ->where('code', $voucherCode)
                ->where('is_active', true)
                ->where(function ($q) use ($storeId) {
                    $q->whereNull('store_id')->orWhere('store_id', $storeId);
                })
                ->first();

            if ($voucherPromo) {
                if ($voucherPromo->min_purchase_amount && $subtotal < $voucherPromo->min_purchase_amount) {
                    $voucher = null;
                } elseif ($voucherPromo->usage_limit && $voucherPromo->usage_count >= $voucherPromo->usage_limit) {
                    $voucher = null;
                } else {
                    $discount = $voucherPromo->discount_nominal;
                    if (! $discount && $voucherPromo->discount_percentage) {
                        $discount = $subtotal * ($voucherPromo->discount_percentage / 100);
                        if ($voucherPromo->max_discount_amount && $discount > $voucherPromo->max_discount_amount) {
                            $discount = $voucherPromo->max_discount_amount;
                        }
                    }
                    $total -= $discount;
                    $total = max(0, $total);

                    $voucherPromo->increment('usage_count');

                    $voucher = Voucher::firstOrCreate(
                        ['code' => $voucherPromo->code, 'is_used' => false],
                        ['discount_amount' => $discount]
                    );
                    if ($voucher->wasRecentlyCreated === false && $voucher->discount_amount != $discount) {
                        $voucher = Voucher::create([
                            'code' => $voucherPromo->code.'-'.strtoupper(substr(md5(uniqid()), 0, 4)),
                            'discount_amount' => $discount,
                        ]);
                    }
                }
            } else {
                $voucher = Voucher::where('code', $voucherCode)->where('is_used', false)->first();
                if ($voucher && $subtotal >= 75000) {
                    $total -= $voucher->discount_amount;
                    $total = max(0, $total);
                } else {
                    $voucher = null;
                }
            }
        }

        // Points Redemption
        if ($customer && $request->points_to_redeem > 0) {
            $loyaltyPointValue = floatval(StoreSetting::getVal('loyalty_point_value', $storeId, '1000'));
            $loyaltyMinRedeem = intval(StoreSetting::getVal('loyalty_min_redeem', $storeId, '10'));

            $maxRedeemable = min($request->points_to_redeem, $customer->available_points);
            if ($maxRedeemable >= $loyaltyMinRedeem) {
                $pointsDiscount = $maxRedeemable * $loyaltyPointValue;
                $total -= $pointsDiscount;
                $total = max(0, $total);
            }
        }

        if ($totalPaid < $total) {
            return back()->withErrors(['amount_received' => 'Amount received is less than total amount']);
        }

        $history = History::create([
            'invoice_id' => $request->invoice_id,
            'user_id' => auth()->id(),
            'cashier_name' => auth()->user()->name,
            'customer_id' => $customerId,
            'payment_method' => count($paymentData) > 1 ? 'split' : $paymentData[0]['method'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service' => $service,
            'promo_discount' => $promoDiscount,
            'points_discount' => $pointsDiscount,
            'tier_discount' => $tierDiscount,
            'voucher_discount' => $voucher ? $voucher->discount_amount : 0,
            'total_amount' => $total,
            'amount_received' => $totalPaid,
            'change_amount' => $totalPaid - $total,
            'store_id' => $storeId,
            'points_earned' => 0,
            'points_redeemed' => $maxRedeemable,
            'voucher_id' => $voucher ? $voucher->id : null,
        ]);

        foreach ($paymentData as $pmt) {
            $history->payments()->create($pmt);
        }

        // Points earned and Tier Update
        if ($customerId && $customer) {
            $pointsPerRupiah = intval(StoreSetting::getVal('loyalty_points_per_rupiah', $storeId, '10000'));
            if ($pointsPerRupiah > 0) {
                $basePoints = floor($total / $pointsPerRupiah);
                $earnedPoints = floor($basePoints * $customer->tier_multiplier);

                $customer->addPoints($earnedPoints);
                if ($maxRedeemable > 0) {
                    $customer->decrement('available_points', $maxRedeemable);
                    $customer->increment('used_points', $maxRedeemable);
                }

                // Update total spent and tier
                $customer->increment('total_spent', $total);
                $customer->updateTier();

                $history->update(['points_earned' => $earnedPoints]);
            }
        }

        $request->session()->forget('customer_id');

        // Calculate per-item discounts from applied promos
        $itemDiscounts = $this->calculateItemDiscounts($cart, $appliedPromos);

        foreach ($cart as $item) {
            $discount = $itemDiscounts[$item['id']]['discount'] ?? 0;
            $discountDesc = $itemDiscounts[$item['id']]['description'] ?? null;
            HistoryItem::create([
                'history_id' => $history->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => $discount,
                'discount_description' => $discountDesc,
            ]);

            // Deduct stock based on recipe
            $product = Product::find($item['id']);
            if ($product) {
                $storeId = auth()->user()->store_id;

                // Check if product has a recipe (finished good)
                $recipe = $product->recipe;

                if ($recipe && $recipe->items->isNotEmpty()) {
                    // This is a finished good - deduct from raw materials
                    foreach ($recipe->items as $recipeItem) {
                        $rawProduct = Product::find($recipeItem->product_id);
                        if ($rawProduct && $storeId) {
                            $stock = StockProduct::where('product_id', $rawProduct->id)
                                ->where('store_id', $storeId)
                                ->first();

                            if ($stock) {
                                $stock->quantity = max(0, $stock->quantity - ($recipeItem->quantity * $item['quantity']));
                                $stock->save();
                            }
                        }
                    }
                } else {
                    // Direct stock deduction for products without recipe
                    if ($storeId) {
                        $stock = StockProduct::where('product_id', $product->id)
                            ->where('store_id', $storeId)
                            ->first();

                        if ($stock) {
                            $stock->quantity = max(0, $stock->quantity - $item['quantity']);
                            $stock->save();
                        }
                    }
                }
            }
        }

        if ($voucher) {
            if (! $voucher->exists) {
                $voucher->generated_by_history_id = $history->id;
            }
            $voucher->is_used = true;
            $voucher->used_by_history_id = $history->id;
            $voucher->save();
        }

        $voucherMinGet = floatval(StoreSetting::getVal('voucher_min_get', $storeId, '50000'));
        $voucherDiscount = floatval(StoreSetting::getVal('voucher_discount', $storeId, '10000'));

        // Check for voucher-earning promotions first
        $voucherEarnedPromo = Promotion::where('type', 'voucher')
            ->whereNull('code')
            ->where('is_active', true)
            ->where(function ($q) use ($storeId) {
                $q->whereNull('store_id')->orWhere('store_id', $storeId);
            })
            ->whereNotNull('voucher_threshold')
            ->first();

        $shouldGenerateVoucher = false;
        if ($voucherEarnedPromo && $subtotal >= $voucherEarnedPromo->voucher_threshold) {
            $shouldGenerateVoucher = true;
            $voucherMinGet = 0; // Disable old voucher generation
            $voucherDiscount = $voucherEarnedPromo->discount_nominal ?? 0;
        }

        if ($subtotal >= $voucherMinGet) {
            Voucher::create([
                'code' => 'VCH-'.strtoupper(substr(md5(uniqid()), 0, 6)),
                'discount_amount' => $voucherDiscount,
                'is_used' => false,
                'generated_by_history_id' => $history->id,
            ]);
        }

        $request->session()->forget('cart');

        if ($request->session()->has('pending_order_id')) {
            $pendingOrderId = $request->session()->get('pending_order_id');
            PendingOrder::where('id', $pendingOrderId)->delete();
            $request->session()->forget('pending_order_id');
        }

        return redirect()->route('recipe', $history->id);
    }

    public function generateQris(Request $request)
    {
        $request->validate(['invoice_id' => 'required']);

        // If amount is provided directly (split QRIS), use it
        if ($request->filled('amount') && $request->amount > 0) {
            $total = (int) $request->amount;
        } else {
            $storeId = auth()->user()->store_id;
            $cart = $request->session()->get('cart', []);
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $tax = 0;
            $serviceRate = floatval(StoreSetting::getVal('service_charge', $storeId, '0'));
            $service = $subtotal * ($serviceRate / 100);
            $total = $subtotal + $service;

            if ($request->voucher_code) {
                $voucherCode = strtoupper($request->voucher_code);
                $voucherPromo = Promotion::where('type', 'voucher')
                    ->where('code', $voucherCode)
                    ->where('is_active', true)
                    ->where(function ($q) use ($storeId) {
                        $q->whereNull('store_id')->orWhere('store_id', $storeId);
                    })
                    ->first();

                if ($voucherPromo) {
                    $voucherMinRedeem = $voucherPromo->min_purchase_amount ?? 75000;
                    if ($subtotal >= $voucherMinRedeem) {
                        $discount = $voucherPromo->discount_nominal;
                        if (! $discount && $voucherPromo->discount_percentage) {
                            $discount = $subtotal * ($voucherPromo->discount_percentage / 100);
                            if ($voucherPromo->max_discount_amount && $discount > $voucherPromo->max_discount_amount) {
                                $discount = $voucherPromo->max_discount_amount;
                            }
                        }
                        $total -= $discount;
                        $total = max(0, $total);
                    }
                } else {
                    $voucher = Voucher::where('code', $voucherCode)->where('is_used', false)->first();
                    $voucherMinRedeem = 75000;
                    if ($voucher && $subtotal >= $voucherMinRedeem) {
                        $total -= $voucher->discount_amount;
                        $total = max(0, $total);
                    }
                }
            }
        }

        Config::$serverKey = Setting::getVal('midtrans_server_key', '');
        Config::$isProduction = Setting::getVal('midtrans_is_production', '0') === '1';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = $request->invoice_id;
        $midtransOrderId = $orderId.'-'.time();

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) round($total),
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        try {
            $response = CoreApi::charge($params);

            $qrUrl = null;
            if (isset($response->actions)) {
                foreach ($response->actions as $action) {
                    if ($action->name === 'generate-qr-code') {
                        $qrUrl = $action->url;
                        break;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'order_id' => $midtransOrderId,
                'qr_url' => $qrUrl,
                'raw' => $response,
            ]);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Payment channel is not activated')) {
                $msg = 'QRIS payment channel belum diaktifkan di akun Midtrans. Silakan aktifkan QRIS di dashboard Midtrans (Settings > Payment Channels).';
            }

            return response()->json(['status' => 'error', 'message' => $msg]);
        }
    }

    public function checkQrisStatus($order_id)
    {
        Config::$serverKey = Setting::getVal('midtrans_server_key', '');
        Config::$isProduction = Setting::getVal('midtrans_is_production', '0') === '1';

        try {
            $status = Transaction::status($order_id);

            return response()->json([
                'transaction_status' => $status->transaction_status,
            ]);
        } catch (\Exception $e) {
            // If Midtrans API fails or transaction is not found
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    protected function calculateItemDiscounts(array $cart, array $appliedPromos): array
    {
        $itemDiscounts = [];

        foreach ($appliedPromos as $promo) {
            $promoDiscountTotal = $promo['discount'] ?? 0;
            if ($promoDiscountTotal <= 0) {
                continue;
            }

            $promoModel = Promotion::find($promo['promotion_id'] ?? 0);
            $promoName = $promoModel ? $promoModel->name : ($promo['name'] ?? 'Diskon');

            switch ($promo['type']) {
                case 'buy_x_get_y':
                    $getProdId = $promoModel ? ($promoModel->get_product_id ?: $promoModel->buy_product_id) : null;
                    $targetId = null;
                    foreach ($cart as $ci) {
                        if ($ci['id'] == ($getProdId ?? 0)) {
                            $targetId = $ci['id'];
                            break;
                        }
                    }
                    if ($targetId) {
                        $itemDiscounts[$targetId] = $itemDiscounts[$targetId] ?? ['discount' => 0, 'description' => $promoName];
                        $itemDiscounts[$targetId]['discount'] += (int) $promoDiscountTotal;
                        break;
                    }
                    // fallback to proportional
                    $this->distributeProportional($itemDiscounts, $cart, $promoDiscountTotal, $promoName);
                    break;

                case 'product':
                    $prodId = $promoModel ? $promoModel->product_id : null;
                    if ($prodId && in_array($prodId, array_column($cart, 'id'))) {
                        $itemDiscounts[$prodId] = $itemDiscounts[$prodId] ?? ['discount' => 0, 'description' => $promoName];
                        $itemDiscounts[$prodId]['discount'] += (int) $promoDiscountTotal;
                        break;
                    }
                    $this->distributeProportional($itemDiscounts, $cart, $promoDiscountTotal, $promoName);
                    break;

                case 'category':
                    $catId = $promoModel ? $promoModel->category_id : null;
                    if ($catId) {
                        $catItems = [];
                        $catTotal = 0;
                        foreach ($cart as $ci) {
                            $p = Product::find($ci['id']);
                            if ($p && $p->category_id == $catId) {
                                $val = $ci['price'] * $ci['quantity'];
                                $catItems[$ci['id']] = $val;
                                $catTotal += $val;
                            }
                        }
                        if ($catTotal > 0) {
                            foreach ($catItems as $pid => $val) {
                                $disc = (int) round($promoDiscountTotal * ($val / $catTotal));
                                if ($disc > 0) {
                                    $itemDiscounts[$pid] = $itemDiscounts[$pid] ?? ['discount' => 0, 'description' => $promoName];
                                    $itemDiscounts[$pid]['discount'] += $disc;
                                }
                            }
                            break;
                        }
                    }
                    $this->distributeProportional($itemDiscounts, $cart, $promoDiscountTotal, $promoName);
                    break;

                default:
                    $this->distributeProportional($itemDiscounts, $cart, $promoDiscountTotal, $promoName);
                    break;
            }
        }

        return $itemDiscounts;
    }

    protected function distributeProportional(array &$itemDiscounts, array $cart, float $totalDiscount, string $promoName): void
    {
        $totalValue = 0;
        $values = [];
        foreach ($cart as $ci) {
            $val = $ci['price'] * $ci['quantity'];
            $values[$ci['id']] = $val;
            $totalValue += $val;
        }
        if ($totalValue <= 0) {
            return;
        }

        foreach ($values as $pid => $val) {
            $disc = (int) round($totalDiscount * ($val / $totalValue));
            if ($disc > 0) {
                $itemDiscounts[$pid] = $itemDiscounts[$pid] ?? ['discount' => 0, 'description' => $promoName];
                $itemDiscounts[$pid]['discount'] += $disc;
            }
        }
    }

    private function parsePaymentData(Request $request): array
    {
        if ($request->filled('payment_data')) {
            $raw = json_decode($request->payment_data, true);
            if (is_array($raw) && count($raw) > 0) {
                return collect($raw)->map(function ($p) {
                    return [
                        'method' => $p['method'] ?? 'cash',
                        'amount' => (int) ($p['amount'] ?? 0),
                        'card_number' => $p['card_number'] ?? null,
                        'cardholder_name' => $p['cardholder_name'] ?? null,
                        'approval_code' => $p['approval_code'] ?? null,
                        'bank_name' => $p['bank_name'] ?? null,
                    ];
                })->toArray();
            }
        }

        return [
            [
                'method' => $request->payment_method,
                'amount' => (int) $request->amount_received,
                'card_number' => $request->card_number,
                'cardholder_name' => $request->cardholder_name,
                'approval_code' => $request->approval_code,
                'bank_name' => $request->bank_name,
            ],
        ];
    }
}
