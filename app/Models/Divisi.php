<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $fillable = ['nama_divisi'];

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class);
    }

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class);
    }
}