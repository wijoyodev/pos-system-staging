<?php

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $branch = Branch::factory()->create();
    $store = Store::factory()->create([
        'branch_id' => $branch->id,
        'status' => 'active',
    ]);
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'store_id' => $store->id,
    ]);
    $this->superAdmin = User::factory()->create([
        'role' => 'super_admin',
        'store_id' => null,
    ]);
    $category = Category::factory()->create(['name' => 'Makanan']);
    Product::factory()->create([
        'name' => 'Nasi Goreng',
        'category_id' => $category->id,
        'selling_price' => 15000,
        'sku' => 'SKU-001',
    ]);
    Product::factory()->create([
        'name' => 'Mie Goreng',
        'category_id' => $category->id,
        'selling_price' => 12000,
        'sku' => 'SKU-002',
    ]);
});

test('admin can view products page', function () {
    $response = $this->actingAs($this->admin)->get('/product');
    $response->assertStatus(200);
    $response->assertSee('Nasi Goreng');
    $response->assertSee('Mie Goreng');
});

test('admin can search products', function () {
    $response = $this->actingAs($this->admin)->get('/product?search=Nasi');
    $response->assertSee('Nasi Goreng');
    $response->assertDontSee('Mie Goreng');
});

test('guest cannot view products page', function () {
    $response = $this->get('/product');
    $response->assertRedirect('/login');
});
