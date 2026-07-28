<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->with('store')->orderBy('created_at', 'desc')->get();
        $stores = Store::where('status', 'active')->with('branch')->get();

        return view('pages.user', [
            'title' => 'User Management',
            'users' => $users,
            'stores' => $stores,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['kasir', 'admin', 'super_admin'])],
            'store_id' => ['nullable', 'exists:stores,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email ?? '',
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'store_id' => $request->role === 'super_admin' ? null : $request->store_id,
        ];

        $user = User::create($data);

        if (empty($user->nik)) {
            $user->nik = User::generateNik();
            $user->save();
        }

        return redirect('/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['kasir', 'admin', 'super_admin'])],
            'password' => ['nullable', 'string', 'min:6'],
            'store_id' => ['nullable', 'exists:stores,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'store_id' => $request->role === 'super_admin' ? null : $request->store_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect('/users')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect('/users')->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect('/users')->with('success', 'User berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:users,id']);

        $count = User::whereIn('id', $request->ids)
            ->where('id', '!=', auth()->id())
            ->delete();

        $skipped = count($request->ids) - $count;
        $msg = $count.' user berhasil dihapus.';
        if ($skipped > 0) {
            $msg .= ' '.$skipped.' user dilewati (termasuk akun sendiri).';
        }

        return redirect('/users')->with('success', $msg);
    }
}
