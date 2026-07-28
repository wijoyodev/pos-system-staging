<?php

namespace App\Http\Controllers;

use App\Models\ShiftAssignment;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $storeId = auth()->user()->store_id;
        $user = auth()->user();

        if ($user->isSuperAdmin() && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        $referenceDate = $request->filled('date')
            ? Carbon::parse($request->date)
            : now();

        $shifts = ShiftSchedule::where('store_id', $storeId)->orderBy('start_time')->get();
        $stores = $user->isSuperAdmin() ? Store::where('status', 'active')->with('branch')->get() : collect();
        $staff = User::whereIn('role', ['kasir', 'admin'])->where('store_id', $storeId)->get();

        return view('pages.shift-schedule', [
            'title' => 'Jadwal Shift',
            'shifts' => $shifts,
            'stores' => $stores,
            'staff' => $staff,
            'currentStoreId' => $storeId,
            'referenceDate' => $referenceDate,
        ]);
    }

    public function store(Request $request)
    {
        $storeId = auth()->user()->store_id;

        if (auth()->user()->isSuperAdmin() && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required',
            'end_time' => 'required',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['store_id'] = $storeId;

        ShiftSchedule::create($validated);

        return back()->with('success', 'Shift berhasil ditambahkan.');
    }

    public function update(Request $request, ShiftSchedule $shift)
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $shift->store_id !== $user->store_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required',
            'end_time' => 'required',
            'color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $shift->update($validated);

        return back()->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(ShiftSchedule $shift)
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $shift->store_id !== $user->store_id) {
            abort(403);
        }

        $shift->delete();

        return back()->with('success', 'Shift berhasil dihapus.');
    }

    public function assignments(Request $request)
    {
        $storeId = auth()->user()->store_id;
        $user = auth()->user();

        if ($user->isSuperAdmin() && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        $date = $request->filled('date') ? $request->date : now()->toDateString();

        $assignments = ShiftAssignment::with(['user', 'shift'])
            ->where('store_id', $storeId)
            ->where('date', $date)
            ->orderBy('shift_id')
            ->get();

        $shifts = ShiftSchedule::where('store_id', $storeId)->where('is_active', true)->get();
        $kasirs = User::where('role', 'kasir')->where('store_id', $storeId)->get();
        $stores = $user->isSuperAdmin() ? Store::where('status', 'active')->with('branch')->get() : collect();

        return view('pages.shift-assignment', [
            'title' => 'Penugasan Shift',
            'assignments' => $assignments,
            'shifts' => $shifts,
            'kasirs' => $kasirs,
            'date' => $date,
            'currentStoreId' => $storeId,
            'stores' => $stores,
        ]);
    }

    public function assign(Request $request)
    {
        $storeId = auth()->user()->store_id;

        if (auth()->user()->isSuperAdmin() && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'nullable|exists:shift_schedules,id',
            'date' => 'required|date',
            'status' => 'nullable|in:scheduled,present,absent,late',
        ]);

        $validated['store_id'] = $storeId;
        $validated['status'] = $validated['status'] ?? 'scheduled';

        $dateOnly = Carbon::parse($validated['date'])->toDateString();
        $validated['date'] = $dateOnly;

        ShiftAssignment::updateOrCreate(
            ['user_id' => $validated['user_id'], 'date' => $dateOnly],
            $validated
        );

        return back()->with('success', 'Penugasan shift berhasil disimpan.');
    }

    public function deleteAssignment(ShiftAssignment $assignment)
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $assignment->store_id !== $user->store_id) {
            abort(403);
        }

        $assignment->delete();

        return back()->with('success', 'Penugasan shift dihapus.');
    }

    public function initDefaultShifts(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return back()->with('error', 'Anda harus login terlebih dahulu.');
        }

        $storeId = $user->store_id;

        if ($user->isSuperAdmin() && $request->filled('store_id')) {
            $storeId = $request->store_id;
        }

        if (! $storeId) {
            return back()->with('error', 'Toko tidak ditemukan. Hubungi super admin.');
        }

        ShiftSchedule::where('store_id', $storeId)->delete();

        $defaults = ShiftSchedule::getDefaults();

        foreach ($defaults as $shift) {
            ShiftSchedule::create([
                'store_id' => $storeId,
                'name' => $shift['name'],
                'start_time' => $shift['start_time'],
                'end_time' => $shift['end_time'],
                'color' => $shift['color'],
                'shift_key' => $shift['shift_key'] ?? null,
            ]);
        }

        return back()->with('success', 'Shift default berhasil dibuat.');
    }
}
