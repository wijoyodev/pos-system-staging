<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        $units = Unit::all();

        return view('pages/category', [
            'title' => 'Kategori & Satuan',
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->whereNull('deleted_at')],
        ]);
        Category::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:categories,id']);
        $count = Category::whereIn('id', $request->ids)->delete();

        return back()->with('success', $count.' kategori berhasil dihapus.');
    }
}
