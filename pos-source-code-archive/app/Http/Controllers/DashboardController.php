<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\History;
use App\Models\HistoryItem;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Get filter values
        $branchId = $request->branch_id;
        $storeId = $request->store_id;

        // Base queries
        $historyQuery = $this->getFilteredHistoryQuery($user, $branchId, $storeId);
        $expenseQuery = $this->getFilteredExpenseQuery($user, $branchId, $storeId);

        // 1. Today's Revenue
        $todaysRevenue = (clone $historyQuery)->whereDate('created_at', $today)->sum('total_amount');
        $yesterdaysRevenue = (clone $historyQuery)->whereDate('created_at', $today->copy()->subDay())->sum('total_amount');
        $revenueGrowth = $yesterdaysRevenue > 0 ? round((($todaysRevenue - $yesterdaysRevenue) / $yesterdaysRevenue) * 100) : 0;

        // 2. Today's Expenses
        $todaysExpenses = (clone $expenseQuery)->whereDate('expense_date', $today)->sum('amount');

        // 3. Today's COGS & Gross Profit
        $todayHistoryIds = (clone $historyQuery)->whereDate('created_at', $today)->pluck('id');
        $todaysCogs = HistoryItem::whereIn('history_id', $todayHistoryIds)
            ->join('products', 'history_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(history_items.quantity * products.cost_price) as total_cogs')
            ->value('total_cogs') ?? 0;

        $todaysGrossProfit = $todaysRevenue - $todaysCogs;
        $todaysNetProfit = $todaysGrossProfit - $todaysExpenses;

        // 4. Overall Totals
        $totalTransactions = (clone $historyQuery)->count();
        $totalRevenue = (clone $historyQuery)->sum('total_amount');
        $avgTransactionValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // 5. Items Sold
        $historyIds = (clone $historyQuery)->pluck('id');
        $itemsSold = HistoryItem::whereIn('history_id', $historyIds)->sum('quantity');

        // Low stock alert
        $lowStockCount = $this->getLowStockCount($user);

        // 5. Revenue Trend (Last 7 days)
        $revenueTrend = $this->getRevenueTrend($historyQuery);

        // 6. Top 5 Best Sellers
        $bestSellers = HistoryItem::with('product')
            ->whereIn('history_id', $historyIds)
            ->selectRaw('product_id, sum(quantity) as total_sold')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Calculate percentages for best sellers
        $topSellersTotal = $bestSellers->sum('total_sold') ?: 1;
        foreach ($bestSellers as $seller) {
            $seller->percentage = ($seller->total_sold / $topSellersTotal) * 100;
        }

        // 7. Payment Methods
        $paymentMethods = (clone $historyQuery)
            ->selectRaw('payment_method, count(*) as count')
            ->groupBy('payment_method')
            ->get();

        $totalPayments = $paymentMethods->sum('count') ?: 1;
        $paymentMethodsFormatted = $paymentMethods->map(function ($item) use ($totalPayments) {
            return [
                'method' => $item->payment_method,
                'count' => $item->count,
                'percentage' => round(($item->count / $totalPayments) * 100),
            ];
        });

        // Get branches and stores for filter (Super Admin only)
        $branches = Branch::with('stores')->get();
        $stores = Store::where('status', 'active')->get();

        return view('pages.dashboard', [
            'title' => 'Dashboard',
            'todaysRevenue' => $todaysRevenue,
            'revenueGrowth' => $revenueGrowth,
            'todaysExpenses' => $todaysExpenses,
            'todaysGrossProfit' => $todaysGrossProfit,
            'todaysNetProfit' => $todaysNetProfit,
            'totalTransactions' => $totalTransactions,
            'avgTransactionValue' => $avgTransactionValue,
            'itemsSold' => $itemsSold,
            'revenueTrend' => collect($revenueTrend),
            'bestSellers' => $bestSellers,
            'paymentMethods' => $paymentMethodsFormatted,
            'lowStockCount' => $lowStockCount,
            'branches' => $branches,
            'stores' => $stores,
            'branchId' => $branchId,
            'storeId' => $storeId,
        ]);
    }

    private function getFilteredHistoryQuery($user, $branchId, $storeId)
    {
        $query = History::query();

        if ($user->isSuperAdmin()) {
            if ($branchId) {
                $query->whereHas('store', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
        } elseif ($user->store_id) {
            $query->where('store_id', $user->store_id);
        }

        $query->where('status', '!=', 'voided');

        return $query;
    }

    private function getFilteredExpenseQuery($user, $branchId, $storeId)
    {
        $query = Expense::query();

        if ($user->isSuperAdmin()) {
            if ($branchId) {
                $query->whereHas('store', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
        } elseif ($user->store_id) {
            $query->where('store_id', $user->store_id);
        }

        return $query;
    }

    private function getLowStockCount($user)
    {
        if ($user->store_id) {
            return Product::whereHas('stocks', function ($q) use ($user) {
                $q->where('store_id', $user->store_id)
                    ->whereColumn('quantity', '<=', 'threshold');
            })->count();
        }

        return Product::get()->filter(function ($p) {
            return $p->getStockTotal() <= $p->threshold;
        })->count();
    }

    private function getRevenueTrend($historyQuery)
    {
        $revenueTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenueTrend[] = [
                'day' => $date->format('D'),
                'revenue' => (clone $historyQuery)->whereDate('created_at', $date)->sum('total_amount'),
            ];
        }

        return $revenueTrend;
    }
}
