<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Redirect authenticated user to their role-specific dashboard.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => $this->adminDashboard(),
            'lecturer' => $this->lecturerDashboard(),
            'student' => $this->studentDashboard(),
            default => redirect()->route('login'),
        };
    }

    /**
     * Admin Dashboard with system-wide analytics.
     */
    public function adminDashboard(): View
    {
        $totalStudents = Student::count();
        $totalLecturers = Lecturer::count();
        $totalCourses = Course::count();
        $totalSessions = AttendanceSession::count();
        $totalAttendances = Attendance::count();

        // Status counts
        $statusCounts = [
            'present' => Attendance::where('status', 'present')->count(),
            'late' => Attendance::where('status', 'late')->count(),
            'excused' => Attendance::where('status', 'excused')->count(),
            'sick' => Attendance::where('status', 'sick')->count(),
            'absent' => Attendance::where('status', 'absent')->count(),
        ];

        // Active sessions right now
        $activeSessions = AttendanceSession::with(['schedule.course', 'creator'])
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        // Recent audit logs
        $recentAuditLogs = AuditLog::with('user')
            ->latest('id')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalLecturers',
            'totalCourses',
            'totalSessions',
            'totalAttendances',
            'statusCounts',
            'activeSessions',
            'recentAuditLogs'
        ));
    }

    /**
     * Lecturer Dashboard with teaching schedules, active sessions, and attendance metrics.
     */
    public function lecturerDashboard(): View
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        $courses = $lecturer ? $lecturer->courses()->withCount('students')->get() : collect();
        $schedules = $lecturer ? $lecturer->schedules()->with('course')->get() : collect();

        // Find active sessions belonging to this lecturer
        $activeSessions = AttendanceSession::with(['schedule.course'])
            ->whereHas('schedule', fn ($q) => $q->where('lecturer_id', $lecturer?->id))
            ->where('status', 'active')
            ->get();

        // Recent closed/active sessions with attendee counts
        $recentSessions = AttendanceSession::with(['schedule.course'])
            ->whereHas('schedule', fn ($q) => $q->where('lecturer_id', $lecturer?->id))
            ->withCount('attendances')
            ->latest('starts_at')
            ->take(5)
            ->get();

        // Total attendance count under this lecturer
        $totalAttendances = Attendance::whereHas('attendanceSession.schedule', function ($q) use ($lecturer) {
            $q->where('lecturer_id', $lecturer?->id);
        })->count();

        return view('lecturer.dashboard', compact(
            'user',
            'lecturer',
            'courses',
            'schedules',
            'activeSessions',
            'recentSessions',
            'totalAttendances'
        ));
    }

    /**
     * Student Dashboard with course enrollments, active open sessions, and attendance history.
     */
    public function studentDashboard(): View
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            abort(403, 'Profil mahasiswa tidak ditemukan.');
        }

        // Get enrolled courses
        $enrollments = $student->enrollments()->with(['course.lecturer.user'])->get();
        $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();

        // Find active sessions in enrolled courses
        $activeSessions = AttendanceSession::with(['schedule.course.lecturer.user'])
            ->whereIn('status', ['active'])
            ->whereHas('schedule', fn ($q) => $q->whereIn('course_id', $enrolledCourseIds))
            ->get();

        // Attach student's attendance status on each active session
        foreach ($activeSessions as $session) {
            $session->my_attendance = Attendance::where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();
        }

        // Student's attendances summary
        $attendances = $student->attendances()->with(['attendanceSession.schedule.course'])->latest('attended_at')->get();

        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $excusedCount = $attendances->where('status', 'excused')->count();
        $sickCount = $attendances->where('status', 'sick')->count();

        // Calculate rate over closed sessions in enrolled courses
        $totalClosedSessions = AttendanceSession::whereHas('schedule', fn ($q) => $q->whereIn('course_id', $enrolledCourseIds))
            ->where('status', 'closed')
            ->count();

        $attendedClosedCount = $attendances->where('attendanceSession.status', 'closed')->count();
        $absentCount = max(0, $totalClosedSessions - $attendedClosedCount);

        $totalValid = $presentCount + $lateCount;
        $totalSessionsCount = max(1, $totalClosedSessions + $activeSessions->count());
        $attendanceRate = round(($totalValid / $totalSessionsCount) * 100, 1);

        $recentAttendances = $attendances->take(5);

        return view('student.dashboard', compact(
            'user',
            'student',
            'enrollments',
            'activeSessions',
            'presentCount',
            'lateCount',
            'excusedCount',
            'sickCount',
            'absentCount',
            'attendanceRate',
            'recentAttendances'
        ));
    }
}
