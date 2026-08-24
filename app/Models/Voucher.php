<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'nomor_surat',
        'user_id',
        'bagian_id',
        'pos_biaya_id',
        'tanggal',
        'nilai',
        'terbilang',
        'keterangan',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'nilai'       => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Relasi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class);
    }

    public function posBiaya(): BelongsTo
    {
        return $this->belongsTo(PosBiaya::class, 'pos_biaya_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}