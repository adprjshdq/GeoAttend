<x-app-layout>
    <x-slot name="title">Manajemen Mahasiswa</x-slot>
    <x-slot name="header">Data Mahasiswa Terdaftar</x-slot>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Top Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex items-center gap-2 flex-1">
                <div class="relative w-full sm:w-80">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIM, atau program studi..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.students.index') }}" class="text-xs text-slate-500 hover:text-slate-800 p-2">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition-all shrink-0">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah Mahasiswa
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">NIM</th>
                        <th class="py-3.5 px-6">Nama Mahasiswa</th>
                        <th class="py-3.5 px-6">Program Studi & Fakultas</th>
                        <th class="py-3.5 px-6">Semester</th>
                        <th class="py-3.5 px-6">Email Akun</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-indigo-700">
                                {{ $student->student_number }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $student->user->name }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-medium text-slate-800">{{ $student->study_program }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $student->department }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 font-semibold text-slate-700">
                                    Semester {{ $student->semester }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-500">
                                {{ $student->user->email }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.students.edit', $student) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Hapus mahasiswa {{ $student->user->name }} beserta akun loginnya?')">
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
                                Belum ada data mahasiswa yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
