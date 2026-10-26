<x-app-layout>
    <x-slot name="title">Jadwal & Geofence</x-slot>
    <x-slot name="header">Manajemen Jadwal Perkuliahan & Geofence</x-slot>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Top Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.schedules.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                <select name="course_id" class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Mata Kuliah</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->course_code }} - {{ $c->course_name }}
                        </option>
                    @endforeach
                </select>
                <select name="day" class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Hari</option>
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                        <option value="{{ $d }}" {{ request('day') === $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 transition">
                    Filter
                </button>
                @if(request('course_id') || request('day'))
                    <a href="{{ route('admin.schedules.index') }}" class="text-xs text-slate-500 hover:text-slate-800 p-2">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition-all shrink-0">
                <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                Tambah Jadwal
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">Hari & Jam</th>
                        <th class="py-3.5 px-6">Mata Kuliah</th>
                        <th class="py-3.5 px-6">Dosen Pengampu</th>
                        <th class="py-3.5 px-6">Ruangan Kelas</th>
                        <th class="py-3.5 px-6">Geofence Koordinat</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($schedules as $schedule)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $schedule->day_of_week }}
                                </span>
                                <div class="font-bold text-slate-900 mt-1.5">{{ $schedule->formatted_time }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-mono text-xs font-bold text-slate-500">{{ $schedule->course->course_code }}</span>
                                <div class="font-bold text-slate-900">{{ $schedule->course->course_name }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-medium text-slate-800">{{ $schedule->lecturer?->user?->name ?? 'Dosen' }}</div>
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-700">
                                <i data-lucide="door-open" class="inline w-3.5 h-3.5 text-slate-400 mr-1"></i>
                                {{ $schedule->room }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-mono text-[11px] text-slate-600">
                                    {{ number_format($schedule->location_latitude, 5) }}, {{ number_format($schedule->location_longitude, 5) }}
                                </div>
                                <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold mt-0.5">
                                    <i data-lucide="radio" class="w-3 h-3 text-emerald-500"></i>
                                    Radius {{ $schedule->geofence_radius }}m
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Hapus jadwal perkuliahan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada jadwal perkuliahan terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
