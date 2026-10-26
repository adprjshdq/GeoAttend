<?php

namespace App\Services;

use App\Models\SystemSetting;

class GeofenceService
{
    /**
     * Calculate great-circle distance between two points in meters using Haversine formula.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * (sin($deltaLon / 2) ** 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Validate whether student coordinates fall within allowable radius and GPS accuracy.
     *
     * @return array{is_valid: bool, distance: float, message: string}
     */
    public function validateLocation(
        float $studentLat,
        float $studentLon,
        float $studentAccuracy,
        float $targetLat,
        float $targetLon,
        int $allowedRadius
    ): array {
        $maxAccuracy = (float) SystemSetting::get('max_gps_accuracy', 50);

        if ($studentAccuracy > $maxAccuracy) {
            return [
                'is_valid' => false,
                'distance' => 0.0,
                'message' => "Akurasi GPS perangkat Anda terlalu rendah ({$studentAccuracy}m > batas {$maxAccuracy}m). Silakan pindah ke area terbuka dan coba kembali.",
            ];
        }

        $distance = $this->calculateDistance($studentLat, $studentLon, $targetLat, $targetLon);

        if ($distance > $allowedRadius) {
            $excess = round($distance - $allowedRadius, 1);

            return [
                'is_valid' => false,
                'distance' => $distance,
                'message' => "Anda berada di luar radius kelas! Jarak Anda: {$distance}m (Batas radius: {$allowedRadius}m, selisih {$excess}m).",
            ];
        }

        return [
            'is_valid' => true,
            'distance' => $distance,
            'message' => "Lokasi terverifikasi! Anda berada dalam radius {$allowedRadius}m (Jarak aktual: {$distance}m).",
        ];
    }
}
