<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\User;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReportController extends Controller
{
    public function __construct(private readonly SpreadsheetService $spreadsheetService) {}

    public function index(Request $request): View
    {
        [$start, $end] = $this->resolvePeriod($request);

        $query = Attendance::with(['employee.user', 'location'])
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString());

        $this->applyFilters($request, $query);

        $attendances = $query->latest('date')->latest('check_in')->paginate(20)->withQueryString();

        $rekap = $this->buildRekap($request, $start, $end);

        $employees = User::with('employee')
            ->where('role', User::ROLE_EMPLOYEE)
            ->orderBy('name')
            ->get();

        $locations = AttendanceLocation::orderBy('name')->get();

        $statusOptions = $this->statusOptions();

        return view('admin.reports.index', compact(
            'attendances',
            'rekap',
            'start',
            'end',
            'employees',
            'locations',
            'statusOptions',
            'request',
        ));
    }

    public function export(Request $request): BinaryFileResponse|RedirectResponse
    {
        [$start, $end] = $this->resolvePeriod($request);

        $query = Attendance::with(['employee.user', 'location'])
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString());

        $this->applyFilters($request, $query);

        $rows = $query->orderBy('date')->orderBy('check_in')->get();

        if ($rows->isEmpty()) {
            return back()->with('error', 'Tidak ada data absensi pada periode yang dipilih, file Excel tidak dibuat.');
        }

        $headers = ['Tanggal', 'Nama', 'NIK', 'Jabatan', 'Lokasi', 'Jam Masuk', 'Jam Keluar', 'Status', 'Jarak Check-in (m)'];

        $data = $rows->map(fn (Attendance $attendance) => [
            $attendance->date->format('Y-m-d'),
            $attendance->employee?->user?->name ?? '-',
            $attendance->employee?->nik ?? '-',
            $attendance->employee?->position ?? '-',
            $attendance->location?->name ?? '-',
            $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i:s') : '-',
            $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i:s') : '-',
            $attendance->statusLabel(),
            $attendance->check_in_distance ?? '-',
        ])->all();

        return $this->spreadsheetService->download(
            'laporan-absensi-'.now()->format('Y-m-d').'.xlsx',
            $headers,
            $data,
        );
    }

    public function print(Request $request): View
    {
        [$start, $end] = $this->resolvePeriod($request);

        $query = Attendance::with(['employee.user', 'location'])
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString());

        $this->applyFilters($request, $query);

        $attendances = $query->orderBy('date')->orderBy('check_in')->get();

        $rekap = $this->buildRekap($request, $start, $end);

        $currentUser = auth('web')->user();

        $statusOptions = $this->statusOptions();

        $filterEmployeeId = $request->filled('employee') && $request->query('employee') !== 'all'
            ? (int) $request->query('employee')
            : null;

        $filterLocationId = $request->filled('lokasi') && $request->query('lokasi') !== 'all'
            ? (int) $request->query('lokasi')
            : null;

        $filterStatus = $request->filled('status') && $request->query('status') !== 'all'
            ? $request->query('status')
            : null;

        return view('admin.reports.print', compact(
            'attendances',
            'rekap',
            'start',
            'end',
            'currentUser',
            'request',
            'statusOptions',
            'filterEmployeeId',
            'filterLocationId',
            'filterStatus',
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        if ($dari && $sampai) {
            $start = Carbon::parse($dari)->startOfDay();
            $end = Carbon::parse($sampai)->endOfDay();
        } else {
            $bulan = max(1, min(12, $bulan));
            $tahun = max(2000, $tahun);

            $start = Carbon::create($tahun, $bulan, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->endOfDay();
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    private function applyFilters(Request $request, $query): void
    {
        $employeeId = $request->query('employee');
        $locationId = $request->query('lokasi');
        $status = $request->query('status');

        if ($request->filled('employee') && $employeeId !== 'all') {
            $query->where('employee_id', $employeeId);
        }

        if ($request->filled('lokasi') && $locationId !== 'all') {
            $query->where('attendance_location_id', $locationId);
        }

        if ($request->filled('status') && $status !== 'all') {
            if ($status === 'present') {
                $query->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LEFT]);
            } else {
                $query->where('status', $status);
            }
        }
    }

    private function buildRekap(Request $request, Carbon $start, Carbon $end): array
    {
        $employeeId = $request->filled('employee') && $request->query('employee') !== 'all'
            ? (int) $request->query('employee')
            : null;

        $locationId = $request->filled('lokasi') && $request->query('lokasi') !== 'all'
            ? (int) $request->query('lokasi')
            : null;

        $employees = Employee::with(['user', 'scheduleAssignments.workSchedule'])
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->orderBy('id')
            ->get()
            ->filter(fn (Employee $employee) => $employee->user !== null)
            ->values();

        $activeHolidays = Holiday::where('status', Holiday::STATUS_ACTIVE)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get()
            ->map(fn (Holiday $holiday) => $holiday->date->toDateString())
            ->flip();

        $statsQuery = Attendance::selectRaw('employee_id, status, COUNT(*) as total')
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString());

        if ($employeeId) {
            $statsQuery->where('employee_id', $employeeId);
        }

        if ($locationId) {
            $statsQuery->where('attendance_location_id', $locationId);
        }

        $statsByEmployee = $statsQuery
            ->groupBy('employee_id', 'status')
            ->get()
            ->groupBy('employee_id');

        $rekap = [];

        foreach ($employees as $employee) {
            $rows = $statsByEmployee->get($employee->id, collect());

            $count = fn (array $statuses): int => $rows->whereIn('status', $statuses)->sum('total');

            $hadir = $count([Attendance::STATUS_PRESENT, Attendance::STATUS_LEFT]);
            $terlambat = $count([Attendance::STATUS_LATE]);
            $izin = $count([Attendance::STATUS_IZIN]);
            $sakit = $count([Attendance::STATUS_SICK]);
            $alpha = $count([Attendance::STATUS_ALPHA]);
            $libur = $count([Attendance::STATUS_LIBUR]);

            $attended = (int) $rows->sum('total');
            $expected = $this->countWorkDays($employee, $start, $end, $activeHolidays);

            $rekap[] = [
                'employee' => $employee,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'libur' => $libur,
                'attended' => $attended,
                'expected' => $expected,
                'tidak_hadir' => max(0, $expected - $attended),
                'percentage' => $expected > 0 ? round(($attended / $expected) * 100, 1) : null,
            ];
        }

        return $rekap;
    }

    private function countWorkDays(Employee $employee, Carbon $start, Carbon $end, $activeHolidays): int
    {
        $assignments = $employee->scheduleAssignments->keyBy('day_of_week');

        $days = 0;

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if (isset($activeHolidays[$day->toDateString()])) {
                continue;
            }

            $assignment = $assignments->get($day->isoWeekday());

            if (! $assignment?->workSchedule?->isActive()) {
                continue;
            }

            $days++;
        }

        return $days;
    }

    private function statusOptions(): array
    {
        return [
            'present' => 'Hadir',
            Attendance::STATUS_LATE => 'Terlambat',
            Attendance::STATUS_IZIN => 'Izin',
            Attendance::STATUS_SICK => 'Sakit',
            Attendance::STATUS_ALPHA => 'Alpha',
            Attendance::STATUS_LIBUR => 'Libur',
        ];
    }
}