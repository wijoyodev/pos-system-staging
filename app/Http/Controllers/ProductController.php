<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HistoryItem;
use App\Models\Product;
use App\Models\StockInItem;
use App\Models\StockProduct;
use App\Models\StockTransferItem;
use App\Models\Store;
use App\Models\StoreSetting;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        $query = Product::with(['category', 'recipe.items.product', 'stocks', 'stockInItems'])->latest();

        if (request()->has('search') && request('search') != '') {
            $query->where('name', 'like', '%'.request()->search.'%')
                ->orWhere('sku', 'like', '%'.request()->search.'%')
                ->orWhere('barcode', 'like', '%'.request()->search.'%');
        }
        if (request()->has('category_id') && request('category_id') != '') {
            $query->where('category_id', request('category_id'));
        }

        // Stats from full query (before pagination)
        $allProducts = (clone $query)->get();
        $totalProducts = $allProducts->count();

        if ($storeId) {
            $totalValue = $allProducts->sum(function ($p) use ($storeId) {
                return $p->selling_price * $p->getStockForStore($storeId);
            });
            $totalUnits = $allProducts->sum(function ($p) use ($storeId) {
                return $p->getStockForStore($storeId);
            });
        } else {
            $totalValue = $allProducts->sum(fn ($p) => $p->selling_price * $p->getStockTotal());
            $totalUnits = $allProducts->sum(fn ($p) => $p->getStockTotal());
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::all();
        $low_stock_count = $storeId
            ? Product::whereHas('stocks', function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->whereColumn('quantity', '<=', 'threshold');
            })->count()
            : $allProducts->filter(fn ($p) => $p->getStockTotal() <= $p->threshold)->count();

        return view('pages/product', [
            'title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'suppliers' => Supplier::all(),
            'units' => Unit::all(),
            'low_stock_count' => $low_stock_count,
            'totalProducts' => $totalProducts,
            'totalValue' => $totalValue,
            'totalUnits' => $totalUnits,
            'storeId' => $storeId,
        ]);
    }

    public function store(Request $request)
    {
        foreach (['primary_supplier_id', 'expired_date', 'promo_start', 'promo_end'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'profit_percentage' => 'nullable|numeric|min:0|max:100',
            'include_tax' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer',
            'threshold' => 'required|integer',
            'image' => 'nullable|image|max:2048',
            'promo_price' => 'nullable|numeric|min:0',
            'promo_start' => 'nullable|date',
            'promo_end' => 'nullable|date|after_or_equal:promo_start',
            'barcode' => 'nullable|string|max:255',
            'primary_supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'nullable|string|max:50',
            'expired_date' => 'nullable|date',
        ]);

        $storeId = auth()->user()->store_id;
        $vatRate = floatval(StoreSetting::getVal('vat', $storeId, '11'));

        // Calculate selling_price: HPP + (HPP * profit%) + VAT
        $costPrice = $data['cost_price'];
        $profitPercentage = $data['profit_percentage'] ?? 0;
        $includeTax = $request->boolean('include_tax');

        if (! empty($data['selling_price'])) {
            $calculatedSellingPrice = $data['selling_price'];
        } else {
            $profitAmount = $costPrice * ($profitPercentage / 100);
            $subtotal = $costPrice + $profitAmount;

            // Add VAT if include_tax is checked
            $taxAmount = $includeTax ? ($subtotal * $vatRate / 100) : 0;
            $calculatedSellingPrice = $subtotal + $taxAmount;
        }

        // Round to nearest 100
        $data['selling_price'] = (int) ceil($calculatedSellingPrice / 100) * 100;
        $data['profit_percentage'] = (int) $profitPercentage;
        $data['tax_amount'] = $includeTax ? 1 : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['sku'] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Clear promo fields if empty or zero
        $promoPrice = $request->input('promo_price');
        if (empty($promoPrice) || $promoPrice == 0) {
            $data['promo_price'] = null;
            $data['promo_start'] = null;
            $data['promo_end'] = null;
        }

        $storeId = auth()->user()->store_id;
        $stockQty = $data['stock'];
        unset($data['stock']);

        // For super admin, require store selection
        if (! $storeId && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        $storeName = $storeId ? Store::find($storeId)?->name : null;
        $storeSuffix = $storeName ? ' di '.$storeName : '';

        // Check if product with same name already exists (including soft-deleted)
        $existingProduct = Product::withTrashed()->where('name', $data['name'])->first();

        if ($existingProduct) {
            // Restore if soft-deleted
            if ($existingProduct->trashed()) {
                $existingProduct->restore();
            }

            // Check if stock for this store already exists
            $existingStock = StockProduct::where('product_id', $existingProduct->id)
                ->where('store_id', $storeId)
                ->first();

            if ($existingStock) {
                return back()->with('error', 'Produk '.$data['name'].' sudah ada'.$storeSuffix.' dengan stok '.$existingStock->quantity);
            }

            // Add stock for this store
            StockProduct::create([
                'product_id' => $existingProduct->id,
                'store_id' => $storeId,
                'quantity' => $stockQty,
            ]);

            return back()->with('success', 'Stok produk '.$data['name'].' ditambahkan'.$storeSuffix.' ('.$stockQty.')');
        }

        // Validate unique product name to prevent duplicates (exclude soft-deleted)
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name,NULL,id,deleted_at,NULL',
        ]);

        $product = Product::create($data);

        // Create stock entries
        if ($storeId) {
            StockProduct::create([
                'product_id' => $product->id,
                'store_id' => $storeId,
                'quantity' => $stockQty,
            ]);
        }

        return back()->with('success', 'Produk '.$data['name'].' berhasil ditambahkan'.$storeSuffix.'.');
    }

    public function update(Request $request, Product $product)
    {
        // If product_id is in request, use it instead of route binding
        if ($request->has('product_id')) {
            $product = Product::findOrFail($request->product_id);
        }

        foreach (['primary_supplier_id', 'expired_date', 'promo_start', 'promo_end'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'profit_percentage' => 'nullable|numeric|min:0|max:100',
            'include_tax' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer',
            'threshold' => 'required|integer',
            'image' => 'nullable|image|max:2048',
            'promo_price' => 'nullable|numeric|min:0',
            'promo_start' => 'nullable|date',
            'promo_end' => 'nullable|date',
            'barcode' => 'nullable|string|max:255',
            'primary_supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'nullable|string|max:50',
            'expired_date' => 'nullable|date',
        ]);

        $storeId = auth()->user()->store_id;
        $vatRate = floatval(StoreSetting::getVal('vat', $storeId, '11'));

        $costPrice = $data['cost_price'];
        $profitPercentage = $data['profit_percentage'] ?? 0;
        $includeTax = $request->boolean('include_tax');

        if (! empty($data['selling_price'])) {
            $calculatedSellingPrice = $data['selling_price'];
        } else {
            $profitAmount = $costPrice * ($profitPercentage / 100);
            $subtotal = $costPrice + $profitAmount;

            $taxAmount = $includeTax ? ($subtotal * $vatRate / 100) : 0;
            $calculatedSellingPrice = $subtotal + $taxAmount;
        }

        // Round to nearest 100
        $data['selling_price'] = (int) ceil($calculatedSellingPrice / 100) * 100;
        $data['profit_percentage'] = (int) $profitPercentage;
        $data['tax_amount'] = $includeTax ? 1 : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle promo fields
        $promoPrice = $request->input('promo_price');
        if (empty($promoPrice) || $promoPrice == 0) {
            $product->promo_price = null;
            $product->promo_start = null;
            $product->promo_end = null;
        } else {
            $product->promo_price = $promoPrice;
            $product->promo_start = $request->input('promo_start');
            $product->promo_end = $request->input('promo_end');
        }

        // Remove stock from data before filling product (stock is in stock_products table)
        $stockQty = $data['stock'];
        unset($data['stock']);

        $product->fill($data)->save();

        // Update stock for this store
        $storeId = auth()->user()->store_id;
        if (! $storeId && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        if ($storeId) {
            StockProduct::updateOrCreate(
                ['product_id' => $product->id, 'store_id' => $storeId],
                ['quantity' => $stockQty]
            );
        }

        $storeName = $storeId ? Store::find($storeId)?->name : null;
        $storeSuffix = $storeName ? ' di '.$storeName : '';

        return back()->with('success', 'Produk '.$data['name'].' berhasil diperbarui'.$storeSuffix.'.');
    }

    public function destroy(Product $product)
    {
        $productName = $product->name;
        $product->delete();

        return back()->with('success', 'Produk "'.$productName.'" berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:products,id']);
        $count = Product::whereIn('id', $request->ids)->delete();

        return back()->with('success', $count.' produk berhasil dihapus.');
    }

    public function promoImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xls,xlsx|max:5120']);

        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        $rows = [];

        if (in_array($extension, ['xls', 'xlsx'])) {
            try {
                $spreadsheet = IOFactory::load($request->file('file')->path());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            } catch (\Exception $e) {
                $results['errors'][] = 'Gagal membaca file: '.$e->getMessage();

                return back()->with('promo_import_results', $results);
            }
        } else {
            $handle = fopen($request->file('file')->path(), 'r');
            if (! $handle) {
                return back()->with('promo_import_results', $results);
            }

            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        if (empty($rows)) {
            $results['errors'][] = 'File kosong';

            return back()->with('promo_import_results', $results);
        }

        $header = array_shift($rows);

        $nameIndex = -1;
        $promoPriceIndex = -1;
        $startIndex = -1;
        $endIndex = -1;

        foreach ($header as $i => $col) {
            $col = strtolower(trim($col));
            if ($nameIndex === -1 && in_array($col, ['product', 'nama', 'name'])) {
                $nameIndex = $i;
            }
            if ($promoPriceIndex === -1 && in_array($col, ['promo_price', 'promo', 'harga'])) {
                $promoPriceIndex = $i;
            }
            if ($startIndex === -1 && in_array($col, ['start', 'mulai'])) {
                $startIndex = $i;
            }
            if ($endIndex === -1 && in_array($col, ['end', 'berakhir'])) {
                $endIndex = $i;
            }
        }

        if ($nameIndex === -1 || $promoPriceIndex === -1) {
            $results['errors'][] = 'Format tidak valid';

            return back()->with('promo_import_results', $results);
        }

        foreach ($rows as $row) {
            $name = isset($row[$nameIndex]) ? trim($row[$nameIndex]) : '';
            $promoPrice = isset($row[$promoPriceIndex]) ? (int) trim($row[$promoPriceIndex]) : 0;
            $start = isset($row[$startIndex]) ? trim($row[$startIndex]) : null;
            $end = isset($row[$endIndex]) ? trim($row[$endIndex]) : null;

            if (empty($name) || $promoPrice <= 0) {
                continue;
            }

            $product = Product::where('name', $name)->first();
            if (! $product) {
                $product = Product::where('name', 'like', '%'.$name.'%')->first();
            }

            if ($product) {
                $product->promo_price = $promoPrice;
                $product->promo_start = $start;
                $product->promo_end = $end;
                $product->save();
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Tidak ketemu: $name";
            }
        }

        return back()->with('promo_import_results', $results);
    }

    public function detail(Product $product)
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        $product->load(['category', 'primarySupplier']);

        if ($storeId) {
            $product->load(['stocks' => fn ($q) => $q->where('store_id', $storeId)->with('store')]);
        } else {
            $product->load(['stocks.store']);
        }

        $stockInQuery = StockInItem::where('product_id', $product->id)
            ->with(['stockIn' => fn ($q) => $q->with(['supplier', 'store', 'user'])]);
        if ($storeId) {
            $stockInQuery->whereHas('stockIn', fn ($q) => $q->where('store_id', $storeId));
        }
        $stockInHistory = $stockInQuery->latest()->limit(20)->get()->map(function ($item) {
            return [
                'type' => 'stock_in',
                'date' => $item->stockIn->date->format('d/m/Y H:i'),
                'store' => $item->stockIn->store->name,
                'supplier' => $item->stockIn->supplier->name ?? '-',
                'quantity' => $item->quantity,
                'reference' => $item->stockIn->reference_no ?? '#'.$item->stockIn->id,
                'user' => $item->stockIn->user->name ?? '-',
            ];
        });

        $salesQuery = HistoryItem::where('product_id', $product->id)
            ->with(['history' => fn ($q) => $q->with(['store', 'user', 'customer'])]);
        if ($storeId) {
            $salesQuery->whereHas('history', fn ($q) => $q->where('store_id', $storeId));
        }
        $salesHistory = $salesQuery->latest()->limit(20)->get()->map(function ($item) {
            return [
                'type' => 'sale',
                'date' => $item->history->created_at->format('d/m/Y H:i'),
                'store' => $item->history->store->name ?? '-',
                'invoice' => $item->history->invoice_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->price * $item->quantity,
                'cashier' => $item->history->cashier_name,
                'customer' => $item->history->customer?->name ?? 'Umum',
            ];
        });

        $transferQuery = StockTransferItem::where('product_id', $product->id)
            ->whereHas('stockTransfer', fn ($q) => $q->where('status', 'received'))
            ->with(['stockTransfer' => fn ($q) => $q->with(['sourceStore', 'destinationStore', 'createdBy'])]);
        if ($storeId) {
            $transferQuery->whereHas('stockTransfer', fn ($q) => $q->where(function ($sub) use ($storeId) {
                $sub->where('source_store_id', $storeId)->orWhere('destination_store_id', $storeId);
            }));
        }
        $currentStoreId = $storeId;
        $transferHistory = $transferQuery->latest()->limit(20)->get()->map(function ($item) use ($currentStoreId) {
            if ($currentStoreId) {
                $type = $item->stockTransfer->source_store_id == $currentStoreId ? 'transfer_out' : 'transfer_in';
            } else {
                $type = 'transfer_out';
            }

            return [
                'type' => $type,
                'date' => $item->stockTransfer->received_at->format('d/m/Y H:i'),
                'from' => $item->stockTransfer->sourceStore->name,
                'to' => $item->stockTransfer->destinationStore->name,
                'quantity' => $item->quantity,
                'reference' => $item->stockTransfer->transfer_number,
            ];
        });

        $allHistory = $stockInHistory->concat($salesHistory)->concat($transferHistory)
            ->sortByDesc('date')
            ->values();

        $totalStockIn = $stockInHistory->sum('quantity');
        $totalSales = $salesHistory->sum('quantity');
        $totalTransferOut = $transferHistory->where('type', 'transfer_out')->sum('quantity');
        $totalTransferIn = $transferHistory->where('type', 'transfer_in')->sum('quantity');

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category' => $product->category?->name ?? '-',
                'supplier' => $product->primarySupplier?->name ?? 'Belum diset',
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'promo_price' => $product->promo_price,
                'is_promo_active' => $product->isPromoActive(),
                'threshold' => $product->threshold,
                'image' => $product->image,
                'profit_percentage' => $product->profit_percentage,
                'tax_amount' => $product->tax_amount,
                'include_tax' => $product->include_tax,
                'unit' => $product->unit,
                'expired_date' => $product->expired_date,
                'earliest_expiry' => $product->getEarliestExpiryDate(),
            ],
            'stocks' => $product->stocks->map(function ($stock) {
                return [
                    'store' => $stock->store->name,
                    'code' => $stock->store->code,
                    'quantity' => $stock->quantity,
                    'is_low' => $stock->quantity <= ($stock->product?->threshold ?? 5),
                ];
            }),
            'summary' => [
                'total_stock_in' => $totalStockIn,
                'total_sales' => $totalSales,
                'total_transfer_out' => $totalTransferOut,
                'total_transfer_in' => $totalTransferIn,
            ],
            'history' => $allHistory,
        ]);
    }
}
