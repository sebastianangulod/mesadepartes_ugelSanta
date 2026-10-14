<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Vencimientos automáticos cada madrugada (hora de Lima).
// Requiere cron en el servidor: * * * * * php /ruta/artisan schedule:run >> /dev/null 2>&1
Schedule::command('tramites:vencer-automaticos')->dailyAt('01:00')->timezone('America/Lima')->withoutOverlapping();
