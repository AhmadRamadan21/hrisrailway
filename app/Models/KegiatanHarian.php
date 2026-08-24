<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanHarian extends Model
{
    protected $table = 'kegiatan_harians';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'kegiatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}
