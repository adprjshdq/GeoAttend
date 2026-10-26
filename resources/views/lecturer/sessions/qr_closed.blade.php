<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Telah Ditutup — GeoAttend</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-white flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 rounded-3xl bg-slate-800 border border-slate-700 text-slate-400 mx-auto flex items-center justify-center">
            <i data-lucide="lock" class="w-10 h-10"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold">Sesi Presensi Telah Ditutup</h1>
            <p class="text-sm text-slate-400 mt-2">Sesi pertemuan {{ $session->session_number }} ({{ $session->schedule->course->course_name }}) saat ini berstatus ditutup dan tidak dapat menerima presensi baru.</p>
        </div>
        <div class="pt-4 flex items-center justify-center gap-3">
            <a href="{{ route('lecturer.sessions.show', $session) }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
                Lihat Detail Presensi
            </a>
            <a href="{{ route('lecturer.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 font-semibold text-xs transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
