<x-app-layout>
    <x-slot name="title">Tambah Dosen Baru</x-slot>
    <x-slot name="header">Pendaftaran Dosen Pengampu Baru</x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.lecturers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Lengkap & Gelar</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Contoh: Dr. Budi Santoso, M.Kom." required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="lecturer_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">NIDN / NIP Dosen</label>
                    <input type="text" name="lecturer_number" id="lecturer_number" value="{{ old('lecturer_number') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Akun Dosen</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password Login (Min. 8 Karakter)</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="department" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fakultas / Departemen</label>
                    <input type="text" name="department" id="department" value="{{ old('department', 'Fakultas Ilmu Komputer') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="phone_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nomor Telepon / HP</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.lecturers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                    Daftarkan Dosen
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
