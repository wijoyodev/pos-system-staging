<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->company(),
            'code' => strtoupper(fake()->lexify('???-??')),
            'status' => 'active',
        ];
    }
}
