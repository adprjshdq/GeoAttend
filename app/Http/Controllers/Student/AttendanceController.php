<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected GeofenceService $geofenceService,
    ) {}

    /**
     * Show student's complete attendance history.
     */
    public function history(Request $request): View
    {
        $student = Auth::user()->student;
        if (! $student) {
            abort(403, 'Profil mahasiswa tidak ditemukan.');
        }

        $query = Attendance::where('student_id', $student->id)
            ->with(['attendanceSession.schedule.course.lecturer.user']);

        if ($request->filled('course_id')) {
            $query->whereHas('attendanceSession.schedule', fn ($q) => $q->where('course_id', $request->input('course_id')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $attendances = $query->latest('attended_at')->paginate(10);
        $enrolledCourses = $student->courses;

        return view('student.attendance.history', compact('student', 'attendances', 'enrolledCourses'));
    }

    /**
     * Show the 3-step Attendance Wizard (QR, Geofence, Selfie).
     */
    public function create(AttendanceSession $session): View|RedirectResponse
    {
        $student = Auth::user()->student;
        if (! $student) {
            abort(403, 'Profil mahasiswa tidak ditemukan.');
        }

        // Check if enrolled
        $isEnrolled = $student->enrollments()
            ->where('course_id', $session->schedule->course_id)
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar dalam mata kuliah ini.');
        }

        // Check if already attended
        $existingAttendance = Attendance::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttendance) {
            return view('student.attendance.already_attended', compact('session', 'existingAttendance'));
        }

        // Check if session is open
        if (! $session->isOpen()) {
            return view('student.attendance.session_closed', compact('session'));
        }

        return view('student.attendance.create', compact('session', 'student'));
    }

    /**
     * AJAX endpoint to pre-validate GPS location before final submission.
     */
    public function validateLocation(Request $request, AttendanceSession $session): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->geofenceService->validateLocation(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $validated['accuracy'],
            $session->effective_latitude,
            $session->effective_longitude,
            $session->effective_radius
        );

        return response()->json($result);
    }

    /**
     * Submit attendance record with QR Token, Geolocation, and Selfie.
     */
    public function store(Request $request, AttendanceSession $session): RedirectResponse
    {
        $student = Auth::user()->student;
        if (! $student) {
            abort(403, 'Profil mahasiswa tidak ditemukan.');
        }

        $request->validate([
            'qr_token' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'selfie_image' => ['nullable', 'string'], // base64 canvas data
            'selfie_file' => ['nullable', 'file', 'image', 'max:5120'], // file fallback max 5MB
        ], [
            'qr_token.required' => 'Token QR Code wajib dipindai atau dimasukkan.',
            'latitude.required' => 'Koordinat GPS latitude wajib tersedia.',
            'longitude.required' => 'Koordinat GPS longitude wajib tersedia.',
            'accuracy.required' => 'Akurasi GPS perangkat wajib tersedia.',
        ]);

        // Pick selfie from base64 or file
        $selfie = null;
        if ($request->hasFile('selfie_file')) {
            $selfie = $request->file('selfie_file');
        } elseif ($request->filled('selfie_image')) {
            $selfie = $request->input('selfie_image');
        }

        if (! $selfie) {
            return back()->withInput()->withErrors([
                'selfie' => 'Foto selfie wajib diambil melalui kamera atau diunggah sebagai bukti kehadiran.',
            ]);
        }

        $result = $this->attendanceService->recordAttendance(
            student: $student,
            session: $session,
            qrTokenInput: $request->input('qr_token'),
            latitude: (float) $request->input('latitude'),
            longitude: (float) $request->input('longitude'),
            accuracy: (float) $request->input('accuracy'),
            selfieFile: $selfie
        );

        if (! $result['success']) {
            return back()->withInput()->withErrors([
                'attendance_error' => $result['message'],
            ]);
        }

        return redirect()->route('student.dashboard')
            ->with('success', $result['message']);
    }
}
