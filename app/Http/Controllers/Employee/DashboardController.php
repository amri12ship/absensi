<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use App\Services\AttendanceStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::guard('web')->user()->load('employee');

        $todayAttendance = $user->employee?->attendances()
            ->whereDate('date', now()->toDateString())
            ->with('location')
            ->first();

        $recentAttendances = $user->employee?->attendances()
            ->with('location')
            ->latest()
            ->take(10)
            ->get();

        $activeLocation = AttendanceLocation::where('status', AttendanceLocation::STATUS_ACTIVE)->first();

        $statusService = app(AttendanceStatusService::class);

        $todayHoliday = $statusService->holidayFor(now());
        $todaySchedule = $user->employee?->scheduleForDate(now())?->workSchedule;

        $statusLabel = match (true) {
            $todayHoliday !== null => 'Libur',
            $todayAttendance?->hasCheckedOut() => 'Absensi Selesai',
            $todayAttendance?->hasCheckedIn() => 'Sudah Check-in',
            default => 'Belum Absen',
        };

        $statusBadge = match (true) {
            $todayHoliday !== null => 'bg-secondary',
            $todayAttendance?->hasCheckedOut() => 'bg-success',
            $todayAttendance?->hasCheckedIn() => 'bg-warning text-dark',
            default => 'bg-danger',
        };

        return view('employee.dashboard', compact(
            'user',
            'todayAttendance',
            'recentAttendances',
            'activeLocation',
            'todayHoliday',
            'todaySchedule',
            'statusLabel',
            'statusBadge',
        ));
    }
}