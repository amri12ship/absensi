<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAssignment extends Model
{
    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'day_of_week',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }
}