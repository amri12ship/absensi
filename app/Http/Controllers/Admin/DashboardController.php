<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $totalEmployees = User::query()->where('role', User::ROLE_EMPLOYEE)->count();
        $activeEmployees = User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->where('status', User::STATUS_ACTIVE)
            ->count();
        $inactiveEmployees = $totalEmployees - $activeEmployees;

        $totalLocations = AttendanceLocation::count();
        $activeLocations = AttendanceLocation::where('status', AttendanceLocation::STATUS_ACTIVE)->count();

        $todayAttendances = Attendance::whereDate('date', $today)->count();

        $statusCounts = Attendance::whereDate('date', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($total, $status) => [$status => (int) $total]);

        $presentToday = ($statusCounts[Attendance::STATUS_PRESENT] ?? 0)
            + ($statusCounts[Attendance::STATUS_LEFT] ?? 0);

        $lateToday = $statusCounts[Attendance::STATUS_LATE] ?? 0;

        $notYetPresent = max(0, $activeEmployees - $todayAttendances);

        $statusToday = [
            Attendance::STATUS_PRESENT => $presentToday,
            Attendance::STATUS_LATE => $lateToday,
            Attendance::STATUS_IZIN => $statusCounts[Attendance::STATUS_IZIN] ?? 0,
            Attendance::STATUS_SICK => $statusCounts[Attendance::STATUS_SICK] ?? 0,
            Attendance::STATUS_ALPHA => $statusCounts[Attendance::STATUS_ALPHA] ?? 0,
            Attendance::STATUS_LIBUR => $statusCounts[Attendance::STATUS_LIBUR] ?? 0,
        ];

        $todayAttendanceRows = Attendance::with(['employee.user', 'location'])
            ->whereDate('date', $today)
            ->latest('check_in')
            ->paginate(10);

        $latestEmployees = User::with('employee')
            ->where('role', User::ROLE_EMPLOYEE)
            ->latest()
            ->take(5)
            ->get();

        $latestLocations = AttendanceLocation::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'inactiveEmployees',
            'totalLocations',
            'activeLocations',
            'todayAttendances',
            'presentToday',
            'lateToday',
            'notYetPresent',
            'todayAttendanceRows',
            'latestEmployees',
            'latestLocations',
            'statusToday',
        ));
    }
}