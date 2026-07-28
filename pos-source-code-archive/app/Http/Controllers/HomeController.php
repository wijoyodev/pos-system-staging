<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Closing;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $storeId = $user->store_id;
        $today = now()->format('Y-m-d');

        $hasClosed = Closing::where('user_id', $user->id)
            ->where('closing_date', $today)
            ->where('status', '!=', 'rejected')
            ->exists();

        if ($hasClosed) {
            $redirectPath = $user->hasMinRole('admin') ? '/clerek/data' : '/dashboard';

            return redirect($redirectPath)->with('info', 'Anda sudah melakukan clerek hari ini. Akses terminal POS ditutup.');
        }

        $pendingCart = $request->session()->get('pending_cart');
        if ($pendingCart) {
            $request->session()->forget('pending_cart');
        }

        $products = Product::with(['category', 'stocks', 'primarySupplier'])->get()->unique('id')->map(function ($p) use ($storeId) {
            $p->current_stock = $storeId ? $p->getStockForStore($storeId) : $p->getStockTotal();

            return $p;
        });

        $categories = Category::all();
        $promotions = Promotion::where('is_active', true)
            ->where(function ($q) use ($storeId) {
                $q->whereNull('store_id')
                    ->orWhere('store_id', $storeId);
            })
            ->get();

        return view('pages/home', [
            'title' => 'Point Of Sale',
            'products' => $products,
            'categories' => $categories,
            'vat' => StoreSetting::getVal('vat', $storeId, '11'),
            'service_charge' => StoreSetting::getVal('service_charge', $storeId, '0'),
            'currency' => Setting::getVal('currency_code', 'id-ID'),
            'storeId' => $storeId,
            'pendingCart' => $pendingCart,
            'promotions' => $promotions,
        ]);
    }

    public function searchProduct(Request $request)
    {
        $user = auth()->user();
        $storeId = $user->store_id;
        $query = $request->get('q', '');

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $products = Product::with(['category', 'stocks'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('sku', 'like', '%'.$query.'%')
                    ->orWhere('barcode', 'like', '%'.$query.'%');
            })
            ->limit(20)
            ->get()
            ->map(function ($p) use ($storeId) {
                $p->current_stock = $storeId ? $p->getStockForStore($storeId) : $p->getStockTotal();

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'selling_price' => $p->selling_price,
                    'promo_price' => $p->promo_price,
                    'promo_start' => $p->promo_start,
                    'promo_end' => $p->promo_end,
                    'image' => $p->image,
                    'stock' => $p->current_stock,
                ];
            });

        return response()->json($products);
    }
}
