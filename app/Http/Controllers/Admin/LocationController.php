<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\AttendanceLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = AttendanceLocation::latest()->paginate(10);

        return view('admin.locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('admin.locations.create', [
            'location' => null,
            'statusOptions' => [AttendanceLocation::STATUS_ACTIVE => 'Aktif', AttendanceLocation::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        AttendanceLocation::create($validated + [
            'public_token' => AttendanceLocation::generateToken(),
        ]);

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Lokasi absensi berhasil ditambahkan.');
    }

    public function show(AttendanceLocation $location): View
    {
        return view('admin.locations.show', ['location' => $location]);
    }

    public function edit(AttendanceLocation $location): View
    {
        return view('admin.locations.edit', [
            'location' => $location,
            'statusOptions' => [AttendanceLocation::STATUS_ACTIVE => 'Aktif', AttendanceLocation::STATUS_INACTIVE => 'Nonaktif'],
        ]);
    }

    public function update(UpdateLocationRequest $request, AttendanceLocation $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Lokasi absensi berhasil diperbarui.');
    }

    public function regenerateToken(AttendanceLocation $location): RedirectResponse
    {
        $location->update(['public_token' => AttendanceLocation::generateToken()]);

        return redirect()
            ->route('admin.locations.show', $location)
            ->with('success', 'Public token berhasil digenerate ulang. QR Code lama tidak lagi berlaku.');
    }

    public function toggleStatus(AttendanceLocation $location): RedirectResponse
    {
        $location->update([
            'status' => $location->isActive() ? AttendanceLocation::STATUS_INACTIVE : AttendanceLocation::STATUS_ACTIVE,
        ]);

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Status lokasi diubah menjadi '.($location->isActive() ? 'aktif' : 'nonaktif').'.');
    }

    public function destroy(AttendanceLocation $location): RedirectResponse
    {
        if ($location->attendances()->exists()) {
            return redirect()
                ->route('admin.locations.index')
                ->with('error', 'Lokasi tidak dapat dihapus karena sudah memiliki data absensi. Nonaktifkan lokasi tersebut.');
        }

        $location->delete();

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Lokasi absensi berhasil dihapus.');
    }
}
