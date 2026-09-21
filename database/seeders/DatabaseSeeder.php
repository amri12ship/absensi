<?php

namespace Database\Seeders;

use App\Models\AttendanceLocation;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->delete();
        Employee::query()->delete();
        AttendanceLocation::query()->delete();
        WorkSchedule::query()->delete();
        Holiday::query()->delete();

        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@absensi.test',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $employees = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@absensi.test',
                'nik' => '2026000001',
                'phone' => '081234567801',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'position' => 'Staff IT',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@absensi.test',
                'nik' => '2026000002',
                'phone' => '081234567802',
                'address' => 'Jl. Sudirman No. 22, Jakarta',
                'position' => 'Administrasi',
            ],
        ];

        foreach ($employees as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => 'password123',
                'role' => User::ROLE_EMPLOYEE,
                'status' => User::STATUS_ACTIVE,
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'nik' => $data['nik'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'position' => $data['position'],
            ]);

            $this->assignDefaultSchedules($employee->id);
        }

        $this->seedDefaultHoliday();

        $locations = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'address' => 'Jl. Jenderal Sudirman Kav. 52, Jakarta Selatan',
                'latitude' => -6.2254000,
                'longitude' => 106.8065000,
                'radius' => 100,
            ],
            [
                'name' => 'Cabang Bandung',
                'address' => 'Jl. Asia Afrika No. 8, Bandung',
                'latitude' => -6.9175000,
                'longitude' => 107.6191000,
                'radius' => 80,
            ],
        ];

        foreach ($locations as $data) {
            AttendanceLocation::create($data + [
                'public_token' => AttendanceLocation::generateToken(),
                'status' => AttendanceLocation::STATUS_ACTIVE,
            ]);
        }
    }

    private function assignDefaultSchedules(int $employeeId): void
    {
        $reguler = WorkSchedule::firstOrCreate([
            'name' => 'Reguler',
        ], [
            'name' => 'Reguler',
            'time_in' => '08:00',
            'time_out' => '17:00',
            'tolerance_minutes' => 15,
            'status' => WorkSchedule::STATUS_ACTIVE,
        ]);

        $shiftPagi = WorkSchedule::firstOrCreate([
            'name' => 'Shift Pagi',
        ], [
            'name' => 'Shift Pagi',
            'time_in' => '07:00',
            'time_out' => '15:00',
            'tolerance_minutes' => 10,
            'status' => WorkSchedule::STATUS_ACTIVE,
        ]);

        foreach ([1, 2, 3, 4, 5] as $day) {
            ScheduleAssignment::firstOrCreate([
                'employee_id' => $employeeId,
                'day_of_week' => $day,
            ], [
                'employee_id' => $employeeId,
                'work_schedule_id' => $reguler->id,
                'day_of_week' => $day,
            ]);
        }

        ScheduleAssignment::firstOrCreate([
            'employee_id' => $employeeId,
            'day_of_week' => 6,
        ], [
            'employee_id' => $employeeId,
            'work_schedule_id' => $shiftPagi->id,
            'day_of_week' => 6,
        ]);
    }

    private function seedDefaultHoliday(): void
    {
        $date = Carbon::now()->addMonthNoOverflow()->startOfMonth()->addDays(10);

        if (! Holiday::whereDate('date', $date->toDateString())->exists()) {
            Holiday::create([
                'name' => 'Cuti Bersama Contoh',
                'date' => $date->toDateString(),
                'description' => 'Data contoh hari libur untuk menguji fitur.',
                'status' => Holiday::STATUS_ACTIVE,
            ]);
        }
    }
}
