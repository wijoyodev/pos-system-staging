<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpnameAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'category_id',
        'area_name',
        'status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_ASSIGNED = 'ASSIGNED';

    const STATUS_IN_PROGRESS = 'IN_PROGRESS';

    const STATUS_COMPLETED = 'COMPLETED';

    public function session(): BelongsTo
    {
        return $this->belongsTo(StockOpnameSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getProgressAttribute(): int
    {
        $totalProducts = $this->category
            ? Product::where('category_id', $this->category_id)->count()
            : Product::count();

        if ($totalProducts === 0) {
            return 0;
        }

        $countedCount = $this->session->counts()
            ->where('assignment_id', $this->id)
            ->count();

        return round(($countedCount / $totalProducts) * 100);
    }
}
