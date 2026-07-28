<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function index()
    {
        $cashiers = collect();

        if (auth()->user()->isAdmin() && auth()->user()->store_id) {
            $cashiers = User::where('role', 'kasir')
                ->where('store_id', auth()->user()->store_id)
                ->orderBy('name')
                ->get();
        }

        return view('pages.setting', [
            'title' => 'User Settings',
            'cashiers' => $cashiers,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.auth()->id(),
            'password_current' => 'nullable|string',
            'password_new' => 'nullable|string|min:6|same:password_confirm',
            'password_confirm' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Update name & email
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Handle password change
        if ($request->filled('password_current') && $request->filled('password_new')) {
            if (! Hash::check($request->password_current, $user->password)) {
                throw ValidationException::withMessages([
                    'password_current' => 'Password saat ini tidak sesuai.',
                ]);
            }
            $user->update(['password' => Hash::make($request->password_new)]);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function updateCashier(Request $request, User $cashier)
    {
        $admin = auth()->user();

        if (! $admin->isAdmin() || ! $admin->store_id) {
            abort(403);
        }

        if ($cashier->role !== 'kasir' || $cashier->store_id !== $admin->store_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$cashier->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $cashier->update($data);

        return back()->with('success', 'Data kasir berhasil diperbarui.');
    }
}
