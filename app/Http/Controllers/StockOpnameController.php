<?php

namespace App\Http\Controllers;

use App\Models\OpnameAdjustment;
use App\Models\OpnameCount;
use App\Models\Product;
use App\Models\StockOpnameSession;
use App\Models\StockProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        // Super admin can filter by store
        if ($user->isSuperAdmin() && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        $query = StockOpnameSession::with(['creator', 'store'])
            ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn ($q, $d) => $q->where('planned_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->where('planned_date', '<=', $d))
            ->orderByDesc('created_at');

        $sessions = $query->paginate(20);

        return response()->json($sessions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'planned_date' => 'required|date',
            'store_id' => 'required|exists:stores,id',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'PLANNED';

        $session = StockOpnameSession::create($validated);

        return response()->json([
            'message' => 'Session created successfully',
            'session' => $session->load(['creator', 'store']),
        ], 201);
    }

    public function show(StockOpnameSession $session)
    {
        return response()->json($session->load([
            'creator', 'store', 'adjuster', 'poster',
            'counts.product.category',
            'adjustments',
        ]));
    }

    public function update(Request $request, StockOpnameSession $session)
    {
        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'planned_date' => 'date',
            'notes' => 'nullable|string',
        ]);

        $session->update($request->only(['name', 'description', 'planned_date', 'notes']));

        return response()->json([
            'message' => 'Session updated',
            'session' => $session->fresh(),
        ]);
    }

    public function updateStatus(StockOpnameSession $session, string $newStatus)
    {
        $dateField = strtolower($newStatus).'_date';

        $update = ['status' => $newStatus];

        if (in_array($dateField, ['cetakkertas_date', 'entry_date', 'checkdata_date', 'proses_date', 'cetakselisih_date', 'editdata_date', 'fixed_date', 'adjust_date'])) {
            $update[$dateField] = now()->toDateString();
        }

        $session->update($update);

        return response()->json([
            'message' => "Session status updated to {$newStatus}",
            'session' => $session->fresh(),
        ]);
    }

    public function printKertas(StockOpnameSession $session)
    {
        if (! $session->canCetakKertas()) {
            return response()->json(['error' => 'Session cannot proceed to CETAK_KERTAS'], 400);
        }

        return $this->updateStatus($session, StockOpnameSession::STATUS_CETAK_KERTAS);
    }

    public function printKertasView(StockOpnameSession $session)
    {
        $products = $session->getProducts();

        return view('pages.print.opname-kertas', compact('session', 'products'));
    }

    public function startEntry(StockOpnameSession $session)
    {
        if (! $session->canEntry()) {
            return response()->json(['error' => 'Session cannot proceed to ENTRY'], 400);
        }

        return $this->updateStatus($session, StockOpnameSession::STATUS_ENTRY);
    }

    public function checkData(StockOpnameSession $session)
    {
        if (! $session->canCheckData()) {
            return response()->json(['error' => 'Session cannot proceed to CHECK_DATA'], 400);
        }

        $totalProducts = Product::whereHas('stocks', fn ($q) => $q->where('store_id', $session->store_id)
        )->count();

        $enteredCount = $session->counts()->count();
        $unenteredCount = $totalProducts - $enteredCount;

        return response()->json([
            'total_products' => $totalProducts,
            'entered_count' => $enteredCount,
            'unentered_count' => $unenteredCount,
            'is_complete' => $unenteredCount === 0,
            'session' => $session->fresh(),
        ]);
    }

    public function proses(StockOpnameSession $session)
    {
        if (! $session->canProses()) {
            return response()->json(['error' => 'Session cannot proceed to PROSES'], 400);
        }

        $session->counts()->update(['status' => OpnameCount::STATUS_ENTERED]);

        foreach ($session->counts()->where('variance_qty', '!=', 0)->get() as $count) {
            OpnameAdjustment::updateOrCreate(
                ['session_id' => $session->id, 'count_id' => $count->id],
                [
                    'product_id' => $count->product_id,
                    'status' => OpnameAdjustment::STATUS_PENDING,
                ]
            );
        }

        return $this->updateStatus($session, StockOpnameSession::STATUS_PROSES);
    }

    public function printSelisih(StockOpnameSession $session)
    {
        if (! $session->canCetakSelisih()) {
            return response()->json(['error' => 'Session cannot proceed to CETAK_SELISIH'], 400);
        }

        $discrepancies = $session->getDiscrepancies();

        $session->update([
            'status' => StockOpnameSession::STATUS_CETAK_SELISIH,
            'cetakselisih_date' => now()->toDateString(),
        ]);

        return response()->json([
            'discrepancies' => $discrepancies,
            'summary' => [
                'total_items' => $discrepancies->count(),
                'total_variance_qty' => $discrepancies->sum('variance_qty'),
                'total_variance_value' => $discrepancies->sum('variance_value'),
            ],
            'session' => $session->fresh(),
        ]);
    }

    public function printSelisihView(StockOpnameSession $session)
    {
        $discrepancies = $session->getDiscrepancies();
        $summary = [
            'total_items' => $discrepancies->count(),
            'total_variance_qty' => $discrepancies->sum('variance_qty'),
            'total_variance_value' => $discrepancies->sum('variance_value'),
        ];

        return view('pages.print.opname-selisih', compact('session', 'discrepancies', 'summary'));
    }

    public function editData(StockOpnameSession $session)
    {
        if (! $session->canEditData()) {
            return response()->json(['error' => 'Session cannot proceed to EDIT_DATA'], 400);
        }

        return $this->updateStatus($session, StockOpnameSession::STATUS_EDIT_DATA);
    }

    public function fixed(StockOpnameSession $session)
    {
        if (! $session->canFixed()) {
            return response()->json(['error' => 'Session cannot proceed to FIXED'], 400);
        }

        $session->counts()->update(['status' => OpnameCount::STATUS_LOCKED]);

        return $this->updateStatus($session, StockOpnameSession::STATUS_FIXED);
    }

    public function adjust(StockOpnameSession $session)
    {
        if (! $session->canAdjust()) {
            return response()->json(['error' => 'Session cannot proceed to ADJUST'], 400);
        }

        DB::beginTransaction();
        try {
            // Update session status to ADJUST
            $session->update([
                'status' => StockOpnameSession::STATUS_ADJUST,
                'adjusted_by' => auth()->id(),
                'adjusted_at' => now(),
            ]);

            // Create or update adjustments for all variances
            $counts = $session->counts()->where('variance_qty', '!=', 0)->get();
            foreach ($counts as $count) {
                OpnameAdjustment::updateOrCreate(
                    ['session_id' => $session->id, 'product_id' => $count->product_id],
                    [
                        'status' => OpnameAdjustment::STATUS_APPROVED,
                        'adjustment_qty' => $count->variance_qty,
                    ]
                );
            }

            // Update adjustments to APPROVED
            $session->adjustments()->where('status', OpnameAdjustment::STATUS_PENDING)->update([
                'status' => OpnameAdjustment::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Apply adjustments to stock
            $adjustments = $session->adjustments()->whereIn('status', [
                OpnameAdjustment::STATUS_APPROVED,
                OpnameAdjustment::STATUS_APPLIED,
            ])->get();

            foreach ($adjustments as $adj) {
                if ($adj->adjustment_qty === 0) {
                    continue;
                }

                $stock = StockProduct::firstOrNew([
                    'product_id' => $adj->product_id,
                    'store_id' => $session->store_id,
                ]);

                $stock->quantity = max(0, $stock->quantity + $adj->adjustment_qty);
                $stock->save();

                Log::info('Stock opname adjustment applied', [
                    'session_id' => $session->id,
                    'product_id' => $adj->product_id,
                    'adjustment' => $adj->adjustment_qty,
                    'new_stock' => $stock->quantity,
                ]);
            }

            // Mark adjustments as APPLIED
            $session->adjustments()->where('status', OpnameAdjustment::STATUS_APPROVED)->update([
                'status' => OpnameAdjustment::STATUS_APPLIED,
            ]);

            // Update session status to POSTED
            $session->update([
                'status' => StockOpnameSession::STATUS_POSTED,
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Stock adjusted and posted successfully',
                'session' => $session->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, StockOpnameSession $session)
    {
        if (! $session->canCancel()) {
            return response()->json(['error' => 'Session cannot be cancelled'], 400);
        }

        $request->validate([
            'reason' => 'required|string',
        ]);

        $session->update([
            'status' => StockOpnameSession::STATUS_CANCELLED,
            'notes' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Session cancelled',
            'session' => $session->fresh(),
        ]);
    }

    public function changeStatus(StockOpnameSession $session, string $status)
    {
        $validStatuses = [
            'PLANNED', 'CETAK_KERTAS', 'ENTRY', 'CHECK_DATA', 'PROSES',
            'CETAK_SELISIH', 'EDIT_DATA', 'FIXED', 'ADJUST', 'POSTED',
        ];

        if (! in_array($status, $validStatuses)) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $session->update(['status' => $status]);

        return response()->json([
            'message' => 'Status changed',
            'session' => $session->fresh(),
        ]);
    }

    public function submitCounts(Request $request, StockOpnameSession $session)
    {
        if (! $session->canEntry()) {
            return response()->json(['error' => 'Session is not in ENTRY status'], 400);
        }

        $request->validate([
            'counts' => 'required|array',
            'counts.*.product_id' => 'required|exists:products,id',
            'counts.*.physical_stock' => 'required|integer|min:0',
            'counts.*.unit' => 'nullable|string',
            'counts.*.notes' => 'nullable|string',
        ]);

        $results = [];

        foreach ($request->counts as $countData) {
            $product = Product::find($countData['product_id']);
            $systemStock = $product->getStockForStore($session->store_id) ?? 0;
            $physicalStock = $countData['physical_stock'];
            $varianceQty = $physicalStock - $systemStock;

            $count = OpnameCount::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'product_id' => $countData['product_id'],
                ],
                [
                    'counted_by' => auth()->id(),
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'variance_qty' => $varianceQty,
                    'variance_value' => $varianceQty * ($product->selling_price ?? 0),
                    'unit' => $countData['unit'] ?? 'PCS',
                    'notes' => $countData['notes'] ?? null,
                    'count_method' => $countData['count_method'] ?? 'MANUAL',
                    'status' => OpnameCount::STATUS_ENTERED,
                ]
            );

            $results[] = $count;
        }

        return response()->json([
            'message' => 'Counts submitted',
            'counts' => $results,
        ]);
    }

    public function updateCount(Request $request, OpnameCount $count)
    {
        $session = $count->session;

        if ($session->status === StockOpnameSession::STATUS_FIXED) {
            return response()->json(['error' => 'Session is already FIXED, cannot edit'], 400);
        }

        $request->validate([
            'physical_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = $count->product;
        $systemStock = $count->system_stock;
        $physicalStock = $request->physical_stock;
        $varianceQty = $physicalStock - $systemStock;

        $count->update([
            'physical_stock' => $physicalStock,
            'variance_qty' => $varianceQty,
            'variance_value' => $varianceQty * ($product->selling_price ?? 0),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Count updated',
            'count' => $count->fresh(),
        ]);
    }

    public function editCounts(Request $request, StockOpnameSession $session)
    {
        if (! in_array($session->status, [StockOpnameSession::STATUS_PROSES, StockOpnameSession::STATUS_CETAK_SELISIH, StockOpnameSession::STATUS_EDIT_DATA])) {
            return response()->json(['error' => 'Session cannot edit data in current status'], 400);
        }

        $request->validate([
            'counts' => 'required|array',
            'counts.*.count_id' => 'required|exists:opname_counts,id',
            'counts.*.physical_stock' => 'required|integer|min:0',
        ]);

        $results = [];

        foreach ($request->counts as $countData) {
            $count = OpnameCount::find($countData['count_id']);
            $product = $count->product;
            $systemStock = $count->system_stock;
            $physicalStock = $countData['physical_stock'];
            $varianceQty = $physicalStock - $systemStock;

            $count->update([
                'physical_stock' => $physicalStock,
                'variance_qty' => $varianceQty,
                'variance_value' => $varianceQty * ($product->selling_price ?? 0),
            ]);

            $results[] = $count;
        }

        return response()->json([
            'message' => 'Counts updated',
            'counts' => $results,
        ]);
    }

    public function getProducts(StockOpnameSession $session)
    {
        return response()->json([
            'products' => $session->getProducts(),
            'session' => $session,
        ]);
    }

    public function getUnenteredProducts(StockOpnameSession $session)
    {
        return response()->json([
            'products' => $session->getUnenteredProducts(),
            'pending_count' => $session->getPendingCount(),
        ]);
    }

    public function getEnteredProducts(StockOpnameSession $session)
    {
        return response()->json([
            'counts' => $session->getEnteredProducts(),
            'entered_count' => $session->getEnteredCount(),
        ]);
    }

    public function updateAdjustment(Request $request, OpnameAdjustment $adjustment)
    {
        $request->validate([
            'adjustment_qty' => 'nullable|integer',
            'reason' => 'nullable|string',
            'status' => 'nullable|in:PENDING,APPROVED,REJECTED',
        ]);

        if ($request->has('adjustment_qty')) {
            $adjustment->update(['adjustment_qty' => $request->adjustment_qty]);
        }

        if ($request->has('status')) {
            if ($request->status === 'APPROVED') {
                $adjustment->update([
                    'status' => OpnameAdjustment::STATUS_APPROVED,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
            } elseif ($request->status === 'REJECTED') {
                $adjustment->update([
                    'status' => OpnameAdjustment::STATUS_REJECTED,
                    'rejection_reason' => $request->rejection_reason,
                ]);
            }
        }

        if ($request->has('reason')) {
            $adjustment->update(['reason' => $request->reason]);
        }

        return response()->json([
            'message' => 'Adjustment updated',
            'adjustment' => $adjustment->fresh(),
        ]);
    }

    public function approveAdjustment(Request $request, OpnameAdjustment $adjustment)
    {
        $adjustment->update([
            'status' => OpnameAdjustment::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Adjustment approved',
            'adjustment' => $adjustment->fresh(),
        ]);
    }

    public function rejectAdjustment(Request $request, OpnameAdjustment $adjustment)
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $adjustment->update([
            'status' => OpnameAdjustment::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'message' => 'Adjustment rejected',
            'adjustment' => $adjustment->fresh(),
        ]);
    }
}
