<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->format('Y-m'));

        $report = $this->buildMonthlyReport($user, $month);

        return Inertia::render('Reports/Index', [
            'month' => $month,
            'report' => $report,
        ]);
    }

    public function exportCSV(Request $request): StreamedResponse
    {
        $user = $request->user();
        $month = $request->input('month', now()->format('Y-m'));

        $report = $this->buildMonthlyReport($user, $month);

        $fileName = 'laporan-bulanan-' . $month . '-' . now()->format('His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($report, $user, $month) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $delimiter = ';';

            fputcsv($file, ['LAPORAN BULANAN'], $delimiter);
            fputcsv($file, ['Nama User', $user->name], $delimiter);
            fputcsv($file, ['Email', $user->email], $delimiter);
            fputcsv($file, ['Bulan', $month], $delimiter);
            fputcsv($file, ['Tanggal Export', now()->format('d-m-Y H:i:s')], $delimiter);
            fputcsv($file, [], $delimiter);

            fputcsv($file, ['RINGKASAN'], $delimiter);
            fputcsv($file, ['Total Pemasukan', $report['summary']['totalIncome']], $delimiter);
            fputcsv($file, ['Total Pengeluaran', $report['summary']['totalExpense']], $delimiter);
            fputcsv($file, ['Saldo Bersih', $report['summary']['netBalance']], $delimiter);
            fputcsv($file, ['Total Transaksi', $report['summary']['transactionCount']], $delimiter);
            fputcsv($file, ['Total Budget', $report['budget']['totalBudget']], $delimiter);
            fputcsv($file, ['Budget Terpakai', $report['budget']['totalUsed']], $delimiter);
            fputcsv($file, ['Sisa Budget', $report['budget']['totalRemaining']], $delimiter);
            fputcsv($file, [], $delimiter);

            fputcsv($file, ['PENGELUARAN PER KATEGORI'], $delimiter);
            fputcsv($file, ['No', 'Kategori', 'Total', 'Jumlah Transaksi'], $delimiter);

            foreach ($report['expenseByCategory'] as $index => $category) {
                fputcsv($file, [
                    $index + 1,
                    $category['name'],
                    $category['total'],
                    $category['count'],
                ], $delimiter);
            }

            fputcsv($file, [], $delimiter);

            fputcsv($file, ['PEMASUKAN PER KATEGORI'], $delimiter);
            fputcsv($file, ['No', 'Kategori', 'Total', 'Jumlah Transaksi'], $delimiter);

            foreach ($report['incomeByCategory'] as $index => $category) {
                fputcsv($file, [
                    $index + 1,
                    $category['name'],
                    $category['total'],
                    $category['count'],
                ], $delimiter);
            }

            fputcsv($file, [], $delimiter);

            fputcsv($file, ['TRANSAKSI TERBESAR'], $delimiter);
            fputcsv($file, ['No', 'Tanggal', 'Kategori', 'Akun', 'Tipe', 'Nominal', 'Deskripsi'], $delimiter);

            foreach ($report['largestTransactions'] as $index => $transaction) {
                fputcsv($file, [
                    $index + 1,
                    $transaction['date'],
                    $transaction['category_name'],
                    $transaction['account_name'],
                    $transaction['type'],
                    $transaction['amount'],
                    $transaction['description'],
                ], $delimiter);
            }

            fclose($file);
        }, $fileName, $headers);
    }

    public function printReport(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->format('Y-m'));

        $report = $this->buildMonthlyReport($user, $month);

        return view('reports.print', compact('user', 'month', 'report'));
    }

    private function buildMonthlyReport($user, string $month): array
    {
        $year = substr($month, 0, 4);
        $monthNumber = substr($month, 5, 2);

        $transactions = $user->transactions()
            ->with(['account', 'category'])
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $monthNumber)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $incomeTransactions = $transactions->where('type', 'income');
        $expenseTransactions = $transactions->where('type', 'expense');

        $totalIncome = (float) $incomeTransactions->sum('amount');
        $totalExpense = (float) $expenseTransactions->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $incomeByCategory = $incomeTransactions
            ->groupBy('category_id')
            ->map(function ($items) {
                $category = $items->first()->category;

                return [
                    'id' => $category->id ?? null,
                    'name' => $category->name ?? '-',
                    'color' => $category->color ?? '#22c55e',
                    'icon' => $category->icon ?? 'CircleDollarSign',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $expenseByCategory = $expenseTransactions
            ->groupBy('category_id')
            ->map(function ($items) {
                $category = $items->first()->category;

                return [
                    'id' => $category->id ?? null,
                    'name' => $category->name ?? '-',
                    'color' => $category->color ?? '#ef4444',
                    'icon' => $category->icon ?? 'CircleDollarSign',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $largestTransactions = $transactions
            ->sortByDesc('amount')
            ->take(8)
            ->values()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => optional($transaction->transaction_date)->format('d-m-Y'),
                    'category_name' => $transaction->category->name ?? '-',
                    'category_color' => $transaction->category->color ?? '#6366f1',
                    'account_name' => $transaction->account->name ?? '-',
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'description' => $transaction->description ?? '-',
                ];
            });

        $budgetItems = $user->budgets()
            ->with('category')
            ->where('month', $month)
            ->get()
            ->map(function ($budget) use ($user, $year, $monthNumber) {
                $usedAmount = $user->transactions()
                    ->where('type', 'expense')
                    ->where('category_id', $budget->category_id)
                    ->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $monthNumber)
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
                    'category_name' => $budget->category->name ?? '-',
                    'category_color' => $budget->category->color ?? '#6366f1',
                    'amount' => $amount,
                    'used_amount' => $used,
                    'remaining_amount' => $remaining,
                    'percentage' => $percentage,
                    'status' => $status,
                ];
            })
            ->sortByDesc('percentage')
            ->values();

        $totalBudget = (float) $budgetItems->sum('amount');
        $totalBudgetUsed = (float) $budgetItems->sum('used_amount');
        $totalBudgetRemaining = $totalBudget - $totalBudgetUsed;
        $totalBudgetPercentage = $totalBudget > 0
            ? round(($totalBudgetUsed / $totalBudget) * 100)
            : 0;

        $monthLabel = Carbon::createFromFormat('Y-m', $month)
            ->locale('id')
            ->translatedFormat('F Y');

        return [
            'monthLabel' => $monthLabel,
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netBalance' => $netBalance,
                'transactionCount' => $transactions->count(),
                'incomeCount' => $incomeTransactions->count(),
                'expenseCount' => $expenseTransactions->count(),
            ],
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'largestTransactions' => $largestTransactions,
            'budget' => [
                'totalBudget' => $totalBudget,
                'totalUsed' => $totalBudgetUsed,
                'totalRemaining' => $totalBudgetRemaining,
                'totalPercentage' => $totalBudgetPercentage,
                'items' => $budgetItems,
                'warnings' => $budgetItems
                    ->filter(fn ($item) => in_array($item['status'], ['warning', 'over']))
                    ->values(),
            ],
        ];
    }
}
