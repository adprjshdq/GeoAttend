<x-app-layout>
    <x-slot name="title">Dashboard Administrator</x-slot>
    <x-slot name="header">Dashboard Utama Administrator</x-slot>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Mahasiswa -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Mahasiswa</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($totalStudents) }}</h3>
                <p class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Terdaftar aktif
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="graduation-cap" class="w-7 h-7"></i>
            </div>
        </div>

        <!-- Dosen -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Dosen</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($totalLecturers) }}</h3>
                <p class="text-xs text-blue-600 font-semibold mt-1 flex items-center gap-1">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i> Dosen pengampu
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="briefcase" class="w-7 h-7"></i>
            </div>
        </div>

        <!-- Mata Kuliah -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mata Kuliah</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($totalCourses) }}</h3>
                <p class="text-xs text-purple-600 font-semibold mt-1 flex items-center gap-1">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i> Semester aktif
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="book-open" class="w-7 h-7"></i>
            </div>
        </div>

        <!-- Total Presensi -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Presensi</p>
                <h3 class="text-3xl font-extrabold text-indigo-600 mt-2">{{ number_format($totalAttendances) }}</h3>
                <p class="text-xs text-slate-500 font-semibold mt-1 flex items-center gap-1">
                    <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-indigo-500"></i> {{ $totalSessions }} sesi perkuliahan
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-check" class="w-7 h-7"></i>
            </div>
        </div>
    </div>

    <!-- Attendance Breakdown & Chart.js Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Status Breakdown Statistics -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="pie-chart" class="w-5 h-5 text-indigo-600"></i>
                Rekapitulasi Kehadiran
            </h3>
            
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-emerald-900">Hadir Tepat Waktu</span>
                    </div>
                    <span class="text-sm font-extrabold text-emerald-800">{{ $statusCounts['present'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50 border border-amber-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-bold text-amber-900">Hadir Terlambat</span>
                    </div>
                    <span class="text-sm font-extrabold text-amber-800">{{ $statusCounts['late'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 border border-blue-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-bold text-blue-900">Izin</span>
                    </div>
                    <span class="text-sm font-extrabold text-blue-800">{{ $statusCounts['excused'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-purple-50 border border-purple-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                        <span class="text-xs font-bold text-purple-900">Sakit</span>
                    </div>
                    <span class="text-sm font-extrabold text-purple-800">{{ $statusCounts['sick'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-rose-50 border border-rose-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="text-xs font-bold text-rose-900">Alpa / Tanpa Keterangan</span>
                    </div>
                    <span class="text-sm font-extrabold text-rose-800">{{ $statusCounts['absent'] }}</span>
                </div>
            </div>
        </div>

        <!-- Interactive Chart.js Container -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-600"></i>
                    Grafik Komposisi Status Kehadiran
                </h3>
                <span class="text-xs text-slate-500 font-medium">Berdasarkan total rekaman presensi</span>
            </div>
            <div class="relative h-64 w-full flex items-center justify-center">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Active Sessions & Recent Activity Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Live Active Sessions -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    Sesi Presensi Aktif Saat Ini
                </h3>
                <span class="text-xs text-emerald-700 bg-emerald-100/80 font-bold px-2.5 py-1 rounded-full border border-emerald-200">
                    {{ $activeSessions->count() }} Sesi Berjalan
                </span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($activeSessions as $session)
                    <div class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between">
                        <div class="space-y-1">
                            <span class="text-xs font-extrabold text-indigo-600 uppercase">{{ $session->schedule->course->course_code }}</span>
                            <h4 class="text-sm font-bold text-slate-800">{{ $session->title }}</h4>
                            <p class="text-xs text-slate-500 flex items-center gap-3">
                                <span><i data-lucide="user" class="inline w-3 h-3"></i> {{ $session->schedule->course->lecturer?->user?->name ?? 'Dosen' }}</span>
                                <span><i data-lucide="map-pin" class="inline w-3 h-3"></i> {{ $session->schedule->room }}</span>
                            </p>
                        </div>
                        <div class="text-right flex flex-col items-end gap-1.5">
                            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">
                                {{ $session->starts_at->format('H:i') }} - {{ $session->ends_at->format('H:i') }}
                            </span>
                            <a href="{{ route('lecturer.sessions.qr', $session) }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                                Layar QR <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        Tidak ada sesi presensi yang sedang berlangsung saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Audit Logs -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4 text-indigo-600"></i>
                    Aktivitas Sistem Terbaru
                </h3>
                <a href="{{ route('admin.audit_logs.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                    Lihat Semua &rarr;
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentAuditLogs as $log)
                    <div class="p-3.5 hover:bg-slate-50 transition-colors flex items-center justify-between text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">{{ $log->user?->name ?? 'Sistem' }}</span>
                                <span class="px-2 py-0.5 rounded-sm bg-slate-100 text-slate-600 font-mono text-[10px]">{{ $log->action }}</span>
                            </div>
                            <p class="text-slate-500">{{ $log->reason ?? '-' }}</p>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0 font-medium">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Belum ada aktivitas tercatat di audit log.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('attendanceChart');
            if (ctx && window.Chart) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hadir Tepat Waktu', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
                        datasets: [{
                            data: [
                                {{ $statusCounts['present'] }},
                                {{ $statusCounts['late'] }},
                                {{ $statusCounts['excused'] }},
                                {{ $statusCounts['sick'] }},
                                {{ $statusCounts['absent'] }}
                            ],
                            backgroundColor: [
                                '#10b981', // emerald
                                '#f59e0b', // amber
                                '#3b82f6', // blue
                                '#a855f7', // purple
                                '#f43f5e'  // rose
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: { family: 'Plus Jakarta Sans', size: 12 }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
