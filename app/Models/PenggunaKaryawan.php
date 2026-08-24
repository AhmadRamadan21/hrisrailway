<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PenggunaKaryawan extends Authenticatable
{
    protected $table = 'pengguna_karyawans';

    protected $fillable = [
        'karyawan_id',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'password_plain',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    // Cek role, mirip helper di model User lama
    public function hasRole(string|array $roles): bool
    {
        $current = strtolower($this->role ?? '');

        if (is_array($roles)) {
            $roles = array_map('strtolower', $roles);
            return in_array($current, $roles);
        }

        $allowed = array_map('strtolower', array_map('trim', explode('|', $roles)));
        return in_array($current, $allowed);
    }
}