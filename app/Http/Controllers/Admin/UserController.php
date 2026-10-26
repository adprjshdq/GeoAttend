<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->latest('id')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,lecturer,student'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone_number' => $validated['phone_number'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::log(
            action: 'USER_CREATED',
            entityType: 'User',
            entityId: $user->id,
            oldValues: null,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            reason: 'Administrator membuat akun pengguna baru'
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:admin,lecturer,student'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $user->only(['name', 'email', 'role', 'is_active']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->is_active = $request->boolean('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        AuditLog::log(
            action: 'USER_UPDATED',
            entityType: 'User',
            entityId: $user->id,
            oldValues: $oldValues,
            newValues: $user->only(['name', 'email', 'role', 'is_active']),
            reason: 'Administrator memperbarui data pengguna'
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $userId = $user->id;

        $user->delete();

        AuditLog::log(
            action: 'USER_DELETED',
            entityType: 'User',
            entityId: $userId,
            oldValues: ['name' => $name],
            newValues: null,
            reason: 'Administrator menghapus akun pengguna'
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$name} telah berhasil dihapus.");
    }
}
