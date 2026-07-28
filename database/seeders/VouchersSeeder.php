<?php

namespace Database\Seeders;

use App\Models\History;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VouchersSeeder extends Seeder
{
    public function run(): void
    {
        $histories = History::all();
        $firstHistory = $histories->first();
        $lastHistory = $histories->last();

        // 5 unused vouchers
        for ($i = 1; $i <= 5; $i++) {
            $code = 'VCH-UNUSED-'.str_pad($i, 2, '0', STR_PAD_LEFT);
            Voucher::firstOrCreate(['code' => $code], [
                'discount_amount' => [5000, 10000, 15000, 10000, 5000][$i - 1],
                'is_used' => false,
                'generated_by_history_id' => $histories->random()->id,
                'used_by_history_id' => null,
            ]);
        }

        // 5 used vouchers — link to two different random histories
        $usedHistories = $histories->random(min(10, $histories->count()));
        $usedHistories = $usedHistories->chunk(2);

        for ($i = 1; $i <= 5; $i++) {
            $code = 'VCH-USED-'.str_pad($i, 2, '0', STR_PAD_LEFT);
            $pair = $usedHistories->get($i - 1) ?? $usedHistories->first();
            $genHistory = $pair[0] ?? $firstHistory;
            $useHistory = $pair[1] ?? $lastHistory;

            Voucher::firstOrCreate(['code' => $code], [
                'discount_amount' => [10000, 15000, 5000, 10000, 20000][$i - 1],
                'is_used' => true,
                'generated_by_history_id' => $genHistory->id,
                'used_by_history_id' => $useHistory->id,
            ]);
        }
    }
}
