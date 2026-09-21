<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleAssignmentController extends Controller
{
    public const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function index(): View
    {
        $employees = User::with(['employee.scheduleAssignments.workSchedule'])
            ->where('role', User::ROLE_EMPLOYEE)
            ->orderBy('name')
            ->paginate(15);

        return view('admin.assignments.index', compact('employees'));
    }

    public function edit(User $employee): View
    {
        abort_unless($employee->isEmployee(), 404);

        $employee->load('employee.scheduleAssignments.workSchedule');

        $schedules = WorkSchedule::where('status', WorkSchedule::STATUS_ACTIVE)
            ->orderBy('time_in')
            ->get();

        $assignments = $employee->employee?->scheduleAssignments
            ->keyBy('day_of_week') ?? collect();

        return view('admin.assignments.edit', [
            'employee' => $employee,
            'schedules' => $schedules,
            'assignments' => $assignments,
            'dayNames' => self::DAY_NAMES,
        ]);
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);

        $payload = array_filter((array) $request->input('days', []));
        $dayValues = [];

        foreach (self::DAY_NAMES as $day => $name) {
            if (isset($payload[$day]) && $payload[$day] !== '' && $payload[$day] !== null) {
                $dayValues[$day] = (int) $payload[$day];
            }
        }

        if ($dayValues !== []) {
            $validIds = WorkSchedule::whereIn('id', array_values($dayValues))
                ->pluck('id')
                ->all();

            foreach ($dayValues as $day => $scheduleId) {
                if (! in_array($scheduleId, $validIds, true)) {
                    return back()->withErrors([
                        'days' => "Jadwal untuk hari ".self::DAY_NAMES[$day]." tidak ditemukan.",
                    ])->withInput();
                }
            }
        }

        $employeeId = $employee->employee?->id;

        if (! $employeeId) {
            return back()->withErrors([
                'employee' => 'Data karyawan tidak ditemukan.',
            ]);
        }

        DB::transaction(function () use ($employeeId, $dayValues) {
            ScheduleAssignment::where('employee_id', $employeeId)->delete();

            foreach ($dayValues as $day => $scheduleId) {
                ScheduleAssignment::create([
                    'employee_id' => $employeeId,
                    'work_schedule_id' => $scheduleId,
                    'day_of_week' => $day,
                ]);
            }
        });

        return redirect()
            ->route('admin.assignments.index')
            ->with('success', 'Penempatan jadwal untuk '.$employee->name.' berhasil disimpan.');
    }
}