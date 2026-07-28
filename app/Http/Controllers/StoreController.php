<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required',
            'code' => 'required|unique:stores,code',
        ]);

        Store::create($request->all());

        return back()->with('success', 'Toko berhasil ditambahkan');
    }

    public function update(Request $request, Store $store)
    {
        $store->update($request->only(['branch_id', 'name', 'status']));

        return back()->with('success', 'Toko diperbarui');
    }

    public function destroy(Store $store)
    {
        if ($store->users()->count() > 0) {
            return back()->withErrors(['message' => 'Toko memiliki user, hapus user terlebih dahulu']);
        }
        $store->delete();

        return back()->with('success', 'Toko dihapus');
    }

    public function assignUser(Request $request, Store $store)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::find($request->user_id);
        $user->store_id = $store->id;
        $user->save();

        return back()->with('success', 'User ditambahkan ke toko');
    }

    public function toggleStatus(Store $store)
    {
        $store->status = $store->status === 'active' ? 'inactive' : 'active';
        $store->save();

        return response()->json([
            'success' => true,
            'status' => $store->status,
            'message' => 'Status toko berhasil diubah',
        ]);
    }
}
