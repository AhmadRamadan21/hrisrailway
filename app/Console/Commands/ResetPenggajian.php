<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penggajian;

class ResetPenggajian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'penggajian:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset penggajian (menghapus data) otomatis pada awal bulan berjalan tanpa menyentuh bulan sebelumnya';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = now()->month;
        $year = now()->year;

        // Reset data penggajian di bulan berjalan
        // Data bulan-bulan sebelumnya tetap aman
        $deleted = Penggajian::where('bulan', $month)
            ->where('tahun', $year)
            ->delete();

        $this->info("Reset Penggajian untuk {$month}/{$year} berhasil dijalankan. {$deleted} data terhapus.");
    }
}
