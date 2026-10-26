<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Schedule;
use App\Services\AttendanceService;
use App\Services\QrTokenService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SessionController extends Controller
{
    public function __construct(
        protected QrTokenService $qrTokenService,
        protected AttendanceService $attendanceService,
        protected ReportService $reportService,
    ) {}

    /**
     * Display a listing of teaching schedules and sessions for the current lecturer.
     */
    public function index(): View
    {
        $lecturer = Auth::user()->lecturer;
        if (! $lecturer) {
            abort(403, 'Profil dosen tidak ditemukan.');
        }

        $schedules = $lecturer->schedules()->with('course')->get();

        $sessions = AttendanceSession::whereHas('schedule', fn ($q) => $q->where('lecturer_id', $lecturer->id))
            ->with(['schedule.course'])
            ->withCount('attendances')
            ->latest('starts_at')
            ->paginate(10);

        return view('lecturer.sessions.index', compact('schedules', 'sessions', 'lecturer'));
    }

    /**
     * Show form to create a new attendance session.
     */
    public function create(Schedule $schedule): View
    {
        $this->authorizeSchedule($schedule);

        $nextSessionNumber = AttendanceSession::where('schedule_id', $schedule->id)->max('session_number') + 1;

        return view('lecturer.sessions.create', compact('schedule', 'nextSessionNumber'));
    }

    /**
     * Store newly created attendance session.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'session_number' => ['required', 'integer', 'min:1', 'max:32'],
            'title' => ['required', 'string', 'max:150'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'override_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'override_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'override_radius' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'auto_open' => ['nullable', 'boolean'],
        ]);

        $schedule = Schedule::findOrFail($validated['schedule_id']);
        $this->authorizeSchedule($schedule);

        $status = $request->boolean('auto_open') ? 'active' : 'scheduled';

        $session = AttendanceSession::create([
            'schedule_id' => $schedule->id,
            'session_number' => $validated['session_number'],
            'title' => $validated['title'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'status' => $status,
            'override_latitude' => $validated['override_latitude'] ?? null,
            'override_longitude' => $validated['override_longitude'] ?? null,
            'override_radius' => $validated['override_radius'] ?? null,
            'created_by' => Auth::id(),
        ]);

        if ($status === 'active') {
            $this->qrTokenService->generateToken($session, 30);
        }

        AuditLog::log(
            action: 'ATTENDANCE_SESSION_CREATED',
            entityType: 'AttendanceSession',
            entityId: $session->id,
            oldValues: null,
            newValues: ['title' => $session->title, 'status' => $status],
            reason: 'Dosen membuat sesi presensi baru'
        );

        if ($status === 'active') {
            return redirect()->route('lecturer.sessions.qr', $session)
                ->with('success', "Sesi Pertemuan {$session->session_number} berhasil dibuka! QR Code dinamis siap dipindai.");
        }

        return redirect()->route('lecturer.sessions.show', $session)
            ->with('success', "Sesi Pertemuan {$session->session_number} berhasil dibuat.");
    }

    /**
     * Display session details, attendee list, and statistics.
     */
    public function show(AttendanceSession $session): View
    {
        $this->authorizeSession($session);

        $session->load(['schedule.course.lecturer.user', 'creator']);

        $enrolledStudents = $session->schedule->course->students()->with('user')->get();
        $attendances = $session->attendances()->with(['student.user', 'verifier'])->get()->keyBy('student_id');

        $attendeeRows = $enrolledStudents->map(function ($student) use ($attendances) {
            return [
                'student' => $student,
                'attendance' => $attendances->get($student->id),
            ];
        });

        $stats = [
            'total_enrolled' => $enrolledStudents->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
            'absent' => $session->status === 'closed'
                ? max(0, $enrolledStudents->count() - $attendances->count()) + $attendances->where('status', 'absent')->count()
                : $attendances->where('status', 'absent')->count(),
        ];

        return view('lecturer.sessions.show', compact('session', 'attendeeRows', 'stats'));
    }

    /**
     * Open an attendance session and start dynamic QR cycle.
     */
    public function open(AttendanceSession $session): RedirectResponse
    {
        $this->authorizeSession($session);

        $session->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addHours(2), // default 2 hours window
        ]);

        $this->qrTokenService->generateToken($session, 30);

        AuditLog::log(
            action: 'ATTENDANCE_SESSION_OPENED',
            entityType: 'AttendanceSession',
            entityId: $session->id,
            oldValues: ['status' => 'scheduled'],
            newValues: ['status' => 'active'],
            reason: 'Dosen membuka sesi presensi'
        );

        return redirect()->route('lecturer.sessions.qr', $session)
            ->with('success', 'Sesi presensi sekarang AKTIF! Tampilkan QR Code ke mahasiswa.');
    }

    /**
     * Close an attendance session.
     */
    public function close(AttendanceSession $session): RedirectResponse
    {
        $this->authorizeSession($session);

        $session->update([
            'status' => 'closed',
            'ends_at' => now(),
        ]);

        AuditLog::log(
            action: 'ATTENDANCE_SESSION_CLOSED',
            entityType: 'AttendanceSession',
            entityId: $session->id,
            oldValues: ['status' => 'active'],
            newValues: ['status' => 'closed'],
            reason: 'Dosen menutup sesi presensi'
        );

        return redirect()->route('lecturer.sessions.show', $session)
            ->with('info', 'Sesi presensi telah ditutup. Presensi baru tidak lagi dapat dikirimkan.');
    }

    /**
     * Show dedicated dynamic QR Code presentation view with auto-refresh and attendance ticker.
     */
    public function qrView(AttendanceSession $session): View
    {
        $this->authorizeSession($session);

        if ($session->status !== 'active') {
            return view('lecturer.sessions.qr_closed', compact('session'));
        }

        $tokenData = $this->qrTokenService->generateToken($session, 30);
        $totalEnrolled = $session->schedule->course->students()->count();
        $attendedCount = $session->attendances()->count();

        return view('lecturer.sessions.qr', compact('session', 'tokenData', 'totalEnrolled', 'attendedCount'));
    }

    /**
     * JSON API to generate & fetch a fresh QR token every 30 seconds.
     */
    public function refreshToken(AttendanceSession $session): JsonResponse
    {
        $this->authorizeSession($session);

        if ($session->status !== 'active') {
            return response()->json([
                'active' => false,
                'message' => 'Sesi presensi telah ditutup.',
            ]);
        }

        $tokenData = $this->qrTokenService->generateToken($session, 30);

        return response()->json([
            'active' => true,
            'token' => $tokenData['token'],
            'manual_code' => $tokenData['manual_code'],
            'qr_data_uri' => $tokenData['qr_data_uri'],
            'expires_in' => 30,
        ]);
    }

    /**
     * JSON API for live polling of attendee count and latest arrivals.
     */
    public function pollAttendees(AttendanceSession $session): JsonResponse
    {
        $this->authorizeSession($session);

        $totalEnrolled = $session->schedule->course->students()->count();
        $attendances = $session->attendances()
            ->with('student.user')
            ->latest('attended_at')
            ->take(10)
            ->get();

        $latestList = $attendances->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->student->user->name,
            'nim' => $a->student->student_number,
            'status' => $a->status_label,
            'status_color' => $a->status_color,
            'time' => $a->attended_at->format('H:i:s'),
            'distance' => round($a->distance_meters, 1).'m',
        ]);

        return response()->json([
            'total_enrolled' => $totalEnrolled,
            'attended_count' => $session->attendances()->count(),
            'latest' => $latestList,
        ]);
    }

    /**
     * Manually correct an attendance record with mandatory reason.
     */
    public function correctAttendance(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorizeSession($attendance->attendanceSession);

        $validated = $request->validate([
            'status' => ['required', 'in:present,late,excused,sick,absent'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'reason.required' => 'Alasan perubahan status presensi wajib diisi.',
            'reason.min' => 'Alasan perubahan minimal 5 karakter.',
        ]);

        $this->attendanceService->correctAttendance(
            $attendance,
            $validated['status'],
            $validated['reason'],
            Auth::user()
        );

        return back()->with('success', 'Status presensi mahasiswa berhasil diperbarui.');
    }

    /**
     * Export session attendance to CSV.
     */
    public function export(AttendanceSession $session): StreamedResponse
    {
        $this->authorizeSession($session);

        $course = $session->schedule->course;
        $filename = "Presensi_Pertemuan_{$session->session_number}_{$course->course_code}_".now()->format('Ymd_His').'.csv';

        $enrolledStudents = $course->students()->with('user')->get();
        $attendances = $session->attendances()->with('student.user')->get()->keyBy('student_id');

        return new StreamedResponse(function () use ($session, $course, $enrolledStudents, $attendances) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['LAPORAN PRESENSI SESI PERKULIAHAN']);
            fputcsv($handle, ['Mata Kuliah', "{$course->course_name} ({$course->course_code})"]);
            fputcsv($handle, ['Pertemuan Ke-', $session->session_number]);
            fputcsv($handle, ['Topik / Judul', $session->title]);
            fputcsv($handle, ['Waktu Sesi', $session->starts_at->format('d/m/Y H:i').' - '.$session->ends_at->format('H:i').' WIB']);
            fputcsv($handle, ['Status Sesi', $session->status_label]);
            fputcsv($handle, []);

            fputcsv($handle, ['No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Status Presensi', 'Waktu Hadir', 'Jarak (meter)', 'Akurasi GPS (meter)', 'Catatan Validasi']);

            foreach ($enrolledStudents as $i => $student) {
                $att = $attendances->get($student->id);
                $status = $att ? $att->status_label : ($session->status === 'closed' ? 'Alpa' : 'Belum Hadir');

                fputcsv($handle, [
                    $i + 1,
                    $student->student_number,
                    $student->user->name,
                    $student->study_program,
                    $status,
                    $att ? $att->attended_at->format('H:i:s') : '-',
                    $att ? $att->distance_meters : '-',
                    $att ? $att->gps_accuracy : '-',
                    $att ? $att->validation_notes : '-',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export entire course attendance to CSV.
     */
    public function exportCourse(Course $course): StreamedResponse
    {
        $lecturer = Auth::user()->lecturer;
        if (! Auth::user()->isAdmin() && ($lecturer && $course->lecturer_id !== $lecturer->id)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengekspor laporan mata kuliah ini.');
        }

        return $this->reportService->exportCourseCsv($course);
    }

    /**
     * Helper to verify lecturer ownership over schedule.
     */
    protected function authorizeSchedule(Schedule $schedule): void
    {
        if (Auth::user()->isAdmin()) {
            return;
        }

        $lecturer = Auth::user()->lecturer;
        if (! $lecturer || $schedule->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki akses ke jadwal mengajar ini.');
        }
    }

    /**
     * Helper to verify lecturer ownership over session.
     */
    protected function authorizeSession(AttendanceSession $session): void
    {
        if (Auth::user()->isAdmin()) {
            return;
        }

        $lecturer = Auth::user()->lecturer;
        if (! $lecturer || $session->schedule->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki akses ke sesi perkuliahan ini.');
        }
    }
}
