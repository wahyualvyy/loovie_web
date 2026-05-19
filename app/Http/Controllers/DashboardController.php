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

        // Summary Cards Data
        $totalBalance = $user->financialAccounts()
            ->where('is_active', true)
            ->sum('current_balance');

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlyIncome = $user->transactions()
            ->where('type', 'income')
            ->whereYear('transaction_date', $currentYear)
            ->whereMonth('transaction_date', $currentMonth)
            ->sum('amount');

        $monthlyExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereYear('transaction_date', $currentYear)
            ->whereMonth('transaction_date', $currentMonth)
            ->sum('amount');

        $netBalance = $monthlyIncome - $monthlyExpense;

        // Chart Data - Monthly transactions for selected year
        $chartData = $this->getMonthlyChartData($user, $year);

        // Recent Transactions
        $recentTransactions = $user->transactions()
            ->with('account', 'category')
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'category_name' => $transaction->category->name,
                    'category_color' => $transaction->category->color,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'account_name' => $transaction->account->name,
                    'transaction_date' => $transaction->transaction_date,
                    'description' => $transaction->description,
                ];
            });

        // Recent Notes
        $recentNotes = $user->notes()
            ->orderBy('note_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'content' => substr($note->content, 0, 100),
                    'label' => $note->label,
                    'note_date' => $note->note_date,
                ];
            });

        // Account Summary
        $accountSummary = $user->financialAccounts()
            ->where('is_active', true)
            ->orderBy('current_balance', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'current_balance' => $account->current_balance,
                    'initial_balance' => $account->initial_balance,
                ];
            });

        return Inertia::render('Dashboard', [
            'totalBalance' => $totalBalance,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'netBalance' => $netBalance,
            'chartData' => $chartData,
            'recentTransactions' => $recentTransactions,
            'recentNotes' => $recentNotes,
            'accountSummary' => $accountSummary,
            'selectedYear' => $year,
            'availableYears' => $this->getAvailableYears($user),
        ]);
    }

    private function getMonthlyChartData($user, $year)
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

            $incomeData[] = (int) $income;
            $expenseData[] = (int) $expense;
        }

        return [
            'months' => $months,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }

    private function getAvailableYears($user)
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

        return $years->toArray();
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
