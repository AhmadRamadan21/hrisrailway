<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bagian extends Model
{
    protected $fillable = ['nama_bagian', 'kode_bagian'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
}