<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EodReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'eod_report_id',
        'product_id',
        'total_qty_sold',
        'total_revenue',
    ];

    public function eodReport()
    {
        return $this->belongsTo(EodReport::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
