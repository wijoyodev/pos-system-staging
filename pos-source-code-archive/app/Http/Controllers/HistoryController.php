<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\StockProduct;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function voidOtpPage()
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('pages/void-otp', [
            'title' => 'Void OTP Generator',
        ]);
    }

    public function searchVoidTransaction(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'invoice_id' => 'required|string',
        ]);

        $history = History::with(['items.product', 'user', 'store'])
            ->where('invoice_id', $request->invoice_id)
            ->first();

        if (! $history) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.']);
        }

        if ($history->status === 'voided') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah di-void sebelumnya.']);
        }

        // Get admins for this store
        $admins = User::where('store_id', $history->store_id)
            ->where(function ($q) {
                $q->where('role', 'admin')->orWhere('role', 'super_admin');
            })->get();

        return response()->json([
            'success' => true,
            'history' => [
                'id' => $history->id,
                'invoice_id' => $history->invoice_id,
                'total_amount' => $history->total_amount,
                'payment_method' => $history->payment_method,
                'cashier_name' => $history->cashier_name,
                'created_at' => $history->created_at->format('d/m/Y H:i'),
                'status' => $history->status,
                'items_count' => $history->items->count(),
            ],
            'admins' => $admins->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'role' => $a->role_label,
            ]),
        ]);
    }

    public function generateVoidOtp(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Hanya Super Admin yang dapat generate OTP.'], 403);
        }

        $request->validate([
            'history_id' => 'required|exists:histories,id',
            'admin_id' => 'required|exists:users,id',
        ]);

        $history = History::find($request->history_id);

        if ($history->status === 'voided') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah di-void sebelumnya.'], 400);
        }

        $admin = User::find($request->admin_id);
        if (! $admin->hasMinRole('admin')) {
            return response()->json(['success' => false, 'message' => 'User harus berposisi Admin.'], 403);
        }

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Set OTP on the history record with 10 minutes expiry
        $history->update([
            'void_otp' => $otp,
            'void_otp_expires_at' => now()->addMinutes(10),
            'void_otp_admin_id' => $admin->id,
        ]);

        return response()->json([
            'success' => true,
            'otp' => $otp,
            'admin_name' => $admin->name,
            'expires_at' => $history->void_otp_expires_at->format('H:i:s'),
            'invoice_id' => $history->invoice_id,
            'message' => 'OTP berhasil digenerate untuk '.$admin->name,
        ]);
    }

    public function void(Request $request, History $history)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'otp' => 'required|string|size:6',
        ]);

        // Verify OTP against the history record
        if (! $history->void_otp || $history->void_otp !== $request->otp) {
            return response()->json(['success' => false, 'message' => 'OTP salah.'], 403);
        }

        // Check OTP expiry
        if (! $history->void_otp_expires_at || now()->gt($history->void_otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP sudah kadaluarsa. Minta OTP baru dari Super Admin.'], 403);
        }

        if ($history->status === 'voided') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah dibatalkan sebelumnya.'], 400);
        }

        // Get the admin who was authorized
        $admin = User::find($history->void_otp_admin_id);
        if (! $admin || ! $admin->hasMinRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Admin otorisasi tidak valid.'], 403);
        }

        DB::beginTransaction();
        try {
            // Restore Stocks
            foreach ($history->items as $item) {
                $stock = StockProduct::where('product_id', $item->product_id)
                    ->where('store_id', $history->store_id)
                    ->first();

                if ($stock) {
                    $stock->increment('quantity', $item->quantity);
                }
            }

            // Update History
            $history->update([
                'status' => 'voided',
                'void_reason' => $request->reason,
                'voided_by' => $admin->id,
                'void_otp' => null,
                'void_otp_expires_at' => null,
                'void_otp_admin_id' => null,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal membatalkan transaksi: '.$e->getMessage()], 500);
        }
    }

    public function index()
    {
        $query = History::with('items.product')->latest();

        // Kasir hanya melihat transaksi miliknya sendiri
        if (auth()->user()->isKasir()) {
            $query->where('user_id', auth()->id());
        }

        if (request()->has('search') && request('search') != '') {
            $query->where('invoice_id', 'like', '%'.request('search').'%');
        }

        if (request()->has('date')) {
            $date = request('date');
            if ($date == 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($date == 'week') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($date == 'month') {
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
            }
        }

        if (request()->has('method') && request('method') != 'all') {
            $query->where('payment_method', request('method'));
        }

        // Export CSV uses full dataset (no pagination)
        if (request()->has('export') && request('export') == 'csv') {
            $allHistories = (clone $query)->with('items')->get();
            $filename = 'transactions_export_'.date('Y-m-d').'.csv';
            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$filename",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            $columns = ['Date & Time', 'Transaction ID', 'Cashier', 'Items', 'Total', 'Payment Method'];
            $callback = function () use ($allHistories, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                foreach ($allHistories as $history) {
                    $items = $history->items->map(function ($i) {
                        return $i->product_name.' ('.$i->quantity.')';
                    })->implode(', ');
                    fputcsv($file, [
                        $history->created_at->format('Y-m-d H:i:s'),
                        $history->invoice_id,
                        $history->cashier_name,
                        $items,
                        $history->total_amount,
                        $history->payment_method,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Compute stats from full filtered query (before pagination) - Exclude voided
        $statsQuery = clone $query;
        $totalCount = $statsQuery->where('status', '!=', 'voided')->count();
        $totalRevenue = (clone $query)->where('status', '!=', 'voided')->sum('total_amount');
        $avgTicket = $totalCount > 0 ? $totalRevenue / $totalCount : 0;

        // Payment method counts for the chart - Exclude voided
        $cashCount = (clone $query)->where('status', '!=', 'voided')->where('payment_method', 'cash')->count();
        $qrisCount = (clone $query)->where('status', '!=', 'voided')->where('payment_method', 'qris')->count();
        $debitCount = (clone $query)->where('status', '!=', 'voided')->where('payment_method', 'debit')->count();

        // Paginate with eager-loaded items (include all statuses)
        $histories = $query->with('items')->paginate(5)->withQueryString();

        $todayQuery = History::whereDate('created_at', Carbon::today());
        if (auth()->user()->isKasir()) {
            $todayQuery->where('user_id', auth()->id());
        }
        $today_count = (clone $todayQuery)->where('status', '!=', 'voided')->count();
        $today_revenue = (clone $todayQuery)->where('status', '!=', 'voided')->sum('total_amount');

        return view('pages/history', [
            'title' => 'Transactions',
            'histories' => $histories,
            'today_count' => $today_count,
            'today_revenue' => $today_revenue,
            'totalCount' => $totalCount,
            'totalRevenue' => $totalRevenue,
            'avgTicket' => $avgTicket,
            'cashCount' => $cashCount,
            'qrisCount' => $qrisCount,
            'debitCount' => $debitCount,
        ]);
    }
}
