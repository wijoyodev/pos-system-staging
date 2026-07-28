<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('created_at', 'desc')->get();

        return view('pages.supplier', [
            'title' => 'Manajemen Supplier',
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        Supplier::create($request->only(['name', 'contact_name', 'phone', 'email', 'address']));

        return redirect('/suppliers')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $supplier->update($request->only(['name', 'contact_name', 'phone', 'email', 'address']));

        return redirect('/suppliers')->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->stockIns()->exists()) {
            return redirect('/suppliers')->with('error', 'Supplier tidak dapat dihapus karena memiliki transaksi stok masuk.');
        }

        $supplier->delete();

        return redirect('/suppliers')->with('success', 'Supplier berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:suppliers,id']);

        $suppliers = Supplier::whereIn('id', $request->ids)->get();
        $deleted = 0;

        foreach ($suppliers as $supplier) {
            if (! $supplier->stockIns()->exists()) {
                $supplier->delete();
                $deleted++;
            }
        }

        return redirect('/suppliers')->with('success', $deleted.' supplier berhasil dihapus'.($deleted < count($request->ids) ? ', beberapa dilewati karena memiliki transaksi.' : '.'));
    }
}
