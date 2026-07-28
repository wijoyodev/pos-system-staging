<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PurchaseOrder::with(['supplier', 'store', 'orderedBy'])->latest('order_date');

        if (! $user->isSuperAdmin() && $user->store_id) {
            $query->where('store_id', $user->store_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }

        $purchaseOrders = $query->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('pages.purchase-orders', [
            'title' => 'Purchase Order',
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'store', 'orderedBy', 'eodReport', 'items.product']);

        return view('pages.po-detail', [
            'title' => 'Detail Purchase Order',
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function printFaktur(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'store', 'orderedBy', 'items.product']);

        return view('print.faktur-po', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        if (! $purchaseOrder->isReceivable()) {
            return redirect('/purchase-orders/'.$purchaseOrder->id)
                ->with('error', 'PO tidak dapat diterima. Status: '.$purchaseOrder->status);
        }

        $success = $purchaseOrder->processReceive();

        if ($success) {
            return redirect('/purchase-orders/'.$purchaseOrder->id)
                ->with('success', 'Barang berhasil diterima dan stok telah diperbarui.');
        }

        return redirect('/purchase-orders/'.$purchaseOrder->id)
            ->with('error', 'Gagal menerima barang.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (! $purchaseOrder->isCancelable()) {
            return redirect('/purchase-orders/'.$purchaseOrder->id)
                ->with('error', 'PO tidak dapat dibatalkan. Status: '.$purchaseOrder->status);
        }

        $purchaseOrder->cancel();

        return redirect('/purchase-orders/'.$purchaseOrder->id)
            ->with('success', 'PO berhasil dibatalkan.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect('/purchase-orders')
                ->with('error', 'Hanya PO dengan status draft yang dapat dihapus.');
        }

        $purchaseOrder->delete();

        return redirect('/purchase-orders')
            ->with('success', 'PO berhasil dihapus.');
    }
}
