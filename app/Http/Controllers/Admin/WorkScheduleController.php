<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkScheduleRequest;
use App\Http\Requests\UpdateWorkScheduleRequest;
use App\Models\WorkSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = WorkSchedule::latest()->paginate(10);

        return view('admin.schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        return view('admin.schedules.create', [
            'schedule' => null,
            'statusOptions' => [WorkSchedule::STATUS_ACTIVE => 'Aktif', WorkSchedule::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function store(StoreWorkScheduleRequest $request): RedirectResponse
    {
        WorkSchedule::create($request->validated());

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function edit(WorkSchedule $schedule): View
    {
        return view('admin.schedules.edit', [
            'schedule' => $schedule,
            'statusOptions' => [WorkSchedule::STATUS_ACTIVE => 'Aktif', WorkSchedule::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function update(UpdateWorkScheduleRequest $request, WorkSchedule $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function toggleStatus(WorkSchedule $schedule): RedirectResponse
    {
        $schedule->update([
            'status' => $schedule->isActive() ? WorkSchedule::STATUS_INACTIVE : WorkSchedule::STATUS_ACTIVE,
        ]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Status jadwal diubah menjadi '.($schedule->isActive() ? 'aktif' : 'nonaktif').'.');
    }

    public function destroy(WorkSchedule $schedule): RedirectResponse
    {
        if ($schedule->assignments()->exists()) {
            return redirect()
                ->route('admin.schedules.index')
                ->with('error', 'Jadwal tidak dapat dihapus karena sudah digunakan oleh penempatan jadwal karyawan. Ubah penempatannya terlebih dahulu.');
        }

        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil dihapus.');
    }
}