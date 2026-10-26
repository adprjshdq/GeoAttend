<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Student;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Generate structured attendance statistics for a specific course.
     *
     * @return array{
     *     total_sessions: int,
     *     total_students: int,
     *     summary: array{present: int, late: int, excused: int, sick: int, absent: int},
     *     student_records: list<array{
     *         student: Student,
     *         present_count: int,
     *         late_count: int,
     *         excused_count: int,
     *         sick_count: int,
     *         absent_count: int,
     *         attendance_rate: float
     *     }>
     * }
     */
    public function getCourseReport(Course $course): array
    {
        $sessions = AttendanceSession::whereHas('schedule', fn ($q) => $q->where('course_id', $course->id))
            ->whereIn('status', ['active', 'closed'])
            ->get();

        $students = $course->students()->with('user')->get();
        $totalSessions = $sessions->count();

        $studentRecords = [];
        $totalPresent = 0;
        $totalLate = 0;
        $totalExcused = 0;
        $totalSick = 0;
        $totalAbsent = 0;

        $sessionIds = $sessions->pluck('id')->toArray();

        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereIn('attendance_session_id', $sessionIds)
                ->get();

            $pCount = $attendances->where('status', 'present')->count();
            $lCount = $attendances->where('status', 'late')->count();
            $eCount = $attendances->where('status', 'excused')->count();
            $sCount = $attendances->where('status', 'sick')->count();

            // Attended sessions count
            $attendedCount = $attendances->count();
            // In closed sessions, non-attendees count as absent
            $closedSessionsCount = $sessions->where('status', 'closed')->count();
            $aCount = max(0, $closedSessionsCount - $attendedCount) + $attendances->where('status', 'absent')->count();

            $rate = $totalSessions > 0
                ? round((($pCount + $lCount) / $totalSessions) * 100, 1)
                : 100.0;

            $totalPresent += $pCount;
            $totalLate += $lCount;
            $totalExcused += $eCount;
            $totalSick += $sCount;
            $totalAbsent += $aCount;

            $studentRecords[] = [
                'student' => $student,
                'present_count' => $pCount,
                'late_count' => $lCount,
                'excused_count' => $eCount,
                'sick_count' => $sCount,
                'absent_count' => $aCount,
                'attendance_rate' => $rate,
            ];
        }

        return [
            'total_sessions' => $totalSessions,
            'total_students' => $students->count(),
            'summary' => [
                'present' => $totalPresent,
                'late' => $totalLate,
                'excused' => $totalExcused,
                'sick' => $totalSick,
                'absent' => $totalAbsent,
            ],
            'student_records' => $studentRecords,
        ];
    }

    /**
     * Export Course attendance report as downloadable CSV.
     */
    public function exportCourseCsv(Course $course): StreamedResponse
    {
        $report = $this->getCourseReport($course);
        $filename = "Laporan_Presensi_{$course->course_code}_".now()->format('Ymd_His').'.csv';

        return new StreamedResponse(function () use ($course, $report) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header info
            fputcsv($handle, ['LAPORAN PRESENSI MAHASISWA — GEOATTEND']);
            fputcsv($handle, ['Mata Kuliah', "{$course->course_name} ({$course->course_code})"]);
            fputcsv($handle, ['SKS / Semester', "{$course->credits} SKS / Semester {$course->semester}"]);
            fputcsv($handle, ['Tahun Akademik', $course->academic_year]);
            fputcsv($handle, ['Dosen Pengampu', $course->lecturer?->user?->name ?? '-']);
            fputcsv($handle, ['Total Pertemuan Selesai', $report['total_sessions']]);
            fputcsv($handle, ['Tanggal Unduh', now()->format('d/m/Y H:i:s').' WIB']);
            fputcsv($handle, []);

            // Table headers
            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Program Studi',
                'Hadir',
                'Terlambat',
                'Izin',
                'Sakit',
                'Alpa',
                'Persentase Kehadiran (%)',
            ]);

            foreach ($report['student_records'] as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['student']->student_number,
                    $row['student']->user->name,
                    $row['student']->study_program,
                    $row['present_count'],
                    $row['late_count'],
                    $row['excused_count'],
                    $row['sick_count'],
                    $row['absent_count'],
                    "{$row['attendance_rate']}%",
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
