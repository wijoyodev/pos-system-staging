<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\History;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function expenses(Request $request)
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        $query = Expense::with(['store', 'user']);

        if ($user->isSuperAdmin()) {
            if ($request->has('store_id') && $request->store_id) {
                $query->where('store_id', $request->store_id);
            }
        } else {
            $query->where('store_id', $storeId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);
        $stores = Store::all();

        return view('pages.expenses', [
            'title' => 'Pengeluaran Toko',
            'expenses' => $expenses,
            'stores' => $stores,
            'storeId' => $request->store_id ?? $storeId,
        ]);
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $validated['user_id'] = auth()->id();

        if (! auth()->user()->isSuperAdmin() || ! $request->store_id) {
            $validated['store_id'] = auth()->user()->store_id;
        } else {
            $validated['store_id'] = $request->store_id;
        }

        Expense::create($validated);

        return back()->with('success', 'Pengeluaran berhasil dicatat');
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus');
    }

    public function pnl(Request $request)
    {
        $user = auth()->user();
        $storeId = $request->store_id ?? $user->store_id;

        // Date range
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        // 1. Revenue
        $revenueQuery = History::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'voided');

        if (! $user->isSuperAdmin() || $storeId) {
            $revenueQuery->where('store_id', $storeId);
        }
        $totalRevenue = $revenueQuery->sum('total_amount');

        // 2. COGS (HPP)
        $cogsQuery = DB::table('history_items')
            ->join('histories', 'history_items.history_id', '=', 'histories.id')
            ->join('products', 'history_items.product_id', '=', 'products.id')
            ->whereBetween('histories.created_at', [$startDate, $endDate])
            ->where('histories.status', '!=', 'voided');

        if (! $user->isSuperAdmin() || $storeId) {
            $cogsQuery->where('histories.store_id', $storeId);
        }

        $totalCogs = $cogsQuery->sum(DB::raw('history_items.quantity * products.cost_price'));

        // 3. Operating Expenses
        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if (! $user->isSuperAdmin() || $storeId) {
            $expenseQuery->where('store_id', $storeId);
        }
        $totalExpenses = $expenseQuery->sum('amount');

        // 4. Calculations
        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalExpenses;

        $stores = Store::all();
        $branches = Branch::all();

        return view('pages.reports.pnl', [
            'title' => 'Laporan Laba Rugi (P&L)',
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'stores' => $stores,
            'branches' => $branches,
            'storeId' => $storeId,
        ]);
    }
}
