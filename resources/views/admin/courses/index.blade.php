<x-app-layout>
    <x-slot name="title">Manajemen Mata Kuliah</x-slot>
    <x-slot name="header">Daftar Mata Kuliah & Kelas</x-slot>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Top Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="flex items-center gap-2 flex-1">
                <div class="relative w-full sm:w-80">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama mata kuliah..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.courses.index') }}" class="text-xs text-slate-500 hover:text-slate-800 p-2">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition-all shrink-0">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Tambah Mata Kuliah
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">Kode & Nama Mata Kuliah</th>
                        <th class="py-3.5 px-6">SKS / Semester</th>
                        <th class="py-3.5 px-6">Tahun Akademik</th>
                        <th class="py-3.5 px-6">Dosen Pengampu</th>
                        <th class="py-3.5 px-6">Peserta</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($courses as $course)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                    {{ $course->course_code }}
                                </span>
                                <div class="font-bold text-slate-900 mt-1">{{ $course->course_name }}</div>
                            </td>
                            <td class="py-4 px-6 text-slate-700 font-medium">
                                {{ $course->credits }} SKS &bull; Sem. {{ $course->semester }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $course->academic_year }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-900">{{ $course->lecturer?->user?->name ?? 'Belum Ditentukan' }}</div>
                                @if($course->lecturer)
                                    <div class="text-[11px] text-slate-400 font-mono">NIDN: {{ $course->lecturer->lecturer_number }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.courses.enrollments', $course) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 font-semibold text-slate-700 transition">
                                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                    {{ $course->students_count }} Mahasiswa
                                </a>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.courses.enrollments', $course) }}" title="Kelola Peserta Kelas" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition">
                                        <i data-lucide="user-check" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.courses.edit', $course) }}" title="Edit Mata Kuliah" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah {{ $course->course_name }}?')">
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
                                Belum ada data mata kuliah terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($courses->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
