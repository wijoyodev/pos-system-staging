<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\StoreSetting;

class ReceiptController extends Controller
{
    public function index($history)
    {
        $history = History::with('items.product', 'usedVoucher', 'earnedVoucher', 'customer')->findOrFail($history);
        $storeId = $history->store_id;

        $subtotal = $history->items->reduce(function ($carry, $item) {
            return $carry + ($item->price * $item->quantity);
        }, 0);

        $totalQty = $history->items->sum('quantity');

        // Tax sudah termasuk dalam harga produk
        $tax = 0;
        $serviceRate = floatval(StoreSetting::getVal('service_charge', $storeId, '0'));
        $service = $subtotal * ($serviceRate / 100);

        $pointsPerRupiah = intval(StoreSetting::getVal('loyalty_points_per_rupiah', $storeId, '10000'));
        $pointsEarned = $history->customer ? floor($subtotal / $pointsPerRupiah) : 0;

        return view('pages/recipe', [
            'title' => 'Receipt',
            'history' => $history,
            'subtotal' => $subtotal,
            'total_qty' => $totalQty,
            'tax' => $tax,
            'service' => $service,
            'pointsEarned' => $pointsEarned,
        ]);
    }

    public function printFaktur($history)
    {
        $history = History::with('items.product', 'usedVoucher', 'earnedVoucher', 'customer', 'store')->findOrFail($history);

        return view('print.faktur-sales', [
            'history' => $history,
        ]);
    }

    public function print($history)
    {
        $history = History::with('items.product', 'usedVoucher', 'earnedVoucher', 'customer')->findOrFail($history);
        $storeId = $history->store_id;

        $subtotal = $history->items->reduce(function ($carry, $item) {
            return $carry + ($item->price * $item->quantity);
        }, 0);

        $totalQty = $history->items->sum('quantity');

        // Tax sudah termasuk dalam harga produk
        $tax = 0;
        $serviceRate = floatval(StoreSetting::getVal('service_charge', $storeId, '0'));
        $service = $subtotal * ($serviceRate / 100);

        return view('print.receipt', [
            'history' => $history,
            'subtotal' => $subtotal,
            'total_qty' => $totalQty,
            'tax' => $tax,
            'service' => $service,
        ]);
    }
}
