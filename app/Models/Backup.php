<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeSynced($q)
    {
        return $q->where('status', 'synced');
    }

    public function scopeFailed($q)
    {
        return $q->where('status', 'failed');
    }

    public function isSynced(): bool
    {
        return $this->status === 'synced';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
