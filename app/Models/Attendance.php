<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'attended_at',
        'latitude',
        'longitude',
        'gps_accuracy',
        'distance_meters',
        'selfie_path',
        'status',
        'validation_notes',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'attended_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'gps_accuracy' => 'float',
            'distance_meters' => 'float',
        ];
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'excused' => 'Izin',
            'sick' => 'Sakit',
            'absent' => 'Alpa',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'emerald',
            'late' => 'amber',
            'excused' => 'blue',
            'sick' => 'purple',
            'absent' => 'rose',
            default => 'slate',
        };
    }
}
