<?php

use App\Models\Branch;
use App\Models\Category;
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
    $this->superAdmin = User::factory()->create([
        'role' => 'super_admin',
        'store_id' => null,
    ]);
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'store_id' => $store->id,
    ]);
});

test('admin can view categories page', function () {
    Category::factory()->count(3)->create();
    $response = $this->actingAs($this->admin)->get('/category');
    $response->assertStatus(200);
});

test('super admin can create a category', function () {
    $response = $this->actingAs($this->superAdmin)->post('/category', [
        'name' => 'Minuman',
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('categories', ['name' => 'Minuman']);
});

test('admin cannot create a category', function () {
    $response = $this->actingAs($this->admin)->post('/category', [
        'name' => 'Minuman',
    ]);
    $response->assertStatus(403);
});

test('category name must be unique', function () {
    Category::factory()->create(['name' => 'Makanan']);
    $response = $this->actingAs($this->superAdmin)->post('/category', [
        'name' => 'Makanan',
    ]);
    $response->assertSessionHasErrors('name');
});

test('super admin can delete a category', function () {
    $category = Category::factory()->create(['name' => 'Minuman']);
    $response = $this->actingAs($this->superAdmin)->delete('/category/'.$category->id);
    $response->assertStatus(302);
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('admin cannot delete a category', function () {
    $category = Category::factory()->create();
    $response = $this->actingAs($this->admin)->delete('/category/'.$category->id);
    $response->assertStatus(403);
});

test('categories page shows all categories', function () {
    Category::factory()->create(['name' => 'Makanan']);
    Category::factory()->create(['name' => 'Minuman']);
    $response = $this->actingAs($this->admin)->get('/category');
    $response->assertSee('Makanan');
    $response->assertSee('Minuman');
});

test('unauthenticated user cannot access categories', function () {
    $response = $this->get('/category');
    $response->assertRedirect('/login');
});
