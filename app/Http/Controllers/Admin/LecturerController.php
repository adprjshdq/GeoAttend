<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LecturerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Lecturer::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('lecturer_number', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $lecturers = $query->latest('id')->paginate(10)->withQueryString();

        return view('admin.lecturers.index', compact('lecturers'));
    }

    public function create(): View
    {
        return view('admin.lecturers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'lecturer_number' => ['required', 'string', 'max:50', 'unique:lecturers,lecturer_number'],
            'department' => ['required', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:25'],
        ]);

        DB::transaction(function () use ($validated, &$lecturer) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'lecturer',
                'phone_number' => $validated['phone_number'] ?? null,
                'is_active' => true,
            ]);

            $lecturer = Lecturer::create([
                'user_id' => $user->id,
                'lecturer_number' => $validated['lecturer_number'],
                'department' => $validated['department'],
            ]);

            AuditLog::log(
                action: 'LECTURER_CREATED',
                entityType: 'Lecturer',
                entityId: $lecturer->id,
                oldValues: null,
                newValues: ['nidn' => $lecturer->lecturer_number, 'name' => $user->name],
                reason: 'Administrator menambahkan data dosen baru'
            );
        });

        return redirect()->route('admin.lecturers.index')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function edit(Lecturer $lecturer): View
    {
        $lecturer->load('user');

        return view('admin.lecturers.edit', compact('lecturer'));
    }

    public function update(Request $request, Lecturer $lecturer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users')->ignore($lecturer->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'lecturer_number' => ['required', 'string', 'max:50', Rule::unique('lecturers')->ignore($lecturer->id)],
            'department' => ['required', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:25'],
        ]);

        DB::transaction(function () use ($validated, $request, $lecturer) {
            $user = $lecturer->user;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone_number = $validated['phone_number'] ?? null;
            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            $lecturer->update([
                'lecturer_number' => $validated['lecturer_number'],
                'department' => $validated['department'],
            ]);

            AuditLog::log(
                action: 'LECTURER_UPDATED',
                entityType: 'Lecturer',
                entityId: $lecturer->id,
                oldValues: null,
                newValues: ['nidn' => $lecturer->lecturer_number, 'name' => $user->name],
                reason: 'Administrator memperbarui data dosen'
            );
        });

        return redirect()->route('admin.lecturers.index')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Lecturer $lecturer): RedirectResponse
    {
        $name = $lecturer->user->name;
        $user = $lecturer->user;

        $lecturer->delete();
        $user->delete();

        AuditLog::log(
            action: 'LECTURER_DELETED',
            entityType: 'Lecturer',
            entityId: $lecturer->id,
            oldValues: ['name' => $name],
            newValues: null,
            reason: 'Administrator menghapus data dosen'
        );

        return redirect()->route('admin.lecturers.index')
            ->with('success', "Dosen {$name} telah berhasil dihapus.");
    }
}
