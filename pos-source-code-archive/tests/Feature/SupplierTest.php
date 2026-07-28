<?php

use App\Models\Branch;
use App\Models\Store;
use App\Models\Supplier;
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
});

test('admin can view suppliers page', function () {
    Supplier::factory()->count(3)->create();
    $response = $this->actingAs($this->admin)->get('/suppliers');
    $response->assertStatus(200);
});

test('admin can create a supplier', function () {
    $response = $this->actingAs($this->admin)->post('/suppliers', [
        'name' => 'PT Sumber Makmur',
        'contact_name' => 'Budi',
        'phone' => '08123456789',
        'email' => 'budi@example.com',
        'address' => 'Jl. Merdeka No. 1',
    ]);
    $response->assertRedirect('/suppliers');
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('suppliers', ['name' => 'PT Sumber Makmur']);
});

test('admin can update a supplier', function () {
    $supplier = Supplier::factory()->create(['name' => 'PT Lama']);
    $response = $this->actingAs($this->admin)->put('/suppliers/'.$supplier->id, [
        'name' => 'PT Baru',
    ]);
    $response->assertRedirect('/suppliers');
    $this->assertDatabaseHas('suppliers', ['name' => 'PT Baru']);
});

test('admin can delete a supplier without stock entries', function () {
    $supplier = Supplier::factory()->create();
    $response = $this->actingAs($this->admin)->delete('/suppliers/'.$supplier->id);
    $response->assertRedirect('/suppliers');
    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});

test('supplier name is required', function () {
    $response = $this->actingAs($this->admin)->post('/suppliers', [
        'name' => '',
    ]);
    $response->assertSessionHasErrors('name');
});

test('admin can search suppliers', function () {
    Supplier::factory()->create(['name' => 'PT ABC']);
    Supplier::factory()->create(['name' => 'PT XYZ']);
    $response = $this->actingAs($this->admin)->get('/suppliers?search=ABC');
    $response->assertSee('PT ABC');
    $response->assertDontSee('PT XYZ');
});
