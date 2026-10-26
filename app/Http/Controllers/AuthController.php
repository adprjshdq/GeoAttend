<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login page with demo credentials helper.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->to(match ($user->role) {
                'admin' => route('admin.dashboard'),
                'lecturer' => route('lecturer.dashboard'),
                'student' => route('student.dashboard'),
                default => route('dashboard'),
            });
        }

        return view('auth.login');
    }

    /**
     * Authenticate user credentials.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'email' => 'Kredensial yang Anda masukkan tidak cocok dengan data kami.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan oleh administrator. Hubungi bagian akademik.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        AuditLog::log(
            action: 'USER_LOGIN',
            entityType: 'User',
            entityId: $user->id,
            oldValues: null,
            newValues: ['role' => $user->role],
            reason: 'Pengguna berhasil login ke sistem',
            userId: $user->id
        );

        $targetRoute = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'lecturer' => route('lecturer.dashboard'),
            'student' => route('student.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($targetRoute)
            ->with('success', "Selamat datang kembali, {$user->name}!");
    }

    /**
     * Logout authenticated user.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::log(
                action: 'USER_LOGOUT',
                entityType: 'User',
                entityId: Auth::id(),
                oldValues: null,
                newValues: null,
                reason: 'Pengguna logout dari sistem',
                userId: Auth::id()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar dari akun.');
    }
}
