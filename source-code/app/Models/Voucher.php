<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'discount_amount', 'is_used', 'used_by_history_id',
        'generated_by_history_id', 'used_at',
    ];

    public function generatedByHistory()
    {
        return $this->belongsTo(History::class, 'generated_by_history_id');
    }

    public function usedByHistory()
    {
        return $this->belongsTo(History::class, 'used_by_history_id');
    }
}
