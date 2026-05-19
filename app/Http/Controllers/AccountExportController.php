<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountExportController extends Controller
{
    /**
     * Export financial accounts to CSV
     */
    public function exportCSV(): StreamedResponse
    {
        $user = Auth::user();
        $accounts = $user->financialAccounts()->orderBy('name')->get();

        $callback = function () use ($accounts) {
            $file = fopen('php://output', 'w');

            // Header row with BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Account Name',
                'Type',
                'Initial Balance',
                'Current Balance',
                'Status',
            ]);

            // Data rows
            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->name,
                    $account->type,
                    $account->initial_balance,
                    $account->current_balance,
                    $account->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'accounts_' . now()->format('Y-m-d_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="accounts.csv"',
        ]);
    }

    /**
     * Show print-friendly account summary
     */
    public function printReport()
    {
        $user = Auth::user();
        $accounts = $user->financialAccounts()->orderBy('name')->get();

        $totalBalance = $accounts->sum('current_balance');
        $totalInitialBalance = $accounts->sum('initial_balance');

        return view('accounts.print', compact('accounts', 'totalBalance', 'totalInitialBalance'));
    }
}
