<?php

namespace App\Http\Controllers;

use App\Models\ShiftAssignment;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    const MAX_DISTANCE_METERS = 100;

    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect('/dashboard');
        }

        $today = now()->format('Y-m-d');
        $storeId = $user->store_id;

        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($assignment && $assignment->check_in && $assignment->shift) {
            $startHour = (int) Carbon::parse($assignment->shift->start_time)->format('H');
            $startMinute = (int) Carbon::parse($assignment->shift->start_time)->format('i');
            $startTime = Carbon::today()->setTime($startHour, $startMinute, 0);
            $checkInTime = Carbon::today()->setTime(
                (int) Carbon::parse($assignment->check_in)->format('H'),
                (int) Carbon::parse($assignment->check_in)->format('i'),
                0
            );

            $correctStatus = $checkInTime->gt($startTime) ? 'late' : 'present';

            if ($assignment->status !== $correctStatus) {
                $assignment->update(['status' => $correctStatus]);
            }
        }

        $qrData = json_encode([
            'store_id' => $storeId,
            'date' => $today,
        ]);

        return view('pages.presensi', [
            'title' => 'Presensi',
            'assignment' => $assignment ?? null,
            'qrData' => $qrData,
            'today' => $today,
        ]);
    }

    public function presensiPage()
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        $stores = Store::where('status', 'active')->with('branch')->get();

        $history = collect([]);
        if ($user && ! $user->isSuperAdmin()) {
            $history = ShiftAssignment::with('shift')
                ->where('user_id', $user->id)
                ->whereDate('date', '<=', $today)
                ->whereDate('date', '>=', now()->subDays(7)->format('Y-m-d'))
                ->orderByDesc('date')
                ->get();

            foreach ($history as $h) {
                if ($h->check_in && $h->shift) {
                    $startHour = (int) Carbon::parse($h->shift->start_time)->format('H');
                    $startMinute = (int) Carbon::parse($h->shift->start_time)->format('i');
                    $startTime = Carbon::today()->setTime($startHour, $startMinute, 0);
                    $checkInTime = Carbon::today()->setTime(
                        (int) Carbon::parse($h->check_in)->format('H'),
                        (int) Carbon::parse($h->check_in)->format('i'),
                        0
                    );

                    $correctStatus = $checkInTime->gt($startTime) ? 'late' : 'present';

                    if ($h->status !== $correctStatus) {
                        $h->update(['status' => $correctStatus]);
                    }
                }
            }
        }

        return view('pages.presensi-page', [
            'title' => 'Presensi',
            'stores' => $stores,
            'today' => $today,
            'history' => $history,
        ]);
    }

    public function getPresensiData(Request $request)
    {
        $storeId = $request->store_id;
        $today = now()->format('Y-m-d');

        $assignments = ShiftAssignment::with(['user', 'shift'])
            ->where('store_id', $storeId)
            ->whereDate('date', $today)
            ->orderBy('shift_id')
            ->get();

        $data = $assignments->map(function ($a) {
            if ($a->check_in && $a->shift) {
                $startHour = (int) Carbon::parse($a->shift->start_time)->format('H');
                $startMinute = (int) Carbon::parse($a->shift->start_time)->format('i');
                $startTime = Carbon::today()->setTime($startHour, $startMinute, 0);
                $checkInTime = Carbon::today()->setTime(
                    (int) Carbon::parse($a->check_in)->format('H'),
                    (int) Carbon::parse($a->check_in)->format('i'),
                    0
                );

                $correctStatus = $checkInTime->gt($startTime) ? 'late' : 'present';

                if ($a->status !== $correctStatus) {
                    $a->update(['status' => $correctStatus]);
                }

                $a->status = $correctStatus;
            }

            return [
                'user_name' => $a->user->name,
                'shift_name' => $a->shift->name ?? null,
                'shift_start_time' => $a->shift ? Carbon::parse($a->shift->start_time)->format('H:i') : null,
                'check_in' => $a->check_in,
                'check_out' => $a->check_out,
                'status' => $a->status,
            ];
        });

        return response()->json([
            'assignments' => $data,
            'total' => $assignments->count(),
            'hadir' => $assignments->whereIn('status', ['present', 'late'])->count(),
        ]);
    }

    public function qr()
    {
        $user = Auth::user();
        $storeId = $user->store_id;
        $today = now()->format('Y-m-d');

        $assignments = ShiftAssignment::with(['user', 'shift'])
            ->where('store_id', $storeId)
            ->whereDate('date', $today)
            ->orderBy('shift_id')
            ->get();

        foreach ($assignments as $a) {
            if ($a->check_in && $a->shift) {
                $startHour = (int) Carbon::parse($a->shift->start_time)->format('H');
                $startMinute = (int) Carbon::parse($a->shift->start_time)->format('i');
                $startTime = Carbon::today()->setTime($startHour, $startMinute, 0);
                $checkInTime = Carbon::today()->setTime(
                    (int) Carbon::parse($a->check_in)->format('H'),
                    (int) Carbon::parse($a->check_in)->format('i'),
                    0
                );

                $correctStatus = $checkInTime->gt($startTime) ? 'late' : 'present';

                if ($a->status !== $correctStatus) {
                    $a->update(['status' => $correctStatus]);
                }
            }
        }

        $store = Store::with('branch')->find($storeId);

        return view('pages.presensi-qr', [
            'title' => 'QR Presensi',
            'storeId' => $storeId,
            'store' => $store,
            'assignments' => $assignments,
            'today' => $today,
        ]);
    }

    public function presensi(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');
        $now = now();
        $storeId = $user->store_id;

        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $assignment || ! $assignment->shift) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Belum ada jadwal shift hari ini.']);
            }

            return back()->with('error', 'Belum ada jadwal shift hari ini.');
        }

        if ($assignment->check_in) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen hari ini.']);
            }

            return back()->with('error', 'Anda sudah absen hari ini.');
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($lat && $lng) {
            $store = Store::find($storeId);
            if ($store && $store->latitude && $store->longitude) {
                $distance = $store->distanceFrom($lat, $lng);
                if ($distance > self::MAX_DISTANCE_METERS) {
                    $message = 'Anda berada di luar area presensi. Jarak: '.round($distance).'m. Maksimal: '.self::MAX_DISTANCE_METERS.'m';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message]);
                    }

                    return back()->with('error', $message);
                }
            }
        }

        $shift = $assignment->shift;
        $startHour = (int) Carbon::parse($shift->start_time)->format('H');
        $startMinute = (int) Carbon::parse($shift->start_time)->format('i');
        $startTime = Carbon::today()->setTime($startHour, $startMinute, 0);
        $checkInTime = now();

        if ($checkInTime->gt($startTime)) {
            $status = 'late';
        } else {
            $status = 'present';
        }

        $assignment->update([
            'check_in' => $checkInTime->format('H:i:s'),
            'status' => $status,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $status, 'message' => 'Presensi berhasil! Status: '.$status]);
        }

        return back()->with('success', 'Presensi berhasil! Status: '.$status);
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');
        $now = now();

        $assignment = ShiftAssignment::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $assignment) {
            return back()->with('error', 'Belum ada jadwal shift hari ini.');
        }

        if (! $assignment->check_in) {
            return back()->with('error', 'Anda belum absen masuk.');
        }

        if ($assignment->check_out) {
            return back()->with('error', 'Anda sudah absen pulang.');
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($lat && $lng) {
            $store = Store::find($user->store_id);
            if ($store && $store->latitude && $store->longitude) {
                $distance = $store->distanceFrom($lat, $lng);
                if ($distance > self::MAX_DISTANCE_METERS) {
                    $message = 'Anda berada di luar area presensi. Jarak: '.round($distance).'m. Maksimal: '.self::MAX_DISTANCE_METERS.'m';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message]);
                    }

                    return back()->with('error', $message);
                }
            }
        }

        $assignment->update([
            'check_out' => $now->format('H:i:s'),
        ]);

        return back()->with('success', 'Presensi pulang berhasil!');
    }

    public function checkStatus(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($assignment && $assignment->check_in && $assignment->shift) {
            $startHour = (int) Carbon::parse($assignment->shift->start_time)->format('H');
            $startMinute = (int) Carbon::parse($assignment->shift->start_time)->format('i');
            $startTime = Carbon::today()->setTime($startHour, $startMinute, 0);
            $checkInTime = Carbon::today()->setTime(
                (int) Carbon::parse($assignment->check_in)->format('H'),
                (int) Carbon::parse($assignment->check_in)->format('i'),
                0
            );

            $correctStatus = $checkInTime->gt($startTime) ? 'late' : 'present';

            if ($assignment->status !== $correctStatus) {
                $assignment->update(['status' => $correctStatus]);
            }
        }

        return response()->json([
            'has_presensi' => $assignment && $assignment->check_in,
            'assignment' => $assignment,
        ]);
    }

    public function scan(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'store_id' => 'required|integer',
            'date' => 'required|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $storeId = $request->store_id;
        $date = Carbon::parse($request->date)->format('Y-m-d');
        $today = now()->format('Y-m-d');

        if ($date !== $today) {
            return response()->json(['success' => false, 'message' => 'QR hanya berlaku hari ini']);
        }

        if ($user->store_id != $storeId) {
            return response()->json(['success' => false, 'message' => 'QR tidak untuk toko ini']);
        }

        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $assignment) {
            return response()->json(['success' => false, 'message' => 'Belum ada jadwal shift hari ini']);
        }

        if ($assignment->check_in) {
            return response()->json(['success' => false, 'message' => 'Sudah presensi']);
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($lat && $lng) {
            $store = Store::find($storeId);
            if ($store && $store->latitude && $store->longitude) {
                $distance = $store->distanceFrom($lat, $lng);
                if ($distance > self::MAX_DISTANCE_METERS) {
                    return response()->json(['success' => false, 'message' => 'Anda berada di luar area presensi. Jarak: '.round($distance).'m. Maksimal: '.self::MAX_DISTANCE_METERS.'m']);
                }
            }
        }

        $now = now();
        $shift = $assignment->shift;
        $startHour = (int) Carbon::parse($shift->start_time)->format('H');
        $startMinute = (int) Carbon::parse($shift->start_time)->format('i');
        $startTime = Carbon::today()->setTime($startHour, $startMinute, 0);

        $status = $now->gt($startTime) ? 'late' : 'present';

        $assignment->update([
            'check_in' => $now->format('H:i:s'),
            'status' => $status,
        ]);

        return response()->json(['success' => true, 'message' => 'Presensi berhasil! Status: '.$status]);
    }

    public function scanPulang(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'store_id' => 'required|integer',
            'date' => 'required|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $storeId = $request->store_id;
        $date = Carbon::parse($request->date)->format('Y-m-d');
        $today = now()->format('Y-m-d');

        if ($date !== $today) {
            return response()->json(['success' => false, 'message' => 'QR hanya berlaku hari ini']);
        }

        if ($user->store_id != $storeId) {
            return response()->json(['success' => false, 'message' => 'QR tidak untuk toko ini']);
        }

        $assignment = ShiftAssignment::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $assignment) {
            return response()->json(['success' => false, 'message' => 'Belum ada jadwal shift hari ini']);
        }

        if (! $assignment->check_in) {
            return response()->json(['success' => false, 'message' => 'Belum absen masuk']);
        }

        if ($assignment->check_out) {
            return response()->json(['success' => false, 'message' => 'Sudah absen pulang']);
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($lat && $lng) {
            $store = Store::find($storeId);
            if ($store && $store->latitude && $store->longitude) {
                $distance = $store->distanceFrom($lat, $lng);
                if ($distance > self::MAX_DISTANCE_METERS) {
                    return response()->json(['success' => false, 'message' => 'Anda berada di luar area presensi. Jarak: '.round($distance).'m. Maksimal: '.self::MAX_DISTANCE_METERS.'m']);
                }
            }
        }

        $assignment->update([
            'check_out' => now()->format('H:i:s'),
        ]);

        return response()->json(['success' => true, 'message' => 'Presensi pulang berhasil!']);
    }
}
