<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportController extends Controller
{
    /**
     * Export transactions to CSV
     */
    public function exportCSV(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $filters = $request->only(['account_id', 'category_id', 'type', 'date_from', 'date_to', 'month']);
        $transactions = $this->getFilteredTransactions($user, $filters);

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Header row with BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Date',
                'Account',
                'Category',
                'Type',
                'Amount',
                'Description',
            ]);

            // Data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->account->name,
                    $transaction->category->name,
                    ucfirst($transaction->type),
                    $transaction->amount,
                    $transaction->description ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'transactions_' . now()->format('Y-m-d_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
        ]);
    }

    /**
     * Show print-friendly transaction report
     */
    public function printReport(Request $request)
    {
        $user = Auth::user();

        $filters = $request->only(['account_id', 'category_id', 'type', 'date_from', 'date_to', 'month']);
        $transactions = $this->getFilteredTransactions($user, $filters);

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpense;

        return view('transactions.print', compact('transactions', 'totalIncome', 'totalExpense', 'net'));
    }

    /**
     * Get filtered transactions
     */
    protected function getFilteredTransactions($user, array $filters = [])
    {
        $query = Transaction::where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if (!empty($filters['account_id'])) {
            $query->where('financial_account_id', $filters['account_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('transaction_date', $filters['month']);
        }

        return $query->get();
    }
}
