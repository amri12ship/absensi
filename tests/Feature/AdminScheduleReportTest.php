<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Holiday;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\SpreadsheetService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use ZipArchive;

class AdminScheduleReportTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@absensi.test')->first();
    }

    private function employeeUser(): User
    {
        return User::where('email', 'budi@absensi.test')->first();
    }

    public function test_admin_sees_schedule_pages(): void
    {
        $admin = $this->seedData();

        $this->actingAs($admin)->get(route('admin.schedules.index'))->assertOk()->assertSee('Jadwal Kerja');
        $this->actingAs($admin)->get(route('admin.schedules.create'))->assertOk();
    }

    public function test_admin_can_create_schedule(): void
    {
        $admin = $this->seedData();

        $this->actingAs($admin)->post(route('admin.schedules.store'), [
            'name' => 'Shift Sore',
            'time_in' => '13:00',
            'time_out' => '21:00',
            'tolerance_minutes' => 15,
            'status' => WorkSchedule::STATUS_ACTIVE,
        ])->assertRedirect(route('admin.schedules.index'));

        $this->assertDatabaseHas('work_schedules', ['name' => 'Shift Sore', 'time_in' => '13:00', 'time_out' => '21:00']);
    }

    public function test_admin_cannot_create_schedule_with_invalid_time(): void
    {
        $admin = $this->seedData();

        $this->actingAs($admin)->post(route('admin.schedules.store'), [
            'name' => 'Salah',
            'time_in' => '21:00',
            'time_out' => '13:00',
            'tolerance_minutes' => 15,
            'status' => WorkSchedule::STATUS_ACTIVE,
        ])->assertSessionHasErrors('time_out');

        $this->assertDatabaseMissing('work_schedules', ['name' => 'Salah']);
    }

    public function test_admin_can_toggle_schedule_status(): void
    {
        $admin = $this->seedData();
        $schedule = WorkSchedule::where('name', 'Reguler')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.schedules.toggle-status', $schedule))
            ->assertRedirect(route('admin.schedules.index'));

        $this->assertEquals(WorkSchedule::STATUS_INACTIVE, $schedule->fresh()->status);
    }

    public function test_admin_cannot_delete_schedule_in_use(): void
    {
        $admin = $this->seedData();
        $schedule = WorkSchedule::where('name', 'Reguler')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule))
            ->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('work_schedules', ['id' => $schedule->id]);
    }

    public function test_admin_sees_all_assignments_for_employees(): void
    {
        $admin = $this->seedData();

        $this->actingAs($admin)->get(route('admin.assignments.index'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Siti Aminah');
    }

    public function test_admin_can_update_employee_assignment(): void
    {
        $admin = $this->seedData();
        $employee = $this->employeeUser();
        $shiftSore = WorkSchedule::create([
            'name' => 'Shift Sore',
            'time_in' => '13:00',
            'time_out' => '21:00',
            'tolerance_minutes' => 10,
            'status' => WorkSchedule::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)->put(route('admin.assignments.update', $employee), [
            'days' => [1 => $shiftSore->id, 3 => $shiftSore->id],
        ])->assertRedirect(route('admin.assignments.index'));

        $assignments = $employee->employee->scheduleAssignments;
        $this->assertCount(2, $assignments);
        $this->assertTrue($assignments->contains('day_of_week', 1));
        $this->assertTrue($assignments->contains('day_of_week', 3));
        $this->assertTrue($assignments->every(fn ($a) => $a->work_schedule_id === $shiftSore->id));
    }

    public function test_admin_can_clear_employee_assignment(): void
    {
        $admin = $this->seedData();
        $employee = $this->employeeUser();

        $this->actingAs($admin)->put(route('admin.assignments.update', $employee), [
            'days' => [],
        ])->assertRedirect(route('admin.assignments.index'));

        $this->assertDatabaseCount('schedule_assignments', 6); // only other employee left
    }

    public function test_admin_holiday_crud(): void
    {
        $admin = $this->seedData();

        $this->actingAs($admin)->get(route('admin.holidays.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.holidays.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.holidays.store'), [
            'name' => 'Tahun Baru',
            'date' => now()->addDays(20)->toDateString(),
            'description' => 'Libur nasional',
            'status' => Holiday::STATUS_ACTIVE,
        ])->assertRedirect(route('admin.holidays.index'));

        $holiday = Holiday::where('name', 'Tahun Baru')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.holidays.update', $holiday), [
            'name' => 'Tahun Baru 2027',
            'date' => now()->addDays(21)->toDateString(),
            'description' => 'Update',
            'status' => Holiday::STATUS_INACTIVE,
        ])->assertRedirect(route('admin.holidays.index'));

        $this->assertEquals('Tahun Baru 2027', $holiday->fresh()->name);
        $this->assertEquals(Holiday::STATUS_INACTIVE, $holiday->fresh()->status);
    }

    public function test_admin_can_toggle_holiday_status(): void
    {
        $admin = $this->seedData();
        $holiday = Holiday::firstOrFail();

        $this->actingAs($admin)->post(route('admin.holidays.toggle-status', $holiday))
            ->assertRedirect(route('admin.holidays.index'));

        $this->assertEquals(
            Holiday::STATUS_ACTIVE,
            (($holiday->fresh()->status === Holiday::STATUS_ACTIVE) ? Holiday::STATUS_INACTIVE : Holiday::STATUS_ACTIVE),
        );
    }

    public function test_employee_cannot_access_schedule_assignment_holiday_report_pages(): void
    {
        $this->seedData();
        User::where('email', 'siti@absensi.test')->first();

        $employee = $this->employeeUser();

        $this->actingAs($employee)->get(route('admin.schedules.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.assignments.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.holidays.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('employee.dashboard'))->assertOk();
    }

    public function test_report_index_shows_rekap_and_detail(): void
    {
        $admin = $this->seedData();
        $employee = $this->employeeUser();
        $location = AttendanceLocation::where('name', 'Kantor Pusat Jakarta')->firstOrFail();

        Attendance::create([
            'employee_id' => $employee->employee->id,
            'attendance_location_id' => $location->id,
            'date' => now()->toDateString(),
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'status' => Attendance::STATUS_PRESENT,
            'check_in_distance' => 45,
        ]);

        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Rekap Absensi')
            ->assertSee('Budi Santoso')
            ->assertSee('45 m')
            ->assertSee('Export Excel')
            ->assertSee('Print');
    }

    public function test_report_empty_state_message(): void
    {
        $admin = $this->seedData();

        $this->actingAs($admin)->get(route('admin.reports.index', [
            'dari' => now()->subYear()->format('Y-m-d'),
            'sampai' => now()->subYear()->addMonth()->format('Y-m-d'),
        ]))->assertOk()->assertSee('Tidak ada data absensi pada periode yang dipilih.');
    }

    public function test_report_export_produces_valid_xlsx(): void
    {
        $admin = $this->seedData();
        $employee = $this->employeeUser();
        $location = AttendanceLocation::where('name', 'Kantor Pusat Jakarta')->firstOrFail();

        Attendance::create([
            'employee_id' => $employee->employee->id,
            'attendance_location_id' => $location->id,
            'date' => now()->toDateString(),
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'status' => Attendance::STATUS_LATE,
            'check_in_distance' => 120,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.export'));

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type') ?? '',
        );
        $this->assertStringContainsString('laporan-absensi-', $response->headers->get('Content-Disposition') ?? '');

        $temp = tempnam(sys_get_temp_dir(), 'xl_');
        $this->assertTrue(file_put_contents($temp, '') !== false);

        (new SpreadsheetService)->build($temp, [
            'Tanggal', 'Nama', 'NIK', 'Jabatan', 'Lokasi', 'Jam Masuk', 'Jam Keluar', 'Status', 'Jarak Check-in (m)',
        ], [
            ['2026-09-21', 'Budi Santoso', '2026000001', 'IT Staff', 'Kantor Pusat Jakarta', '08:30:00', '17:00:00', 'Terlambat', 120],
        ]);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($temp) === true);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertNotFalse($sheetXml);
        $this->assertStringContainsString('Tanggal', $sheetXml);
        $this->assertStringContainsString('Budi Santoso', $sheetXml);
        $this->assertStringContainsString('Terlambat', $sheetXml);
        $this->assertNotFalse($zip->getFromName('xl/workbook.xml'));
        $this->assertNotFalse($zip->getFromName('_rels/.rels'));
        $zip->close();

        unlink($temp);
    }

    public function test_report_export_blocked_when_empty(): void
    {
        $admin = $this->seedData();

        $url = route('admin.reports.export', [
            'dari' => now()->subYear()->format('Y-m-d'),
            'sampai' => now()->subYear()->addMonth()->format('Y-m-d'),
        ]);

        $this->actingAs($admin)->from(route('admin.reports.index'))
            ->get($url)
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHas('error', 'Tidak ada data absensi pada periode yang dipilih, file Excel tidak dibuat.');
    }

    public function test_report_print_page_has_no_sidebar(): void
    {
        $admin = $this->seedData();

        $this->actingAs($admin)->get(route('admin.reports.print'))
            ->assertOk()
            ->assertSee('Laporan Absensi Periode')
            ->assertDontSee('Mulai Absensi')
            ->assertSee('Administrator');
    }

    public function test_checkin_after_tolerance_is_late(): void
    {
        $this->seedData();
        $employee = $this->employeeUser();
        $location = AttendanceLocation::where('name', 'Kantor Pusat Jakarta')->firstOrFail();

        Carbon::setTestNow('2026-09-21 08:30:00'); // Senin, jadwal masuk 08:00, toleransi 15 menit

        try {
            $this->actingAs($employee)->postJson(route('employee.absensi.checkin'), [
                'location_id' => $location->id,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'selfie' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            ])->assertOk()->assertJsonPath('status', Attendance::STATUS_LATE);

            $this->assertDatabaseHas('attendances', [
                'employee_id' => $employee->employee->id,
                'status' => Attendance::STATUS_LATE,
                'check_in' => '08:30:00',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_employee_dashboard_shows_holiday(): void
    {
        $this->seedData();
        $employee = $this->employeeUser();

        Holiday::create([
            'name' => 'Cuti Bersama',
            'date' => now()->toDateString(),
            'description' => null,
            'status' => Holiday::STATUS_ACTIVE,
        ]);

        $this->actingAs($employee)->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSee('Libur')
            ->assertSee('Cuti Bersama');
    }
}