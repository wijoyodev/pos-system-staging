<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $history->invoice_id }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.3;
            width: 80mm;
            margin: 0 auto;
            padding: 8px 6px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .divider-thick { border-top: 2px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; align-items: baseline; }
        .mt-1 { margin-top: 3px; }
        .mt-2 { margin-top: 6px; }
        .mb-1 { margin-bottom: 3px; }
        .item-row { margin-bottom: 4px; }
        .item-name { font-weight: bold; }
        .item-detail { display: flex; justify-content: space-between; padding-left: 8px; }
        .discount-row { color: #333; }
        .total-row { font-size: 13px; font-weight: bold; margin-top: 4px; }
        .label { flex: 1; }
        .value { text-align: right; white-space: nowrap; }

        @media print {
            body { width: 100%; padding: 4px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #f4f4f4; padding: 12px; margin-bottom: 16px; text-align: center; border-radius: 4px;">
        <button onclick="window.print()" style="padding: 10px 24px; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 6px; font-weight: bold; font-size: 13px;">PRINT STRUK</button>
        <button onclick="window.close()" style="padding: 10px 24px; cursor: pointer; background: #ccc; color: #000; border: none; border-radius: 6px; margin-left: 8px; font-weight: bold; font-size: 13px;">CLOSE</button>
    </div>

    @php
        $storeId = $history->store_id;
        $storeName = \App\Models\StoreSetting::getVal('store_name', $storeId, 'Arka POS');
        $storeAddress = \App\Models\StoreSetting::getVal('store_address', $storeId, '');
        $storePhone = \App\Models\StoreSetting::getVal('store_phone', $storeId, '');
        $vatRate = \App\Models\StoreSetting::getVal('vat', $storeId, '11');
        $serviceRate = \App\Models\StoreSetting::getVal('service_charge', $storeId, '0');
        $pointValue = \App\Models\StoreSetting::getVal('loyalty_point_value', $storeId, '1000');
    @endphp

    <!-- Store Header -->
    <div class="text-center" style="margin-bottom: 6px;">
        <div style="font-size: 14px; font-weight: bold; letter-spacing: 1px;">{{ $storeName }}</div>
        @if($storeAddress)
        <div style="font-size: 9px;">{{ $storeAddress }}</div>
        @endif
        @if($storePhone)
        <div style="font-size: 9px;">Telp: {{ $storePhone }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Transaction Info -->
    <div class="row">
        <span>No. Invoice</span>
        <span class="font-bold">{{ $history->invoice_id }}</span>
    </div>
    <div class="row">
        <span>Tanggal</span>
        <span>{{ $history->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="row">
        <span>Kasir</span>
        <span>{{ $history->cashier_name }}</span>
    </div>

    @if($history->customer)
    <div class="divider"></div>
    <div class="row">
        <span>Member</span>
        <span class="font-bold">{{ $history->customer->name }}</span>
    </div>
    <div class="row">
        <span>Tier</span>
        <span style="text-transform: uppercase;">{{ $history->customer->tier }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <!-- Items -->
    @foreach($history->items as $item)
    <div class="item-row">
        <div class="item-name">{{ $item->product ? $item->product->name : 'Produk dihapus' }}</div>
        <div class="item-detail">
            <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
            <span>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
        </div>
        @if($item->discount > 0)
        <div class="item-detail discount-row" style="font-size: 10px;">
            <span>{{ $item->discount_description ?? 'Diskon' }}</span>
            <span>-{{ number_format($item->discount, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>
    @endforeach

    <div class="divider"></div>

    <!-- Summary -->
    <div class="row">
        <span>Subtotal</span>
        <span>{{ number_format($subtotal, 0, ',', '.') }}</span>
    </div>

    @if($history->promo_discount > 0)
    <div class="row discount-row">
        <span>Diskon Promo</span>
        <span>-{{ number_format($history->promo_discount, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($history->tier_discount > 0)
    <div class="row discount-row">
        <span>Diskon Member ({{ ucfirst($history->customer->tier ?? '') }})</span>
        <span>-{{ number_format($history->tier_discount, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($history->usedVoucher)
    <div class="row discount-row">
        <span>Voucher ({{ $history->usedVoucher->code }})</span>
        <span>-{{ number_format($history->usedVoucher->discount_amount, 0, ',', '.') }}</span>
    </div>
    @elseif($history->voucher_discount > 0)
    <div class="row discount-row">
        <span>Diskon Voucher</span>
        <span>-{{ number_format($history->voucher_discount, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($history->points_redeemed > 0)
    <div class="row discount-row">
        <span>Poin ({{ $history->points_redeemed }})</span>
        <span>-{{ number_format($history->points_redeemed * $pointValue, 0, ',', '.') }}</span>
    </div>
    @elseif($history->points_discount > 0)
    <div class="row discount-row">
        <span>Diskon Poin</span>
        <span>-{{ number_format($history->points_discount, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($service > 0)
    <div class="row">
        <span>Service ({{ $serviceRate }}%)</span>
        <span>{{ number_format($service, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($history->tax > 0)
    <div class="row">
        <span>Pajak ({{ $vatRate }}%)</span>
        <span>{{ number_format($history->tax, 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="divider-thick"></div>

    <div class="row total-row">
        <span>TOTAL</span>
        <span>{{ number_format($history->total_amount, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <!-- Payment -->
    @if($history->payment_method === 'split')
        @foreach($history->payments as $pmt)
        <div class="row">
            <span>{{ ucfirst($pmt->method) }}</span>
            <span>{{ number_format($pmt->amount, 0, ',', '.') }}</span>
        </div>
        @endforeach
    @else
    <div class="row">
        <span>{{ ucfirst($history->payment_method) }}</span>
        <span>{{ number_format($history->amount_received, 0, ',', '.') }}</span>
    </div>
    @endif
    @if($history->change_amount > 0)
    <div class="row">
        <span>Kembalian</span>
        <span>{{ number_format($history->change_amount, 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <!-- Points Earned -->
    @php
        $pointsPerRupiah = intval(\App\Models\StoreSetting::getVal('loyalty_points_per_rupiah', $storeId, '10000'));
        $pointsEarned = $history->customer ? floor($subtotal / $pointsPerRupiah) : 0;
    @endphp
    @if($history->customer && $pointsEarned > 0)
    <div class="text-center mt-1" style="font-size: 10px;">
        <div>Poin didapat: <span class="font-bold">{{ number_format($pointsEarned) }} pts</span></div>
        <div>Total poin: {{ number_format($history->customer->total_points) }} pts</div>
    </div>
    <div class="divider"></div>
    @endif

    <!-- Earned Voucher -->
    @if($history->earnedVoucher)
    <div class="text-center mt-1">
        <div style="font-weight: bold;">*** VOUCHER UNTUK ANDA ***</div>
        <div style="font-size: 13px; font-weight: bold; letter-spacing: 2px; margin: 3px 0;">{{ $history->earnedVoucher->code }}</div>
        <div style="font-size: 9px;">Diskon Rp{{ number_format($history->earnedVoucher->discount_amount, 0, ',', '.') }}</div>
    </div>
    <div class="divider"></div>
    @endif

    <!-- Footer -->
    <div class="text-center mt-2">
        <div style="font-weight: bold;">Terima Kasih</div>
        <div style="font-size: 9px;">Selamat Datang Kembali</div>
    </div>

    <div class="text-center mt-2" style="font-size: 8px; color: #666;">
        Dicetak: {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <script>
        @if(\App\Models\StoreSetting::getVal('auto_print', $storeId, '0') == '1')
        window.onload = function() { window.print(); }
        @endif
    </script>
</body>
</html>
