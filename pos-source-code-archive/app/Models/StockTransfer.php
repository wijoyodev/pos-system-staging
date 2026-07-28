<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'source_store_id',
        'destination_store_id',
        'status',
        'transfer_date',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'received_at',
        'rejection_reason',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function sourceStore()
    {
        return $this->belongsTo(Store::class, 'source_store_id');
    }

    public function destinationStore()
    {
        return $this->belongsTo(Store::class, 'destination_store_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function isPending()
    {
        return in_array($this->status, ['draft', 'sent', 'in_transit']);
    }

    public function canBeReceived()
    {
        return $this->status === 'in_transit';
    }

    public static function hasActiveTransferForStore(int $storeId): bool
    {
        return self::where(function ($q) use ($storeId) {
            $q->where('source_store_id', $storeId)
                ->orWhere('destination_store_id', $storeId);
        })
            ->where('status', '!=', 'received')
            ->where('status', '!=', 'rejected')
            ->whereHas('createdBy', function ($q) {
                $q->where('role', 'super_admin');
            })
            ->exists();
    }

    public function generateNumber()
    {
        $date = now()->format('Ymd');
        $latest = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        $sequence = $latest ? (intval(substr($latest->transfer_number, -4)) + 1) : 1;
        $this->transfer_number = 'TRF-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function send()
    {
        if ($this->status !== 'draft') {
            return false;
        }

        foreach ($this->items as $item) {
            $stock = StockProduct::where('product_id', $item->product_id)
                ->where('store_id', $this->source_store_id)
                ->first();

            if (! $stock || $stock->quantity < $item->quantity) {
                return false;
            }
        }

        foreach ($this->items as $item) {
            $stock = StockProduct::where('product_id', $item->product_id)
                ->where('store_id', $this->source_store_id)
                ->first();
            $stock->quantity -= $item->quantity;
            $stock->save();
        }

        $this->status = 'in_transit';
        $this->save();

        return true;
    }

    public function receive()
    {
        if (! $this->canBeReceived()) {
            return false;
        }

        foreach ($this->items as $item) {
            $stock = StockProduct::firstOrCreate(
                ['product_id' => $item->product_id, 'store_id' => $this->destination_store_id],
                ['quantity' => 0]
            );
            $stock->quantity += $item->quantity;
            $stock->save();
        }

        $this->status = 'received';
        $this->received_at = now();
        $this->save();

        return true;
    }

    public function reject($reason)
    {
        if (! $this->isPending()) {
            return false;
        }

        if ($this->status === 'in_transit') {
            foreach ($this->items as $item) {
                $stock = StockProduct::where('product_id', $item->product_id)
                    ->where('store_id', $this->source_store_id)
                    ->first();
                if ($stock) {
                    $stock->quantity += $item->quantity;
                    $stock->save();
                }
            }
        }

        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->save();

        return true;
    }
}
