<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penggajian extends Model
{
    protected $table = 'penggajians';

    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'gaji_pokok',
        'makan_transport',
        'tj_jabatan',
        'bonus',
        'thr',
        'pot_bpjs',
        'pot_dplk',
        'pot_koperasi',
        'pot_absensi',
        'pot_lainnya',
        'tunjangan',
        'potongan',
        'total',
        'status',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}
