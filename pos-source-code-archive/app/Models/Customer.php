<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'total_points',
        'used_points',
        'available_points',
        'tier',
        'total_spent',
    ];

    /* ── Relationships ────────────────────────────── */

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    /* ── Tier Logic ─────────────────────────────── */

    public function updateTier(): void
    {
        $spent = $this->total_spent;
        $newTier = 'bronze';

        if ($spent >= 5000000) {
            $newTier = 'gold';
        } elseif ($spent >= 1000000) {
            $newTier = 'silver';
        }

        if ($this->tier !== $newTier) {
            $this->update(['tier' => $newTier]);
        }
    }

    public function getTierDiscountAttribute(): float
    {
        return match ($this->tier) {
            'gold' => 0.05,
            'silver' => 0.02,
            default => 0,
        };
    }

    public function getTierMultiplierAttribute(): float
    {
        return match ($this->tier) {
            'gold' => 1.5,
            'silver' => 1.2,
            default => 1.0,
        };
    }

    public function getTierColorAttribute(): string
    {
        return match ($this->tier) {
            'gold' => 'amber',
            'silver' => 'slate',
            default => 'orange',
        };
    }

    public function getTransactionCountAttribute(): int
    {
        return $this->histories()->count();
    }

    /* ── Points Methods ──────────────────────────── */

    public function addPoints(int $points): void
    {
        $this->increment('total_points', $points);
        $this->increment('available_points', $points);
    }

    public function usePoints(int $points): void
    {
        $available = $this->available_points;
        $pointsToUse = min($points, $available);
        $this->increment('used_points', $pointsToUse);
    }

    /* ── Code Generation ─────────────────────────── */

    public static function generateCode(): string
    {
        do {
            $code = 'MBR-'.strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
