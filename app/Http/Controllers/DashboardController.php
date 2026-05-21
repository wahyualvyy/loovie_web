<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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

        // Saving Goals Summary
        $savingGoalsSummary = $this->getSavingGoalsSummary($user);
        $activeSavingGoals = $this->getActiveSavingGoals($user);

        // Recurring Transactions Summary
        $recurringSummary = $this->getRecurringSummary($user);
        $dueRecurringTransactions = $this->getDueRecurringTransactions($user);
        $upcomingRecurringTransactions = $this->getUpcomingRecurringTransactions($user);

        // Dashboard Notifications
        $dashboardNotifications = $this->getDashboardNotifications(
            $user,
            $budgetSummary,
            $dueRecurringTransactions
        );

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
            'savingGoalsSummary' => $savingGoalsSummary,
            'activeSavingGoals' => $activeSavingGoals,
            'recurringSummary' => $recurringSummary,
            'dueRecurringTransactions' => $dueRecurringTransactions,
            'upcomingRecurringTransactions' => $upcomingRecurringTransactions,
            'dashboardNotifications' => $dashboardNotifications,
        ]);
    }

    private function getRecurringSummary($user): array
    {
        $recurring = $user->recurringTransactions();

        $total = (int) (clone $recurring)->count();
        $active = (int) (clone $recurring)->where('is_active', true)->count();
        $inactive = (int) (clone $recurring)->where('is_active', false)->count();

        $incomeTotal = (float) (clone $recurring)
            ->where('is_active', true)
            ->where('type', 'income')
            ->sum('amount');

        $expenseTotal = (float) (clone $recurring)
            ->where('is_active', true)
            ->where('type', 'expense')
            ->sum('amount');

        $dueCount = (int) (clone $recurring)
            ->where('is_active', true)
            ->whereDate('next_date', '<=', now()->toDateString())
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'dueCount' => $dueCount,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'netTotal' => $incomeTotal - $expenseTotal,
        ];
    }

    private function getDueRecurringTransactions($user)
    {
        return $user->recurringTransactions()
            ->with(['account', 'category'])
            ->where('is_active', true)
            ->whereDate('next_date', '<=', now()->toDateString())
            ->orderBy('next_date')
            ->limit(5)
            ->get()
            ->map(function ($recurring) {
                return [
                    'id' => $recurring->id,
                    'title' => $recurring->title,
                    'type' => $recurring->type,
                    'type_label' => $recurring->type_label,
                    'amount' => (float) $recurring->amount,
                    'frequency' => $recurring->frequency,
                    'frequency_label' => $recurring->frequency_label,
                    'next_date' => $recurring->next_date,
                    'account_name' => $recurring->account->name ?? '-',
                    'category_name' => $recurring->category->name ?? '-',
                    'category_color' => $recurring->category->color ?? '#6366f1',
                    'description' => $recurring->description,
                ];
            });
    }

    private function getUpcomingRecurringTransactions($user)
    {
        return $user->recurringTransactions()
            ->with(['account', 'category'])
            ->where('is_active', true)
            ->whereDate('next_date', '>=', now()->toDateString())
            ->orderBy('next_date')
            ->limit(5)
            ->get()
            ->map(function ($recurring) {
                return [
                    'id' => $recurring->id,
                    'title' => $recurring->title,
                    'type' => $recurring->type,
                    'type_label' => $recurring->type_label,
                    'amount' => (float) $recurring->amount,
                    'frequency' => $recurring->frequency,
                    'frequency_label' => $recurring->frequency_label,
                    'next_date' => $recurring->next_date,
                    'account_name' => $recurring->account->name ?? '-',
                    'category_name' => $recurring->category->name ?? '-',
                    'category_color' => $recurring->category->color ?? '#6366f1',
                    'description' => $recurring->description,
                ];
            });
    }

    private function getDashboardNotifications($user, array $budgetSummary, $dueRecurringTransactions): array
    {
        $notifications = collect();

        /**
         * 1. Notifikasi Budget
         */
        collect($budgetSummary['warnings'] ?? [])->each(function ($budget) use ($notifications) {
            $isOver = ($budget['status'] ?? '') === 'over';

            $notifications->push([
                'id' => 'budget-' . $budget['id'],
                'type' => 'budget',
                'severity' => $isOver ? 'danger' : 'warning',
                'title' => $isOver ? 'Budget Melebihi Limit' : 'Budget Hampir Habis',
                'message' => ($budget['category_name'] ?? 'Kategori') .
                    ' sudah terpakai ' .
                    ($budget['percentage'] ?? 0) .
                    '% dari budget.',
                'amount' => (float) ($budget['used_amount'] ?? 0),
                'href' => '/budgets',
                'date' => now()->toDateString(),
            ]);
        });

        /**
         * 2. Notifikasi Target Tabungan
         */
        $user->savingGoals()
            ->where('status', 'active')
            ->get()
            ->each(function ($goal) use ($notifications) {
                $targetAmount = (float) $goal->target_amount;
                $currentAmount = (float) $goal->current_amount;

                if ($targetAmount <= 0) {
                    return;
                }

                $progress = min(round(($currentAmount / $targetAmount) * 100), 100);
                $isAlmostDone = $progress >= 80;
                $isNearDeadline = $goal->target_date
                    && Carbon::parse($goal->target_date)->lte(now()->addDays(7));

                if (!$isAlmostDone && !$isNearDeadline) {
                    return;
                }

                $notifications->push([
                    'id' => 'goal-' . $goal->id,
                    'type' => 'saving_goal',
                    'severity' => $isAlmostDone ? 'success' : 'warning',
                    'title' => $isAlmostDone ? 'Target Hampir Tercapai' : 'Deadline Target Dekat',
                    'message' => $goal->title . ' sudah mencapai ' . $progress . '% dari target.',
                    'amount' => $currentAmount,
                    'href' => '/saving-goals/' . $goal->id . '/edit',
                    'date' => optional($goal->target_date)->toDateString(),
                ]);
            });

        /**
         * 3. Notifikasi Recurring Jatuh Tempo
         */
        collect($dueRecurringTransactions)->each(function ($recurring) use ($notifications) {
            $notifications->push([
                'id' => 'recurring-' . $recurring['id'],
                'type' => 'recurring',
                'severity' => 'warning',
                'title' => 'Recurring Jatuh Tempo',
                'message' => $recurring['title'] . ' sudah jatuh tempo untuk digenerate.',
                'amount' => (float) $recurring['amount'],
                'href' => '/recurring-transactions/' . $recurring['id'] . '/edit',
                'date' => $recurring['next_date'],
            ]);
        });

        /**
         * 4. Notifikasi Saldo Akun Rendah
         */
        $lowBalanceLimit = 50000;

        $user->financialAccounts()
            ->where('is_active', true)
            ->where('current_balance', '<=', $lowBalanceLimit)
            ->orderBy('current_balance')
            ->limit(5)
            ->get()
            ->each(function ($account) use ($notifications) {
                $notifications->push([
                    'id' => 'account-' . $account->id,
                    'type' => 'account',
                    'severity' => 'danger',
                    'title' => 'Saldo Akun Rendah',
                    'message' => 'Saldo akun ' . $account->name . ' sedang rendah.',
                    'amount' => (float) $account->current_balance,
                    'href' => '/financial-accounts',
                    'date' => now()->toDateString(),
                ]);
            });

        $severityOrder = [
            'danger' => 1,
            'warning' => 2,
            'success' => 3,
            'info' => 4,
        ];

        $items = $notifications
            ->sortBy(fn($item) => $severityOrder[$item['severity']] ?? 99)
            ->values();

        return [
            'total' => $items->count(),
            'danger' => $items->where('severity', 'danger')->count(),
            'warning' => $items->where('severity', 'warning')->count(),
            'success' => $items->where('severity', 'success')->count(),
            'items' => $items->take(8)->values(),
        ];
    }
    private function getSavingGoalsSummary($user): array
    {
        $goals = $user->savingGoals();

        $totalGoals = (int) (clone $goals)->count();
        $activeGoals = (int) (clone $goals)->where('status', 'active')->count();
        $completedGoals = (int) (clone $goals)->where('status', 'completed')->count();
        $cancelledGoals = (int) (clone $goals)->where('status', 'cancelled')->count();

        $totalTarget = (float) (clone $goals)->sum('target_amount');
        $totalCollected = (float) (clone $goals)->sum('current_amount');
        $totalRemaining = max($totalTarget - $totalCollected, 0);

        $overallProgress = $totalTarget > 0
            ? min(round(($totalCollected / $totalTarget) * 100), 100)
            : 0;

        return [
            'totalGoals' => $totalGoals,
            'activeGoals' => $activeGoals,
            'completedGoals' => $completedGoals,
            'cancelledGoals' => $cancelledGoals,
            'totalTarget' => $totalTarget,
            'totalCollected' => $totalCollected,
            'totalRemaining' => $totalRemaining,
            'overallProgress' => $overallProgress,
        ];
    }

    private function getActiveSavingGoals($user)
    {
        return $user->savingGoals()
            ->where('status', 'active')
            ->orderBy('target_date')
            ->orderByDesc('current_amount')
            ->limit(3)
            ->get()
            ->map(function ($goal) {
                $targetAmount = (float) $goal->target_amount;
                $currentAmount = (float) $goal->current_amount;

                $progressPercentage = $targetAmount > 0
                    ? min(round(($currentAmount / $targetAmount) * 100), 100)
                    : 0;

                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'target_amount' => $targetAmount,
                    'current_amount' => $currentAmount,
                    'remaining_amount' => max($targetAmount - $currentAmount, 0),
                    'progress_percentage' => $progressPercentage,
                    'target_date' => $goal->target_date,
                    'status' => $goal->status,
                    'status_label' => $goal->status_label,
                    'description' => $goal->description,
                ];
            });
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
                ->filter(fn($item) => in_array($item['status'], ['warning', 'over']))
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
            ->map(fn($year) => (int) $year)
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
