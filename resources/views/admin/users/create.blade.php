<x-app-layout>
    <x-slot name="title">Tambah Pengguna Baru</x-slot>
    <x-slot name="header">Tambah Akun Pengguna Baru</x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="phone_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">No. WhatsApp / HP</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password (Min. 8 Karakter)</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Peran Pengguna</label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="lecturer" {{ old('role') === 'lecturer' ? 'selected' : '' }}>Dosen</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded-sm text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-slate-700">Akun Aktif (Dapat masuk ke dalam sistem)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
