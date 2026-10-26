<x-app-layout>
    <x-slot name="title">Presensi Telah Tercatat</x-slot>
    <x-slot name="header">Bukti Kehadiran Perkuliahan</x-slot>

    <div class="max-w-xl mx-auto bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 md:p-8 space-y-6 text-center">
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
            <i data-lucide="check-circle-2" class="w-10 h-10"></i>
        </div>

        <div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                Presensi Berhasil Terverifikasi
            </span>
            <h2 class="text-xl font-extrabold text-slate-900 mt-2">{{ $session->title }}</h2>
            <p class="text-xs text-slate-500 mt-1">{{ $session->schedule->course->course_name }} ({{ $session->schedule->course->course_code }})</p>
        </div>

        <!-- Attendance Details Card -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 text-xs space-y-3 text-left">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Status Kehadiran</span>
                <span class="px-2.5 py-0.5 rounded-md font-bold bg-{{ $existingAttendance->status_color }}-100 text-{{ $existingAttendance->status_color }}-800">
                    {{ $existingAttendance->status_label }}
                </span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Waktu Presensi</span>
                <span class="font-bold text-slate-800 font-mono">{{ $existingAttendance->attended_at->format('d/m/Y H:i:s') }} WIB</span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Jarak Terverifikasi (GPS)</span>
                <span class="font-bold text-slate-800">{{ round($existingAttendance->distance_meters, 1) }} meter dari kelas</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500 font-medium">Akurasi Perangkat</span>
                <span class="font-bold text-slate-800 font-mono">&plusmn;{{ $existingAttendance->gps_accuracy }} meter</span>
            </div>
        </div>

        <!-- Selfie Photo Preview if available -->
        @if($existingAttendance->selfie_path)
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Foto Selfie Terverifikasi</p>
                <div class="w-32 h-32 mx-auto rounded-2xl overflow-hidden border-2 border-emerald-500/40 shadow-md">
                    <img src="{{ route('selfies.show', $existingAttendance) }}" alt="Selfie" class="w-full h-full object-cover">
                </div>
            </div>
        @endif

        <div class="pt-4 flex items-center justify-center gap-3">
            <a href="{{ route('student.dashboard') }}" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition">
                Kembali ke Dashboard
            </a>
            <a href="{{ route('student.history') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                Lihat Riwayat Presensi
            </a>
        </div>
    </div>
</x-app-layout>
