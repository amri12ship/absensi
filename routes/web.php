<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleAssignmentController;
use App\Http\Controllers\Admin\WorkScheduleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\HistoryController;
use App\Http\Controllers\Employee\ScanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route($request->user()->isAdmin() ? 'admin.dashboard' : 'employee.dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::resource('employees', EmployeeController::class)->except(['destroy']);
            Route::post('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
            Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

            Route::resource('locations', LocationController::class)->except(['destroy']);
            Route::post('locations/{location}/regenerate-token', [LocationController::class, 'regenerateToken'])->name('locations.regenerate-token');
            Route::post('locations/{location}/toggle-status', [LocationController::class, 'toggleStatus'])->name('locations.toggle-status');
            Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

            Route::get('qrcodes', [QrCodeController::class, 'index'])->name('qrcodes.index');
            Route::get('qrcodes/{location}', [QrCodeController::class, 'show'])->name('qrcodes.show');
            Route::get('qrcodes/{location}/print', [QrCodeController::class, 'print'])->name('qrcodes.print');

            Route::get('absensi', [AdminAttendanceController::class, 'index'])->name('absensi.index');
            Route::get('absensi/{attendance}', [AdminAttendanceController::class, 'show'])->name('absensi.show');

            Route::resource('schedules', WorkScheduleController::class)->except(['show']);
            Route::post('schedules/{schedule}/toggle-status', [WorkScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status');

            Route::get('assignments', [ScheduleAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('assignments/{employee}', [ScheduleAssignmentController::class, 'edit'])->name('assignments.edit');
            Route::put('assignments/{employee}', [ScheduleAssignmentController::class, 'update'])->name('assignments.update');

            Route::resource('holidays', HolidayController::class)->except(['show']);
            Route::post('holidays/{holiday}/toggle-status', [HolidayController::class, 'toggleStatus'])->name('holidays.toggle-status');

            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
            Route::get('reports/print', [ReportController::class, 'print'])->name('reports.print');
        });

    Route::name('employee.')
        ->group(function () {
            Route::get('dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

            Route::get('absensi', [EmployeeAttendanceController::class, 'index'])->name('absensi.index');
            Route::post('absensi/verify', [EmployeeAttendanceController::class, 'verify'])->name('absensi.verify');
            Route::post('absensi/checkin', [EmployeeAttendanceController::class, 'checkIn'])->name('absensi.checkin');
            Route::post('absensi/checkout', [EmployeeAttendanceController::class, 'checkOut'])->name('absensi.checkout');

            Route::get('absensi/scan/{token}', [ScanController::class, 'scan'])->name('absensi.scan');

            Route::get('riwayat', [HistoryController::class, 'index'])->name('history.index');
            Route::get('riwayat/kalender', [HistoryController::class, 'calendar'])->name('history.calendar');
        });
});
