<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily summary at 9 PM
Schedule::command('telegram:daily-summary')->dailyAt('21:00');

// Schedule monthly summary on the last day of each month at 11 PM
Schedule::command('telegram:monthly-summary')->monthlyOn(Carbon\Carbon::now()->endOfMonth()->day, '23:00');

// Schedule yearly summary on December 31st at 11:59 PM
Schedule::command('telegram:yearly-summary')->yearlyOn(12, 31, '23:59');
