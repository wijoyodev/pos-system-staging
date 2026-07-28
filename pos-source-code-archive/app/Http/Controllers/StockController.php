<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockProduct;
use App\Models\Store;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StockController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        if ($storeId) {
            $low_stocks = Product::whereHas('stocks', function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->whereColumn('quantity', '<=', 'threshold');
            })->with(['stocks' => function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            }])->orderBy('name')->get();

            $critical_count = 0;
            $warning_count = 0;
            foreach ($low_stocks as $p) {
                $s = $p->stocks->first();
                if ($s && $s->quantity == 0) {
                    $critical_count++;
                } else {
                    $warning_count++;
                }
            }
        } else {
            // Super admin: show all low stock
            $low_stocks = Product::with('stocks')->get()->filter(function ($p) {
                return $p->getStockTotal() <= $p->threshold;
            });

            $critical_count = $low_stocks->filter(fn ($p) => $p->getStockTotal() == 0)->count();
            $warning_count = $low_stocks->count() - $critical_count;
        }

        return view('pages/stock', [
            'title' => 'Stock Alerts',
            'low_stocks' => $low_stocks,
            'critical_count' => $critical_count,
            'warning_count' => $warning_count,
            'storeId' => $storeId,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:5120',
        ]);

        $storeId = auth()->user()->store_id;
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        $rows = [];

        if (in_array($extension, ['xls', 'xlsx'])) {
            try {
                $spreadsheet = IOFactory::load($file->path());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            } catch (\Exception $e) {
                $results['errors'][] = 'Gagal membaca file Excel: '.$e->getMessage();

                return back()->with('import_results', $results);
            }
        } else {
            $handle = fopen($file->path(), 'r');
            if (! $handle) {
                $results['errors'][] = 'Tidak dapat membaca file';

                return back()->with('import_results', $results);
            }

            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        if (empty($rows)) {
            $results['errors'][] = 'File kosong';

            return back()->with('import_results', $results);
        }

        $header = array_shift($rows);

        $nameIndex = -1;
        $qtyIndex = -1;

        foreach ($header as $i => $col) {
            $colLower = strtolower(trim($col));
            if ($nameIndex === -1 && in_array($colLower, ['product', 'nama', 'name', 'item', 'barang'])) {
                $nameIndex = $i;
            }
            if ($qtyIndex === -1 && in_array($colLower, ['qty', 'quantity', 'jumlah', 'stock'])) {
                $qtyIndex = $i;
            }
        }

        if ($nameIndex === -1 || $qtyIndex === -1) {
            $results['errors'][] = 'Header tidak valid. Gunakan: product, qty';

            return back()->with('import_results', $results);
        }

        foreach ($rows as $lineNum => $row) {
            if (! isset($row[$nameIndex]) || ! isset($row[$qtyIndex])) {
                continue;
            }

            $name = trim($row[$nameIndex]);
            $qtyRaw = trim($row[$qtyIndex]);
            $qty = (int) $qtyRaw;

            if (empty($name) || $qty <= 0) {
                continue;
            }

            $product = Product::where('name', $name)->first();

            if (! $product) {
                $product = Product::where('sku', $name)->first();
            }

            if (! $product) {
                $product = Product::where('barcode', $name)->first();
            }

            if (! $product) {
                $product = Product::where('name', 'like', '%'.$name.'%')->first();
            }

            if ($product) {
                if ($storeId) {
                    $stock = StockProduct::firstOrCreate(
                        ['product_id' => $product->id, 'store_id' => $storeId],
                        ['quantity' => 0]
                    );
                    $stock->quantity = $stock->quantity + $qty;
                    $stock->save();
                } else {
                    $stores = Store::where('status', 'active')->get();
                    foreach ($stores as $store) {
                        $stock = StockProduct::firstOrCreate(
                            ['product_id' => $product->id, 'store_id' => $store->id],
                            ['quantity' => 0]
                        );
                        $stock->quantity = $stock->quantity + $qty;
                        $stock->save();
                    }
                }
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Tidak ketemu: $name (qty: $qty)";
            }
        }

        return back()->with('import_results', $results);
    }
}
