<x-app-layout>
    <x-slot name="title">Sesi Presensi & Dynamic QR</x-slot>
    <x-slot name="header">Kelola Sesi Perkuliahan & Presensi</x-slot>

    <!-- Schedules Section: Quick Create Session -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 mb-8">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
            <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
            Buka Sesi Presensi Berdasarkan Jadwal Mengajar Anda
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($schedules as $sch)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:border-indigo-300 transition-colors flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-indigo-600">{{ $sch->course->course_code }}</span>
                            <span class="text-[11px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-sm border border-slate-200">{{ $sch->day_of_week }}</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 mt-1">{{ $sch->course->course_name }}</h4>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $sch->formatted_time }} &bull; {{ $sch->room }}
                        </p>
                    </div>

                    <a href="{{ route('lecturer.sessions.create', $sch) }}" class="w-full py-2 px-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs text-center shadow-xs transition flex items-center justify-center gap-1.5">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        Buka Sesi Pertemuan
                    </a>
                </div>
            @empty
                <div class="col-span-3 text-center py-6 text-slate-400 text-xs">
                    Belum ada jadwal perkuliahan yang ditetapkan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Attendance Sessions Archive Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Riwayat Sesi Perkuliahan</h3>
                <p class="text-xs text-slate-500">Seluruh pertemuan yang telah dibuat dan status presensinya.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">Pertemuan Ke-</th>
                        <th class="py-3.5 px-6">Mata Kuliah & Judul Sesi</th>
                        <th class="py-3.5 px-6">Waktu Sesi</th>
                        <th class="py-3.5 px-6">Status Sesi</th>
                        <th class="py-3.5 px-6">Kehadiran</th>
                        <th class="py-3.5 px-6 text-right">Aksi & Layar QR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($sessions as $sess)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6 font-bold text-indigo-700">
                                Pertemuan {{ $sess->session_number }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-xs font-mono font-bold text-slate-500">{{ $sess->schedule->course->course_code }}</span>
                                <div class="font-bold text-slate-900">{{ $sess->title }}</div>
                                <div class="text-[11px] text-slate-500">{{ $sess->schedule->room }} (Radius: {{ $sess->effective_radius }}m)</div>
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                <div>{{ $sess->starts_at->format('d/m/Y') }}</div>
                                <div class="font-mono text-[11px] text-slate-400">{{ $sess->starts_at->format('H:i') }} - {{ $sess->ends_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-4 px-6">
                                @if($sess->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Aktif
                                    </span>
                                @elseif($sess->status === 'closed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Ditutup
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Terjadwal
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-extrabold text-slate-900">{{ $sess->attendances_count }}</span>
                                <span class="text-slate-400">/ {{ $sess->schedule->course->students()->count() }} Mahasiswa</span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($sess->status === 'active')
                                        <a href="{{ route('lecturer.sessions.qr', $sess) }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1 shadow-xs transition">
                                            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> Layar QR
                                        </a>
                                        <form action="{{ route('lecturer.sessions.close', $sess) }}" method="POST" onsubmit="return confirm('Tutup sesi presensi ini?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition">
                                                Tutup
                                            </button>
                                        </form>
                                    @elseif($sess->status === 'scheduled')
                                        <form action="{{ route('lecturer.sessions.open', $sess) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-xs transition flex items-center gap-1">
                                                <i data-lucide="play" class="w-3.5 h-3.5"></i> Buka Sesi
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('lecturer.sessions.show', $sess) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
                                        Detail & Koreksi
                                    </a>

                                    <a href="{{ route('lecturer.sessions.export', $sess) }}" title="Ekspor CSV Presensi" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada riwayat sesi perkuliahan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
