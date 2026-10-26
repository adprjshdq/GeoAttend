<x-app-layout>
    <x-slot name="title">Pengaturan Sistem</x-slot>
    <x-slot name="header">Konfigurasi & Parameter Sistem Presensi</x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="campus_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Kampus / Instansi</label>
                <input type="text" name="campus_name" id="campus_name" value="{{ old('campus_name', $settings['campus_name']->value ?? 'Universitas Teknologi Nusantara') }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-slate-400 mt-1">Nama instansi yang tampil di header dan laporan ekspor presensi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="max_gps_accuracy" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Batas Toleransi Akurasi GPS (Meter)</label>
                    <input type="number" name="max_gps_accuracy" id="max_gps_accuracy" value="{{ old('max_gps_accuracy', $settings['max_gps_accuracy']->value ?? 50) }}" min="10" max="200" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-slate-400 mt-1">GPS dengan ketidakpastian lebih besar dari nilai ini akan ditolak.</p>
                </div>
                <div>
                    <label for="late_threshold_minutes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Batas Menit Terlambat (Menit)</label>
                    <input type="number" name="late_threshold_minutes" id="late_threshold_minutes" value="{{ old('late_threshold_minutes', $settings['late_threshold_minutes']->value ?? 15) }}" min="0" max="120" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-slate-400 mt-1">Presensi setelah menit ke-N dihitung sebagai Hadir Terlambat.</p>
                </div>
            </div>

            <div>
                <label for="default_geofence_radius" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Default Radius Geofence (Meter)</label>
                <input type="number" name="default_geofence_radius" id="default_geofence_radius" value="{{ old('default_geofence_radius', $settings['default_geofence_radius']->value ?? 100) }}" min="10" max="1000" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-slate-400 mt-1">Radius default saat membuat jadwal perkuliahan baru.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                    Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
