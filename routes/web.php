<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LecturerController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lecturer\SessionController as LecturerSessionController;
use App\Http\Controllers\SelfieController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — GeoAttend
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Core Routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/selfies/{attendance}', [SelfieController::class, 'show'])->name('selfies.show');

    // ------------------------------------------------------------------------
    // Administrator Routes
    // ------------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')->middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Academic CRUD
        Route::resource('users', UserController::class);
        Route::resource('students', StudentController::class);
        Route::resource('lecturers', LecturerController::class);
        Route::resource('courses', CourseController::class);
        Route::resource('schedules', ScheduleController::class);

        // Course enrollments
        Route::get('courses/{course}/enrollments', [CourseController::class, 'enrollments'])->name('courses.enrollments');
        Route::post('courses/{course}/enrollments', [CourseController::class, 'enrollStudent'])->name('courses.enroll');
        Route::delete('courses/{course}/enrollments/{student}', [CourseController::class, 'removeStudent'])->name('courses.unenroll');

        // Audit & Settings
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // ------------------------------------------------------------------------
    // Lecturer Routes
    // ------------------------------------------------------------------------
    Route::prefix('lecturer')->name('lecturer.')->middleware(['role:lecturer,admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'lecturerDashboard'])->name('dashboard');

        // Session & Attendance Management
        Route::get('/sessions', [LecturerSessionController::class, 'index'])->name('sessions.index');
        Route::get('/sessions/create/{schedule}', [LecturerSessionController::class, 'create'])->name('sessions.create');
        Route::post('/sessions', [LecturerSessionController::class, 'store'])->name('sessions.store');
        Route::get('/sessions/{session}', [LecturerSessionController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/open', [LecturerSessionController::class, 'open'])->name('sessions.open');
        Route::post('/sessions/{session}/close', [LecturerSessionController::class, 'close'])->name('sessions.close');

        // Dynamic QR View & Real-time APIs
        Route::get('/sessions/{session}/qr', [LecturerSessionController::class, 'qrView'])->name('sessions.qr');
        Route::get('/sessions/{session}/refresh-token', [LecturerSessionController::class, 'refreshToken'])->name('sessions.refresh_token');
        Route::get('/sessions/{session}/poll-attendees', [LecturerSessionController::class, 'pollAttendees'])->name('sessions.poll_attendees');

        // Attendance corrections & Reports
        Route::patch('/attendances/{attendance}/correct', [LecturerSessionController::class, 'correctAttendance'])->name('attendances.correct');
        Route::get('/sessions/{session}/export', [LecturerSessionController::class, 'export'])->name('sessions.export');
        Route::get('/courses/{course}/export', [LecturerSessionController::class, 'exportCourse'])->name('courses.export');
    });

    // ------------------------------------------------------------------------
    // Student Routes
    // ------------------------------------------------------------------------
    Route::prefix('student')->name('student.')->middleware(['role:student'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'studentDashboard'])->name('dashboard');
        Route::get('/history', [StudentAttendanceController::class, 'history'])->name('history');

        // 3-step Attendance Wizard
        Route::get('/sessions/{session}/attend', [StudentAttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/sessions/{session}/validate-location', [StudentAttendanceController::class, 'validateLocation'])->name('attendance.validate_location');
        Route::post('/sessions/{session}/attend', [StudentAttendanceController::class, 'store'])->name('attendance.store');
    });
});
