<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'session_number',
        'title',
        'starts_at',
        'ends_at',
        'status',
        'override_latitude',
        'override_longitude',
        'override_radius',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'session_number' => 'integer',
            'override_latitude' => 'float',
            'override_longitude' => 'float',
            'override_radius' => 'integer',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class);
    }

    public function currentQrToken(): HasOne
    {
        return $this->hasOne(QrToken::class)
            ->where('expires_at', '>', now())
            ->latestOfMany('id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get effective target latitude for geofence validation.
     */
    public function getEffectiveLatitudeAttribute(): float
    {
        return (float) ($this->override_latitude ?? $this->schedule->location_latitude);
    }

    /**
     * Get effective target longitude for geofence validation.
     */
    public function getEffectiveLongitudeAttribute(): float
    {
        return (float) ($this->override_longitude ?? $this->schedule->location_longitude);
    }

    /**
     * Get effective geofence radius in meters.
     */
    public function getEffectiveRadiusAttribute(): int
    {
        return (int) ($this->override_radius ?? $this->schedule->geofence_radius);
    }

    /**
     * Determine if session is currently active and can receive attendance.
     */
    public function isOpen(): bool
    {
        return $this->status === 'active' && now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * Get readable Indonesian status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Sesi Aktif',
            'closed' => 'Sesi Ditutup',
            'scheduled' => 'Terjadwal',
            default => ucfirst($this->status),
        };
    }
}
