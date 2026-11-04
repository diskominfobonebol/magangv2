<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pangkat:kirim-pengingat')->dailyAt('08:00');

// Pengingat H-30 Kenaikan Pangkat/KGB In-App (setiap hari jam 08:00)
Schedule::command('notifikasi:kirim-pengingat-jatuh-tempo')->dailyAt('08:00');

// Pembersihan berkala notifikasi usang (setiap hari Minggu jam 02:00)
Schedule::command('notifikasi:bersihkan')->weeklyOn(0, '02:00');
