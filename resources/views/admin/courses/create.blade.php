<x-app-layout>
    <x-slot name="title">Tambah Mata Kuliah</x-slot>
    <x-slot name="header">Tambah Mata Kuliah Baru</x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.courses.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label for="course_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kode MK</label>
                    <input type="text" name="course_code" id="course_code" value="{{ old('course_code') }}" placeholder="Contoh: IF2104" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
                <div class="sm:col-span-2">
                    <label for="course_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Mata Kuliah</label>
                    <input type="text" name="course_name" id="course_name" value="{{ old('course_name') }}" placeholder="Contoh: Pemrograman Web Lanjut" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="credits" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Bobot SKS</label>
                    <input type="number" name="credits" id="credits" value="{{ old('credits', 3) }}" min="1" max="6" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="semester" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Semester</label>
                    <input type="number" name="semester" id="semester" value="{{ old('semester', 4) }}" min="1" max="8" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="academic_year" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tahun Akademik</label>
                    <input type="text" name="academic_year" id="academic_year" value="{{ old('academic_year', '2025/2026 Genap') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="lecturer_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Dosen Pengampu Utama</label>
                <select name="lecturer_id" id="lecturer_id"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Dosen Pengampu --</option>
                    @foreach($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                            {{ $lecturer->user->name }} (NIDN: {{ $lecturer->lecturer_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.courses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                    Simpan Mata Kuliah
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
