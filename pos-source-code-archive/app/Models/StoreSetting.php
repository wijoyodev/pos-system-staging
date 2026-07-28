<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    protected $fillable = ['store_id', 'key', 'value'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public static function getVal($key, $storeId, $default = null)
    {
        if (! $storeId) {
            // Fallback to global setting
            return Setting::getVal($key, $default);
        }

        $cacheKey = "store_settings_{$storeId}";
        $settings = Cache::rememberForever($cacheKey, function () use ($storeId) {
            return self::where('store_id', $storeId)->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function setVal($key, $value, $storeId)
    {
        self::updateOrCreate(
            ['store_id' => $storeId, 'key' => $key],
            ['value' => $value]
        );
        Cache::forget("store_settings_{$storeId}");
    }

    public static function setMany(array $settings, $storeId)
    {
        foreach ($settings as $key => $value) {
            self::setVal($key, $value, $storeId);
        }
    }

    public static function getAllForStore($storeId)
    {
        $cacheKey = "store_settings_{$storeId}";

        return Cache::rememberForever($cacheKey, function () use ($storeId) {
            return self::where('store_id', $storeId)->pluck('value', 'key')->toArray();
        });
    }
}
