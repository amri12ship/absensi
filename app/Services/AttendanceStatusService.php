<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Support\Carbon;

class AttendanceStatusService
{
    /**
     * Tentukan status kehadiran saat check-in berdasarkan jadwal kerja.
     */
    public function statusAtCheckIn(Employee $employee, string $checkInTime, ?Carbon $date = null): string
    {
        $date ??= Carbon::now();

        $assignment = $employee->scheduleForDate($date);

        if (! $assignment?->workSchedule?->isActive()) {
            return Attendance::STATUS_PRESENT;
        }

        $start = Carbon::parse($assignment->workSchedule->time_in);
        $limit = $start->copy()->addMinutes($assignment->workSchedule->tolerance_minutes);

        return Carbon::parse($checkInTime)->gt($limit)
            ? Attendance::STATUS_LATE
            : Attendance::STATUS_PRESENT;
    }

    /**
     * Cek apakah tanggal adalah hari libur aktif.
     */
    public function isHoliday($date): bool
    {
        return Holiday::where('status', Holiday::STATUS_ACTIVE)
            ->whereDate('date', Carbon::parse($date)->toDateString())
            ->exists();
    }

    public function holidayFor($date): ?Holiday
    {
        return Holiday::where('status', Holiday::STATUS_ACTIVE)
            ->whereDate('date', Carbon::parse($date)->toDateString())
            ->first();
    }

    /**
     * Label status kehadiran untuk tampilan (termasuk status turunan).
     */
    public static function label(string $status): string
    {
        return Attendance::STATUS_LABELS[$status] ?? $status;
    }
}