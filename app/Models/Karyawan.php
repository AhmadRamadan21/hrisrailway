<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = [
        'nama', 'email', 'no_pegawai', 'no_hp', 'tanggal_lahir', 'alamat',
        'divisi_id', 'jabatan_id', 'status', 'gaji_pokok',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function penggunaKaryawan()
    {
        return $this->hasOne(PenggunaKaryawan::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }
}