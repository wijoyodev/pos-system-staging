<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->product_id;

        DB::transaction(function () use ($productId, $request) {
            $recipe = Recipe::updateOrCreate(
                ['product_id' => $productId],
                ['product_id' => $productId]
            );

            RecipeItem::where('recipe_id', $recipe->id)->delete();

            foreach ($request->items as $item) {
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return back()->with('success', 'Recipe saved successfully!');
    }

    public function getRecipe($productId)
    {
        $product = Product::with('recipe.items.product')->findOrFail($productId);
        $rawMaterials = Product::whereDoesntHave('recipe')
            ->where('id', '!=', $productId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'product' => $product,
            'recipe' => $product->recipe,
            'rawMaterials' => $rawMaterials,
        ]);
    }
}
