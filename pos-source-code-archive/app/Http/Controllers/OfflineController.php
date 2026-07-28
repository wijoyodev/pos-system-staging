<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\History;
use App\Models\HistoryItem;
use App\Models\Product;
use App\Models\StockProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfflineController extends Controller
{
    public function getProducts(Request $request)
    {
        $storeId = $request->header('X-Store-ID');

        $products = Product::with(['category', 'stocks' => function ($q) use ($storeId) {
            if ($storeId) {
                $q->where('store_id', $storeId);
            }
        }])->get()->map(function ($p) {
            $stock = $p->stocks->first();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'selling_price' => $p->selling_price,
                'category_id' => $p->category_id,
                'category_name' => $p->category?->name,
                'image' => $p->image,
                'stock' => $stock ? $stock->quantity : 0,
            ];
        });

        return response()->json($products);
    }

    public function getStocks(Request $request)
    {
        $storeId = $request->header('X-Store-ID');

        $stocks = StockProduct::when($storeId, function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        })->get();

        return response()->json($stocks);
    }

    public function getCategories()
    {
        $categories = Category::all(['id', 'name']);

        return response()->json($categories);
    }

    public function saveTransaction(Request $request)
    {
        $data = $request->all();

        // Handle offline transaction
        $cart = $data['cart'] ?? [];
        $storeId = $data['store_id'] ?? auth()->user()->store_id;

        DB::beginTransaction();
        try {
            // Create history record
            $history = History::create([
                'invoice_id' => $data['invoice_id'] ?? 'OFFLINE-'.time(),
                'user_id' => $data['user_id'] ?? auth()->id(),
                'cashier_name' => $data['cashier_name'] ?? auth()->user()->name,
                'customer_id' => $data['customer_id'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'offline',
                'total_amount' => $data['total_amount'] ?? 0,
                'amount_received' => $data['amount_received'] ?? 0,
                'change_amount' => $data['change_amount'] ?? 0,
                'offline_id' => $data['offline_id'] ?? null,
                'synced_at' => now(),
            ]);

            // Create history items
            foreach ($cart as $item) {
                HistoryItem::create([
                    'history_id' => $history->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Deduct stock
                $product = Product::find($item['id']);
                if ($product && $storeId) {
                    $stock = StockProduct::where('product_id', $product->id)
                        ->where('store_id', $storeId)
                        ->first();

                    if ($stock) {
                        $stock->quantity = max(0, $stock->quantity - $item['quantity']);
                        $stock->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $history->id,
                'invoice_id' => $history->invoice_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function stockDeduction(Request $request)
    {
        $data = $request->all();

        try {
            $stock = StockProduct::where('product_id', $data['product_id'])
                ->where('store_id', $data['store_id'])
                ->first();

            if ($stock) {
                $stock->quantity = max(0, $stock->quantity - $data['quantity']);
                $stock->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPendingTransactions()
    {
        // For admin to see pending offline transactions
        $pending = History::whereNotNull('offline_id')
            ->whereNull('synced_at')
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pending);
    }
}
