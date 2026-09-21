<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use App\Services\AttendanceStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AttendanceController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::guard('web')->user()->load(['employee']);

        $todayAttendance = $user->employee?->attendances()
            ->whereDate('date', now()->toDateString())
            ->with('location')
            ->first();

        $mode = match (true) {
            $todayAttendance?->hasCheckedIn() && $todayAttendance->hasCheckedOut() => 'complete',
            $todayAttendance?->hasCheckedIn() => 'checkout',
            default => 'checkin',
        };

        $activeLocation = AttendanceLocation::where('status', AttendanceLocation::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('employee.absensi', compact('user', 'todayAttendance', 'mode', 'activeLocation'));
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'scan_value' => ['required', 'string', 'max:255'],
        ]);

        $token = $this->extractToken($request->input('scan_value'));

        $location = AttendanceLocation::where('public_token', $token)
            ->where('status', AttendanceLocation::STATUS_ACTIVE)
            ->first();

        if (! $location) {
            return response()->json([
                'ok' => false,
                'message' => 'QR tidak dikenali atau lokasi tidak aktif. Gunakan QR Code dari lokasi yang tersedia.',
            ], 404);
        }

        $user = Auth::guard('web')->user();
        $todayAttendance = $user->employee?->attendances()
            ->whereDate('date', now()->toDateString())
            ->first();

        $mode = match (true) {
            $todayAttendance?->hasCheckedIn() && $todayAttendance->hasCheckedOut() => 'complete',
            $todayAttendance?->hasCheckedIn() => 'checkout',
            default => 'checkin',
        };

        return response()->json([
            'ok' => true,
            'mode' => $mode,
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'radius' => $location->radius,
            ],
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $data = $this->validateAttendancePayload($request, requireSelfie: true);

        if (! $data instanceof AttendanceLocation) {
            return $data;
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $user->employee) {
            return response()->json([
                'ok' => false,
                'message' => 'Profil karyawan tidak ditemukan. Hubungi administrator.',
            ], 422);
        }

        $existing = $user->employee->attendances()
            ->whereDate('date', now()->toDateString())
            ->whereNotNull('check_in')
            ->first();

        if ($existing) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda sudah melakukan check-in hari ini.',
            ], 422);
        }

        try {
            $selfiePath = $this->storeSelfie($request->input('selfie'));

            $checkInTime = now()->format('H:i:s');
            $status = app(AttendanceStatusService::class)
                ->statusAtCheckIn($user->employee, $checkInTime);

            $attendance = Attendance::create([
                'employee_id' => $user->employee->id,
                'attendance_location_id' => $data->id,
                'date' => now()->toDateString(),
                'check_in' => $checkInTime,
                'status' => $status,
                'selfie_path' => $selfiePath,
                'check_in_lat' => (float) $request->input('latitude'),
                'check_in_lng' => (float) $request->input('longitude'),
                'check_in_distance' => (int) round($data->distanceTo((float) $request->input('latitude'), (float) $request->input('longitude'))),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan selfie. Pastikan foto valid dan coba lagi.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'action' => 'checkin',
            'status' => $attendance->status,
            'attendance' => $attendance->refresh(),
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $data = $this->validateAttendancePayload($request, requireSelfie: false);

        if (! $data instanceof AttendanceLocation) {
            return $data;
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $user->employee) {
            return response()->json([
                'ok' => false,
                'message' => 'Profil karyawan tidak ditemukan. Hubungi administrator.',
            ], 422);
        }

        $attendance = $user->employee->attendances()
            ->whereDate('date', now()->toDateString())
            ->first();

        if (! $attendance || ! $attendance->hasCheckedIn()) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda belum melakukan check-in hari ini.',
            ], 422);
        }

        if ($attendance->hasCheckedOut()) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda sudah melakukan check-out hari ini.',
            ], 422);
        }

        $selfiePath = $attendance->selfie_path;

        try {
            if ($request->filled('selfie')) {
                $selfiePath = $this->storeSelfie($request->input('selfie'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan selfie. Pastikan foto valid dan coba lagi.',
            ], 422);
        }

        $currentStatus = $attendance->status;

        $attendance->update([
            'check_out' => now()->format('H:i:s'),
            'check_out_lat' => (float) $request->input('latitude'),
            'check_out_lng' => (float) $request->input('longitude'),
            'check_out_distance' => (int) round($data->distanceTo((float) $request->input('latitude'), (float) $request->input('longitude'))),
            'selfie_path' => $selfiePath,
            'status' => $currentStatus === Attendance::STATUS_LATE ? Attendance::STATUS_LATE : Attendance::STATUS_LEFT,
        ]);

        return response()->json([
            'ok' => true,
            'action' => 'checkout',
            'attendance' => $attendance->fresh(),
        ]);
    }

    private function validateAttendancePayload(Request $request, bool $requireSelfie): AttendanceLocation|JsonResponse
    {
        $rules = [
            'location_id' => ['required', 'integer'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'selfie' => $requireSelfie ? ['required', 'string'] : ['nullable', 'string'],
        ];

        $validated = $request->validate($rules);

        /** @var AttendanceLocation|null $location */
        $location = AttendanceLocation::where('id', $validated['location_id'])
            ->where('status', AttendanceLocation::STATUS_ACTIVE)
            ->first();

        if (! $location) {
            return response()->json([
                'ok' => false,
                'message' => 'Lokasi tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        if (! $location->isWithinRadius((float) $validated['latitude'], (float) $validated['longitude'])) {
            $distance = (int) round($location->distanceTo((float) $validated['latitude'], (float) $validated['longitude']));

            return response()->json([
                'ok' => false,
                'message' => "Anda berada di luar radius absensi. Jarak {$distance} m dari titik lokasi, padahal radius {$location->radius} m.",
                'distance' => $distance,
                'radius' => $location->radius,
            ], 422);
        }

        return $location;
    }

    private function extractToken(string $scanValue): ?string
    {
        if (preg_match('/absensi\/scan\/([A-Za-z0-9]+)/', $scanValue, $matches)) {
            return $matches[1];
        }

        $trimmed = trim($scanValue);

        return preg_match('/^[A-Za-z0-9]{16,64}$/', $trimmed) ? $trimmed : null;
    }

    private function storeSelfie(string $dataUri): string
    {
        if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $dataUri, $matches)) {
            throw new \InvalidArgumentException('Format foto tidak valid.');
        }

        $contents = base64_decode(substr($dataUri, strpos($dataUri, ',') + 1), true);

        if ($contents === false) {
            throw new \InvalidArgumentException('Data foto tidak valid.');
        }

        if (strlen($contents) > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Ukuran foto terlalu besar.');
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $filename = now()->format('Ymd_His').'_'.auth('web')->id().'_'.Str::random(6).'.'.$extension;
        $path = 'selfies/'.now()->format('Y/m/d').'/'.$filename;

        if (! Storage::disk('public')->put($path, $contents)) {
            throw new \RuntimeException('Gagal menulis file selfie.');
        }

        return $path;
    }
}
