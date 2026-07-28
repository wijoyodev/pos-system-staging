<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clerek Receipt #{{ $closing->id }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.2;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .header { margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; }
        .mt-2 { margin-top: 10px; }
        .mb-1 { margin-bottom: 5px; }
        
        @media print {
            body { width: 100%; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #f4f4f4; padding: 10px; margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 5px; font-weight: bold;">PRINT RECEIPT</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #ccc; color: #000; border: none; border-radius: 5px; margin-left: 10px;">CLOSE</button>
    </div>

    <div class="header text-center">
        <h2 style="margin: 0;">{{ $store->name ?? 'Arka POS' }}</h2>
        <div style="font-size: 10px;">{{ $store->address ?? 'End-of-Shift Report' }}</div>
    </div>

    <div class="divider"></div>
    <div class="text-center font-bold">VERIFIKASI CLEREK</div>
    <div class="divider"></div>

    <div class="row">
        <span>ID:</span>
        <span>#CLRK-{{ str_pad($closing->id, 5, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="row">
        <span>Tanggal:</span>
        <span>{{ $closing->closing_date->format('d/m/Y') }}</span>
    </div>
    <div class="row">
        <span>Shift:</span>
        <span style="text-transform: uppercase;">{{ $closing->shift }}</span>
    </div>
    <div class="row">
        <span>Kasir:</span>
        <span>{{ $closing->user->name }}</span>
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>Penjualan Tunai:</span>
        <span>{{ number_format($closing->cash_sales, 0, ',', '.') }}</span>
    </div>
    <div class="row">
        <span>Penjualan QRIS:</span>
        <span>{{ number_format($closing->qris_sales, 0, ',', '.') }}</span>
    </div>
    <div class="row">
        <span>Penjualan Debit/Kredit:</span>
        <span>{{ number_format($closing->debit_sales + $closing->credit_sales, 0, ',', '.') }}</span>
    </div>
    
    <div class="row font-bold mt-2">
        <span>TOTAL SALES:</span>
        <span>{{ number_format($closing->total_sales, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>Uang Diharapkan:</span>
        <span>{{ number_format($closing->expected_cash, 0, ',', '.') }}</span>
    </div>
    <div class="row font-bold">
        <span>Uang Diterima:</span>
        <span>{{ number_format($closing->actual_cash, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <div class="row font-bold" style="font-size: 14px;">
        <span>SELISIH:</span>
        <span>{{ $closing->difference > 0 ? '+' : '' }}{{ number_format($closing->difference, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <div class="mt-2">
        <div class="font-bold">Catatan:</div>
        <div style="font-size: 10px; font-style: italic;">{{ $closing->notes ?? '-' }}</div>
    </div>

    <div class="mt-2" style="display: flex; justify-content: space-around; margin-top: 30px;">
        <div class="text-center">
            <div style="height: 40px;"></div>
            <div class="divider" style="width: 60px; margin: 0 auto;"></div>
            <div style="font-size: 9px;">KASIR</div>
        </div>
        <div class="text-center">
            <div style="height: 40px;"></div>
            <div class="divider" style="width: 60px; margin: 0 auto;"></div>
            <div style="font-size: 9px;">ADMIN</div>
        </div>
    </div>

    <div class="text-center mt-2" style="font-size: 9px; opacity: 0.5;">
        Printed at: {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <script>
        // Auto print on load if needed, but manual button is safer for preview
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
