<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->unique()->lexify('SKU-???')),
            'selling_price' => fake()->numberBetween(5000, 100000),
            'cost_price' => fake()->numberBetween(3000, 50000),
            'threshold' => 5,
        ];
    }
}
