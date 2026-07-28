<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['branch_id', 'name', 'address', 'code', 'status', 'latitude', 'longitude'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function closings()
    {
        return $this->hasMany(Closing::class);
    }

    public function stockProducts()
    {
        return $this->hasMany(StockProduct::class);
    }

    public function settings()
    {
        return $this->hasMany(StoreSetting::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function distanceFrom($lat, $lng): float
    {
        if (! $this->latitude || ! $this->longitude) {
            return 0;
        }

        $earthRadius = 6371000;

        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
