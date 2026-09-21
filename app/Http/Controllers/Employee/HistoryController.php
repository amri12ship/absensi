<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::guard('web')->user()->load('employee');

        $start = $request->query('dari');
        $end = $request->query('sampai');

        $attendances = $user->employee?->attendances()
            ->with('location')
            ->when($start, fn ($q) => $q->whereDate('date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('date', '<=', $end))
            ->latest('date')
            ->latest('check_in')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'hadir' => $this->countStatus($user, $start, $end, [Attendance::STATUS_PRESENT, Attendance::STATUS_LEFT, Attendance::STATUS_LATE]),
            'terlambat' => $this->countStatus($user, $start, $end, Attendance::STATUS_LATE),
            'total' => $this->countStatus($user, $start, $end, null),
        ];

        return view('employee.history', compact('user', 'attendances', 'start', 'end', 'summary'));
    }

    public function calendar(Request $request): View
    {
        /** @var User $user */
        $user = Auth::guard('web')->user()->load('employee');

        $month = (int) $request->query('bulan', now()->month);
        $year = (int) $request->query('tahun', now()->year);

        $month = max(1, min(12, $month));
        $year = max(2000, $year);

        $startOfMonth = Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;

        $attendances = $user->employee?->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('location')
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->date)->day);

        $holidays = Holiday::where('status', Holiday::STATUS_ACTIVE)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($h) => Carbon::parse($h->date)->day);

        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $startOfMonth->copy()->addDays($day - 1);
            $days[$day] = [
                'date' => $date,
                'isToday' => $date->isToday(),
                'isSunday' => $date->isoWeekday() === 7,
                'holiday' => $holidays->get($day),
                'attendance' => $attendances->get($day),
            ];
        }

        return view('employee.kalender', compact('user', 'days', 'daysInMonth', 'month', 'year', 'startOfMonth'));
    }

    private function countStatus(?User $user, ?string $start, ?string $end, $statuses): int
    {
        if (! $user?->employee) {
            return 0;
        }

        $query = $user->employee->attendances()
            ->when($start, fn ($q) => $q->whereDate('date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('date', '<=', $end));

        if ($statuses === null) {
            return (clone $query)->count();
        }

        $statuses = is_array($statuses) ? $statuses : [$statuses];

        return (clone $query)->whereIn('status', $statuses)->count();
    }
}