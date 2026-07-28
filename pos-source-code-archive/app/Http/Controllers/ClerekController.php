<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\History;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClerekController extends Controller
{
    /**
     * Data Clerek (Reports & Reconciliation for Admin)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $nik = $request->nik;
        $date = $request->date ?? now()->format('Y-m-d');
        $targetUser = null;
        $pendingClerek = null;
        $history = null;

        if ($nik) {
            // Restriction: Only search for users in the same store
            $targetUser = User::where('nik', $nik)
                ->where('store_id', $user->store_id)
                ->first();

            if ($targetUser) {
                // Find pending clerek ONLY for the selected date
                $pendingClerek = Closing::where('user_id', $targetUser->id)
                    ->where('status', 'pending')
                    ->whereDate('closing_date', $date)
                    ->latest()
                    ->first();

                // Get history for this user at specific date
                $history = Closing::where('user_id', $targetUser->id)
                    ->whereDate('closing_date', $date)
                    ->where('status', 'completed')
                    ->latest()
                    ->get();

                // If no history on that date, show last 10 entries as fallback
                if ($history->isEmpty()) {
                    $history = Closing::where('user_id', $targetUser->id)
                        ->where('status', 'completed')
                        ->latest()
                        ->take(10)
                        ->get();
                }
            }
        }

        return view('pages.clerek-data', [
            'title' => 'Data Clerek',
            'nik' => $nik,
            'date' => $date,
            'targetUser' => $targetUser,
            'pendingClerek' => $pendingClerek,
            'history' => $history,
        ]);
    }

    /**
     * Get summary data for POS Modal
     */
    public function summary()
    {
        $user = Auth::user();
        $summary = $this->getShiftSummary($user);

        return response()->json($summary);
    }

    /**
     * Helper to calculate sales summary for a user today.
     */
    private function getShiftSummary($user)
    {
        $today = now()->format('Y-m-d');

        $sales = History::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->get();

        $cashSales = 0;
        $qrisSales = 0;
        $debitSales = 0;
        $creditSales = 0;

        foreach ($sales as $trx) {
            if ($trx->payment_method === 'split') {
                foreach ($trx->payments as $pmt) {
                    $method = $pmt->method;
                    ${$method.'Sales'} += $pmt->amount;
                }
            } else {
                $method = $trx->payment_method;
                ${$method.'Sales'} += $trx->total_amount;
            }
        }

        $totalSales = $cashSales + $qrisSales + $debitSales + $creditSales;

        // Check if already closed today (completed)
        $existing = Closing::where('user_id', $user->id)
            ->whereDate('closing_date', $today)
            ->where('status', 'completed')
            ->first();

        // Check for pending
        $pending = Closing::where('user_id', $user->id)
            ->whereDate('closing_date', $today)
            ->where('status', 'pending')
            ->first();

        return [
            'cashSales' => (int) $cashSales,
            'qrisSales' => (int) $qrisSales,
            'debitSales' => (int) $debitSales,
            'creditSales' => (int) $creditSales,
            'totalSales' => (int) $totalSales,
            'expectedCash' => (int) $cashSales,
            'isClosed' => (bool) $existing,
            'hasPending' => (bool) $pending,
            'existingData' => $existing ?? $pending,
        ];
    }

    /**
     * Initial shift closure by Cashier (creates 'pending' status).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $summary = $this->getShiftSummary($user);

        if ($summary['isClosed']) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan clerek hari ini.'], 400);
        }

        if ($summary['hasPending']) {
            return response()->json(['success' => false, 'message' => 'Anda memiliki clerek yang sedang menunggu verifikasi.'], 400);
        }

        $isStaff = ! $user->hasMinRole('admin');

        $closing = Closing::create([
            'user_id' => $user->id,
            'store_id' => $user->store_id,
            'closing_date' => $today,
            'total_sales' => $summary['totalSales'],
            'cash_sales' => $summary['cashSales'],
            'qris_sales' => $summary['qrisSales'],
            'debit_sales' => $summary['debitSales'],
            'credit_sales' => $summary['creditSales'],
            'expected_cash' => $summary['expectedCash'],
            'actual_cash' => 0,
            'difference' => -$summary['expectedCash'],
            'shift' => $request->shift ?? 'pagi',
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        if ($isStaff) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => $isStaff
                ? 'Shift berhasil ditutup. Sistem akan logout otomatis. Silahkan serahkan uang ke Admin.'
                : 'Shift berhasil ditutup. Anda tidak dapat melakukan transaksi lagi hari ini.',
            'shouldLogout' => $isStaff,
            'data' => $closing,
        ]);
    }

    /**
     * Admin processes the clerek by counting physical cash.
     */
    public function process(Request $request)
    {
        $request->validate([
            'closing_id' => 'required|exists:closings,id',
            'actual_cash' => 'required|numeric|min:0',
        ]);

        $closing = Closing::findOrFail($request->closing_id);

        if ($closing->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Clerek ini sudah diproses.'], 400);
        }

        // Prevent multiple completions on same day for same user
        $alreadyCompleted = Closing::where('user_id', $closing->user_id)
            ->whereDate('closing_date', $closing->closing_date)
            ->where('status', 'completed')
            ->exists();

        if ($alreadyCompleted) {
            return response()->json(['success' => false, 'message' => 'Kasir ini sudah menyelesaikan clerek hari ini.'], 400);
        }

        $actualCash = $request->actual_cash;
        $difference = $actualCash - $closing->expected_cash;

        $closing->update([
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'status' => 'completed',
            'approver_id' => Auth::id(),
            'notes' => $request->notes ?? $closing->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Clerek berhasil diverifikasi dan disimpan.',
        ]);
    }

    /**
     * Print Clerek Settlement Receipt
     */
    public function print(Closing $closing)
    {
        return view('print.clerek', [
            'closing' => $closing,
            'store' => $closing->store,
        ]);
    }
}
