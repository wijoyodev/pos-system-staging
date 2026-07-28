<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'user_id', 'customer_id', 'invoice_id', 'subtotal', 'total_amount',
        'payment_method', 'payment_status', 'status', 'tax', 'service',
        'promo_discount', 'points_discount', 'tier_discount', 'voucher_discount',
        'amount_received', 'change_amount', 'notes', 'void_reason', 'voided_by',
        'cashier_name',
        'void_otp', 'void_otp_expires_at', 'void_otp_admin_id',
        'points_earned', 'points_redeemed', 'voucher_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(HistoryItem::class);
    }

    public function earnedVoucher()
    {
        return $this->hasOne(Voucher::class, 'generated_by_history_id');
    }

    public function usedVoucher()
    {
        return $this->hasOne(Voucher::class, 'used_by_history_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
