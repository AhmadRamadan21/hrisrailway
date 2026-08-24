<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MarkAbsensiAlpa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absensi:mark-alpa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menandai Alpa bagi karyawan yang belum absen hari ini setelah jam 15:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->info("Menjalankan pengecekan Absensi Alpa untuk tanggal: " . $today->format('Y-m-d'));
        
        $karyawans = Karyawan::all();
        $countAlpa = 0;

        foreach ($karyawans as $karyawan) {
            $absensi = Absensi::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', $today)
                ->first();
                
            if (!$absensi) {
                Absensi::create([
                    'karyawan_id' => $karyawan->id,
                    'tanggal' => $today,
                    'status' => 'Alpa',
                    'masuk' => null,
                    'keluar' => null,
                    'distance' => null
                ]);
                $countAlpa++;
            }
        }

        $this->info("Pengecekan selesai. {$countAlpa} karyawan telah ditandai Alpa otomatis.");
        Log::info("Command absensi:mark-alpa dijalankan. {$countAlpa} karyawan ditandai Alpa pada " . $today->format('Y-m-d'));
    }
}
