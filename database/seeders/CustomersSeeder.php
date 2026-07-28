<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            // Gold tier (total_spent >= 5jt)
            ['name' => 'Ahmad Fauzi', 'phone' => '081234567890', 'email' => 'ahmad.fauzi@email.com', 'total_points' => 50000, 'available_points' => 42000, 'used_points' => 8000, 'tier' => 'gold', 'total_spent' => 8500000],
            ['name' => 'Dewi Sartika', 'phone' => '081234567891', 'email' => 'dewi.sartika@email.com', 'total_points' => 32000, 'available_points' => 30000, 'used_points' => 2000, 'tier' => 'gold', 'total_spent' => 6200000],
            ['name' => 'Hendra Wijaya', 'phone' => '081234567892', 'email' => 'hendra.w@email.com', 'total_points' => 28000, 'available_points' => 25000, 'used_points' => 3000, 'tier' => 'gold', 'total_spent' => 5500000],
            ['name' => 'Rina Marlina', 'phone' => '081234567893', 'email' => 'rina.marlina@email.com', 'total_points' => 45000, 'available_points' => 40000, 'used_points' => 5000, 'tier' => 'gold', 'total_spent' => 7800000],

            // Silver tier (1jt <= total_spent < 5jt)
            ['name' => 'Bambang Suprapto', 'phone' => '081234567894', 'email' => 'bambang.s@email.com', 'total_points' => 15000, 'available_points' => 12000, 'used_points' => 3000, 'tier' => 'silver', 'total_spent' => 2500000],
            ['name' => 'Sari Dewi', 'phone' => '081234567895', 'email' => 'sari.dewi@email.com', 'total_points' => 12000, 'available_points' => 10000, 'used_points' => 2000, 'tier' => 'silver', 'total_spent' => 1800000],
            ['name' => 'Fajar Hidayat', 'phone' => '081234567896', 'email' => 'fajar.h@email.com', 'total_points' => 8000, 'available_points' => 7000, 'used_points' => 1000, 'tier' => 'silver', 'total_spent' => 1500000],
            ['name' => 'Nurul Aini', 'phone' => '081234567897', 'email' => 'nurul.aini@email.com', 'total_points' => 20000, 'available_points' => 15000, 'used_points' => 5000, 'tier' => 'silver', 'total_spent' => 3200000],

            // Bronze tier (total_spent < 1jt)
            ['name' => 'Agus Salim', 'phone' => '081234567898', 'email' => 'agus.salim@email.com', 'total_points' => 2000, 'available_points' => 2000, 'used_points' => 0, 'tier' => 'bronze', 'total_spent' => 350000],
            ['name' => 'Putri Ayu', 'phone' => '081234567899', 'email' => 'putri.ayu@email.com', 'total_points' => 5000, 'available_points' => 4000, 'used_points' => 1000, 'tier' => 'bronze', 'total_spent' => 750000],
            ['name' => 'Dimas Ardianto', 'phone' => '081234567800', 'email' => 'dimas.a@email.com', 'total_points' => 1000, 'available_points' => 1000, 'used_points' => 0, 'tier' => 'bronze', 'total_spent' => 150000],
            ['name' => 'Lisa Permata', 'phone' => '081234567801', 'email' => 'lisa.permata@email.com', 'total_points' => 3000, 'available_points' => 2500, 'used_points' => 500, 'tier' => 'bronze', 'total_spent' => 520000],
        ];

        foreach ($customers as $data) {
            $code = 'MBR-'.strtoupper(substr(md5($data['phone']), 0, 6));
            Customer::firstOrCreate(
                ['phone' => $data['phone']],
                array_merge($data, ['code' => $code])
            );
        }
    }
}
