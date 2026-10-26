<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AttendanceService
{
    public function __construct(
        protected GeofenceService $geofenceService,
        protected QrTokenService $qrTokenService,
    ) {}

    /**
     * Submit attendance for a student with all server-side validations and DB transaction.
     *
     * @param  UploadedFile|string  $selfieFile  Either an UploadedFile instance or a base64 string from canvas
     * @return array{success: bool, message: string, attendance?: Attendance}
     */
    public function recordAttendance(
        Student $student,
        AttendanceSession $session,
        string $qrTokenInput,
        float $latitude,
        float $longitude,
        float $accuracy,
        UploadedFile|string $selfieFile
    ): array {
        // 1. Verify session status and time window
        if ($session->status !== 'active') {
            return [
                'success' => false,
                'message' => 'Sesi presensi ini sedang tidak aktif.',
            ];
        }

        $now = now();
        if ($now->isBefore($session->starts_at)) {
            return [
                'success' => false,
                'message' => 'Sesi presensi belum dimulai. Dimulai pada '.$session->starts_at->format('H:i').' WIB.',
            ];
        }

        if ($now->isAfter($session->ends_at)) {
            return [
                'success' => false,
                'message' => 'Sesi presensi sudah berakhir pada '.$session->ends_at->format('H:i').' WIB.',
            ];
        }

        // 2. Verify student enrollment in course
        $isEnrolled = $student->enrollments()
            ->where('course_id', $session->schedule->course_id)
            ->exists();

        if (! $isEnrolled) {
            return [
                'success' => false,
                'message' => 'Anda tidak terdaftar sebagai peserta pada mata kuliah ini.',
            ];
        }

        // 3. Verify student has not already attended this session
        $alreadyAttended = Attendance::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyAttended) {
            return [
                'success' => false,
                'message' => 'Anda sudah tercatat melakukan presensi pada sesi ini.',
            ];
        }

        // 4. Validate dynamic QR token
        $qrValidation = $this->qrTokenService->validateToken($session, $qrTokenInput);
        if (! $qrValidation['is_valid']) {
            return [
                'success' => false,
                'message' => $qrValidation['message'],
            ];
        }

        // 5. Validate Geofence and accuracy
        $geofenceValidation = $this->geofenceService->validateLocation(
            $latitude,
            $longitude,
            $accuracy,
            $session->effective_latitude,
            $session->effective_longitude,
            $session->effective_radius
        );

        if (! $geofenceValidation['is_valid']) {
            return [
                'success' => false,
                'message' => $geofenceValidation['message'],
            ];
        }

        // 6. Process and store selfie in private disk
        $selfiePath = $this->storeSelfie($session->id, $student->id, $selfieFile);
        if (! $selfiePath) {
            return [
                'success' => false,
                'message' => 'Gagal memproses foto selfie. Pastikan kamera menghasilkan gambar yang jelas.',
            ];
        }

        // 7. Determine status (present vs late based on configurable threshold)
        $lateThresholdMinutes = (int) SystemSetting::get('late_threshold_minutes', 15);
        $lateTime = $session->starts_at->copy()->addMinutes($lateThresholdMinutes);
        $status = $now->isAfter($lateTime) ? 'late' : 'present';

        // 8. Execute Database Transaction with row locking & unique constraint protection
        try {
            $attendance = DB::transaction(function () use (
                $session,
                $student,
                $now,
                $latitude,
                $longitude,
                $accuracy,
                $geofenceValidation,
                $selfiePath,
                $status
            ) {
                $record = Attendance::create([
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'attended_at' => $now,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'gps_accuracy' => $accuracy,
                    'distance_meters' => $geofenceValidation['distance'],
                    'selfie_path' => $selfiePath,
                    'status' => $status,
                    'validation_notes' => "Geofence verified ({$geofenceValidation['distance']}m <= {$session->effective_radius}m), GPS accuracy: {$accuracy}m",
                ]);

                AuditLog::log(
                    action: 'STUDENT_ATTENDANCE_SUBMITTED',
                    entityType: 'Attendance',
                    entityId: $record->id,
                    oldValues: null,
                    newValues: [
                        'status' => $status,
                        'session_id' => $session->id,
                        'student_id' => $student->id,
                        'distance' => $geofenceValidation['distance'],
                    ],
                    reason: 'Mahasiswa berhasil melakukan presensi mandiri via QR, GPS & Selfie',
                    userId: $student->user_id
                );

                return $record;
            });

            $statusText = $status === 'late' ? 'Hadir (Terlambat)' : 'Hadir Tepat Waktu';

            return [
                'success' => true,
                'message' => "Presensi Anda berhasil dicatat sebagai {$statusText}!",
                'attendance' => $attendance,
            ];
        } catch (\Throwable $e) {
            // Cleanup uploaded selfie if transaction fails
            Storage::disk('local')->delete($selfiePath);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data presensi: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Store selfie to local private storage disk.
     */
    protected function storeSelfie(int $sessionId, int $studentId, UploadedFile|string $selfie): ?string
    {
        $dir = "private/selfies/{$sessionId}";
        $timestamp = now()->timestamp;
        $random = Str::random(8);

        if ($selfie instanceof UploadedFile) {
            $extension = $selfie->getClientOriginalExtension() ?: 'jpg';
            $filename = "{$studentId}_{$timestamp}_{$random}.{$extension}";

            return $selfie->storeAs($dir, $filename, 'local');
        }

        // Base64 string from canvas
        if (is_string($selfie) && Str::contains($selfie, ';base64,')) {
            [, $data] = explode(';', $selfie);
            [, $data] = explode(',', $data);
            $decoded = base64_decode($data);

            if ($decoded === false) {
                return null;
            }

            $filename = "{$studentId}_{$timestamp}_{$random}.jpg";
            $path = "{$dir}/{$filename}";

            Storage::disk('local')->put($path, $decoded);

            return $path;
        }

        return null;
    }

    /**
     * Correct an attendance record with mandatory audit logging.
     */
    public function correctAttendance(
        Attendance $attendance,
        string $newStatus,
        string $reason,
        User $actor
    ): Attendance {
        $allowed = ['present', 'late', 'excused', 'sick', 'absent'];
        if (! in_array($newStatus, $allowed, true)) {
            throw new InvalidArgumentException("Status {$newStatus} tidak valid.");
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('Alasan koreksi presensi wajib diisi.');
        }

        $oldStatus = $attendance->status;

        $attendance->update([
            'status' => $newStatus,
            'verified_by' => $actor->id,
            'validation_notes' => ($attendance->validation_notes ? $attendance->validation_notes.' | ' : '').
                "Koreksi oleh {$actor->name} ({$actor->role}) pada ".now()->format('d/m/Y H:i').": Status diubah dari '{$oldStatus}' ke '{$newStatus}'. Alasan: {$reason}",
        ]);

        AuditLog::log(
            action: 'ATTENDANCE_CORRECTION',
            entityType: 'Attendance',
            entityId: $attendance->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
            reason: $reason,
            userId: $actor->id
        );

        return $attendance;
    }
}
