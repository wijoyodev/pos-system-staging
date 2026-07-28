<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Seeder;

class BranchStoreSeeder extends Seeder
{
    public function run(): void
    {
        // Create branches (idempotent)
        $jakarta = Branch::firstOrCreate(['name' => 'Jakarta Pusat'], ['city' => 'Jakarta', 'address' => 'Jl. Sudirman No. 1']);
        $bandung = Branch::firstOrCreate(['name' => 'Bandung'], ['city' => 'Bandung', 'address' => 'Jl. Braga No. 15']);
        $surabaya = Branch::firstOrCreate(['name' => 'Surabaya'], ['city' => 'Surabaya', 'address' => 'Jl. Basuki Rahmat No. 20']);

        // Create stores (idempotent)
        Store::firstOrCreate(['code' => 'JKT001'], ['branch_id' => $jakarta->id, 'name' => 'Toko Jakarta 1', 'status' => 'active']);
        Store::firstOrCreate(['code' => 'JKT002'], ['branch_id' => $jakarta->id, 'name' => 'Toko Jakarta 2', 'status' => 'active']);
        Store::firstOrCreate(['code' => 'BDG001'], ['branch_id' => $bandung->id, 'name' => 'Toko Bandung 1', 'status' => 'active']);
        Store::firstOrCreate(['code' => 'SBY001'], ['branch_id' => $surabaya->id, 'name' => 'Toko Surabaya 1', 'status' => 'active']);
    }
}
