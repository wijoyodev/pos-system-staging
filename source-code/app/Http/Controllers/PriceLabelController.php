<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PriceLabelController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $storeId = $user->isSuperAdmin() ? $request->store_id : $user->store_id;

            $query = Product::query()->with('category');

            if ($storeId) {
                $query->whereHas('stocks', fn ($q) => $q->where('store_id', $storeId));
            }

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('sku', 'like', "%{$s}%")
                        ->orWhere('barcode', 'like', "%{$s}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('min_price')) {
                $query->where('selling_price', '>=', max(0, (int) $request->min_price));
            }

            if ($request->filled('max_price')) {
                $query->where('selling_price', '<=', max(0, (int) $request->max_price));
            }

            if ($request->filled('min_promo_price')) {
                $query->where('promo_price', '>=', max(0, (int) $request->min_promo_price));
            }

            if ($request->filled('max_promo_price')) {
                $query->where('promo_price', '<=', max(0, (int) $request->max_promo_price));
            }

            if ($request->boolean('promo_only')) {
                $today = now()->toDateString();
                $query->where('promo_price', '>', 0)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('promo_start')->orWhere('promo_start', '<=', $today);
                    })
                    ->where(function ($q) use ($today) {
                        $q->whereNull('promo_end')->orWhere('promo_end', '>=', $today);
                    });
            }

            $products = $query->orderBy('name')->paginate(20);

            if ($request->ajax()) {
                return response()->json([
                    'products' => $products->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'barcode' => $p->barcode,
                        'price' => $p->getCurrentPrice(),
                        'original_price' => $p->selling_price,
                        'is_promo' => $p->isPromoActive(),
                        'category' => $p->category?->name,
                        'stock' => $p->getStockForStore($storeId),
                    ]),
                    'next_page' => $products->nextPageUrl(),
                ]);
            }

            return view('pages.price-label', [
                'title' => 'Cetak Label Harga',
                'products' => $products,
                'categories' => Category::orderBy('name')->get(),
            ]);
        } catch (\Exception $e) {
            Log::error('PriceLabel index error: '.$e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => 'Gagal memuat data produk.'], 500);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan saat memuat data.']);
        }
    }

    public function print(Request $request)
    {
        try {
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'exists:products,id',
                'copies' => 'integer|min:1|max:99',
            ]);

            $products = Product::whereIn('id', $request->product_ids)
                ->orderBy('name')
                ->get();

            if ($products->isEmpty()) {
                return redirect()->back()->withErrors(['error' => 'Tidak ada produk yang ditemukan.']);
            }

            $user = auth()->user();
            $storeId = $user->isSuperAdmin()
                ? ($request->store_id ?? optional($products->first()->stocks()->first())->store_id)
                : $user->store_id;

            $storeName = StoreSetting::getVal('store_name', $storeId, 'Toko Saya');
            $copies = min(max((int) ($request->copies ?? 1), 1), 99);

            return view('print.price-label', [
                'products' => $products,
                'storeName' => $storeName,
                'copies' => $copies,
            ]);
        } catch (\Exception $e) {
            Log::error('PriceLabel print error: '.$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Gagal mencetak label. Silakan coba lagi.']);
        }
    }
}
