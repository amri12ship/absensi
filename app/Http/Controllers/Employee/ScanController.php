<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use Illuminate\Http\RedirectResponse;

class ScanController extends Controller
{
    public function scan(string $token): RedirectResponse
    {
        $location = AttendanceLocation::where('public_token', $token)
            ->where('status', AttendanceLocation::STATUS_ACTIVE)
            ->first();

        if (! $location) {
            abort(404, 'Lokasi absensi tidak ditemukan atau tidak aktif.');
        }

        return redirect()->route('employee.absensi.index', ['token' => $location->public_token]);
    }
}
