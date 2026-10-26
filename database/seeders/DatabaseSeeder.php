<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic Indonesian academic data.
     */
    public function run(): void
    {
        // 1. System Settings
        SystemSetting::set('campus_name', 'Universitas Teknologi Nusantara', 'Nama Kampus / Perguruan Tinggi');
        SystemSetting::set('max_gps_accuracy', '50', 'Maksimal toleransi akurasi GPS perangkat (meter)');
        SystemSetting::set('late_threshold_minutes', '15', 'Batas toleransi menit keterlambatan mahasiswa');
        SystemSetting::set('default_geofence_radius', '100', 'Radius geofence bawaan kelas (meter)');

        // 2. Administrator
        $adminUser = User::create([
            'name' => 'Administrator GeoAttend',
            'email' => 'admin@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'phone_number' => '081234567890',
        ]);

        // 3. Lecturers
        $lecturersData = [
            [
                'name' => 'Dr. Budi Santoso, M.Kom.',
                'email' => 'dosen1@geoattend.ac.id',
                'lecturer_number' => '0412058501',
                'department' => 'Fakultas Ilmu Komputer',
                'phone' => '081298765431',
            ],
            [
                'name' => 'Siti Nurhaliza, S.T., M.T.',
                'email' => 'dosen2@geoattend.ac.id',
                'lecturer_number' => '0420088902',
                'department' => 'Fakultas Ilmu Komputer',
                'phone' => '081298765432',
            ],
            [
                'name' => 'Agus Pratama, M.Cs.',
                'email' => 'dosen3@geoattend.ac.id',
                'lecturer_number' => '0415119003',
                'department' => 'Fakultas Ilmu Komputer',
                'phone' => '081298765433',
            ],
        ];

        $lecturerModels = [];
        foreach ($lecturersData as $d) {
            $user = User::create([
                'name' => $d['name'],
                'email' => $d['email'],
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'is_active' => true,
                'phone_number' => $d['phone'],
            ]);

            $lecturerModels[] = Lecturer::create([
                'user_id' => $user->id,
                'lecturer_number' => $d['lecturer_number'],
                'department' => $d['department'],
            ]);
        }

        // 4. Students
        $studentsData = [
            ['name' => 'Ahmad Fauzan', 'nim' => '202401001', 'email' => 'mahasiswa1@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
            ['name' => 'Bella Safitri', 'nim' => '202401002', 'email' => 'mahasiswa2@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
            ['name' => 'Chandra Wijaya', 'nim' => '202401003', 'email' => 'mahasiswa3@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
            ['name' => 'Dinda Kirana', 'nim' => '202401004', 'email' => 'mahasiswa4@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
            ['name' => 'Eko Prasetyo', 'nim' => '202401005', 'email' => 'mahasiswa5@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
            ['name' => 'Farhan Ramadhan', 'nim' => '202401006', 'email' => 'mahasiswa6@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
            ['name' => 'Gita Permata', 'nim' => '202401007', 'email' => 'mahasiswa7@geoattend.ac.id', 'prodi' => 'Sistem Informasi'],
            ['name' => 'Hadi Saputra', 'nim' => '202401008', 'email' => 'mahasiswa8@geoattend.ac.id', 'prodi' => 'Sistem Informasi'],
            ['name' => 'Indah Lestari', 'nim' => '202401009', 'email' => 'mahasiswa9@geoattend.ac.id', 'prodi' => 'Sistem Informasi'],
            ['name' => 'Joko Susilo', 'nim' => '202401010', 'email' => 'mahasiswa10@geoattend.ac.id', 'prodi' => 'Sistem Informasi'],
            ['name' => 'Kevin Sanjaya', 'nim' => '202401011', 'email' => 'mahasiswa11@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
            ['name' => 'Laila Fitriani', 'nim' => '202401012', 'email' => 'mahasiswa12@geoattend.ac.id', 'prodi' => 'Teknik Informatika'],
        ];

        $studentModels = [];
        foreach ($studentsData as $s) {
            $user = User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_active' => true,
            ]);

            $studentModels[] = Student::create([
                'user_id' => $user->id,
                'student_number' => $s['nim'],
                'department' => 'Fakultas Ilmu Komputer',
                'study_program' => $s['prodi'],
                'semester' => 4,
            ]);
        }

        // 5. Courses
        $course1 = Course::create([
            'course_code' => 'IF2104',
            'course_name' => 'Pemrograman Web Lanjut',
            'credits' => 3,
            'semester' => 4,
            'academic_year' => '2025/2026 Genap',
            'lecturer_id' => $lecturerModels[0]->id,
        ]);

        $course2 = Course::create([
            'course_code' => 'IF2205',
            'course_name' => 'Rekayasa Perangkat Lunak',
            'credits' => 3,
            'semester' => 4,
            'academic_year' => '2025/2026 Genap',
            'lecturer_id' => $lecturerModels[0]->id,
        ]);

        $course3 = Course::create([
            'course_code' => 'SI2103',
            'course_name' => 'Sistem Basis Data Relasional',
            'credits' => 3,
            'semester' => 4,
            'academic_year' => '2025/2026 Genap',
            'lecturer_id' => $lecturerModels[1]->id,
        ]);

        $course4 = Course::create([
            'course_code' => 'IF2308',
            'course_name' => 'Jaringan Komputer & Komunikasi Data',
            'credits' => 3,
            'semester' => 4,
            'academic_year' => '2025/2026 Genap',
            'lecturer_id' => $lecturerModels[2]->id,
        ]);

        // 6. Enrollments (Students enroll into courses)
        foreach ($studentModels as $index => $st) {
            Enrollment::create([
                'student_id' => $st->id,
                'course_id' => $course1->id,
                'class_name' => 'IF-4A',
            ]);

            Enrollment::create([
                'student_id' => $st->id,
                'course_id' => $course2->id,
                'class_name' => 'IF-4A',
            ]);

            // Some students also take course 3 or 4
            if ($index % 2 === 0) {
                Enrollment::create([
                    'student_id' => $st->id,
                    'course_id' => $course3->id,
                    'class_name' => 'SI-4B',
                ]);
            } else {
                Enrollment::create([
                    'student_id' => $st->id,
                    'course_id' => $course4->id,
                    'class_name' => 'IF-4B',
                ]);
            }
        }

        // 7. Schedules (Senin - Jumat)
        // Default campus classroom coordinates (Monas / Central Jakarta area coordinates for reference)
        $campLat = -6.175392;
        $campLon = 106.827153;

        $schedule1 = Schedule::create([
            'course_id' => $course1->id,
            'lecturer_id' => $lecturerModels[0]->id,
            'day_of_week' => 'Senin',
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
            'room' => 'Lab Komputer 3 - Gedung B Lt. 2',
            'location_latitude' => $campLat,
            'location_longitude' => $campLon,
            'geofence_radius' => 100,
        ]);

        $schedule2 = Schedule::create([
            'course_id' => $course2->id,
            'lecturer_id' => $lecturerModels[0]->id,
            'day_of_week' => 'Senin',
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
            'room' => 'Ruang Teori 204 - Gedung A',
            'location_latitude' => $campLat,
            'location_longitude' => $campLon,
            'geofence_radius' => 120,
        ]);

        $schedule3 = Schedule::create([
            'course_id' => $course3->id,
            'lecturer_id' => $lecturerModels[1]->id,
            'day_of_week' => 'Selasa',
            'start_time' => '09:00:00',
            'end_time' => '11:30:00',
            'room' => 'Lab Basis Data - Gedung B Lt. 1',
            'location_latitude' => $campLat,
            'location_longitude' => $campLon,
            'geofence_radius' => 100,
        ]);

        $schedule4 = Schedule::create([
            'course_id' => $course4->id,
            'lecturer_id' => $lecturerModels[2]->id,
            'day_of_week' => 'Rabu',
            'start_time' => '10:00:00',
            'end_time' => '12:30:00',
            'room' => 'Lab Jaringan & IoT - Gedung C',
            'location_latitude' => $campLat,
            'location_longitude' => $campLon,
            'geofence_radius' => 100,
        ]);

        // 8. Past Attendance Sessions & Records for Schedule 1 (Pemrograman Web Lanjut)
        $pastDates = [
            ['week' => 1, 'date' => now()->subWeeks(3)->setTime(8, 0), 'title' => 'Pertemuan 1 — Pengantar Web Modern & MVC'],
            ['week' => 2, 'date' => now()->subWeeks(2)->setTime(8, 0), 'title' => 'Pertemuan 2 — Routing, Blade Template & Tailwind CSS'],
            ['week' => 3, 'date' => now()->subWeeks(1)->setTime(8, 0), 'title' => 'Pertemuan 3 — Database Migration & Eloquent ORM'],
        ];

        foreach ($pastDates as $p) {
            $sess = AttendanceSession::create([
                'schedule_id' => $schedule1->id,
                'session_number' => $p['week'],
                'title' => $p['title'],
                'starts_at' => $p['date'],
                'ends_at' => $p['date']->copy()->addHours(2)->addMinutes(30),
                'status' => 'closed',
                'created_by' => $lecturerModels[0]->user_id,
            ]);

            // Add attendance records for enrolled students
            foreach ($studentModels as $idx => $st) {
                // Vary status: mostly present, some late, some excused/sick
                $stat = 'present';
                if ($idx === 3 && $p['week'] === 2) {
                    $stat = 'late';
                } elseif ($idx === 5 && $p['week'] === 1) {
                    $stat = 'excused';
                } elseif ($idx === 8 && $p['week'] === 3) {
                    $stat = 'sick';
                } elseif ($idx === 10 && $p['week'] === 2) {
                    $stat = 'absent';
                }

                if ($stat !== 'absent') {
                    Attendance::create([
                        'attendance_session_id' => $sess->id,
                        'student_id' => $st->id,
                        'attended_at' => $p['date']->copy()->addMinutes(rand(5, 20)),
                        'latitude' => $campLat + (rand(-10, 10) / 100000),
                        'longitude' => $campLon + (rand(-10, 10) / 100000),
                        'gps_accuracy' => rand(8, 25),
                        'distance_meters' => rand(15, 65),
                        'selfie_path' => 'private/selfies/sample_selfie.jpg',
                        'status' => $stat,
                        'validation_notes' => 'Presensi otomatis terverifikasi via GPS dan Selfie',
                    ]);
                }
            }
        }

        // 9. Create Active Session for TODAY so tester can immediately test live QR & attendance!
        $activeSession = AttendanceSession::create([
            'schedule_id' => $schedule1->id,
            'session_number' => 4,
            'title' => 'Pertemuan 4 — Dynamic QR Code & GPS Geolocation',
            'starts_at' => now()->subMinutes(15),
            'ends_at' => now()->addHours(2),
            'status' => 'active',
            'created_by' => $lecturerModels[0]->user_id,
        ]);

        // Generate an initial active dynamic QR token for this session
        $qrService = app(QrTokenService::class);
        $qrService->generateToken($activeSession, 60);

        // Pre-attend first 2 students so lecturer dashboard has immediate live data
        Attendance::create([
            'attendance_session_id' => $activeSession->id,
            'student_id' => $studentModels[0]->id,
            'attended_at' => now()->subMinutes(10),
            'latitude' => $campLat,
            'longitude' => $campLon,
            'gps_accuracy' => 12.5,
            'distance_meters' => 22.0,
            'selfie_path' => 'private/selfies/sample_selfie.jpg',
            'status' => 'present',
            'validation_notes' => 'Presensi live berhasil (Ahmad Fauzan)',
        ]);

        Attendance::create([
            'attendance_session_id' => $activeSession->id,
            'student_id' => $studentModels[1]->id,
            'attended_at' => now()->subMinutes(5),
            'latitude' => $campLat,
            'longitude' => $campLon,
            'gps_accuracy' => 15.0,
            'distance_meters' => 35.4,
            'selfie_path' => 'private/selfies/sample_selfie.jpg',
            'status' => 'present',
            'validation_notes' => 'Presensi live berhasil (Bella Safitri)',
        ]);
    }
}
