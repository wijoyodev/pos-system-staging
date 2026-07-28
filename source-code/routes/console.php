<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:create --no-sync')->dailyAt('02:00');
Schedule::command('backup:create --no-sync')->weeklyOn(7, '03:00');
