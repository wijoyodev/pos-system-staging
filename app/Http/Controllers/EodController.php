<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\EodReport;
use App\Models\EodReportItem;
use App\Models\Expense;
use App\Models\History;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EodController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = EodReport::with(['store', 'generatedBy'])->latest('eod_date');

        if (! $user->isSuperAdmin() && $user->store_id) {
            $query->where('store_id', $user->store_id);
        }

        if ($request->filled('date_from')) {
            $query->where('eod_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('eod_date', '<=', $request->date_to);
        }

        $eodReports = $query->get();

        $todayExists = false;
        if ($user->store_id) {
            $todayExists = EodReport::existsForStoreAndDate($user->store_id, today());
        }

        return view('pages.eod', [
            'title' => 'End Of Day',
            'eodReports' => $eodReports,
            'todayExists' => $todayExists,
        ]);
    }

    public function generate()
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        if (! $storeId) {
            return redirect('/eod')->with('error', 'Anda tidak terhubung ke toko manapun.');
        }

        if (EodReport::existsForStoreAndDate($storeId, today())) {
            return redirect('/eod')->with('error', 'EOD untuk hari ini sudah dibuat.');
        }

        $today = today()->toDateString();

        $transactions = History::where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->get();

        if ($transactions->isEmpty()) {
            return redirect('/eod')->with('error', 'Tidak ada transaksi hari ini.');
        }

        $result = DB::transaction(function () use ($storeId, $today, $user, $transactions) {
            $totalTransactions = $transactions->count();
            $totalTax = $transactions->sum('tax');
            $totalService = $transactions->sum('service');
            $totalPromoDiscount = $transactions->sum('promo_discount');
            $totalPointsDiscount = $transactions->sum('points_discount');
            $totalTierDiscount = $transactions->sum('tier_discount');
            $totalVoucherDiscount = $transactions->sum('voucher_discount');
            $totalNetSales = $transactions->sum('total_amount');
            $totalSales = $totalNetSales + $totalPromoDiscount + $totalTierDiscount + $totalVoucherDiscount + $totalPointsDiscount;

            $salesByMethod = [
                'cash' => 0,
                'qris' => 0,
                'debit' => 0,
                'credit' => 0,
            ];
            foreach ($transactions as $trx) {
                if ($trx->payment_method === 'split') {
                    foreach ($trx->payments as $pmt) {
                        if (isset($salesByMethod[$pmt->method])) {
                            $salesByMethod[$pmt->method] += $pmt->amount;
                        }
                    }
                } else {
                    if (isset($salesByMethod[$trx->payment_method])) {
                        $salesByMethod[$trx->payment_method] += $trx->total_amount;
                    }
                }
            }

            $totalExpenses = Expense::where('store_id', $storeId)
                ->whereDate('expense_date', $today)
                ->sum('amount');

            $closings = Closing::where('store_id', $storeId)
                ->whereDate('closing_date', $today)
                ->where('status', 'completed')
                ->get();

            $totalExpectedCash = $closings->sum('expected_cash');
            $totalActualCash = $closings->sum('actual_cash');
            $cashDifference = $totalActualCash - $totalExpectedCash;
            $totalClosings = $closings->count();

            $eodReport = EodReport::create([
                'store_id' => $storeId,
                'eod_date' => $today,
                'total_transactions' => $totalTransactions,
                'total_sales' => $totalSales,
                'total_tax' => $totalTax,
                'total_service' => $totalService,
                'total_promo_discount' => $totalPromoDiscount,
                'total_points_discount' => $totalPointsDiscount,
                'total_tier_discount' => $totalTierDiscount,
                'total_voucher_discount' => $totalVoucherDiscount,
                'total_net_sales' => $totalNetSales,
                'sales_cash' => $salesByMethod['cash'],
                'sales_qris' => $salesByMethod['qris'],
                'sales_debit' => $salesByMethod['debit'],
                'sales_credit' => $salesByMethod['credit'],
                'total_expenses' => $totalExpenses,
                'total_expected_cash' => $totalExpectedCash,
                'total_actual_cash' => $totalActualCash,
                'cash_difference' => $cashDifference,
                'total_closings' => $totalClosings,
                'status' => 'finalized',
                'generated_by' => $user->id,
            ]);

            $productSales = [];
            foreach ($transactions as $trx) {
                foreach ($trx->items as $item) {
                    $pid = $item->product_id;
                    if (! $pid) {
                        continue;
                    }
                    if (! isset($productSales[$pid])) {
                        $productSales[$pid] = ['qty' => 0, 'revenue' => 0];
                    }
                    $productSales[$pid]['qty'] += $item->quantity;
                    $productSales[$pid]['revenue'] += ($item->quantity * $item->price);
                }
            }

            foreach ($productSales as $productId => $data) {
                EodReportItem::create([
                    'eod_report_id' => $eodReport->id,
                    'product_id' => $productId,
                    'total_qty_sold' => $data['qty'],
                    'total_revenue' => $data['revenue'],
                ]);
            }

            $this->autoCreatePurchaseOrders($eodReport, $productSales, $storeId, $user->id);

            return $eodReport;
        });

        return redirect('/eod/'.$result->id)->with('success', 'EOD berhasil digenerate dan PO otomatis dibuat.');
    }

    private function autoCreatePurchaseOrders($eodReport, $productSales, $storeId, $userId)
    {
        $productsWithSupplier = [];

        foreach ($productSales as $productId => $data) {
            $product = Product::with('primarySupplier')->find($productId);
            if ($product && $product->primary_supplier_id) {
                $supplierId = $product->primary_supplier_id;
                if (! isset($productsWithSupplier[$supplierId])) {
                    $productsWithSupplier[$supplierId] = [];
                }
                $productsWithSupplier[$supplierId][] = [
                    'product' => $product,
                    'qty' => $data['qty'],
                    'revenue' => $data['revenue'],
                ];
            }
        }

        foreach ($productsWithSupplier as $supplierId => $items) {
            $poNumber = PurchaseOrder::generatePoNumber();
            $totalAmount = 0;
            $poItems = [];

            foreach ($items as $item) {
                $costPrice = $item['product']->cost_price;
                $subtotal = $item['qty'] * $costPrice;
                $totalAmount += $subtotal;
                $poItems[] = [
                    'product_id' => $item['product']->id,
                    'quantity_ordered' => $item['qty'],
                    'cost_price' => $costPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $po = PurchaseOrder::create([
                'store_id' => $storeId,
                'supplier_id' => $supplierId,
                'po_number' => $poNumber,
                'eod_report_id' => $eodReport->id,
                'order_date' => today(),
                'expected_delivery' => today()->addDays(3),
                'total_amount' => $totalAmount,
                'status' => 'ordered',
                'notes' => 'Auto-generated from EOD '.today()->format('d/m/Y'),
                'ordered_by' => $userId,
            ]);

            foreach ($poItems as $poItem) {
                PurchaseOrderItem::create(array_merge(['purchase_order_id' => $po->id], $poItem));
            }
        }
    }

    public function show(EodReport $eodReport)
    {
        $eodReport->load(['store', 'generatedBy', 'items.product', 'purchaseOrders.supplier']);

        return view('pages.eod-detail', [
            'title' => 'Detail EOD',
            'eodReport' => $eodReport,
        ]);
    }

    public function print(EodReport $eodReport)
    {
        $eodReport->load(['store', 'generatedBy', 'items.product', 'purchaseOrders.supplier']);

        return view('print.eod', [
            'eodReport' => $eodReport,
        ]);
    }
}
