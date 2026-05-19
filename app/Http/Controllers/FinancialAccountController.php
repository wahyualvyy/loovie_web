<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialAccountRequest;
use App\Http\Requests\UpdateFinancialAccountRequest;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;

class FinancialAccountController extends Controller
{
    /**
     * Display a listing of financial accounts.
     */
    public function index(): Response
    {
        $accounts = auth()->user()
            ->financialAccounts()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalBalance = auth()->user()->financialAccounts()->sum('current_balance');

        return Inertia::render('FinancialAccountsIndex', [
            'accounts' => $accounts,
            'totalBalance' => (float) $totalBalance,
        ]);
    }

    /**
     * Show the form for creating a new financial account.
     */
    public function create(): Response
    {
        return Inertia::render('CreateFinancialAccount');
    }

    /**
     * Store a newly created financial account in storage.
     */
    public function store(StoreFinancialAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['current_balance'] = $validated['initial_balance'];

        FinancialAccount::create($validated);

        return Redirect::route('financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil ditambahkan');
    }

    /**
     * Display the specified financial account.
     */
    public function show(FinancialAccount $financialAccount): Response
    {
        $this->authorizeUser($financialAccount);

        $transactions = $financialAccount->transactions()
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->paginate(5);

        return Inertia::render('ShowFinancialAccount', [
            'account' => $financialAccount,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for editing the specified financial account.
     */
    public function edit(FinancialAccount $financialAccount): Response
    {
        $this->authorizeUser($financialAccount);

        return Inertia::render('EditFinancialAccount', [
            'account' => $financialAccount,
        ]);
    }

    /**
     * Update the specified financial account in storage.
     */
    public function update(UpdateFinancialAccountRequest $request, FinancialAccount $financialAccount): RedirectResponse
    {
        $this->authorizeUser($financialAccount);

        $validated = $request->validated();

        $financialAccount->update($validated);

        return Redirect::route('financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil diperbarui');
    }

    /**
     * Remove the specified financial account from storage.
     */
    public function destroy(FinancialAccount $financialAccount): RedirectResponse
    {
        $this->authorizeUser($financialAccount);

        // Check if account has transactions
        if ($financialAccount->transactions()->exists()) {
            return Redirect::route('financial-accounts.index')
                ->with('error', 'Tidak dapat menghapus akun yang memiliki transaksi. Hapus transaksi terlebih dahulu.');
        }

        $financialAccount->delete();

        return Redirect::route('financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil dihapus');
    }

    /**
     * Check if user owns the account.
     */
    private function authorizeUser(FinancialAccount $account): void
    {
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Export all financial accounts to CSV.
     */
    public function exportCsv()
    {
        $accounts = auth()->user()->financialAccounts()->orderBy('created_at', 'desc')->get();

        $filename = "financial_accounts_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($accounts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Account Name', 'Type', 'Initial Balance', 'Current Balance', 'Status', 'Description']);

            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->name,
                    $account->type,
                    $account->initial_balance,
                    $account->current_balance,
                    $account->is_active ? 'Active' : 'Inactive',
                    $account->description
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print all financial accounts.
     */
    public function exportPrint()
    {
        $accounts = auth()->user()->financialAccounts()->orderBy('created_at', 'desc')->get();
        $totalBalance = $accounts->sum('current_balance');

        return view('reports.financial-accounts', compact('accounts', 'totalBalance'));
    }
}
