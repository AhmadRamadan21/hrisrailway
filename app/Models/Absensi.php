<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'masuk',
        'keluar',
        'status',
        'photo',
        'distance',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'masuk'   => 'datetime',
        'keluar'  => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}
