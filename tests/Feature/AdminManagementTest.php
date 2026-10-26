<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $studentUser;

    protected Student $student;

    protected User $lecturerUser;

    protected Lecturer $lecturer;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::set('campus_name', 'Universitas Teknologi Nusantara');
        SystemSetting::set('max_gps_accuracy', '50');
        SystemSetting::set('late_threshold_minutes', '15');
        SystemSetting::set('default_geofence_radius', '100');

        $this->adminUser = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->lecturerUser = User::create([
            'name' => 'Dosen Contoh',
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

        $this->studentUser = User::create([
            'name' => 'Mahasiswa Contoh',
            'email' => 'student@geoattend.ac.id',
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
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $lecturerResponse = $this->actingAs($this->lecturerUser)->get(route('admin.settings.index'));
        $lecturerResponse->assertStatus(403);
    }

    public function test_admin_can_view_dashboard_with_analytics(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Utama');
        $response->assertSee('Dashboard');
    }

    public function test_admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.users.store'), [
            'name' => 'Pengguna Baru',
            'email' => 'pengguna.baru@geoattend.ac.id',
            'password' => 'secret12345',
            'role' => 'student',
            'phone_number' => '081299998888',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'pengguna.baru@geoattend.ac.id',
            'role' => 'student',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $targetUser = User::create([
            'name' => 'Edit Me',
            'email' => 'editme@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.users.update', $targetUser), [
            'name' => 'User Edited Successfully',
            'email' => 'editme@geoattend.ac.id',
            'role' => 'student',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'User Edited Successfully',
        ]);
    }

    public function test_admin_can_create_course(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.courses.store'), [
            'course_code' => 'CS102',
            'course_name' => 'Struktur Data dan Algoritma',
            'credits' => 4,
            'semester' => 2,
            'academic_year' => '2024/2025 Ganjil',
            'lecturer_id' => $this->lecturer->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', [
            'course_code' => 'CS102',
            'course_name' => 'Struktur Data dan Algoritma',
            'lecturer_id' => $this->lecturer->id,
        ]);
    }

    public function test_admin_can_enroll_and_unenroll_student_in_course(): void
    {
        $course = Course::create([
            'course_code' => 'CS103',
            'course_name' => 'Basis Data Relasional',
            'credits' => 3,
            'semester' => 3,
            'lecturer_id' => $this->lecturer->id,
            'is_active' => true,
        ]);

        // Enroll
        $enrollResponse = $this->actingAs($this->adminUser)->post(
            route('admin.courses.enroll', $course),
            [
                'student_id' => $this->student->id,
            ]
        );

        $enrollResponse->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'student_id' => $this->student->id,
        ]);

        // Unenroll
        $unenrollResponse = $this->actingAs($this->adminUser)->delete(
            route('admin.courses.unenroll', ['course' => $course, 'student' => $this->student])
        );

        $unenrollResponse->assertRedirect();
        $this->assertDatabaseMissing('enrollments', [
            'course_id' => $course->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_admin_can_update_system_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            'campus_name' => 'Institut Teknologi Digital Nusantara',
            'max_gps_accuracy' => 40,
            'late_threshold_minutes' => 20,
            'default_geofence_radius' => 120,
        ]);

        $response->assertRedirect();
        $this->assertEquals('Institut Teknologi Digital Nusantara', SystemSetting::get('campus_name'));
        $this->assertEquals('40', SystemSetting::get('max_gps_accuracy'));
        $this->assertEquals('20', SystemSetting::get('late_threshold_minutes'));
        $this->assertEquals('120', SystemSetting::get('default_geofence_radius'));
    }

    public function test_admin_can_view_audit_logs(): void
    {
        AuditLog::log(
            action: 'TEST_AUDIT_ACTION',
            entityType: 'User',
            entityId: $this->adminUser->id,
            oldValues: null,
            newValues: ['test' => true],
            reason: 'Testing audit trail logging system'
        );

        $response = $this->actingAs($this->adminUser)->get(route('admin.audit_logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Log');
        $response->assertSee('TEST_AUDIT_ACTION');
        $response->assertSee('Testing audit trail logging system');
    }
}
