<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BranchStoreSeeder::class,
            UserSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            CustomersSeeder::class,
            PromotionsSeeder::class,
            HistorySeeder::class,
            VouchersSeeder::class,
        ]);
    }
}
