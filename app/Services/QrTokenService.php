<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\QrToken;
use Carbon\Carbon;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;

class QrTokenService
{
    /**
     * Generate a new dynamic QR token for an active session.
     *
     * @return array{token: string, manual_code: string, expires_at: Carbon, qr_data_uri: string}
     */
    public function generateToken(AttendanceSession $session, int $lifetimeSeconds = 30): array
    {
        $rawToken = Str::random(32);
        $manualCode = strtoupper(Str::random(6));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = now()->addSeconds($lifetimeSeconds);

        // Store new token in database
        $qrToken = QrToken::create([
            'attendance_session_id' => $session->id,
            'token_hash' => $tokenHash,
            'manual_code' => $manualCode,
            'expires_at' => $expiresAt,
        ]);

        // Cleanup expired tokens for this session
        QrToken::where('attendance_session_id', $session->id)
            ->where('expires_at', '<', now()->subMinutes(2))
            ->delete();

        // Generate high quality QR code SVG Data URI
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'outputBase64' => true,
            'svgUseFill' => true,
        ]);

        // The payload scanned by student contains the raw token and manual code
        $payload = json_encode([
            'session_id' => $session->id,
            'token' => $rawToken,
            'code' => $manualCode,
        ]);

        $qrDataUri = (new QRCode($options))->render($payload);

        return [
            'token' => $rawToken,
            'manual_code' => $manualCode,
            'expires_at' => $expiresAt,
            'qr_data_uri' => $qrDataUri,
        ];
    }

    /**
     * Validate an incoming token or manual code for a given session.
     *
     * @return array{is_valid: bool, message: string}
     */
    public function validateToken(AttendanceSession $session, string $input): array
    {
        $trimmed = trim($input);

        // Check if input is a JSON string from camera QR scanner
        if (Str::startsWith($trimmed, '{') && Str::endsWith($trimmed, '}')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded) && isset($decoded['token'])) {
                $trimmed = $decoded['token'];
            }
        }

        $inputHash = hash('sha256', $trimmed);
        $upperInput = strtoupper($trimmed);

        // Look for matching token that has not expired
        $token = QrToken::where('attendance_session_id', $session->id)
            ->where(function ($query) use ($inputHash, $upperInput) {
                $query->where('token_hash', $inputHash)
                    ->orWhere('manual_code', $upperInput);
            })
            ->where('expires_at', '>=', now())
            ->first();

        if (! $token) {
            return [
                'is_valid' => false,
                'message' => 'Token QR tidak valid atau sudah kedaluwarsa. Silakan scan ulang QR code terbaru di layar dosen.',
            ];
        }

        return [
            'is_valid' => true,
            'message' => 'Token QR berhasil divalidasi.',
        ];
    }
}
