<?php

namespace Database\Seeders;

use App\Models\ShiftSchedule;
use Illuminate\Database\Seeder;

class ShiftScheduleSeeder extends Seeder
{
    public function run(): void
    {
        ShiftSchedule::where('store_id', 1)->delete();

        $shifts = ShiftSchedule::getDefaults();

        foreach ($shifts as $shift) {
            ShiftSchedule::create([
                'store_id' => 1,
                'name' => $shift['name'],
                'start_time' => $shift['start_time'],
                'end_time' => $shift['end_time'],
                'color' => $shift['color'],
                'shift_key' => $shift['shift_key'],
            ]);
        }

        echo 'Created '.count($shifts)." shifts for store_id = 1\n";
    }
}
