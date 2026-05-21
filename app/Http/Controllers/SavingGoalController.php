<?php

namespace App\Http\Controllers;

use App\Models\SavingGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SavingGoalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $goals = SavingGoal::query()
            ->where('user_id', $user->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total_goals' => SavingGoal::where('user_id', $user->id)->count(),
            'active_goals' => SavingGoal::where('user_id', $user->id)->where('status', 'active')->count(),
            'completed_goals' => SavingGoal::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled_goals' => SavingGoal::where('user_id', $user->id)->where('status', 'cancelled')->count(),
            'total_target' => SavingGoal::where('user_id', $user->id)->sum('target_amount'),
            'total_collected' => SavingGoal::where('user_id', $user->id)->sum('current_amount'),
        ];

        return Inertia::render('SavingGoals/Index', [
            'goals' => $goals,
            'summary' => $summary,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('SavingGoals/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'completed', 'cancelled'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['user_id'] = Auth::id();
        $validated['current_amount'] = $validated['current_amount'] ?? 0;

        if ((float) $validated['current_amount'] >= (float) $validated['target_amount']) {
            $validated['status'] = 'completed';
        }

        SavingGoal::create($validated);

        return redirect()
            ->route('saving-goals.index')
            ->with('success', 'Target tabungan berhasil dibuat.');
    }

    public function edit(SavingGoal $savingGoal)
    {
        $this->authorizeGoal($savingGoal);

        return Inertia::render('SavingGoals/Edit', [
            'goal' => $savingGoal,
        ]);
    }

    public function update(Request $request, SavingGoal $savingGoal)
    {
        $this->authorizeGoal($savingGoal);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'current_amount' => ['required', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'completed', 'cancelled'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ((float) $validated['current_amount'] >= (float) $validated['target_amount']) {
            $validated['status'] = 'completed';
        }

        $savingGoal->update($validated);

        return redirect()
            ->route('saving-goals.index')
            ->with('success', 'Target tabungan berhasil diperbarui.');
    }

    public function destroy(SavingGoal $savingGoal)
    {
        $this->authorizeGoal($savingGoal);

        $savingGoal->delete();

        return back()->with('success', 'Target tabungan berhasil dihapus.');
    }

    private function authorizeGoal(SavingGoal $savingGoal): void
    {
        if ($savingGoal->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
