<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EodReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'eod_date',
        'total_transactions',
        'total_sales',
        'total_tax',
        'total_service',
        'total_promo_discount',
        'total_points_discount',
        'total_tier_discount',
        'total_voucher_discount',
        'total_net_sales',
        'sales_cash',
        'sales_qris',
        'sales_debit',
        'sales_credit',
        'total_expenses',
        'total_expected_cash',
        'total_actual_cash',
        'cash_difference',
        'total_closings',
        'status',
        'generated_by',
        'notes',
    ];

    protected $casts = [
        'eod_date' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function items()
    {
        return $this->hasMany(EodReportItem::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public static function existsForStoreAndDate($storeId, $date)
    {
        return self::where('store_id', $storeId)
            ->whereDate('eod_date', $date)
            ->exists();
    }

    public static function getByStoreAndDate($storeId, $date)
    {
        return self::where('store_id', $storeId)
            ->whereDate('eod_date', $date)
            ->with(['items.product', 'purchaseOrders.supplier'])
            ->first();
    }
}
