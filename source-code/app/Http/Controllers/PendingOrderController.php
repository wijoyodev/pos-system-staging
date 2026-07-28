<?php

namespace App\Http\Controllers;

use App\Models\PendingOrder;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class PendingOrderController extends Controller
{
    public function index(Request $request)
    {
        $storeId = auth()->user()->store_id;

        $pendingOrders = PendingOrder::with('user')
            ->where('store_id', $storeId)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        $expiredOrders = PendingOrder::where('store_id', $storeId)
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        return view('pages/pending-orders', [
            'title' => 'Pending Orders',
            'pendingOrders' => $pendingOrders,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required',
        ]);

        $cart = json_decode($request->cart, true);
        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $storeId = auth()->user()->store_id;
        $expiryMinutes = intval(StoreSetting::getVal('pending_order_expiry', $storeId, '10'));

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Tax sudah termasuk dalam harga produk
        $tax = 0;
        $serviceRate = floatval(StoreSetting::getVal('service_charge', $storeId, '0'));

        $service = (int) round($subtotal * ($serviceRate / 100));
        $total = $subtotal + $service;

        $orderNumber = 'PND-'.date('Ymd').'-'.strtoupper(substr(md5(uniqid()), 0, 6));

        $pendingOrder = PendingOrder::create([
            'store_id' => $storeId,
            'user_id' => auth()->id(),
            'order_number' => $orderNumber,
            'cart_items' => $cart,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service' => $service,
            'total' => $total,
            'status' => 'pending',
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $pendingOrder->id,
                'order_number' => $pendingOrder->order_number,
                'remaining_minutes' => $pendingOrder->remaining_minutes,
            ],
            'message' => "Order {$orderNumber} saved for {$expiryMinutes} minutes",
        ]);
    }

    public function show(PendingOrder $pendingOrder)
    {
        // If store_id doesn't match, return unauthorized
        if ($pendingOrder->store_id != auth()->user()->store_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($pendingOrder->isExpired()) {
            $pendingOrder->update(['status' => 'expired']);

            return response()->json(['error' => 'Order has expired'], 410);
        }

        if (! $pendingOrder->isPending()) {
            return response()->json(['error' => 'Order is not available', 'status' => $pendingOrder->status], 400);
        }

        return response()->json([
            'order' => [
                'id' => $pendingOrder->id,
                'order_number' => $pendingOrder->order_number,
            ],
            'cart' => $pendingOrder->cart_items,
            'subtotal' => $pendingOrder->subtotal,
            'tax' => $pendingOrder->tax,
            'service' => $pendingOrder->service,
            'total' => $pendingOrder->total,
            'remaining_minutes' => $pendingOrder->remaining_minutes,
        ]);
    }

    public function load(Request $request, PendingOrder $pendingOrder)
    {
        if ($pendingOrder->store_id != auth()->user()->store_id) {
            return back()->with('error', 'Unauthorized');
        }

        if ($pendingOrder->isExpired()) {
            $pendingOrder->update(['status' => 'expired']);

            return back()->with('error', 'Order has expired');
        }

        if (! $pendingOrder->isPending()) {
            return back()->with('error', 'Order is not available');
        }

        $request->session()->put('pending_cart', $pendingOrder->cart_items);
        $request->session()->put('pending_order_id', $pendingOrder->id);

        return redirect('/');
    }

    public function update(Request $request, PendingOrder $pendingOrder)
    {
        if ($pendingOrder->store_id != auth()->user()->store_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($pendingOrder->isExpired()) {
            $pendingOrder->update(['status' => 'expired']);

            return response()->json(['error' => 'Order has expired'], 410);
        }

        if (! $pendingOrder->isPending()) {
            return response()->json(['error' => 'Order is not available'], 400);
        }

        $cart = json_decode($request->cart, true);
        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $storeId = auth()->user()->store_id;
        $expiryMinutes = intval(StoreSetting::getVal('pending_order_expiry', $storeId, '10'));

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Tax sudah termasuk dalam harga produk
        $tax = 0;
        $serviceRate = floatval(StoreSetting::getVal('service_charge', $storeId, '0'));

        $service = (int) round($subtotal * ($serviceRate / 100));
        $total = $subtotal + $service;

        $pendingOrder->update([
            'cart_items' => $cart,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service' => $service,
            'total' => $total,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        return response()->json([
            'success' => true,
            'order' => $pendingOrder,
            'message' => 'Cart updated',
        ]);
    }

    public function destroy(PendingOrder $pendingOrder)
    {
        // If store_id doesn't match, return unauthorized
        if ($pendingOrder->store_id != auth()->user()->store_id) {
            return back()->with('error', 'Unauthorized');
        }

        $pendingOrder->delete();

        return redirect()->route('pending-orders.index')->with('success', 'Pending order deleted');
    }

    public function cancel(PendingOrder $pendingOrder)
    {
        if ($pendingOrder->store_id !== auth()->user()->store_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $pendingOrder->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Pending order cancelled',
        ]);
    }
}
