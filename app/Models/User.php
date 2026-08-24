<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'bagian_id',
        'role_id',
        'name',
        'username',
        'email',
        'password',
        'foto',
        'status',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
    // Relasi
    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class);
    }
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
    // Helper role (mirip Auth::hasRole() di project lama)
    public function hasRole(string|array $roles): bool
    {
        $current = $this->role?->name ?? '';
        if (is_array($roles)) {
            return in_array($current, $roles);
        }
        $allowed = array_map('trim', explode('|', $roles));
        return in_array($current, $allowed);
    }
}
