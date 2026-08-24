<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Otomatis mereset (menghapus) data penggajian bulan berjalan setiap tanggal 1 awal bulan
// Sehingga data siap mulai dari kosong lagi tanpa menghapus bulan lalu
Schedule::command('penggajian:reset')->monthlyOn(1, '00:00');

// Otomatis mencatat Alpa untuk karyawan yang belum absen di atas jam 15:00
Schedule::command('absensi:mark-alpa')->dailyAt('15:01');
