<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
|
| End-of-day attendance processing commands.
| (Requirements 12.1, 12.2)
|
*/

// Mark teachers without any attendance record as absent at 17:30 daily
// (Requirements 12.1, 12.3)
Schedule::command('attendance:mark-absent-teachers')->dailyAt('17:30');

// Mark teachers with pending status as no_scan at 18:00 daily
// (Requirement 12.2)
Schedule::command('attendance:mark-no-scan-teachers')->dailyAt('18:00');
