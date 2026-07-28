<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpnameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'status',
        'planned_date',
        'cetakkertas_date',
        'entry_date',
        'checkdata_date',
        'proses_date',
        'cetakselisih_date',
        'editdata_date',
        'fixed_date',
        'adjust_date',
        'notes',
        'created_by',
        'adjusted_by',
        'posted_by',
        'adjusted_at',
        'posted_at',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'cetakkertas_date' => 'date',
        'entry_date' => 'date',
        'checkdata_date' => 'date',
        'proses_date' => 'date',
        'cetakselisih_date' => 'date',
        'editdata_date' => 'date',
        'fixed_date' => 'date',
        'adjust_date' => 'date',
        'adjusted_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    const STATUS_PLANNED = 'PLANNED';

    const STATUS_CETAK_KERTAS = 'CETAK_KERTAS';

    const STATUS_ENTRY = 'ENTRY';

    const STATUS_CHECK_DATA = 'CHECK_DATA';

    const STATUS_PROSES = 'PROSES';

    const STATUS_CETAK_SELISIH = 'CETAK_SELISIH';

    const STATUS_EDIT_DATA = 'EDIT_DATA';

    const STATUS_FIXED = 'FIXED';

    const STATUS_ADJUST = 'ADJUST';

    const STATUS_POSTED = 'POSTED';

    const STATUS_CANCELLED = 'CANCELLED';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OpnameAssignment::class, 'session_id');
    }

    public function counts(): HasMany
    {
        return $this->hasMany(OpnameCount::class, 'session_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(OpnameAdjustment::class, 'session_id');
    }

    public function getProducts(): Collection
    {
        return Product::whereHas('stocks', function ($query) {
            $query->where('store_id', $this->store_id);
        })->with(['stocks' => function ($query) {
            $query->where('store_id', $this->store_id);
        }])->get();
    }

    public function getUnenteredProducts()
    {
        $enteredProductIds = $this->counts()->pluck('product_id');

        return Product::whereHas('stocks', function ($query) {
            $query->where('store_id', $this->store_id);
        })->whereNotIn('id', $enteredProductIds)->get();
    }

    public function getEnteredProducts()
    {
        return $this->counts()->with('product')->get();
    }

    public function getPendingCount(): int
    {
        return $this->getUnenteredProducts()->count();
    }

    public function getEnteredCount(): int
    {
        return $this->counts()->where('status', '!=', 'PENDING')->count();
    }

    public function getTotalVarianceValue(): float
    {
        return $this->counts()->sum('variance_value');
    }

    public function getDiscrepancies()
    {
        return $this->counts()
            ->where('variance_qty', '!=', 0)
            ->orderByDesc('variance_value')
            ->get();
    }

    public function getTotalDiscrepancyCount(): int
    {
        return $this->counts()->where('variance_qty', '!=', 0)->count();
    }

    public function canCetakKertas(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    public function canEntry(): bool
    {
        return in_array($this->status, [self::STATUS_CETAK_KERTAS, self::STATUS_ENTRY]);
    }

    public function canCheckData(): bool
    {
        return $this->status === self::STATUS_ENTRY;
    }

    public function canProses(): bool
    {
        return in_array($this->status, [self::STATUS_ENTRY, self::STATUS_CHECK_DATA]);
    }

    public function canCetakSelisih(): bool
    {
        return in_array($this->status, [self::STATUS_PROSES, self::STATUS_CETAK_SELISIH]);
    }

    public function canEditData(): bool
    {
        return in_array($this->status, [self::STATUS_PROSES, self::STATUS_CETAK_SELISIH, self::STATUS_EDIT_DATA]);
    }

    public function canFixed(): bool
    {
        return in_array($this->status, [self::STATUS_PROSES, self::STATUS_CETAK_SELISIH, self::STATUS_EDIT_DATA]);
    }

    public function canAdjust(): bool
    {
        return in_array($this->status, [self::STATUS_FIXED, self::STATUS_ADJUST]);
    }

    public function canPost(): bool
    {
        return in_array($this->status, [self::STATUS_ADJUST, self::STATUS_POSTED]);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [
            self::STATUS_PLANNED,
            self::STATUS_CETAK_KERTAS,
            self::STATUS_ENTRY,
            self::STATUS_CHECK_DATA,
            self::STATUS_PROSES,
        ]);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PLANNED => 'Direncanakan',
            self::STATUS_CETAK_KERTAS => 'Cetak Kertas',
            self::STATUS_ENTRY => 'Entry',
            self::STATUS_CHECK_DATA => 'Check Data',
            self::STATUS_PROSES => 'Proses',
            self::STATUS_CETAK_SELISIH => 'Cetak Selisih',
            self::STATUS_EDIT_DATA => 'Edit Data',
            self::STATUS_FIXED => 'Fixed',
            self::STATUS_ADJUST => 'Adjust',
            self::STATUS_POSTED => 'Posted',
            self::STATUS_CANCELLED => 'Cancelled',
        };
    }

    public function getNextStatus(): ?string
    {
        return match ($this->status) {
            self::STATUS_PLANNED => self::STATUS_CETAK_KERTAS,
            self::STATUS_CETAK_KERTAS => self::STATUS_ENTRY,
            self::STATUS_ENTRY => self::STATUS_CHECK_DATA,
            self::STATUS_CHECK_DATA => self::STATUS_PROSES,
            self::STATUS_PROSES => self::STATUS_CETAK_SELISIH,
            self::STATUS_CETAK_SELISIH => self::STATUS_EDIT_DATA,
            self::STATUS_EDIT_DATA => self::STATUS_FIXED,
            self::STATUS_FIXED => self::STATUS_ADJUST,
            self::STATUS_ADJUST => self::STATUS_POSTED,
            default => null,
        };
    }

    public static function hasActiveSchedule(?int $storeId): bool
    {
        if (! $storeId) {
            return false;
        }

        return self::where('store_id', $storeId)
            ->whereIn('status', [self::STATUS_PLANNED, self::STATUS_CETAK_KERTAS, self::STATUS_ENTRY])
            ->exists();
    }

    public static function getActiveSchedule(?int $storeId): ?self
    {
        if (! $storeId) {
            return null;
        }

        return self::where('store_id', $storeId)
            ->whereIn('status', [self::STATUS_PLANNED, self::STATUS_CETAK_KERTAS, self::STATUS_ENTRY])
            ->orderBy('planned_date', 'asc')
            ->first();
    }
}
