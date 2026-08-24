<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = ['nama_jabatan', 'divisi_id'];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class);
    }
}