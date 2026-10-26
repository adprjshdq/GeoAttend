<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lecturer;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $lecturerUser;

    protected Lecturer $lecturer;

    protected User $studentUser;

    protected Student $student;

    protected Course $course;

    protected Schedule $schedule;

    protected AttendanceSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        SystemSetting::set('campus_name', 'Universitas Teknologi Nusantara');
        SystemSetting::set('max_gps_accuracy', '50');
        SystemSetting::set('late_threshold_minutes', '15');
        SystemSetting::set('default_geofence_radius', '100');

        // Create Lecturer
        $this->lecturerUser = User::create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'dosen@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
            'is_active' => true,
        ]);

        $this->lecturer = Lecturer::create([
            'user_id' => $this->lecturerUser->id,
            'lecturer_number' => '0412058501',
            'department' => 'Fakultas Ilmu Komputer',
        ]);

        // Create Student
        $this->studentUser = User::create([
            'name' => 'Ahmad Fauzan',
            'email' => 'mahasiswa@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'student_number' => '202401001',
            'department' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Teknik Informatika',
            'semester' => 3,
        ]);

        // Create Course
        $this->course = Course::create([
            'course_code' => 'TI101',
            'course_name' => 'Pemrograman Web Lanjut',
            'credits' => 3,
            'semester' => 3,
            'lecturer_id' => $this->lecturer->id,
            'is_active' => true,
        ]);

        // Enroll Student in Course
        Enrollment::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        // Create Schedule (Monas, Jakarta coordinates as campus room)
        $this->schedule = Schedule::create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room' => 'Lab Komputer 3',
            'location_latitude' => -6.175392,
            'location_longitude' => 106.827153,
            'geofence_radius' => 100,
        ]);

        // Create active AttendanceSession
        $this->session = AttendanceSession::create([
            'schedule_id' => $this->schedule->id,
            'session_number' => 1,
            'title' => 'Pengenalan Arsitektur MVC',
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addMinutes(85),
            'status' => 'active',
            'override_latitude' => -6.175392,
            'override_longitude' => 106.827153,
            'override_radius' => 100,
            'created_by' => $this->lecturerUser->id,
        ]);
    }

    public function test_lecturer_can_view_qr_and_generate_dynamic_token(): void
    {
        $response = $this->actingAs($this->lecturerUser)->get(route('lecturer.sessions.qr', $this->session));

        $response->assertStatus(200);
        $response->assertSee('Dynamic QR Live');
        $response->assertSee($this->course->course_name);

        $refreshResponse = $this->actingAs($this->lecturerUser)->get(route('lecturer.sessions.refresh_token', $this->session));
        $refreshResponse->assertStatus(200);
        $refreshResponse->assertJsonStructure([
            'active',
            'token',
            'manual_code',
            'expires_in',
            'qr_data_uri',
        ]);
    }

    public function test_student_location_validation_within_geofence(): void
    {
        // Coordinates 20 meters away from target
        $validLocation = [
            'latitude' => -6.175450,
            'longitude' => 106.827200,
            'accuracy' => 10.0,
        ];

        $response = $this->actingAs($this->studentUser)->postJson(
            route('student.attendance.validate_location', $this->session),
            $validLocation
        );

        $response->assertStatus(200);
        $response->assertJson([
            'is_valid' => true,
        ]);
        $this->assertLessThanOrEqual(100, $response->json('distance'));
    }

    public function test_student_location_validation_outside_geofence_is_rejected(): void
    {
        // Coordinates 500 meters away (Bandung/elsewhere)
        $invalidLocation = [
            'latitude' => -6.180000,
            'longitude' => 106.835000,
            'accuracy' => 10.0,
        ];

        $response = $this->actingAs($this->studentUser)->postJson(
            route('student.attendance.validate_location', $this->session),
            $invalidLocation
        );

        $response->assertStatus(200);
        $response->assertJson([
            'is_valid' => false,
        ]);
    }

    public function test_student_submits_attendance_successfully(): void
    {
        $qrService = app(QrTokenService::class);
        $tokenData = $qrService->generateToken($this->session, 30);

        $fakeSelfie = UploadedFile::fake()->image('selfie.jpg', 640, 480);

        $response = $this->actingAs($this->studentUser)->post(
            route('student.attendance.store', $this->session),
            [
                'qr_token' => $tokenData['token'],
                'latitude' => -6.175392,
                'longitude' => 106.827153,
                'accuracy' => 8.5,
                'selfie_file' => $fakeSelfie,
            ]
        );

        $response->assertRedirect(route('student.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);
    }

    public function test_student_cannot_submit_duplicate_attendance(): void
    {
        $qrService = app(QrTokenService::class);
        $tokenData = $qrService->generateToken($this->session, 30);

        // First attendance
        Attendance::create([
            'attendance_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'attended_at' => now(),
            'status' => 'present',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'distance_meters' => 5,
            'gps_accuracy' => 10,
            'selfie_path' => 'private/selfies/test.jpg',
        ]);

        $fakeSelfie = UploadedFile::fake()->image('selfie.jpg', 640, 480);

        $response = $this->actingAs($this->studentUser)->post(
            route('student.attendance.store', $this->session),
            [
                'qr_token' => $tokenData['token'],
                'latitude' => -6.175392,
                'longitude' => 106.827153,
                'accuracy' => 8.5,
                'selfie_file' => $fakeSelfie,
            ]
        );

        $response->assertSessionHasErrors(['attendance_error']);
    }

    public function test_lecturer_can_manually_correct_attendance_status(): void
    {
        $attendance = Attendance::create([
            'attendance_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'attended_at' => now(),
            'status' => 'absent',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'distance_meters' => 5,
            'gps_accuracy' => 10,
            'selfie_path' => 'private/selfies/test.jpg',
        ]);

        $response = $this->actingAs($this->lecturerUser)->patch(
            route('lecturer.attendances.correct', $attendance),
            [
                'status' => 'sick',
                'reason' => 'Mahasiswa menyerahkan surat dokter dari klinik kampus',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'sick',
        ]);
    }

    public function test_lecturer_can_close_session_and_export_data(): void
    {
        $closeResponse = $this->actingAs($this->lecturerUser)->post(
            route('lecturer.sessions.close', $this->session)
        );

        $closeResponse->assertRedirect();
        $this->assertEquals('closed', $this->session->fresh()->status);

        // Test export CSV
        $exportResponse = $this->actingAs($this->lecturerUser)->get(
            route('lecturer.sessions.export', ['session' => $this->session, 'format' => 'csv'])
        );

        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
