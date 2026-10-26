<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SelfieController extends Controller
{
    /**
     * Serve a private attendance selfie image with strict authorization checks.
     */
    public function show(Request $request, Attendance $attendance): BinaryFileResponse
    {
        $user = Auth::user();

        // 1. Authorization check
        $isAuthorized = false;

        if ($user->isAdmin()) {
            $isAuthorized = true;
        } elseif ($user->isLecturer() && $user->lecturer) {
            $lecturerId = $attendance->attendanceSession->schedule->lecturer_id;
            if ($user->lecturer->id === $lecturerId) {
                $isAuthorized = true;
            }
        } elseif ($user->isStudent() && $user->student) {
            if ($attendance->student_id === $user->student->id) {
                $isAuthorized = true;
            }
        }

        if (! $isAuthorized) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat foto selfie ini.');
        }

        // 2. Check file existence in local storage
        $path = $attendance->selfie_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            // Check fallback sample
            if (Storage::disk('local')->exists('private/selfies/sample_selfie.jpg')) {
                $fullPath = Storage::disk('local')->path('private/selfies/sample_selfie.jpg');

                return response()->file($fullPath, [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'private, max-age=3600',
                ]);
            }
            abort(404, 'Berkas foto selfie tidak ditemukan di penyimpanan server.');
        }

        $fullPath = Storage::disk('local')->path($path);
        $mime = mime_content_type($fullPath) ?: 'image/jpeg';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
