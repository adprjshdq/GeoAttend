<x-app-layout>
    <x-slot name="title">Dashboard Mahasiswa</x-slot>
    <x-slot name="header">Portal Presensi Mahasiswa</x-slot>

    <!-- Student Welcome Profile Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-black text-2xl flex items-center justify-center shadow-lg ring-4 ring-white/10 shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="space-y-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                    NIM: {{ $student->student_number }}
                </span>
                <h2 class="text-2xl font-black tracking-tight">{{ $user->name }}</h2>
                <p class="text-xs text-indigo-200/80">
                    {{ $student->study_program }} &bull; {{ $student->department }} &bull; Semester {{ $student->semester }}
                </p>
            </div>
        </div>

        <!-- Attendance Rate Ring Badge -->
        <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-4 shrink-0">
            <div class="text-right">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Persentase Kehadiran</p>
                <p class="text-2xl font-black text-emerald-400">{{ $attendanceRate }}%</p>
                <p class="text-[10px] text-slate-400 font-medium">Berdasarkan total perkuliahan</p>
            </div>
            <div class="w-12 h-12 rounded-full border-4 border-emerald-500/30 border-t-emerald-400 flex items-center justify-center font-bold text-xs text-emerald-300">
                <i data-lucide="award" class="w-6 h-6 text-emerald-400"></i>
            </div>
        </div>
    </div>

    <!-- Active Sessions to Attend NOW! -->
    @if($activeSessions->isNotEmpty())
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                    Sesi Presensi Tersedia Hari Ini
                </h3>
                <span class="text-xs text-slate-500 font-medium">Silakan lakukan presensi sebelum batas waktu berakhir</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($activeSessions as $sess)
                    @php $myAtt = $sess->my_attendance; @endphp
                    <div class="rounded-3xl p-6 border shadow-xs transition-all {{ $myAtt ? 'bg-white border-emerald-200' : 'bg-gradient-to-br from-indigo-50 to-white border-indigo-200 shadow-md ring-2 ring-indigo-500/20' }} flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-100/80 px-2.5 py-0.5 rounded-md">
                                    {{ $sess->schedule->course->course_code }}
                                </span>
                                @if($myAtt)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        Sudah Presensi ({{ $myAtt->status_label }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        Belum Presensi
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-base font-extrabold text-slate-900">{{ $sess->title }}</h4>
                            <p class="text-xs text-slate-600 flex flex-wrap items-center gap-3">
                                <span><i data-lucide="user" class="inline w-3.5 h-3.5 text-slate-400"></i> {{ $sess->schedule->course->lecturer?->user?->name ?? 'Dosen' }}</span>
                                <span><i data-lucide="map-pin" class="inline w-3.5 h-3.5 text-slate-400"></i> {{ $sess->schedule->room }}</span>
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500">
                                Berakhir {{ $sess->ends_at->format('H:i') }} WIB
                            </span>

                            @if($myAtt)
                                <span class="text-xs text-slate-400 font-medium">
                                    Tercatat pukul {{ $myAtt->attended_at->format('H:i:s') }} WIB
                                </span>
                            @else
                                <a href="{{ route('student.attendance.create', $sess) }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-extrabold text-xs shadow-md shadow-indigo-600/30 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                                    Kirim Presensi Sekarang
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Attendance Breakdown Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs text-center">
            <span class="text-xs font-bold text-emerald-700">Hadir Tepat Waktu</span>
            <p class="text-2xl font-black text-emerald-800 mt-1">{{ $presentCount }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs text-center">
            <span class="text-xs font-bold text-amber-700">Terlambat</span>
            <p class="text-2xl font-black text-amber-800 mt-1">{{ $lateCount }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs text-center">
            <span class="text-xs font-bold text-blue-700">Izin</span>
            <p class="text-2xl font-black text-blue-800 mt-1">{{ $excusedCount }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs text-center">
            <span class="text-xs font-bold text-purple-700">Sakit</span>
            <p class="text-2xl font-black text-purple-800 mt-1">{{ $sickCount }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs text-center col-span-2 sm:col-span-1">
            <span class="text-xs font-bold text-rose-700">Alpa</span>
            <p class="text-2xl font-black text-rose-800 mt-1">{{ $absentCount }}</p>
        </div>
    </div>

    <!-- Enrolled Courses Grid & Recent History -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Enrolled Courses -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-indigo-600"></i>
                    Mata Kuliah Semester Ini
                </h3>
                <span class="text-xs text-slate-500 font-semibold">{{ $enrollments->count() }} Mata Kuliah</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($enrollments as $enr)
                    <div class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">
                                    {{ $enr->course->course_code }}
                                </span>
                                <span class="text-xs font-semibold text-slate-500">{{ $enr->class_name ?? 'Kelas A' }} &bull; {{ $enr->course->credits }} SKS</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $enr->course->course_name }}</h4>
                            <p class="text-xs text-slate-500">{{ $enr->course->lecturer?->user?->name ?? 'Dosen Pengampu' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Anda belum terdaftar dalam mata kuliah apapun.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent History Widget -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-indigo-600"></i>
                    Aktivitas Presensi Terakhir
                </h3>
                <a href="{{ route('student.history') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                    Semua &rarr;
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentAttendances as $att)
                    <div class="p-4 hover:bg-slate-50 transition-colors space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-slate-800">{{ $att->attendanceSession->schedule->course->course_code }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-{{ $att->status_color }}-100 text-{{ $att->status_color }}-800">
                                {{ $att->status_label }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 truncate">{{ $att->attendanceSession->title }}</p>
                        <div class="text-[11px] text-slate-400 font-mono flex items-center justify-between">
                            <span>{{ $att->attended_at->format('d/m/Y H:i') }} WIB</span>
                            <span>{{ round($att->distance_meters, 1) }}m</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Belum ada rekaman presensi.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
