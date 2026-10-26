<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('study_program', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $students = $query->latest('id')->paginate(10)->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function create(): View
    {
        return view('admin.students.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'student_number' => ['required', 'string', 'max:50', 'unique:students,student_number'],
            'department' => ['required', 'string', 'max:100'],
            'study_program' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
        ]);

        DB::transaction(function () use ($validated, &$student) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'student',
                'is_active' => true,
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $validated['student_number'],
                'department' => $validated['department'],
                'study_program' => $validated['study_program'],
                'semester' => $validated['semester'],
            ]);

            AuditLog::log(
                action: 'STUDENT_CREATED',
                entityType: 'Student',
                entityId: $student->id,
                oldValues: null,
                newValues: ['nim' => $student->student_number, 'name' => $user->name],
                reason: 'Administrator mendaftarkan mahasiswa baru'
            );
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        $student->load('user');

        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users')->ignore($student->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'student_number' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student->id)],
            'department' => ['required', 'string', 'max:100'],
            'study_program' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
        ]);

        DB::transaction(function () use ($validated, $request, $student) {
            $user = $student->user;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            $student->update([
                'student_number' => $validated['student_number'],
                'department' => $validated['department'],
                'study_program' => $validated['study_program'],
                'semester' => $validated['semester'],
            ]);

            AuditLog::log(
                action: 'STUDENT_UPDATED',
                entityType: 'Student',
                entityId: $student->id,
                oldValues: null,
                newValues: ['nim' => $student->student_number, 'name' => $user->name],
                reason: 'Administrator memperbarui data mahasiswa'
            );
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $name = $student->user->name;
        $user = $student->user;

        $student->delete();
        $user->delete();

        AuditLog::log(
            action: 'STUDENT_DELETED',
            entityType: 'Student',
            entityId: $student->id,
            oldValues: ['name' => $name],
            newValues: null,
            reason: 'Administrator menghapus data mahasiswa'
        );

        return redirect()->route('admin.students.index')
            ->with('success', "Mahasiswa {$name} telah berhasil dihapus.");
    }
}
