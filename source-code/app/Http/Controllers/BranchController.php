<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('stores')->get();

        return view('pages.branch', [
            'title' => 'Kelola Cabang',
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'city' => 'nullable']);

        Branch::create([
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
        ]);

        return back()->with('success', 'Cabang berhasil ditambahkan');
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($request->only(['name', 'address', 'city']));

        return back()->with('success', 'Cabang diperbarui');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->stores()->count() > 0) {
            return back()->withErrors(['message' => 'Cabang memiliki toko, hapus toko terlebih dahulu']);
        }
        $branch->delete();

        return back()->with('success', 'Cabang dihapus');
    }
}
