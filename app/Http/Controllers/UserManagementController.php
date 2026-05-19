<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== 'admin', 403);

        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->where('role', 'user')
                ->latest()
                ->get([
                    'id',
                    'name',
                    'email',
                    'role',
                    'photo_path',
                    'created_at',
                ]),
        ]);
    }

    public function store(Request $request)
    {
        abort_if($request->user()->role !== 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        return back()->with('success', 'User berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        abort_if($request->user()->role !== 'admin', 403);

        if ($user->role === 'admin') {
            return back()->withErrors([
                'user' => 'Akun admin tidak boleh diedit dari Data Master.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = 'user';

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->role !== 'admin', 403);

        if ($user->role === 'admin') {
            return back()->withErrors([
                'user' => 'Akun admin tidak boleh dihapus.',
            ]);
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
