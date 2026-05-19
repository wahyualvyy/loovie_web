<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $year = request('year', now()->year);

        $currentMonth = now()->month;
        $currentYear = now()->year;
        $selectedMonth = now()->format('Y-m');

        // Summary Cards Data
        $totalBalance = (float) $user->financialAccounts()
            ->where('is_active', true)
            ->sum('current_balance');

        $monthlyIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereYear('transaction_date', $currentYear)
            ->whereMonth('transaction_date', $currentMonth)
            ->sum('amount');

        $monthlyExpense = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereYear('transaction_date', $currentYear)
            ->whereMonth('transaction_date', $currentMonth)
            ->sum('amount');

        $netBalance = $monthlyIncome - $monthlyExpense;

        // Chart Data
        $chartData = $this->getMonthlyChartData($user, $year);

        // Recent Transactions
        $recentTransactions = $user->transactions()
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'category_name' => $transaction->category->name ?? '-',
                    'category_color' => $transaction->category->color ?? '#6366f1',
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'account_name' => $transaction->account->name ?? '-',
                    'transaction_date' => $transaction->transaction_date,
                    'description' => $transaction->description,
                ];
            });

        // Recent Notes
        $recentNotes = $user->notes()
            ->orderBy('note_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'content' => substr($note->content ?? '', 0, 100),
                    'label' => $note->label,
                    'note_date' => $note->note_date,
                ];
            });

        // Account Summary
        $accountSummary = $user->financialAccounts()
            ->where('is_active', true)
            ->orderBy('current_balance', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'current_balance' => (float) $account->current_balance,
                    'initial_balance' => (float) $account->initial_balance,
                ];
            });

        // Budget Summary
        $budgetSummary = $this->getBudgetSummary($user, $selectedMonth, $totalBalance);

        return Inertia::render('Dashboard', [
            'totalBalance' => $totalBalance,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'netBalance' => $netBalance,
            'chartData' => $chartData,
            'recentTransactions' => $recentTransactions,
            'recentNotes' => $recentNotes,
            'accountSummary' => $accountSummary,
            'selectedYear' => (int) $year,
            'availableYears' => $this->getAvailableYears($user),
            'budgetSummary' => $budgetSummary,
        ]);
    }

    private function getBudgetSummary($user, string $selectedMonth, float $totalAccountBalance): array
    {
        $budgetItems = $user->budgets()
            ->with('category')
            ->where('month', $selectedMonth)
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

                $percentage = $amount > 0
                    ? min(round(($used / $amount) * 100), 999)
                    : 0;

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
                ];
            });

        $totalBudget = (float) $budgetItems->sum('amount');
        $totalUsed = (float) $budgetItems->sum('used_amount');
        $totalRemaining = $totalBudget - $totalUsed;
        $unallocatedBalance = $totalAccountBalance - $totalBudget;

        $totalPercentage = $totalBudget > 0
            ? round(($totalUsed / $totalBudget) * 100)
            : 0;

        return [
            'month' => $selectedMonth,
            'totalAccountBalance' => $totalAccountBalance,
            'totalBudget' => $totalBudget,
            'totalUsed' => $totalUsed,
            'totalRemaining' => $totalRemaining,
            'unallocatedBalance' => $unallocatedBalance,
            'totalPercentage' => $totalPercentage,
            'warnings' => $budgetItems
                ->filter(fn ($item) => in_array($item['status'], ['warning', 'over']))
                ->values(),
            'items' => $budgetItems
                ->sortByDesc('percentage')
                ->take(5)
                ->values(),
        ];
    }

    private function getMonthlyChartData($user, $year): array
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::createFromDate($year, $month, 1)->format('M');
            $months[] = $monthName;

            $income = $user->transactions()
                ->where('type', 'income')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');

            $expense = $user->transactions()
                ->where('type', 'expense')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');

            $incomeData[] = (float) $income;
            $expenseData[] = (float) $expense;
        }

        return [
            'months' => $months,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }

    private function getAvailableYears($user): array
    {
        $yearExpression = $this->getYearExpression();

        $years = $user->transactions()
            ->selectRaw($yearExpression . ' as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            return [now()->year];
        }

        return $years
            ->map(fn ($year) => (int) $year)
            ->toArray();
    }

    private function getYearExpression(): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'pgsql' => 'EXTRACT(YEAR FROM transaction_date)::int',
            'sqlite' => "CAST(strftime('%Y', transaction_date) AS INTEGER)",
            default => 'YEAR(transaction_date)',
        };
    }
}
