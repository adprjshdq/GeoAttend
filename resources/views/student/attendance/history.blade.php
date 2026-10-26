<x-app-layout>
    <x-slot name="title">Riwayat Presensi Saya</x-slot>
    <x-slot name="header">Riwayat Kehadiran Perkuliahan</x-slot>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('student.history') }}" class="flex flex-wrap items-center gap-2 flex-1">
                <select name="course_id" class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Mata Kuliah</option>
                    @foreach($enrolledCourses as $c)
                        <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->course_code }} - {{ $c->course_name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="excused" {{ request('status') === 'excused' ? 'selected' : '' }}>Izin</option>
                    <option value="sick" {{ request('status') === 'sick' ? 'selected' : '' }}>Sakit</option>
                    <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Alpa</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 transition">
                    Filter
                </button>
                @if(request('course_id') || request('status'))
                    <a href="{{ route('student.history') }}" class="text-xs text-slate-500 hover:text-slate-800 p-2">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">Tanggal & Waktu</th>
                        <th class="py-3.5 px-6">Mata Kuliah & Pertemuan</th>
                        <th class="py-3.5 px-6">Status Kehadiran</th>
                        <th class="py-3.5 px-6">Verifikasi Jarak (GPS)</th>
                        <th class="py-3.5 px-6">Bukti Foto Selfie</th>
                        <th class="py-3.5 px-6">Catatan Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6 text-slate-700 whitespace-nowrap">
                                <div class="font-bold font-mono">{{ $att->attended_at->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $att->attended_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">
                                    {{ $att->attendanceSession->schedule->course->course_code }}
                                </span>
                                <div class="font-bold text-slate-900 mt-1">{{ $att->attendanceSession->schedule->course->course_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $att->attendanceSession->title }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-{{ $att->status_color }}-100 text-{{ $att->status_color }}-800 border border-{{ $att->status_color }}-200">
                                    {{ $att->status_label }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($att->distance_meters !== null)
                                    <div class="font-bold text-slate-800">
                                        {{ round($att->distance_meters, 1) }}m dari kelas
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        Akurasi GPS: {{ $att->gps_accuracy }}m
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($att->selfie_path)
                                    <img src="{{ route('selfies.show', $att) }}" alt="Selfie" class="w-12 h-12 rounded-xl object-cover border-2 border-slate-200 shadow-xs">
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-500 max-w-xs">
                                <p class="truncate" title="{{ $att->validation_notes }}">{{ $att->validation_notes ?? '-' }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada catatan riwayat kehadiran yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
