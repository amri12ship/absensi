<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nik',
        'phone',
        'address',
        'position',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function scheduleAssignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function scheduleForDay(int $dayOfWeek): ?ScheduleAssignment
    {
        return $this->scheduleAssignments()
            ->where('day_of_week', $dayOfWeek)
            ->with('workSchedule')
            ->first();
    }

    public function scheduleForDate($date): ?ScheduleAssignment
    {
        return $this->scheduleForDay(Carbon::parse($date)->isoWeekday());
    }
}
