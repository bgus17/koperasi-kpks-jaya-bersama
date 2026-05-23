<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    // ── Role constants (tetap untuk backward compatibility) ───
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANDOR = 'mandor';
    public const ROLE_STAFF_OPERATOR = 'staff_operator';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'karyawan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Label role untuk tampilan di UI.
     * Mengambil dari Spatie role jika tersedia, fallback ke kolom role.
     */
    public function getRoleLabelAttribute(): string
    {
        $role = $this->getRoleNames()->first() ?? $this->role;

        return match ($role) {
            self::ROLE_MANDOR => 'Mandor',
            self::ROLE_STAFF_OPERATOR => 'Staff/Operator',
            self::ROLE_ADMIN => 'Administrator',
            default => 'Administrator',
        };
    }

    /**
     * Helper: Ambil nama role Spatie pertama (atau fallback ke kolom role).
     */
    public function getSpatieRoleAttribute(): string
    {
        return $this->getRoleNames()->first() ?? $this->role ?? self::ROLE_ADMIN;
    }

    /**
     * Transitional role check while the legacy users.role column still exists.
     */
    public function hasEffectiveRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return $this->hasAnyRole($roles) || in_array($this->role, $roles, true);
    }
}
