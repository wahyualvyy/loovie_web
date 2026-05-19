<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $selectedMonth = $request->input('month', now()->format('Y-m'));

        $totalAccountBalance = (float) $user->financialAccounts()
            ->where('is_active', true)
            ->sum('current_balance');

        $budgets = $user->budgets()
            ->with('category')
            ->where('month', $selectedMonth)
            ->latest()
            ->get()
            ->map(function ($budget) use ($user, $selectedMonth) {
                $usedAmount = $user->transactions()
                    ->where('type', 'expense')
                    ->where('category_id', $budget->category_id)
                    ->whereYear('transaction_date', substr($selectedMonth, 0, 4))
                    ->whereMonth('transaction_date', substr($selectedMonth, 5, 2))
                    ->sum('amount');

                $amount = (float) $budget->amount;
                $used = (float) $usedAmount;
                $remaining = $amount - $used;
                $percentage = $amount > 0 ? min(round(($used / $amount) * 100), 999) : 0;

                $status = 'safe';

                if ($percentage >= 100) {
                    $status = 'over';
                } elseif ($percentage >= 80) {
                    $status = 'warning';
                }

                return [
                    'id' => $budget->id,
                    'category_id' => $budget->category_id,
                    'category_name' => $budget->category->name ?? '-',
                    'category_color' => $budget->category->color ?? '#6366f1',
                    'month' => $budget->month,
                    'amount' => $amount,
                    'used_amount' => $used,
                    'remaining_amount' => $remaining,
                    'percentage' => $percentage,
                    'status' => $status,
                    'description' => $budget->description,
                    'created_at' => $budget->created_at,
                ];
            });

        $categories = $user->categories()
            ->where('type', 'expense')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'color']);

        $totalBudget = (float) $budgets->sum('amount');
        $totalUsed = (float) $budgets->sum('used_amount');
        $totalRemaining = $totalBudget - $totalUsed;

        $unallocatedBalance = $totalAccountBalance - $totalBudget;

        $totalPercentage = $totalBudget > 0
            ? round(($totalUsed / $totalBudget) * 100)
            : 0;

        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'categories' => $categories,
            'selectedMonth' => $selectedMonth,
            'summary' => [
                'totalAccountBalance' => $totalAccountBalance,
                'totalBudget' => $totalBudget,
                'totalUsed' => $totalUsed,
                'totalRemaining' => $totalRemaining,
                'unallocatedBalance' => $unallocatedBalance,
                'totalPercentage' => $totalPercentage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $user->id),
            ],
            'month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $category = $user->categories()
            ->where('id', $validated['category_id'])
            ->where('type', 'expense')
            ->firstOrFail();

        Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $category->id,
                'month' => $validated['month'],
            ],
            [
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
            ]
        );

        return Redirect::route('budgets.index', ['month' => $validated['month']])
            ->with('success', 'Budget berhasil disimpan.');
    }

    public function update(Request $request, Budget $budget)
    {
        $this->authorizeUser($budget);

        $user = $request->user();

        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $user->id),
            ],
            'month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $category = $user->categories()
            ->where('id', $validated['category_id'])
            ->where('type', 'expense')
            ->firstOrFail();

        $budget->update([
            'category_id' => $category->id,
            'month' => $validated['month'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        return Redirect::route('budgets.index', ['month' => $validated['month']])
            ->with('success', 'Budget berhasil diperbarui.');
    }

    public function destroy(Budget $budget)
    {
        $this->authorizeUser($budget);

        $month = $budget->month;

        $budget->delete();

        return Redirect::route('budgets.index', ['month' => $month])
            ->with('success', 'Budget berhasil dihapus.');
    }

    private function authorizeUser(Budget $budget): void
    {
        if ((int) $budget->user_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
