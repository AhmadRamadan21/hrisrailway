<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanCuti extends Model
{
    protected $table = 'pengajuan_cutis';

    protected $fillable = [
        'karyawan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi',
        'alasan',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}
