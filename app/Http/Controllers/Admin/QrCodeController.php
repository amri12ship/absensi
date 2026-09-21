<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Services\QrCodeService;
use Illuminate\View\View;

class QrCodeController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCodeService) {}

    public function index(): View
    {
        $locations = AttendanceLocation::latest()->get();

        $qrcodes = $locations->map(fn (AttendanceLocation $location) => [
            'location' => $location,
            'qr' => $this->qrCodeService->dataUri($location->scan_url, 120),
        ]);

        return view('admin.qrcodes.index', compact('qrcodes'));
    }

    public function show(AttendanceLocation $location): View
    {
        return view('admin.qrcodes.show', [
            'location' => $location,
            'qr' => $this->qrCodeService->dataUri($location->scan_url, 400),
        ]);
    }

    public function print(AttendanceLocation $location): View
    {
        return view('admin.qrcodes.print', [
            'location' => $location,
            'qr' => $this->qrCodeService->dataUri($location->scan_url, 500),
        ]);
    }
}
