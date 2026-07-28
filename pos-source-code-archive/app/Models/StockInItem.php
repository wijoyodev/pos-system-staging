<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_in_id',
        'product_id',
        'quantity',
        'cost_price',
        'expired_date',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'expired_date' => 'date',
    ];

    public function isExpired(): bool
    {
        return $this->expired_date && $this->expired_date->isPast();
    }

    public function isNearExpiry(int $days = 30): bool
    {
        if (! $this->expired_date) {
            return false;
        }

        return $this->expired_date->isFuture() && $this->expired_date->diffInDays(now()) <= $days;
    }

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
