<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'description',
        'type',
        'value',
        'discount_percentage',
        'discount_nominal',
        'min_purchase_amount',
        'voucher_threshold',
        'max_discount_amount',
        'min_quantity',
        'max_quantity',
        'product_id',
        'category_id',
        'buy_product_id',
        'get_product_id',
        'buy_quantity',
        'get_quantity',
        'tiers',
        'products',
        'bundle_price',
        'eligibleRoles',
        'day_of_week',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_count',
        'user_id',
        'priority',
        'is_active',
        'stackable',
    ];

    protected $casts = [
        'discount_percentage' => 'float',
        'discount_nominal' => 'float',
        'min_purchase_amount' => 'float',
        'voucher_threshold' => 'float',
        'max_discount_amount' => 'float',
        'bundle_price' => 'float',
        'tiers' => 'array',
        'products' => 'array',
        'eligibleRoles' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'start_date' => 'date',
        'end_date' => 'date',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'stackable' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function buyProduct()
    {
        return $this->belongsTo(Product::class, 'buy_product_id');
    }

    public function getProduct()
    {
        return $this->belongsTo(Product::class, 'get_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid()
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    public function isValidForTime()
    {
        $now = now();

        if ($this->day_of_week) {
            $days = explode(',', $this->day_of_week);
            if (! in_array($now->dayOfWeek, $days)) {
                return false;
            }
        }

        if ($this->start_time && $this->end_time) {
            $currentTime = $now->format('H:i:s');
            if ($currentTime < $this->start_time || $currentTime > $this->end_time) {
                return false;
            }
        }

        return true;
    }

    public function isValidForUser($user = null)
    {
        if ($this->eligibleRoles) {
            if (! $user || ! in_array($user->role, $this->eligibleRoles)) {
                return false;
            }
        }

        return true;
    }

    public function isValidForCart($cart, $subtotal)
    {
        if ($this->min_purchase_amount && $subtotal < $this->min_purchase_amount) {
            return false;
        }

        return true;
    }

    public function getTypeLabel()
    {
        $labels = [
            'percentage' => 'Diskon Persentase',
            'nominal' => 'Diskon Nominal',
            'buy_x_get_y' => 'Buy X Get Y',
            'bundle' => 'Bundle',
            'min_purchase' => 'Min Purchase',
            'member' => 'Member Only',
            'time_based' => 'Waktu Tertentu',
            'category' => 'Per Kategori',
            'product' => 'Per Produk',
            'tiered' => 'Tiered Discount',
            'voucher' => 'Voucher',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public static function getTypeOptions()
    {
        return [
            'percentage' => 'Diskon Persentase (%)',
            'nominal' => 'Diskon Nominal (Rp)',
            'buy_x_get_y' => 'Buy X Get Y (BOGO)',
            'bundle' => 'Bundle Pricing',
            'min_purchase' => 'Min Purchase Discount',
            'member' => 'Member Only Discount',
            'time_based' => 'Time-Based Promo',
            'category' => 'Per Kategori',
            'product' => 'Per Produk',
            'tiered' => 'Tiered Discount',
            'voucher' => 'Voucher/Kode Kupon',
        ];
    }
}
