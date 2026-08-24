<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosBiaya extends Model
{
    protected $table = 'pos_biayas';

    protected $fillable = ['nama_biaya', 'anggaran', 'realisasi', 'sisa'];

    protected $casts = [
        'anggaran'  => 'decimal:2',
        'realisasi' => 'decimal:2',
        'sisa'      => 'decimal:2',
    ];

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'pos_biaya_id');
    }
}