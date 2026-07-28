<?php

use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $branch = Branch::factory()->create(['name' => 'Test Branch']);
    $store = Store::factory()->create([
        'branch_id' => $branch->id,
        'name' => 'Test Store',
        'code' => 'TST-01',
        'status' => 'active',
    ]);
    $this->superAdmin = User::factory()->create([
        'name' => 'Super Admin',
        'nik' => '26050001',
        'role' => 'super_admin',
        'store_id' => null,
        'password' => bcrypt('password'),
    ]);
    $this->admin = User::factory()->create([
        'name' => 'Admin',
        'nik' => '26050002',
        'role' => 'admin',
        'store_id' => $store->id,
        'password' => bcrypt('password'),
    ]);
    $this->kasir = User::factory()->create([
        'name' => 'Kasir',
        'nik' => '26050005',
        'role' => 'kasir',
        'store_id' => $store->id,
        'password' => bcrypt('password'),
    ]);
});

test('login page is accessible', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('super admin can login with correct credentials', function () {
    $response = $this->post('/login', [
        'nik' => '26050001',
        'password' => 'password',
    ]);
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

test('admin can login with correct credentials', function () {
    $response = $this->post('/login', [
        'nik' => '26050002',
        'password' => 'password',
    ]);
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

test('kasir can login with correct credentials', function () {
    $response = $this->post('/login', [
        'nik' => '26050005',
        'password' => 'password',
    ]);
    $response->assertRedirect('/');
    $this->assertAuthenticated();
});

test('login fails with wrong password', function () {
    $response = $this->post('/login', [
        'nik' => '26050001',
        'password' => 'wrong-password',
    ]);
    $response->assertSessionHasErrors('nik');
    $this->assertGuest();
});

test('login fails with non-existent nik', function () {
    $response = $this->post('/login', [
        'nik' => '99999999',
        'password' => 'password',
    ]);
    $response->assertSessionHasErrors('nik');
    $this->assertGuest();
});

test('authenticated user can logout', function () {
    $this->actingAs($this->superAdmin);
    $response = $this->post('/logout');
    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('admin without store_id cannot login', function () {
    User::factory()->create([
        'nik' => '26050099',
        'role' => 'admin',
        'store_id' => null,
        'password' => bcrypt('password'),
    ]);
    $response = $this->post('/login', [
        'nik' => '26050099',
        'password' => 'password',
    ]);
    $response->assertSessionHasErrors('nik');
});

test('guest is redirected to login', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});
