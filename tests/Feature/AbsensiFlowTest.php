<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbsensiFlowTest extends TestCase
{
    use RefreshDatabase;

    private const FAKE_SELFIE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    private User $admin;

    private User $employee;

    private AttendanceLocation $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@absensi.test')->first();
        $this->employee = User::where('email', 'budi@absensi.test')->first();
        $this->location = AttendanceLocation::where('name', 'Kantor Pusat Jakarta')->first();

        Storage::fake('public');
    }

    public function test_employee_can_open_attendance_flow_page(): void
    {
        $this->actingAs($this->employee)
            ->get(route('employee.absensi.index'))
            ->assertOk()
            ->assertSee('Mulai Kamera')
            ->assertSee('Scan QR Lokasi');
    }

    public function test_admin_cannot_open_employee_flow_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('employee.absensi.index'))
            ->assertForbidden();
    }

    public function test_verify_accepts_scanned_url(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.verify'), [
                'scan_value' => $this->location->scan_url,
            ])->assertOk()
            ->assertJson([
                'ok' => true,
                'mode' => 'checkin',
                'location' => [
                    'id' => $this->location->id,
                    'name' => $this->location->name,
                    'radius' => $this->location->radius,
                ],
            ]);
    }

    public function test_verify_accepts_plain_token(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.verify'), [
                'scan_value' => $this->location->public_token,
            ])->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_verify_rejects_unknown_token(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.verify'), [
                'scan_value' => 'https://example.test/absensi/scan/tokenti-dakdikenal',
            ])->assertStatus(404)
            ->assertJsonPath('ok', false);
    }

    public function test_verify_rejects_inactive_location(): void
    {
        $this->location->update(['status' => AttendanceLocation::STATUS_INACTIVE]);

        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.verify'), [
                'scan_value' => $this->location->scan_url,
            ])->assertStatus(404);
    }

    public function test_scan_url_redirects_to_flow_page_with_token(): void
    {
        $this->actingAs($this->employee)
            ->get(route('employee.absensi.scan', $this->location->public_token))
            ->assertRedirect(route('employee.absensi.index', ['token' => $this->location->public_token]));
    }

    public function test_checkin_creates_attendance_with_selfie(): void
    {
        $response = $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
                'selfie' => self::FAKE_SELFIE,
            ])->assertOk()->assertJsonPath('ok', true);

        $attendance = Attendance::where('employee_id', $this->employee->employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in);
        $this->assertNull($attendance->check_out);
        $this->assertEquals($this->location->id, $attendance->attendance_location_id);
        $this->assertMatchesRegularExpression('/^selfies\/\d{4}\/\d{2}\/\d{2}\//', $attendance->selfie_path);
        Storage::disk('public')->assertExists($attendance->selfie_path);
    }

    public function test_checkin_outside_radius_is_rejected(): void
    {
        $outsideLat = (float) $this->location->latitude + 0.01; // ~1.1 km away

        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => $outsideLat,
                'longitude' => (float) $this->location->longitude,
                'selfie' => self::FAKE_SELFIE,
            ])->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonStructure(['distance', 'radius']);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_duplicate_checkin_is_rejected(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
                'selfie' => self::FAKE_SELFIE,
            ])->assertOk();

        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
                'selfie' => self::FAKE_SELFIE,
            ])->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    public function test_checkin_without_selfie_is_rejected(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
            ])->assertStatus(422);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_checkin_with_inactive_location_is_rejected(): void
    {
        $this->location->update(['status' => AttendanceLocation::STATUS_INACTIVE]);

        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
                'selfie' => self::FAKE_SELFIE,
            ])->assertStatus(404);
    }

    public function test_mode_becomes_checkout_after_checkin(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
                'selfie' => self::FAKE_SELFIE,
            ])->assertOk();

        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.verify'), [
                'scan_value' => $this->location->scan_url,
            ])->assertOk()->assertJsonPath('mode', 'checkout');
    }

    public function test_checkout_completes_attendance(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkin'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
                'selfie' => self::FAKE_SELFIE,
            ])->assertOk();

        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkout'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
            ])->assertOk()->assertJsonPath('action', 'checkout');

        $attendance = Attendance::where('employee_id', $this->employee->employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance->check_out);
        $this->assertEquals(Attendance::STATUS_LEFT, $attendance->status);
    }

    public function test_checkout_without_checkin_is_rejected(): void
    {
        $this->actingAs($this->employee)
            ->postJson(route('employee.absensi.checkout'), [
                'location_id' => $this->location->id,
                'latitude' => (float) $this->location->latitude,
                'longitude' => (float) $this->location->longitude,
            ])->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    public function test_flow_page_shows_complete_state_after_full_day(): void
    {
        $this->actingAs($this->employee)->postJson(route('employee.absensi.checkin'), [
            'location_id' => $this->location->id,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
            'selfie' => self::FAKE_SELFIE,
        ])->assertOk();

        $this->actingAs($this->employee)->postJson(route('employee.absensi.checkout'), [
            'location_id' => $this->location->id,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
        ])->assertOk();

        $this->actingAs($this->employee)
            ->get(route('employee.absensi.index'))
            ->assertOk()
            ->assertSee('Absensi Hari Ini Selesai');
    }

    public function test_admin_attendance_index_lists_records(): void
    {
        $this->actingAs($this->employee)->postJson(route('employee.absensi.checkin'), [
            'location_id' => $this->location->id,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
            'selfie' => self::FAKE_SELFIE,
        ])->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.absensi.index', ['tanggal' => now()->toDateString()]))
            ->assertOk()
            ->assertSee($this->employee->name)
            ->assertSee($this->location->name);
    }

    public function test_admin_attendance_detail_shows_selfie(): void
    {
        $this->actingAs($this->employee)->postJson(route('employee.absensi.checkin'), [
            'location_id' => $this->location->id,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
            'selfie' => self::FAKE_SELFIE,
        ])->assertOk();

        $attendance = Attendance::where('employee_id', $this->employee->employee->id)->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.absensi.show', $attendance))
            ->assertOk()
            ->assertSee('Foto Selfie')
            ->assertSee('storage/selfies');
    }
}
