<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur PO - {{ $purchaseOrder->po_number }}</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            max-width: 210mm;
            margin: 0 auto;
            padding: 15px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 2px solid #000; margin: 10px 0; }
        .divider-thin { border-top: 1px solid #ccc; margin: 5px 0; }
        .header { margin-bottom: 15px; }
        .row { display: flex; justify-content: space-between; padding: 2px 0; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }
        .mb-1 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 5px 6px; text-align: left; border-bottom: 1px solid #ddd; font-size: 10px; }
        th { background: #f5f5f5; font-weight: bold; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; }
        .section-title { font-size: 12px; font-weight: bold; margin: 12px 0 6px 0; padding-bottom: 3px; border-bottom: 1px solid #333; }
        .header-table { width: 100%; border: none; }
        .header-table td { border: none; padding: 3px; vertical-align: top; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px; }
        .info-box { padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .info-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; }
        .info-value { font-size: 11px; font-weight: bold; margin-top: 2px; }
        .total-row td { border-top: 2px solid #000; border-bottom: none; font-weight: bold; font-size: 12px; }
        .stamp-box { margin-top: 30px; display: flex; justify-content: space-between; }
        .stamp-item { text-align: center; width: 30%; }
        .stamp-line { border-bottom: 1px solid #000; height: 50px; margin-bottom: 5px; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #f4f4f4; padding: 10px; margin-bottom: 20px; text-align: center; border-radius: 4px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 5px; font-weight: bold;">PRINT FAKTUR</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #ccc; color: #000; border: none; border-radius: 5px; margin-left: 10px;">CLOSE</button>
    </div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <h2 style="margin: 0 0 3px 0;">{{ $purchaseOrder->store->name ?? 'Store' }}</h2>
                    <div style="font-size: 10px;">{{ $purchaseOrder->store->address ?? '' }}</div>
                    @if($purchaseOrder->store->code)
                    <div style="font-size: 10px;">Kode Toko: {{ $purchaseOrder->store->code }}</div>
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <h1 style="margin: 0; font-size: 20px; color: #333;">FAKTUR PEMBELIAN</h1>
                    <div style="font-size: 14px; font-weight: bold; margin-top: 3px;">{{ $purchaseOrder->po_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Diterbitkan Untuk</div>
            <div class="info-value">{{ $purchaseOrder->supplier?->name ?? '-' }}</div>
            @if($purchaseOrder->supplier->contact_name)
            <div style="font-size: 10px;">Contact: {{ $purchaseOrder->supplier->contact_name }}</div>
            @endif
            @if($purchaseOrder->supplier->address)
            <div style="font-size: 10px;">{{ $purchaseOrder->supplier->address }}</div>
            @endif
            @if($purchaseOrder->supplier?->phone)
            <div style="font-size: 10px;">Telp: {{ $purchaseOrder->supplier->phone }}</div>
            @endif
            @if($purchaseOrder->supplier?->email)
            <div style="font-size: 10px;">Email: {{ $purchaseOrder->supplier->email }}</div>
            @endif
        </div>
        <div class="info-box">
            <div class="info-label">Informasi Pesanan</div>
            <div class="row">
                <span>Tanggal Pesan:</span>
                <span class="font-bold">{{ $purchaseOrder->order_date->format('d F Y') }}</span>
            </div>
            <div class="row">
                <span>Estimasi Kirim:</span>
                <span class="font-bold">{{ $purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('d F Y') : '-' }}</span>
            </div>
            @if($purchaseOrder->delivery_date)
            <div class="row">
                <span>Tanggal Terima:</span>
                <span class="font-bold">{{ $purchaseOrder->delivery_date->format('d F Y') }}</span>
            </div>
            @endif
            <div class="row">
                <span>Status:</span>
                <span class="font-bold" style="text-transform: uppercase;">{{ $purchaseOrder->status }}</span>
            </div>
            <div class="row">
                <span>Dibuat Oleh:</span>
                <span class="font-bold">{{ $purchaseOrder->orderedBy?->name ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Detail Barang</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">SKU</th>
                <th style="width: 35%;">Nama Barang</th>
                <th style="width: 12%; text-align: center;">Qty</th>
                <th style="width: 18%; text-align: right;">Harga Satuan</th>
                <th style="width: 18%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($purchaseOrder->items as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->product->sku ?? '-' }}</td>
                <td>{{ $item->product?->name ?? 'Product Deleted' }}</td>
                <td style="text-align: center;">{{ number_format($item->quantity_ordered) }}</td>
                <td style="text-align: right;">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($purchaseOrder->notes)
    <div class="section-title">Catatan</div>
    <div style="padding: 6px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 3px; font-size: 10px;">{{ $purchaseOrder->notes }}</div>
    @endif

    <div class="stamp-box">
        <div class="stamp-item">
            <div class="info-label">Dibuat Oleh</div>
            <div class="stamp-line"></div>
            <div>{{ $purchaseOrder->orderedBy?->name ?? '-' }}</div>
            <div style="font-size: 9px; color: #666;">Pemesan</div>
        </div>
        <div class="stamp-item">
            <div class="info-label">Disetujui Oleh</div>
            <div class="stamp-line"></div>
            <div>( ........................... )</div>
            <div style="font-size: 9px; color: #666;">Manager</div>
        </div>
        <div class="stamp-item">
            <div class="info-label">Penerima</div>
            <div class="stamp-line"></div>
            <div>( ........................... )</div>
            <div style="font-size: 9px; color: #666;">Supplier</div>
        </div>
    </div>

    <div class="divider-thin mt-2"></div>
    <div class="text-center" style="font-size: 9px; color: #666; margin-top: 5px;">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
