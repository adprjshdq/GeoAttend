<x-app-layout>
    <x-slot name="title">Peserta Kelas: {{ $course->course_name }}</x-slot>
    <x-slot name="header">Kelola Peserta Kelas: {{ $course->course_name }} ({{ $course->course_code }})</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Enroll New Student -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 h-fit space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-indigo-600"></i>
                Daftarkan Mahasiswa Baru
            </h3>
            <p class="text-xs text-slate-500">Pilih mahasiswa yang terdaftar dalam sistem untuk dimasukkan ke dalam kelas mata kuliah ini.</p>

            <form action="{{ route('admin.courses.enroll', $course) }}" method="POST" class="space-y-4 pt-2">
                @csrf
                <div>
                    <label for="student_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Mahasiswa</label>
                    <select name="student_id" id="student_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($availableStudents as $st)
                            <option value="{{ $st->id }}">
                                {{ $st->student_number }} — {{ $st->user->name }} ({{ $st->study_program }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="class_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Rombel / Kelas</label>
                    <input type="text" name="class_name" id="class_name" value="IF-4A" placeholder="Contoh: IF-4A"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Daftarkan ke Kelas
                </button>
            </form>
        </div>

        <!-- Enrolled Students List -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Daftar Mahasiswa Terdaftar</h3>
                    <p class="text-xs text-slate-500">Total: {{ $course->students->count() }} Mahasiswa</p>
                </div>
                <a href="{{ route('admin.courses.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Daftar MK
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-5">No</th>
                            <th class="py-3 px-5">NIM</th>
                            <th class="py-3 px-5">Nama Mahasiswa</th>
                            <th class="py-3 px-5">Program Studi</th>
                            <th class="py-3 px-5">Kelas</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($course->students as $index => $student)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-5 text-slate-400 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3 px-5 font-mono font-bold text-indigo-700">{{ $student->student_number }}</td>
                                <td class="py-3 px-5 font-semibold text-slate-900">{{ $student->user->name }}</td>
                                <td class="py-3 px-5 text-slate-600">{{ $student->study_program }}</td>
                                <td class="py-3 px-5">
                                    <span class="px-2 py-0.5 rounded-sm bg-slate-100 text-slate-700 font-mono text-[11px]">
                                        {{ $student->pivot->class_name ?? 'Kelas A' }}
                                    </span>
                                </td>
                                <td class="py-3 px-5 text-right">
                                    <form action="{{ route('admin.courses.unenroll', [$course, $student]) }}" method="POST" onsubmit="return confirm('Keluarkan {{ $student->user->name }} dari kelas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 font-medium hover:underline inline-flex items-center gap-1">
                                            <i data-lucide="user-x" class="w-3.5 h-3.5"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    Belum ada mahasiswa yang didaftarkan ke mata kuliah ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
