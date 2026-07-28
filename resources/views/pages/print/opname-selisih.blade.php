<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Selisih Stock Opname - {{ $session->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
        body { font-family: 'Arial', sans-serif; font-size: 12px; }
    </style>
</head>
<body class="bg-white p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6 border-b-2 border-slate-800 pb-4">
            <div>
                <h1 class="text-2xl font-bold uppercase tracking-wider text-slate-800">LAPORAN SELISIH STOCK OPNAME</h1>
                <p class="text-slate-600 mt-1">Sesi: <span class="font-semibold">{{ $session->name }}</span></p>
                <p class="text-slate-600">Tanggal Cetak: {{ now()->format('d F Y H:i') }}</p>
            </div>
            <div class="text-right">
                <button onclick="window.print()" class="no-print bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-semibold text-sm">
                    🖨️ Cetak Sekarang
                </button>
            </div>
        </div>

        <!-- Summary -->
        <div class="flex gap-8 mb-6 bg-slate-50 p-4 rounded border border-slate-200">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Total Item Selisih</p>
                <p class="text-lg font-bold text-slate-800">{{ $summary['total_items'] ?? 0 }} Produk</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Total QTY Selisih</p>
                <p class="text-lg font-bold {{ ($summary['total_variance_qty'] ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $summary['total_variance_qty'] ?? 0 }}
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Total Nilai Selisih</p>
                <p class="text-lg font-bold {{ ($summary['total_variance_value'] ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                    Rp {{ number_format($summary['total_variance_value'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full border-collapse border border-slate-400 mb-8">
            <thead>
                <tr class="bg-slate-200">
                    <th class="border border-slate-400 px-3 py-2 text-center w-10">No</th>
                    <th class="border border-slate-400 px-3 py-2 text-left w-24">Kode SKU</th>
                    <th class="border border-slate-400 px-3 py-2 text-left">Nama Produk</th>
                    <th class="border border-slate-400 px-3 py-2 text-right w-20">Stok Sistem</th>
                    <th class="border border-slate-400 px-3 py-2 text-right w-20">Stok Fisik</th>
                    <th class="border border-slate-400 px-3 py-2 text-right w-20">Selisih QTY</th>
                    <th class="border border-slate-400 px-3 py-2 text-right w-32">Nilai Selisih</th>
                    <th class="border border-slate-400 px-3 py-2 text-center w-32">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($discrepancies as $index => $item)
                <tr>
                    <td class="border border-slate-400 px-3 py-2 text-center">{{ $index + 1 }}</td>
                    <td class="border border-slate-400 px-3 py-2">{{ $item->product->code ?? '-' }}</td>
                    <td class="border border-slate-400 px-3 py-2">{{ $item->product->name ?? '-' }}</td>
                    <td class="border border-slate-400 px-3 py-2 text-right">{{ $item->system_stock }}</td>
                    <td class="border border-slate-400 px-3 py-2 text-right">{{ $item->physical_stock }}</td>
                    <td class="border border-slate-400 px-3 py-2 text-right font-bold {{ $item->variance_qty < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $item->variance_qty > 0 ? '+' : '' }}{{ $item->variance_qty }}
                    </td>
                    <td class="border border-slate-400 px-3 py-2 text-right font-bold {{ $item->variance_value < 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rp {{ number_format($item->variance_value, 0, ',', '.') }}
                    </td>
                    <td class="border border-slate-400 px-3 py-2 text-center">{{ $item->notes }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="border border-slate-400 px-3 py-4 text-center italic text-slate-500">Hebat! Tidak ada selisih stok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Block -->
        <div class="flex justify-between mt-12 px-8">
            <div class="text-center">
                <p class="mb-16">Disiapkan Oleh (Admin),</p>
                <p class="border-b border-slate-800 w-40 mx-auto"></p>
                <p class="mt-1 text-xs text-slate-600">Nama & Tanggal</p>
            </div>
            <div class="text-center">
                <p class="mb-16">Disetujui Oleh (Manager),</p>
                <p class="border-b border-slate-800 w-40 mx-auto"></p>
                <p class="mt-1 text-xs text-slate-600">Nama & Tanggal</p>
            </div>
        </div>
    </div>
</body>
</html>
