<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $storeId = auth()->user()->store_id;

        $query = Promotion::with(['product', 'category', 'user']);

        if ($storeId) {
            $query->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $promotions = $query->orderBy('priority', 'desc')->get();
        $types = Promotion::getTypeOptions();

        return view('pages/promotion', [
            'title' => 'Promotions',
            'promotions' => $promotions,
            'types' => $types,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:promotions,code',
            'description' => 'nullable|string',
            'type' => 'required|in:'.implode(',', array_keys(Promotion::getTypeOptions())),
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_nominal' => 'nullable|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'voucher_threshold' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'buy_product_id' => 'nullable|exists:products,id',
            'get_product_id' => 'nullable|exists:products,id',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'bundle_price' => 'nullable|numeric|min:0',
            'products' => 'nullable|array',
            'eligibleRoles' => 'nullable|array',
            'day_of_week' => 'nullable|string|max:20',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'stackable' => 'boolean',
        ]);

        if (auth()->user()->store_id) {
            $validated['store_id'] = auth()->user()->store_id;
        }
        $validated['user_id'] = auth()->id();

        // Handle JSON fields if they are strings
        if ($request->has('tiers') && is_string($request->tiers)) {
            $validated['tiers'] = json_decode($request->tiers, true);
        }
        if ($request->has('eligibleRoles') && is_string($request->eligibleRoles)) {
            $validated['eligibleRoles'] = json_decode($request->eligibleRoles, true);
        }
        if ($request->has('products') && is_string($request->products)) {
            $validated['products'] = array_map('intval', json_decode($request->products, true));
        }
        if ($request->has('products') && is_array($request->products)) {
            $validated['products'] = array_map('intval', $request->products);
        }

        Promotion::create($validated);

        return back()->with('success', 'Promotion created successfully');
    }

    public function show(Promotion $promotion)
    {
        return response()->json(['promotion' => $promotion]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:promotions,code,'.$promotion->id,
            'description' => 'nullable|string',
            'type' => 'required|in:'.implode(',', array_keys(Promotion::getTypeOptions())),
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_nominal' => 'nullable|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'voucher_threshold' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'buy_product_id' => 'nullable|exists:products,id',
            'get_product_id' => 'nullable|exists:products,id',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'bundle_price' => 'nullable|numeric|min:0',
            'products' => 'nullable|array',
            'eligibleRoles' => 'nullable|array',
            'day_of_week' => 'nullable|string|max:20',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'stackable' => 'boolean',
        ]);

        // Handle JSON fields if they are strings
        if ($request->has('tiers') && is_string($request->tiers)) {
            $validated['tiers'] = json_decode($request->tiers, true);
        }
        if ($request->has('eligibleRoles') && is_string($request->eligibleRoles)) {
            $validated['eligibleRoles'] = json_decode($request->eligibleRoles, true);
        }
        if ($request->has('products') && is_string($request->products)) {
            $validated['products'] = array_map('intval', json_decode($request->products, true));
        }
        if ($request->has('products') && is_array($request->products)) {
            $validated['products'] = array_map('intval', $request->products);
        }

        $promotion->update($validated);

        return back()->with('success', 'Promotion updated successfully');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return back()->with('success', 'Promotion deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:promotions,id']);
        $count = Promotion::whereIn('id', $request->ids)->delete();

        return back()->with('success', $count.' promosi berhasil dihapus.');
    }

    public function toggle(Promotion $promotion)
    {
        $promotion->update(['is_active' => ! $promotion->is_active]);

        return back()->with('success', 'Promotion status updated');
    }

    public function getProducts(Request $request)
    {
        $search = $request->q ?? '';

        $query = Product::query();

        // Get all products if no search, limited to 50
        if (empty($search)) {
            $products = $query->limit(50)->get(['id', 'name', 'sku', 'selling_price']);
        } else {
            $products = $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('sku', 'like', '%'.$search.'%')
                ->limit(30)->get(['id', 'name', 'sku', 'selling_price']);
        }

        return response()->json(['products' => $products]);
    }

    public function getCategories()
    {
        $categories = Category::all(['id', 'name']);

        return response()->json(['categories' => $categories]);
    }
}
