<x-app-layout>
    <x-slot name="title">Edit Mahasiswa: {{ $student->user->name }}</x-slot>
    <x-slot name="header">Edit Data Mahasiswa</x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.students.update', $student) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Lengkap Mahasiswa</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $student->user->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="student_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nomor Induk Mahasiswa (NIM)</label>
                    <input type="text" name="student_number" id="student_number" value="{{ old('student_number', $student->student_number) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Akun Mahasiswa</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $student->user->email) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ganti Password (Opsional)</label>
                    <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak diubah"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label for="semester" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Semester</label>
                    <input type="number" name="semester" id="semester" value="{{ old('semester', $student->semester) }}" min="1" max="14" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="sm:col-span-2">
                    <label for="study_program" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Program Studi</label>
                    <input type="text" name="study_program" id="study_program" value="{{ old('study_program', $student->study_program) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="department" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fakultas / Departemen</label>
                <input type="text" name="department" id="department" value="{{ old('department', $student->department) }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.students.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                    Perbarui Data Mahasiswa
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
