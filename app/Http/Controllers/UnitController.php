<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        return response()->json(Unit::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
        ]);

        $unit = Unit::create($data);

        if ($request->isJson()) {
            return response()->json(['success' => true, 'message' => 'Satuan "'.$unit->name.'" berhasil ditambahkan.']);
        }

        return back()->with('success', 'Satuan "'.$unit->name.'" berhasil ditambahkan.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return back()->with('success', 'Satuan berhasil dihapus.');
    }
}
