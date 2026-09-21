<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        $holidays = Holiday::latest('date')->paginate(15);

        return view('admin.holidays.index', compact('holidays'));
    }

    public function create(): View
    {
        return view('admin.holidays.create', [
            'holiday' => null,
            'statusOptions' => [Holiday::STATUS_ACTIVE => 'Aktif', Holiday::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        Holiday::create($request->validated());

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function edit(Holiday $holiday): View
    {
        return view('admin.holidays.edit', [
            'holiday' => $holiday,
            'statusOptions' => [Holiday::STATUS_ACTIVE => 'Aktif', Holiday::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $holiday->update($request->validated());

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function toggleStatus(Holiday $holiday): RedirectResponse
    {
        $holiday->update([
            'status' => $holiday->isActive() ? Holiday::STATUS_INACTIVE : Holiday::STATUS_ACTIVE,
        ]);

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Status hari libur diubah menjadi '.($holiday->isActive() ? 'aktif' : 'nonaktif').'.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil dihapus.');
    }
}