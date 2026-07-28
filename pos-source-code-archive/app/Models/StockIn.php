<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'store_id',
        'reference_no',
        'total_amount',
        'date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockInItem::class);
    }

    public function processStockIn()
    {
        foreach ($this->items as $item) {
            $stock = StockProduct::firstOrCreate(
                ['product_id' => $item->product_id, 'store_id' => $this->store_id],
                ['quantity' => 0]
            );
            $stock->quantity += $item->quantity;
            $stock->save();

            $product = $item->product;
            if ($product->cost_price != $item->cost_price) {
                $product->cost_price = $item->cost_price;
                $product->save();
            }
        }
    }
}
