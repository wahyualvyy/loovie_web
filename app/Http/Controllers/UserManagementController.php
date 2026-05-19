<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            $this->createDefaultCategories($user);
        });

        return back()->with('success', 'User berhasil dibuat dengan kategori default.');
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

    private function createDefaultCategories(User $user): void
    {
        $categories = [
            [
                'name' => 'Gaji',
                'type' => 'income',
                'color' => '#22c55e',
                'icon' => 'Banknote',
            ],
            [
                'name' => 'Bonus',
                'type' => 'income',
                'color' => '#10b981',
                'icon' => 'Gift',
            ],
            [
                'name' => 'Hadiah',
                'type' => 'income',
                'color' => '#14b8a6',
                'icon' => 'PartyPopper',
            ],
            [
                'name' => 'Pemasukan Lainnya',
                'type' => 'income',
                'color' => '#06b6d4',
                'icon' => 'PlusCircle',
            ],

            [
                'name' => 'Makan',
                'type' => 'expense',
                'color' => '#ef4444',
                'icon' => 'Utensils',
            ],
            [
                'name' => 'Transportasi',
                'type' => 'expense',
                'color' => '#f97316',
                'icon' => 'Car',
            ],
            [
                'name' => 'Belanja',
                'type' => 'expense',
                'color' => '#ec4899',
                'icon' => 'ShoppingBag',
            ],
            [
                'name' => 'Tagihan',
                'type' => 'expense',
                'color' => '#8b5cf6',
                'icon' => 'ReceiptText',
            ],
            [
                'name' => 'Hiburan',
                'type' => 'expense',
                'color' => '#6366f1',
                'icon' => 'Gamepad2',
            ],
            [
                'name' => 'Kesehatan',
                'type' => 'expense',
                'color' => '#0ea5e9',
                'icon' => 'HeartPulse',
            ],
            [
                'name' => 'Pendidikan',
                'type' => 'expense',
                'color' => '#eab308',
                'icon' => 'GraduationCap',
            ],
            [
                'name' => 'Pengeluaran Lainnya',
                'type' => 'expense',
                'color' => '#64748b',
                'icon' => 'MoreHorizontal',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'color' => $category['color'],
                    'icon' => $category['icon'],
                ]
            );
        }
    }
}
