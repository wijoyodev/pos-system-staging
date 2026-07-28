<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockProduct;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Store;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = StockTransfer::with(['sourceStore', 'destinationStore', 'createdBy', 'approvedBy'])->latest();

        if (! $user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('source_store_id', $user->store_id)
                    ->orWhere('destination_store_id', $user->store_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('transfer_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->get();
        $stores = Store::where('status', 'active')->orderBy('name')->get();

        return view('pages.stock-transfer', [
            'title' => 'Mutasi Barang',
            'transfers' => $transfers,
            'stores' => $stores,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin()) {
            return redirect('/stock-transfer')->with('error', 'Hanya Super Admin yang dapat membuat mutasi barang.');
        }

        // Group products by name to prevent duplicate display
        // Only show one product per name (the one with lowest ID)
        $productIds = Product::selectRaw('MIN(id) as id')
            ->groupBy('name')
            ->pluck('id');

        $products = Product::whereIn('id', $productIds)
            ->orderBy('name')
            ->get();

        $stores = Store::where('status', 'active')->orderBy('name')->get();

        return view('pages.stock-transfer-create', [
            'title' => 'Buat Mutasi Barang',
            'products' => $products,
            'stores' => $stores,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin()) {
            return redirect('/stock-transfer')->with('error', 'Hanya Super Admin yang dapat membuat mutasi barang.');
        }
        $request->validate([
            'source_store_id' => ['required', 'exists:stores,id'],
            'destination_store_id' => ['required', 'exists:stores,id', 'different:source_store_id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $transfer = new StockTransfer([
            'source_store_id' => $request->source_store_id,
            'destination_store_id' => $request->destination_store_id,
            'transfer_date' => $request->transfer_date,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        $transfer->generateNumber();
        $transfer->save();

        foreach ($request->items as $item) {
            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect('/stock-transfer')->with('success', 'Mutasi barang berhasil dibuat.');
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['sourceStore', 'destinationStore', 'createdBy', 'approvedBy', 'items.product']);

        $sourceStocks = [];
        foreach ($stockTransfer->items as $item) {
            $stock = StockProduct::where('product_id', $item->product_id)
                ->where('store_id', $stockTransfer->source_store_id)
                ->first();
            $sourceStocks[$item->product_id] = $stock ? $stock->quantity : 0;
        }

        return view('pages.stock-transfer-detail', [
            'title' => 'Detail Mutasi Barang',
            'transfer' => $stockTransfer,
            'sourceStocks' => $sourceStocks,
        ]);
    }

    public function send(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'draft') {
            return back()->with('error', 'Mutasi barang sudah dikirim.');
        }

        foreach ($stockTransfer->items as $item) {
            $stock = StockProduct::where('product_id', $item->product_id)
                ->where('store_id', $stockTransfer->source_store_id)
                ->first();

            if (! $stock || $stock->quantity < $item->quantity) {
                $productName = $item->product ? $item->product->name : 'Produk';

                return back()->with('error', "Stok {$productName} di toko asal tidak mencukupi.");
            }
        }

        $stockTransfer->send();

        return back()->with('success', 'Mutasi barang berhasil dikirim.');
    }

    public function receive(StockTransfer $stockTransfer)
    {
        if (! $stockTransfer->canBeReceived()) {
            return back()->with('error', 'Mutasi barang tidak dapat diterima.');
        }

        $stockTransfer->receive();

        return back()->with('success', 'Mutasi barang berhasil diterima.');
    }

    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (! $stockTransfer->isPending()) {
            return back()->with('error', 'Mutasi barang tidak dapat ditolak.');
        }

        $stockTransfer->reject($request->reason);

        return back()->with('success', 'Mutasi barang berhasil ditolak.');
    }

    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'draft') {
            return back()->with('error', 'Hanya mutasi dengan status draft yang dapat dihapus.');
        }

        $stockTransfer->delete();

        return back()->with('success', 'Mutasi barang berhasil dihapus.');
    }
}
