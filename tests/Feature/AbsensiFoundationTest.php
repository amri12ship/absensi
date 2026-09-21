<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): void
    {
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@absensi.test')->first();
    }

    private function employeeUser(): User
    {
        return User::where('email', 'budi@absensi.test')->first();
    }

    public function test_guest_redirected_to_login_from_root(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_guest_blocked_from_protected_pages(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/profil')->assertRedirect(route('login'));
    }

    public function test_admin_can_login(): void
    {
        $this->seedData();
        $this->post(route('login.attempt'), [
            'email' => 'admin@absensi.test',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin());
    }

    public function test_employee_can_login(): void
    {
        $this->seedData();
        $this->post(route('login.attempt'), [
            'email' => 'budi@absensi.test',
            'password' => 'password123',
        ])->assertRedirect(route('employee.dashboard'));
        $this->assertAuthenticatedAs($this->employeeUser());
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->seedData();
        $this->post(route('login.attempt'), [
            'email' => 'admin@absensi.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_dashboard_shows_stats(): void
    {
        $this->seedData();
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Karyawan')
            ->assertSee('Absensi Hari Ini');
    }

    public function test_admin_can_create_employee(): void
    {
        $this->seedData();
        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), [
                'name' => 'Rina Febriani',
                'email' => 'rina@absensi.test',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'nik' => '2026000099',
                'phone' => '081299999999',
                'address' => 'Jl. Melati No. 1',
                'position' => 'HRD',
                'status' => User::STATUS_ACTIVE,
            ])->assertRedirect(route('admin.employees.index'));

        $created = User::where('email', 'rina@absensi.test')->first();
        $this->assertNotNull($created);
        $this->assertNotEquals('secret123', $created->password);
        $this->assertTrue(password_verify('secret123', $created->password));
        $this->assertEquals(User::ROLE_EMPLOYEE, $created->role);
        $this->assertNotNull($created->employee);
        $this->assertEquals('2026000099', $created->employee->nik);
    }

    public function test_admin_cannot_create_duplicate_nik_or_email(): void
    {
        $this->seedData();
        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), [
                'name' => 'Duplikat',
                'email' => 'budi@absensi.test',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'nik' => '2026000001',
                'position' => 'Staff',
                'status' => User::STATUS_ACTIVE,
            ])->assertSessionHasErrors(['email', 'nik']);
    }

    public function test_admin_can_update_employee_without_changing_password(): void
    {
        $this->seedData();
        $user = $this->employeeUser();
        $oldHash = $user->password;

        $this->actingAs($this->admin())
            ->put(route('admin.employees.update', $user), [
                'name' => 'Budi Santoso Baru',
                'email' => 'budi@absensi.test',
                'nik' => '2026000001',
                'phone' => '081200000001',
                'address' => 'Jl. Baru',
                'position' => 'Senior IT',
                'status' => User::STATUS_ACTIVE,
            ])->assertRedirect(route('admin.employees.index'));

        $user->refresh();
        $this->assertEquals('Budi Santoso Baru', $user->name);
        $this->assertEquals('Senior IT', $user->employee->position);
        $this->assertEquals($oldHash, $user->password, 'Password tidak boleh berubah jika dikosongkan.');
    }

    public function test_admin_can_update_employee_with_new_password(): void
    {
        $this->seedData();
        $user = $this->employeeUser();

        $this->actingAs($this->admin())
            ->put(route('admin.employees.update', $user), [
                'name' => 'Budi Santoso',
                'email' => 'budi@absensi.test',
                'nik' => '2026000001',
                'position' => 'Staff IT',
                'status' => User::STATUS_ACTIVE,
                'password' => 'newpassword456',
                'password_confirmation' => 'newpassword456',
            ])->assertRedirect(route('admin.employees.index'));

        $this->assertTrue(password_verify('newpassword456', $user->fresh()->password));
    }

    public function test_inactive_employee_cannot_login_or_attend(): void
    {
        $this->seedData();
        $user = $this->employeeUser();
        $user->update(['status' => User::STATUS_INACTIVE]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)->get(route('employee.dashboard'))->assertRedirect(route('login'));
    }

    public function test_employee_cannot_access_admin_pages(): void
    {
        $this->seedData();
        $this->actingAs($this->employeeUser())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
        $this->actingAs($this->employeeUser())
            ->get(route('admin.employees.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_employee_dashboard(): void
    {
        $this->seedData();
        $this->actingAs($this->admin())
            ->get(route('employee.dashboard'))
            ->assertForbidden();
    }

    public function test_employee_dashboard_shows_profile(): void
    {
        $this->seedData();
        $this->actingAs($this->employeeUser())
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('2026000001');
    }

    public function test_admin_can_toggle_employee_status(): void
    {
        $this->seedData();
        $user = $this->employeeUser();
        $this->actingAs($this->admin())
            ->post(route('admin.employees.toggle-status', $user))
            ->assertRedirect(route('admin.employees.index'));

        $this->assertEquals(User::STATUS_INACTIVE, $user->fresh()->status);
    }

    public function test_employee_without_attendance_is_hard_deleted(): void
    {
        $this->seedData();
        $user = $this->employeeUser();
        $employeeId = $user->employee->id;

        $this->actingAs($this->admin())
            ->delete(route('admin.employees.destroy', $user))
            ->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('employees', ['id' => $employeeId]);
    }

    public function test_employee_with_attendance_is_soft_disabled_not_hard_deleted(): void
    {
        $this->seedData();
        $user = $this->employeeUser();

        Attendance::create([
            'employee_id' => $user->employee->id,
            'attendance_location_id' => AttendanceLocation::first()->id,
            'date' => now()->toDateString(),
            'check_in' => '08:00:00',
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.employees.destroy', $user))
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('info');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => User::STATUS_INACTIVE]);
        $this->assertSoftDeleted('employees', ['id' => $user->employee->id]);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_admin_can_create_location_with_gps_coordinates(): void
    {
        $this->seedData();
        $this->actingAs($this->admin())
            ->post(route('admin.locations.store'), [
                'name' => 'Kantor Cabang Medan',
                'address' => 'Jl. Gatot Subroto No. 1',
                'latitude' => 3.5952000,
                'longitude' => 98.6722000,
                'radius' => 150,
                'status' => AttendanceLocation::STATUS_ACTIVE,
            ])->assertRedirect(route('admin.locations.index'));

        $location = AttendanceLocation::where('name', 'Kantor Cabang Medan')->first();
        $this->assertNotNull($location);
        $this->assertEquals('3.5952000', (string) $location->latitude);
        $this->assertEquals('98.6722000', (string) $location->longitude);
        $this->assertEquals(150, $location->radius);
        $this->assertNotEmpty($location->public_token);
    }

    public function test_location_validation_rejects_bad_coordinates(): void
    {
        $this->seedData();
        $this->actingAs($this->admin())
            ->post(route('admin.locations.store'), [
                'name' => 'Invalid',
                'latitude' => 95.0,
                'longitude' => 200.0,
                'radius' => -10,
                'status' => AttendanceLocation::STATUS_ACTIVE,
            ])->assertSessionHasErrors(['latitude', 'longitude', 'radius']);
    }

    public function test_location_token_is_unique(): void
    {
        $this->seedData();
        $tokens = AttendanceLocation::pluck('public_token');
        $this->assertCount($tokens->unique()->count(), $tokens);
    }

    public function test_admin_can_regenerate_location_token(): void
    {
        $this->seedData();
        $location = AttendanceLocation::first();
        $oldToken = $location->public_token;

        $this->actingAs($this->admin())
            ->post(route('admin.locations.regenerate-token', $location))
            ->assertRedirect(route('admin.locations.show', $location));

        $this->assertNotEquals($oldToken, $location->fresh()->public_token);
    }

    public function test_admin_can_toggle_location_status(): void
    {
        $this->seedData();
        $location = AttendanceLocation::first();
        $this->actingAs($this->admin())
            ->post(route('admin.locations.toggle-status', $location))
            ->assertRedirect(route('admin.locations.index'));
        $this->assertEquals(AttendanceLocation::STATUS_INACTIVE, $location->fresh()->status);
    }

    public function test_qr_code_pages_render(): void
    {
        $this->seedData();
        $location = AttendanceLocation::first();

        $this->actingAs($this->admin())
            ->get(route('admin.qrcodes.index'))
            ->assertOk();

        $this->actingAs($this->admin())
            ->get(route('admin.qrcodes.show', $location))
            ->assertOk()
            ->assertSee($location->scan_url);

        $this->actingAs($this->admin())
            ->get(route('admin.qrcodes.print', $location))
            ->assertOk();
    }

    public function test_scan_page_resolves_active_location_token(): void
    {
        $this->seedData();
        $location = AttendanceLocation::first();
        $token = $location->public_token;

        $this->actingAs($this->employeeUser())
            ->get(route('employee.absensi.scan', $token))
            ->assertRedirect(route('employee.absensi.index', ['token' => $token]));
    }

    public function test_scan_page_rejects_unknown_token(): void
    {
        $this->seedData();
        $this->actingAs($this->employeeUser())
            ->get(route('employee.absensi.scan', 'token-tidak-dikenal'))
            ->assertNotFound();
    }

    public function test_profile_update_flow(): void
    {
        $this->seedData();
        $user = $this->employeeUser();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Budi Santoso Update',
                'phone' => '087777777777',
                'address' => 'Alamat updated',
                'position' => 'Staff IT',
            ])->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertEquals('Budi Santoso Update', $user->name);
        $this->assertEquals('087777777777', $user->employee->phone);
    }
}
