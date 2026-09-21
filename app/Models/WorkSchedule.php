<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'time_in',
        'time_out',
        'tolerance_minutes',
        'status',
    ];

    protected $casts = [
        'tolerance_minutes' => 'integer',
    ];

    protected $appends = ['durasi_label'];

    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getDurasiLabelAttribute(): string
    {
        return $this->time_in.' - '.$this->time_out;
    }
}