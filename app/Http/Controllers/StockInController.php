<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\StockProduct;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = StockIn::with(['supplier', 'store', 'user'])->latest();

        if (! $user->isSuperAdmin() && $user->store_id) {
            $query->where('store_id', $user->store_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $stockIns = $query->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('pages.stock-in', [
            'title' => 'Stok Masuk',
            'stockIns' => $stockIns,
            'suppliers' => $suppliers,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        if ($user->isSuperAdmin()) {
            $stores = Store::where('status', 'active')->orderBy('name')->get();
        } else {
            $stores = Store::where('id', $user->store_id)->get();
        }

        return view('pages.stock-in-create', [
            'title' => 'Tambah Stok Masuk',
            'suppliers' => $suppliers,
            'products' => $products,
            'stores' => $stores,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cost_price' => ['required', 'numeric', 'min:0'],
            'items.*.expired_date' => ['nullable', 'date'],
        ]);

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['cost_price'];
        }

        $stockIn = StockIn::create([
            'supplier_id' => $request->supplier_id,
            'store_id' => $request->store_id,
            'reference_no' => $request->reference_no,
            'total_amount' => $totalAmount,
            'date' => $request->date,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            StockInItem::create([
                'stock_in_id' => $stockIn->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'cost_price' => $item['cost_price'],
                'expired_date' => $item['expired_date'] ?? null,
            ]);
        }

        $stockIn->processStockIn();

        return redirect('/stock-in')->with('success', 'Stok masuk berhasil diproses.');
    }

    public function show(StockIn $stockIn)
    {
        $stockIn->load(['supplier', 'store', 'user', 'items.product']);

        return view('pages.stock-in-detail', [
            'title' => 'Detail Stok Masuk',
            'stockIn' => $stockIn,
        ]);
    }

    public function printFaktur(StockIn $stockIn)
    {
        $stockIn->load(['supplier', 'store', 'user', 'items.product']);

        return view('print.faktur-stock-in', [
            'stockIn' => $stockIn,
        ]);
    }

    public function destroy(StockIn $stockIn)
    {
        foreach ($stockIn->items as $item) {
            $stockProduct = StockProduct::where('product_id', $item->product_id)
                ->where('store_id', $stockIn->store_id)
                ->first();
            if ($stockProduct) {
                $stockProduct->decrement('quantity', $item->quantity);
            }
        }

        $stockIn->delete();

        return redirect('/stock-in')->with('success', 'Data stok masuk berhasil dihapus.');
    }

    public function expired(Request $request)
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        $query = StockProduct::with([
            'product' => function ($q) {
                $q->withTrashed();
            },
            'product.category',
            'product.stockInItems' => function ($q) {
                $q->whereNotNull('expired_date')->orderBy('expired_date');
            },
            'store',
        ]);

        if (! $user->isSuperAdmin() && $storeId) {
            $query->where('store_id', $storeId);
        }

        $stockProducts = $query->where('quantity', '>', 0)->orderBy('quantity', 'desc')->get();

        $filter = $request->input('filter', 'all');

        $products = $stockProducts->map(function ($sp) {
            $product = $sp->product;
            $product->store_stock = $sp->quantity;
            $product->store_id = $sp->store_id;

            return $product;
        })->filter(function ($product) use ($filter) {
            if ($filter === 'expired') {
                return $product->isExpired();
            }
            if ($filter === 'near') {
                return $product->isNearExpiry();
            }
            if ($filter === 'no_data') {
                return is_null($product->getEarliestExpiryDate());
            }

            return true;
        })->sortBy(function ($product) {
            $earliest = $product->getEarliestExpiryDate();
            if (! $earliest) {
                return 4;
            }
            if ($product->isExpired()) {
                return 1;
            }
            if ($product->isNearExpiry()) {
                return 2;
            }

            return 3;
        })->values();

        return view('pages.stock-in-expired', [
            'title' => 'Produk Expired',
            'products' => $products,
            'currentFilter' => $filter,
        ]);
    }

    public function updateExpired(Request $request)
    {
        $request->validate([
            'stock_in_item_id' => 'nullable|exists:stock_in_items,id',
            'product_id' => 'nullable|exists:products,id',
            'expired_date' => 'nullable|date',
        ]);

        if ($request->filled('stock_in_item_id')) {
            $item = StockInItem::withTrashed()->findOrFail($request->stock_in_item_id);
            $item->update(['expired_date' => $request->expired_date]);
        } elseif ($request->filled('product_id')) {
            $product = Product::withTrashed()->findOrFail($request->product_id);
            $product->update(['expired_date' => $request->expired_date]);
        }

        return back()->with('success', 'Tanggal expired berhasil diperbarui.');
    }
}
