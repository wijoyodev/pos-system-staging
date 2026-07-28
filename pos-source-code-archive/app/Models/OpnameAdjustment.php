<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpnameAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'count_id',
        'product_id',
        'adjustment_qty',
        'adjustment_value',
        'type',
        'reason',
        'approved_by',
        'approved_at',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    const TYPE_ADD = 'ADD';

    const TYPE_REDUCE = 'REDUCE';

    const TYPE_NO_ADJUSTMENT = 'NO_ADJUSTMENT';

    const STATUS_PENDING = 'PENDING';

    const STATUS_APPROVED = 'APPROVED';

    const STATUS_REJECTED = 'REJECTED';

    const STATUS_APPLIED = 'APPLIED';

    public function session(): BelongsTo
    {
        return $this->belongsTo(StockOpnameSession::class, 'session_id');
    }

    public function count(): BelongsTo
    {
        return $this->belongsTo(OpnameCount::class, 'count_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->adjustment_qty = -$model->count->variance_qty;

            if ($model->adjustment_qty > 0) {
                $model->type = self::TYPE_ADD;
                $model->adjustment_value = $model->adjustment_qty * ($model->product->selling_price ?? 0);
            } elseif ($model->adjustment_qty < 0) {
                $model->type = self::TYPE_REDUCE;
                $model->adjustment_value = $model->adjustment_qty * ($model->product->selling_price ?? 0);
            } else {
                $model->type = self::TYPE_NO_ADJUSTMENT;
                $model->adjustment_value = 0;
            }
        });
    }

    public function getIsAdjustableAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->adjustment_qty != 0;
    }
}
