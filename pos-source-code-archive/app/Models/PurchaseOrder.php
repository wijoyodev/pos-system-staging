<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'supplier_id',
        'po_number',
        'eod_report_id',
        'order_date',
        'expected_delivery',
        'delivery_date',
        'total_amount',
        'status',
        'notes',
        'ordered_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery' => 'date',
        'delivery_date' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function eodReport()
    {
        return $this->belongsTo(EodReport::class);
    }

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public static function generatePoNumber()
    {
        $year = date('Y');
        $last = self::whereYear('created_at', $year)->latest('id')->first();
        $next = $last ? (intval(substr($last->po_number, -4)) + 1) : 1;

        return 'PO-'.$year.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function isCancelable()
    {
        return in_array($this->status, ['draft', 'ordered']);
    }

    public function isReceivable()
    {
        return $this->status === 'ordered';
    }

    public function cancel()
    {
        if (! $this->isCancelable()) {
            return false;
        }
        $this->update(['status' => 'cancelled']);

        return true;
    }

    public function processReceive()
    {
        if (! $this->isReceivable()) {
            return false;
        }

        DB::transaction(function () {
            $stockIn = StockIn::create([
                'supplier_id' => $this->supplier_id,
                'store_id' => $this->store_id,
                'reference_no' => $this->po_number,
                'total_amount' => $this->total_amount,
                'date' => now(),
                'notes' => 'Received from PO: '.$this->po_number,
                'user_id' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity_ordered,
                    'cost_price' => $item->cost_price,
                ]);
            }

            $stockIn->processStockIn();

            $this->update([
                'status' => 'received',
                'delivery_date' => now(),
            ]);
        });

        return true;
    }
}
