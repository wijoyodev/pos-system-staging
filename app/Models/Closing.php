<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Closing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'shift',
        'opening_balance',
        'total_sales',
        'cash_sales',
        'qris_sales',
        'debit_sales',
        'credit_sales',
        'expenses',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'closing_date',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:0',
        'total_sales' => 'decimal:0',
        'cash_sales' => 'decimal:0',
        'qris_sales' => 'decimal:0',
        'debit_sales' => 'decimal:0',
        'credit_sales' => 'decimal:0',
        'expenses' => 'decimal:0',
        'expected_cash' => 'decimal:0',
        'actual_cash' => 'decimal:0',
        'difference' => 'decimal:0',
        'approved_at' => 'datetime',
        'closing_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
