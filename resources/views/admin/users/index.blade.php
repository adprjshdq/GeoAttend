<x-app-layout>
    <x-slot name="title">Manajemen Pengguna</x-slot>
    <x-slot name="header">Manajemen Akun Pengguna</x-slot>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Top Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <select name="role" class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Peran</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="lecturer" {{ request('role') === 'lecturer' ? 'selected' : '' }}>Dosen</option>
                    <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 transition">
                    Filter
                </button>
                @if(request('search') || request('role'))
                    <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 p-2">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition-all shrink-0">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah Pengguna
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">Nama & Email</th>
                        <th class="py-3.5 px-6">Peran</th>
                        <th class="py-3.5 px-6">No. Telepon</th>
                        <th class="py-3.5 px-6">Status Akun</th>
                        <th class="py-3.5 px-6">Terdaftar Sejak</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center shrink-0 border border-slate-200">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                        <div class="text-slate-400 text-[11px]">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold {{ $user->isAdmin() ? 'bg-amber-100 text-amber-800' : ($user->isLecturer() ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $user->phone_number ?? '-' }}
                            </td>
                            <td class="py-4 px-6">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-500">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada data pengguna yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
