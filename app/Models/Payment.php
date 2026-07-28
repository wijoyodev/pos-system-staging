<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'history_id', 'method', 'amount',
        'card_number', 'cardholder_name', 'approval_code', 'bank_name',
    ];

    public function history()
    {
        return $this->belongsTo(History::class);
    }
}
