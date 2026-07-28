<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'history_id', 'product_id', 'product_name', 'quantity', 'price',
        'subtotal', 'notes',
    ];

    public function history()
    {
        return $this->belongsTo(History::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
