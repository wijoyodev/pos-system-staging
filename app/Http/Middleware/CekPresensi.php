<?php

namespace App\Http\Middleware;

use App\Models\ShiftAssignment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekPresensi
{
    protected $except = [
        'presensi',
        'presensi/*',
        'presensi-page',
        'presensi-page/*',
        '/logout',
        '/login',
        '/dashboard',
        '/shift-schedule',
        '/shift-schedule/*',
        '/shift-assignment',
        '/shift-assignment/*',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $path = $request->path();

        $exceptPaths = [
            'presensi',
            'presensi-page',
            'logout',
            'login',
            'dashboard',
            'shift-schedule',
            'shift-assignment',
        ];

        foreach ($exceptPaths as $pattern) {
            if (str_starts_with($path, $pattern)) {
                return $next($request);
            }
        }

        $today = now()->format('Y-m-d');

        $assignment = ShiftAssignment::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($assignment && ! $assignment->check_in) {
            return redirect('/presensi-page');
        }

        return $next($request);
    }
}
