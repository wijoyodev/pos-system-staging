<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'order_number',
        'cart_items',
        'subtotal',
        'tax',
        'service',
        'total',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'subtotal' => 'integer',
        'tax' => 'integer',
        'service' => 'integer',
        'total' => 'integer',
        'expires_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = ['expires_at', 'created_at', 'updated_at'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function canBeContinued()
    {
        return $this->isPending() && ! $this->isExpired();
    }

    public function getRemainingMinutesAttribute()
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInMinutes($this->expires_at);
    }
}
