<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::with(['lecturer.user'])->withCount('students');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('course_code', 'like', "%{$search}%")
                    ->orWhere('course_name', 'like', "%{$search}%");
            });
        }

        $courses = $query->latest('id')->paginate(10)->withQueryString();

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        $lecturers = Lecturer::with('user')->get();

        return view('admin.courses.create', compact('lecturers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:30', 'unique:courses,course_code'],
            'course_name' => ['required', 'string', 'max:150'],
            'credits' => ['required', 'integer', 'min:1', 'max:6'],
            'semester' => ['required', 'integer', 'min:1', 'max:8'],
            'academic_year' => ['required', 'string', 'max:50'],
            'lecturer_id' => ['nullable', 'exists:lecturers,id'],
        ]);

        $course = Course::create($validated);

        AuditLog::log(
            action: 'COURSE_CREATED',
            entityType: 'Course',
            entityId: $course->id,
            oldValues: null,
            newValues: ['code' => $course->course_code, 'name' => $course->course_name],
            reason: 'Administrator membuat data mata kuliah baru'
        );

        return redirect()->route('admin.courses.index')
            ->with('success', "Mata kuliah {$course->course_name} berhasil ditambahkan.");
    }

    public function edit(Course $course): View
    {
        $lecturers = Lecturer::with('user')->get();

        return view('admin.courses.edit', compact('course', 'lecturers'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:30', Rule::unique('courses')->ignore($course->id)],
            'course_name' => ['required', 'string', 'max:150'],
            'credits' => ['required', 'integer', 'min:1', 'max:6'],
            'semester' => ['required', 'integer', 'min:1', 'max:8'],
            'academic_year' => ['required', 'string', 'max:50'],
            'lecturer_id' => ['nullable', 'exists:lecturers,id'],
        ]);

        $course->update($validated);

        AuditLog::log(
            action: 'COURSE_UPDATED',
            entityType: 'Course',
            entityId: $course->id,
            oldValues: null,
            newValues: ['code' => $course->course_code, 'name' => $course->course_name],
            reason: 'Administrator memperbarui data mata kuliah'
        );

        return redirect()->route('admin.courses.index')
            ->with('success', "Mata kuliah {$course->course_name} berhasil diperbarui.");
    }

    public function destroy(Course $course): RedirectResponse
    {
        $name = $course->course_name;
        $id = $course->id;

        $course->delete();

        AuditLog::log(
            action: 'COURSE_DELETED',
            entityType: 'Course',
            entityId: $id,
            oldValues: ['name' => $name],
            newValues: null,
            reason: 'Administrator menghapus data mata kuliah'
        );

        return redirect()->route('admin.courses.index')
            ->with('success', "Mata kuliah {$name} telah berhasil dihapus.");
    }

    /**
     * Show enrolled students and allow adding/removing students.
     */
    public function enrollments(Course $course): View
    {
        $course->load(['students.user', 'lecturer.user']);
        $enrolledStudentIds = $course->students->pluck('id')->toArray();

        $availableStudents = Student::with('user')
            ->whereNotIn('id', $enrolledStudentIds)
            ->get();

        return view('admin.courses.enrollments', compact('course', 'availableStudents'));
    }

    public function enrollStudent(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_name' => ['nullable', 'string', 'max:50'],
        ]);

        Enrollment::firstOrCreate([
            'course_id' => $course->id,
            'student_id' => $validated['student_id'],
        ], [
            'class_name' => $validated['class_name'] ?? 'Kelas A',
        ]);

        return back()->with('success', 'Mahasiswa berhasil didaftarkan ke mata kuliah.');
    }

    public function removeStudent(Course $course, Student $student): RedirectResponse
    {
        Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->delete();

        return back()->with('info', 'Mahasiswa berhasil dikeluarkan dari kelas.');
    }
}
