<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kertas Stock Opname - {{ $session->name }}</title>
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
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6 border-b-2 border-slate-800 pb-4">
            <div>
                <h1 class="text-2xl font-bold uppercase tracking-wider text-slate-800">KERTAS KERJA STOCK OPNAME</h1>
                <p class="text-slate-600 mt-1">Sesi: <span class="font-semibold">{{ $session->name }}</span></p>
                <p class="text-slate-600">Tanggal Rencana: {{ \Carbon\Carbon::parse($session->planned_date)->format('d F Y') }}</p>
                <p class="text-slate-600">Deskripsi: {{ $session->description ?? '-' }}</p>
            </div>
            <div class="text-right">
                <button onclick="window.print()" class="no-print bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-semibold text-sm">
                    🖨️ Cetak Sekarang
                </button>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full border-collapse border border-slate-400 mb-8">
            <thead>
                <tr class="bg-slate-200">
                    <th class="border border-slate-400 px-3 py-2 text-center w-12">No</th>
                    <th class="border border-slate-400 px-3 py-2 text-left w-32">Kode SKU</th>
                    <th class="border border-slate-400 px-3 py-2 text-left">Nama Produk</th>
                    <th class="border border-slate-400 px-3 py-2 text-center w-32">Kategori</th>
                    <th class="border border-slate-400 px-3 py-2 text-center w-24">Satuan</th>
                    <th class="border border-slate-400 px-3 py-2 text-center w-32">Stok Fisik</th>
                    <th class="border border-slate-400 px-3 py-2 text-center w-48">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                <tr>
                    <td class="border border-slate-400 px-3 py-2 text-center">{{ $index + 1 }}</td>
                    <td class="border border-slate-400 px-3 py-2">{{ $product->sku }}</td>
                    <td class="border border-slate-400 px-3 py-2">{{ $product->name }}</td>
                    <td class="border border-slate-400 px-3 py-2 text-center">{{ $product->category->name ?? '-' }}</td>
                    <td class="border border-slate-400 px-3 py-2 text-center">{{ $product->unit ?? 'PCS' }}</td>
                    <td class="border border-slate-400 px-3 py-4 text-center"></td> <!-- Empty column for manual entry -->
                    <td class="border border-slate-400 px-3 py-4 text-center"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="border border-slate-400 px-3 py-4 text-center italic text-slate-500">Tidak ada produk ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Block -->
        <div class="flex justify-end gap-16 mt-12 pr-8">
            <div class="text-center">
                <p class="mb-16">Dihitung Oleh (Checker),</p>
                <p class="border-b border-slate-800 w-40 mx-auto"></p>
                <p class="mt-1 text-xs text-slate-600">Nama & Tanggal</p>
            </div>
        </div>
    </div>
</body>
</html>
