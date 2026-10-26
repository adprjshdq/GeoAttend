<x-app-layout>
    <x-slot name="title">Sesi Presensi Telah Ditutup</x-slot>
    <x-slot name="header">Sesi Presensi Tidak Tersedia</x-slot>

    <div class="max-w-md mx-auto bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 md:p-8 space-y-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
            <i data-lucide="clock-alert" class="w-8 h-8"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-slate-900">Sesi Presensi Telah Ditutup</h2>
            <p class="text-xs text-slate-500 mt-2">
                Sesi presensi untuk <strong>{{ $session->title }}</strong> ({{ $session->schedule->course->course_name }}) telah ditutup oleh dosen pengampu atau telah melewati batas waktu yang ditentukan.
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('student.dashboard') }}" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition inline-flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Dashboard Mahasiswa
            </a>
        </div>
    </div>
</x-app-layout>
