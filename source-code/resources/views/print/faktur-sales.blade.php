<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur - {{ $history->invoice_id }}</title>
    <style>
        @page { size: 9.5in auto; margin: 8mm; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; line-height: 1.3; color: #000; width: 9.5in; margin: 0 auto; padding: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .header { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { padding: 3px 4px; text-align: left; }
        th { border-bottom: 1px solid #000; font-weight: bold; text-transform: uppercase; }
        .total-row td { border-top: 1px solid #000; font-weight: bold; }
        .stamp-box { margin-top: 20px; display: flex; justify-content: space-between; }
        .stamp-item { text-align: center; width: 30%; }
        .stamp-line { border-bottom: 1px solid #000; height: 40px; margin-bottom: 3px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="background:#f4f4f4;padding:10px;margin-bottom:20px;text-align:center;">
        <button onclick="window.print()" style="padding:8px 16px;cursor:pointer;background:#000;color:#fff;border:none;font-weight:bold;">PRINT</button>
        <button onclick="window.close()" style="padding:8px 16px;cursor:pointer;background:#ccc;border:none;margin-left:8px;">CLOSE</button>
    </div>

    <div class="header text-center">
        <h2 style="margin:0 0 2px;">{{ $history->store->name ?? 'Toko' }}</h2>
        <div style="font-size:10px;">{{ $history->store->address ?? '' }}</div>
        @if($history->store->phone)<div style="font-size:10px;">Telp: {{ $history->store->phone }}</div>@endif
        <h1 style="margin:8px 0 2px;font-size:16px;">FAKTUR PENJUALAN</h1>
        <div style="font-size:12px;font-weight:bold;">{{ $history->invoice_id }}</div>
    </div>

    <div class="divider"></div>

    <table>
        <tr><td style="width:25%;">Tanggal</td><td>: {{ $history->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td>: {{ $history->cashier_name }}</td></tr>
        <tr><td>Customer</td><td>: {{ $history->customer->name ?? 'Umum' }}</td></tr>
    </table>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:45%;">Nama Barang</th>
                <th style="width:12%;text-align:center;">Qty</th>
                <th style="width:18%;text-align:right;">Harga</th>
                <th style="width:20%;text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">Rp{{ number_format($history->total, 0, ',', '.') }}</td>
            </tr>
            @if($history->discount > 0)
            <tr>
                <td colspan="4" class="text-right">Diskon</td>
                <td class="text-right">-Rp{{ number_format($history->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="4" class="text-right">Bayar</td>
                <td class="text-right">Rp{{ number_format($history->payment, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">Kembali</td>
                <td class="text-right">Rp{{ number_format($history->change, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="divider"></div>

    @if($history->payment_method)
    <div style="font-size:10px;">Pembayaran: {{ strtoupper($history->payment_method) }}</div>
    @endif

    <div class="stamp-box">
        <div class="stamp-item">
            <div style="font-size:9px;font-weight:bold;">Hormat Kami</div>
            <div class="stamp-line"></div>
            <div>( ........................... )</div>
        </div>
        <div class="stamp-item">
            <div style="font-size:9px;font-weight:bold;">Penerima</div>
            <div class="stamp-line"></div>
            <div>( ........................... )</div>
        </div>
    </div>

    <div class="divider"></div>
    <div class="text-center" style="font-size:9px;">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>

    <script>window.onload=function(){window.print();}</script>
</body>
</html>
