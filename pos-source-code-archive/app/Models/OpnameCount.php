<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OpnameCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'product_id',
        'counted_by',
        'assignment_id',
        'system_stock',
        'physical_stock',
        'variance_qty',
        'variance_value',
        'unit',
        'notes',
        'count_round',
        'count_method',
        'status',
        'counted_at',
    ];

    protected $casts = [
        'counted_at' => 'datetime',
        'variance_value' => 'decimal:2',
    ];

    const STATUS_PENDING = 'PENDING';

    const STATUS_ENTERED = 'ENTERED';

    const STATUS_LOCKED = 'LOCKED';

    public function session(): BelongsTo
    {
        return $this->belongsTo(StockOpnameSession::class, 'session_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(OpnameAssignment::class, 'assignment_id');
    }

    public function adjustment(): HasOne
    {
        return $this->hasOne(OpnameAdjustment::class, 'count_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->counted_at) {
                $model->counted_at = now();
            }
            if (! $model->status) {
                $model->status = self::STATUS_ENTERED;
            }
        });
    }

    public function getHasDiscrepancyAttribute(): bool
    {
        return $this->variance_qty != 0;
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ENTERED]);
    }
}
