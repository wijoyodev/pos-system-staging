<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nik',
        'email',
        'password',
        'role',
        'store_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->nik)) {
                $user->nik = self::generateNik();
            }
        });
    }

    public static function generateNik(): string
    {
        $year = date('y');
        $month = date('m');
        $prefix = $year.$month;

        $lastUser = self::where('nik', 'like', $prefix.'%')
            ->orderBy('nik', 'desc')
            ->first();

        if ($lastUser) {
            $lastNumber = (int) substr($lastUser->nik, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix.$newNumber;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ── Role Helpers ─────────────────────────────────── */

    protected array $roleLevels = [
        'kasir' => 1,
        'admin' => 2,
        'super_admin' => 3,
    ];

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user has at least the given minimum role.
     */
    public function hasMinRole(string $role): bool
    {
        $userLevel = $this->roleLevels[$this->role] ?? 0;
        $requiredLevel = $this->roleLevels[$role] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Get human-readable role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'kasir' => 'Kasir',
            'admin' => 'Admin',
            'super_admin' => 'Super Admin',
            default => 'Unknown',
        };
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
