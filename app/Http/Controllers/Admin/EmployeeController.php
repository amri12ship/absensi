<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = User::with('employee')
            ->where('role', User::ROLE_EMPLOYEE)
            ->latest()
            ->paginate(10);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('admin.employees.create', [
            'employee' => null,
            'statusOptions' => [User::STATUS_ACTIVE => 'Aktif', User::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_EMPLOYEE,
            'status' => $validated['status'],
        ]);

        Employee::create([
            'user_id' => $user->id,
            'nik' => $validated['nik'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'position' => $validated['position'],
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(User $employee): View
    {
        abort_unless($employee->isEmployee(), 404);

        return view('admin.employees.show', [
            'employee' => $employee->load('employee'),
            'todayAttendance' => $employee->employee?->attendances()
                ->whereDate('date', now()->toDateString())
                ->with('location')
                ->first(),
        ]);
    }

    public function edit(User $employee): View
    {
        abort_unless($employee->isEmployee(), 404);

        return view('admin.employees.edit', [
            'employee' => $employee->load('employee'),
            'statusOptions' => [User::STATUS_ACTIVE => 'Aktif', User::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);

        $validated = $request->validated();

        $employee->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ]);

        if (! empty($validated['password'])) {
            $employee->update(['password' => $validated['password']]);
        }

        $employee->employee->update([
            'nik' => $validated['nik'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'position' => $validated['position'],
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function toggleStatus(User $employee): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);

        $employee->update([
            'status' => $employee->isActive() ? User::STATUS_INACTIVE : User::STATUS_ACTIVE,
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Status karyawan diubah menjadi '.($employee->isActive() ? 'aktif' : 'nonaktif').'.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);

        if ($employee->employee && $employee->employee->attendances()->exists()) {
            $employee->employee->delete();
            $employee->update(['status' => User::STATUS_INACTIVE]);

            return redirect()
                ->route('admin.employees.index')
                ->with('info', 'Karyawan memiliki riwayat absensi sehingga akun hanya dinonaktifkan dan data absensi dipertahankan.');
        }

        if ($employee->employee) {
            $employee->employee->forceDelete();
        }
        $employee->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }
}
