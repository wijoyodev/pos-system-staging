<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'start_time',
        'end_time',
        'color',
        'is_active',
        'shift_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function getTimeRangeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('H:i').' - '.Carbon::parse($this->end_time)->format('H:i');
    }

    public function getShiftKeyAttribute(): ?string
    {
        if (! empty($this->attributes['shift_key'])) {
            return $this->attributes['shift_key'];
        }
        $name = strtolower($this->name);
        if (str_contains($name, 'p7')) {
            return 'morning';
        }
        if (str_contains($name, 's14')) {
            return 'afternoon';
        }
        if (str_contains($name, 'm22')) {
            return 'night';
        }

        return 'morning';
    }

    public static function getDefaults(): array
    {
        $shifts = [];
        $colors = [
            '#10B981',
            '#F59E0B',
            '#6366F1',
        ];

        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);

            if ($i < 8) {
                $code = 'P'.$hour;
                $shiftKey = 'pagi';
                $color = $colors[0];
            } elseif ($i < 16) {
                $code = 'S'.$hour;
                $shiftKey = 'siang';
                $color = $colors[1];
            } else {
                $code = 'M'.$hour;
                $shiftKey = 'malam';
                $color = $colors[2];
            }

            $endHour = ($i + 8) % 24;
            $endTime = sprintf('%02d:00', $endHour);

            $shifts[] = [
                'name' => $code,
                'start_time' => sprintf('%02d:00', $i),
                'end_time' => $endTime,
                'color' => $color,
                'shift_key' => $shiftKey,
            ];
        }

        return $shifts;
    }
}
