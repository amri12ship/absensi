<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Attendance::with(['employee.user', 'location']);

        $date = $request->query('tanggal', now()->toDateString());
        $search = trim((string) $request->query('search'));
        $month = $request->query('bulan');
        $year = $request->query('tahun');
        $locationId = $request->query('lokasi');
        $status = $request->query('status');

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        } elseif ($request->filled('tanggal')) {
            $query->whereDate('date', $date);
        } else {
            $query->whereDate('date', $date);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee.user', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('employee', function ($sub) use ($search) {
                    $sub->where('nik', 'like', "%{$search}%");
                });
            });
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

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->query('employee'));
        }

        $attendances = $query->latest('date')->latest('check_in')->paginate(20)->withQueryString();

        $employees = User::with('employee')
            ->where('role', User::ROLE_EMPLOYEE)
            ->orderBy('name')
            ->get();

        $locations = AttendanceLocation::orderBy('name')->get();

        $statusOptions = [
            Attendance::STATUS_PRESENT => 'Hadir',
            Attendance::STATUS_LATE => 'Terlambat',
            Attendance::STATUS_IZIN => 'Izin',
            Attendance::STATUS_SICK => 'Sakit',
            Attendance::STATUS_ALPHA => 'Alpha',
        ];

        return view('admin.absensi.index', compact(
            'attendances',
            'employees',
            'locations',
            'date',
            'month',
            'year',
            'search',
            'locationId',
            'status',
            'statusOptions',
            'request',
        ));
    }

    public function show(Attendance $attendance): View
    {
        return view('admin.absensi.show', [
            'attendance' => $attendance->load(['employee.user', 'employee.scheduleAssignments.workSchedule', 'location']),
        ]);
    }
}