<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'shift_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class);
    }

    const STATUS_SCHEDULED = 'scheduled';

    const STATUS_PRESENT = 'present';

    const STATUS_ABSENT = 'absent';

    const STATUS_LATE = 'late';

    public static function detectShiftFromTime(?string $time = null): ?ShiftSchedule
    {
        $time = $time ?? now()->format('H:i');
        $storeId = auth()->user()->store_id;

        $shifts = ShiftSchedule::where('store_id', $storeId)
            ->where('is_active', true)
            ->get();

        foreach ($shifts as $shift) {
            $start = Carbon::parse($shift->start_time)->format('H:i');
            $end = Carbon::parse($shift->end_time)->format('H:i');

            // Handle overnight shift (e.g., 22:00 - 06:00)
            if ($end < $start) {
                if ($time >= $start || $time < $end) {
                    return $shift;
                }
            } else {
                if ($time >= $start && $time < $end) {
                    return $shift;
                }
            }
        }

        return null;
    }
}
