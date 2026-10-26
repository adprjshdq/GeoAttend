<x-app-layout>
    <x-slot name="title">Dashboard Dosen</x-slot>
    <x-slot name="header">Portal Dosen Pengampu</x-slot>

    <!-- Welcome Lecturer Card -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                NIDN: {{ $lecturer?->lecturer_number ?? '-' }} &bull; {{ $lecturer?->department ?? 'Fakultas Ilmu Komputer' }}
            </span>
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ $user->name }}!</h2>
            <p class="text-xs md:text-sm text-indigo-200/80 max-w-xl">
                Kelola kehadiran mahasiswa dengan QR Code dinamis berbasis waktu dan geofence lokasi presensi kelas.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('lecturer.sessions.index') }}" class="px-5 py-3 rounded-xl bg-white text-indigo-900 hover:bg-indigo-50 font-bold text-xs shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                Jadwal Mengajar
            </a>
        </div>
    </div>

    <!-- Active Sessions Right Now Banner -->
    @if($activeSessions->isNotEmpty())
        <div class="bg-emerald-500/10 border-2 border-emerald-500/30 rounded-3xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <h3 class="text-base font-extrabold text-emerald-900">Sesi Presensi Sedang Aktif</h3>
                </div>
                <span class="text-xs text-emerald-700 bg-emerald-100 font-bold px-3 py-1 rounded-full">
                    QR Dinamis Berputar
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($activeSessions as $session)
                    <div class="bg-white rounded-2xl p-5 border border-emerald-200 shadow-sm flex flex-col justify-between space-y-4">
                        <div>
                            <span class="text-xs font-bold text-indigo-600 uppercase font-mono">{{ $session->schedule->course->course_code }}</span>
                            <h4 class="text-base font-bold text-slate-900 mt-0.5">{{ $session->title }}</h4>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $session->schedule->room }} (Radius: {{ $session->effective_radius }}m)
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-500 font-medium">
                                Berakhir {{ $session->ends_at->format('H:i') }} WIB
                            </span>
                            <a href="{{ route('lecturer.sessions.qr', $session) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                                <i data-lucide="qr-code" class="w-4 h-4"></i>
                                Buka Layar QR Dinamis
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Teaching Schedule & Courses Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Courses taught -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-indigo-600"></i>
                    Mata Kuliah yang Diampu
                </h3>
                <span class="text-xs text-slate-500 font-semibold">{{ $courses->count() }} Mata Kuliah</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($courses as $course)
                    <div class="p-5 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">
                                    {{ $course->course_code }}
                                </span>
                                <span class="text-xs text-slate-500 font-medium">{{ $course->credits }} SKS &bull; Semester {{ $course->semester }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $course->course_name }}</h4>
                            <p class="text-xs text-slate-500">{{ $course->students_count }} Mahasiswa Terdaftar &bull; {{ $course->academic_year }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('lecturer.courses.export', $course) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition" title="Unduh Laporan Kehadiran CSV">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                Ekspor CSV
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Belum ada mata kuliah yang dialokasikan kepada Anda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Weekly Schedule Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
                    Jadwal Mengajar Mingguan
                </h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($schedules as $sch)
                    <div class="p-4 hover:bg-slate-50 transition-colors space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded-sm bg-indigo-50 text-indigo-700 font-bold text-[11px]">{{ $sch->day_of_week }}</span>
                            <span class="text-xs font-mono text-slate-600">{{ $sch->formatted_time }}</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800">{{ $sch->course->course_name }}</h4>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                            <span><i data-lucide="map-pin" class="inline w-3 h-3"></i> {{ $sch->room }}</span>
                            <a href="{{ route('lecturer.sessions.create', $sch) }}" class="text-indigo-600 hover:underline font-bold inline-flex items-center gap-0.5">
                                + Buat Sesi
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Tidak ada jadwal mengajar.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
