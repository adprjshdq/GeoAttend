<x-app-layout>
    <x-slot name="title">Detail Sesi Pertemuan {{ $session->session_number }}</x-slot>
    <x-slot name="header">Detail Sesi Pertemuan {{ $session->session_number }}: {{ $session->schedule->course->course_name }}</x-slot>

    <!-- Session Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md">
                        {{ $session->schedule->course->course_code }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Pertemuan Ke-{{ $session->session_number }}</span>
                    @if($session->status === 'active')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sesi Aktif
                        </span>
                    @elseif($session->status === 'closed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            Sesi Ditutup
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Terjadwal
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900">{{ $session->title }}</h2>
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 pt-1">
                    <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4 text-slate-400"></i> {{ $session->starts_at->format('d M Y, H:i') }} - {{ $session->ends_at->format('H:i') }} WIB</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i> {{ $session->schedule->room }} (Radius: {{ $session->effective_radius }}m)</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="user" class="w-4 h-4 text-slate-400"></i> Dibuat oleh: {{ $session->creator?->name ?? 'Dosen' }}</span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if($session->status === 'active')
                    <a href="{{ route('lecturer.sessions.qr', $session) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                        Layar QR Dinamis
                    </a>
                    <form action="{{ route('lecturer.sessions.close', $session) }}" method="POST" onsubmit="return confirm('Tutup sesi ini? Mahasiswa tidak akan dapat melakukan presensi lagi.')">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition">
                            Tutup Sesi
                        </button>
                    </form>
                @elseif($session->status === 'scheduled')
                    <form action="{{ route('lecturer.sessions.open', $session) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                            <i data-lucide="play" class="w-4 h-4"></i> Buka Sesi Sekarang
                        </button>
                    </form>
                @endif

                <a href="{{ route('lecturer.sessions.export', $session) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Ekspor CSV
                </a>
            </div>
        </div>

        <!-- Metric Counters -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-6">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <p class="text-[11px] font-bold text-slate-500 uppercase">Total Peserta</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total_enrolled'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-100 text-center">
                <p class="text-[11px] font-bold text-emerald-700 uppercase">Hadir</p>
                <p class="text-2xl font-extrabold text-emerald-800 mt-1">{{ $stats['present'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-100 text-center">
                <p class="text-[11px] font-bold text-amber-700 uppercase">Terlambat</p>
                <p class="text-2xl font-extrabold text-amber-800 mt-1">{{ $stats['late'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-100 text-center">
                <p class="text-[11px] font-bold text-blue-700 uppercase">Izin</p>
                <p class="text-2xl font-extrabold text-blue-800 mt-1">{{ $stats['excused'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-100 text-center">
                <p class="text-[11px] font-bold text-purple-700 uppercase">Sakit</p>
                <p class="text-2xl font-extrabold text-purple-800 mt-1">{{ $stats['sick'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-100 text-center">
                <p class="text-[11px] font-bold text-rose-700 uppercase">{{ $session->status === 'closed' ? 'Alpa' : 'Belum Hadir' }}</p>
                <p class="text-2xl font-extrabold text-rose-800 mt-1">{{ $stats['absent'] }}</p>
            </div>
        </div>
    </div>

    <!-- Attendees Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Daftar Mahasiswa & Bukti Presensi</h3>
            <span class="text-xs text-slate-500 font-medium">Klik pada foto selfie untuk memperbesar gambar bukti</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">Mahasiswa</th>
                        <th class="py-3.5 px-6">Status Presensi</th>
                        <th class="py-3.5 px-6">Waktu Presensi</th>
                        <th class="py-3.5 px-6">Verifikasi Lokasi (GPS)</th>
                        <th class="py-3.5 px-6">Bukti Foto Selfie</th>
                        <th class="py-3.5 px-6 text-right">Koreksi Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($attendeeRows as $row)
                        @php
                            $st = $row['student'];
                            $att = $row['attendance'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6">
                                <div class="font-mono text-xs font-bold text-indigo-700">{{ $st->student_number }}</div>
                                <div class="font-bold text-slate-900">{{ $st->user->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $st->study_program }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @if($att)
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-{{ $att->status_color }}-100 text-{{ $att->status_color }}-800 border border-{{ $att->status_color }}-200">
                                        {{ $att->status_label }}
                                    </span>
                                @else
                                    @if($session->status === 'closed')
                                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            Alpa
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Belum Hadir
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-700">
                                @if($att)
                                    <div class="font-bold font-mono">{{ $att->attended_at->format('H:i:s') }} WIB</div>
                                    <div class="text-[11px] text-slate-400">{{ $att->attended_at->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($att && $att->distance_meters !== null)
                                    <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                        <i data-lucide="navigation" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        Jarak: {{ round($att->distance_meters, 1) }}m
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono">
                                        Akurasi GPS: {{ $att->gps_accuracy }}m
                                    </div>
                                    @if($att->validation_notes)
                                        <p class="text-[10px] text-slate-400 truncate max-w-xs" title="{{ $att->validation_notes }}">
                                            {{ $att->validation_notes }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($att && $att->selfie_path)
                                    <button type="button" onclick="openSelfieModal('{{ route('selfies.show', $att) }}', '{{ $st->user->name }} ({{ $st->student_number }})')" class="relative group cursor-pointer block">
                                        <img src="{{ route('selfies.show', $att) }}" alt="Selfie" class="w-12 h-12 rounded-xl object-cover border-2 border-indigo-200 group-hover:border-indigo-500 transition shadow-xs">
                                        <span class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </span>
                                    </button>
                                @else
                                    <span class="text-slate-400 text-xs">Tidak ada</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if($att)
                                    <button type="button" onclick="openCorrectionModal({{ $att->id }}, '{{ $st->user->name }}', '{{ $att->status }}')" class="px-3 py-1.5 rounded-lg border border-slate-200 text-indigo-700 hover:bg-indigo-50 font-semibold text-xs transition inline-flex items-center gap-1">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Koreksi
                                    </button>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada mahasiswa terdaftar pada kelas mata kuliah ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Selfie Image Preview Modal -->
    <div id="selfie-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-200 animate-scale-up">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h4 id="selfie-modal-title" class="text-xs font-bold text-slate-900 truncate">Foto Selfie Mahasiswa</h4>
                <button type="button" onclick="closeSelfieModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-slate-900">
                <img id="selfie-modal-img" src="" alt="Bukti Selfie" class="max-h-80 rounded-2xl object-contain shadow">
            </div>
            <div class="p-3 text-center bg-slate-50 text-[11px] text-slate-500 font-medium">
                Bukti visual presensi mahasiswa dengan enkripsi akses privat
            </div>
        </div>
    </div>

    <!-- Attendance Correction Modal -->
    <div id="correction-modal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-scale-up">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Koreksi Presensi Mahasiswa</h3>
                <button type="button" onclick="closeCorrectionModal()" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="correction-form" method="POST" action="" class="space-y-4 pt-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mahasiswa</label>
                    <p id="correction-student-name" class="text-sm font-extrabold text-slate-900"></p>
                </div>

                <div>
                    <label for="corr-status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Status Presensi Baru</label>
                    <select name="status" id="corr-status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="present">Hadir Tepat Waktu</option>
                        <option value="late">Hadir Terlambat</option>
                        <option value="excused">Izin (Disetujui)</option>
                        <option value="sick">Sakit (Ada Surat)</option>
                        <option value="absent">Alpa (Tidak Hadir)</option>
                    </select>
                </div>

                <div>
                    <label for="corr-reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Alasan Perubahan (Wajib Diisi untuk Audit Log)
                    </label>
                    <textarea name="reason" id="corr-reason" rows="3" required placeholder="Contoh: Mahasiswa mengajukan surat izin sakit resmi, GPS sempat terkendala di basement kampus."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeCorrectionModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30">
                        Simpan Koreksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openSelfieModal(url, studentInfo) {
            document.getElementById('selfie-modal-title').textContent = 'Bukti Selfie: ' + studentInfo;
            document.getElementById('selfie-modal-img').src = url;
            const modal = document.getElementById('selfie-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeSelfieModal() {
            const modal = document.getElementById('selfie-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openCorrectionModal(attendanceId, studentName, currentStatus) {
            document.getElementById('correction-student-name').textContent = studentName;
            document.getElementById('corr-status').value = currentStatus;
            document.getElementById('correction-form').action = "/lecturer/attendances/" + attendanceId + "/correct";
            const modal = document.getElementById('correction-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCorrectionModal() {
            const modal = document.getElementById('correction-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
    @endpush
</x-app-layout>
