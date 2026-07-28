<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginUI()
    {
        return view('pages.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'nik' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('nik', $request->nik)->first();

        if ($user && \Hash::check($request->password, $user->password)) {
            // Check if user has store assigned (except super_admin)
            if (! $user->isSuperAdmin() && ! $user->store_id) {
                return back()->withErrors([
                    'nik' => 'User belum ditambahkan ke toko. Hubungi super admin.',
                ])->onlyInput('nik');
            }

            // Check if store is active
            if ($user->store && $user->store->status !== 'active') {
                return back()->withErrors([
                    'nik' => 'Toko Anda sedang tidak aktif. Hubungi manager.',
                ])->onlyInput('nik');
            }

            // Check if user has already closed today
            $today = now()->format('Y-m-d');
            $hasClosed = Closing::where('user_id', $user->id)
                ->whereDate('closing_date', $today)
                ->where('status', '!=', 'rejected')
                ->exists();

            if ($hasClosed && ! $user->hasMinRole('admin')) {
                return back()->withErrors([
                    'nik' => 'Anda sudah melakukan clerek hari ini. Akses masuk dibatasi sampai shift berikutnya.',
                ])->onlyInput('nik');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($user->isKasir()) {
                return redirect()->intended('/');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'nik' => 'NIK atau password salah.',
        ])->onlyInput('nik');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
