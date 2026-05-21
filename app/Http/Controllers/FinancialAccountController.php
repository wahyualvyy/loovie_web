<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialAccountRequest;
use App\Http\Requests\UpdateFinancialAccountRequest;
use App\Models\FinancialAccount;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;

class FinancialAccountController extends Controller
{
    public function index(): Response
    {
        $accounts = auth()->user()
            ->financialAccounts()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalBalance = auth()->user()
            ->financialAccounts()
            ->where('is_active', true)
            ->sum('current_balance');

        return Inertia::render('FinancialAccountsIndex', [
            'accounts' => $accounts,
            'totalBalance' => (float) $totalBalance,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CreateFinancialAccount');
    }

    public function store(StoreFinancialAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['current_balance'] = $validated['initial_balance'];

        FinancialAccount::create($validated);

        return Redirect::route('financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil ditambahkan');
    }

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

    public function edit(FinancialAccount $financialAccount): Response
    {
        $this->authorizeUser($financialAccount);

        return Inertia::render('EditFinancialAccount', [
            'account' => $financialAccount,
        ]);
    }

    public function update(UpdateFinancialAccountRequest $request, FinancialAccount $financialAccount): RedirectResponse
    {
        $this->authorizeUser($financialAccount);

        $financialAccount->update($request->validated());

        return Redirect::route('financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil diperbarui');
    }

    public function destroy(FinancialAccount $financialAccount): RedirectResponse
    {
        $this->authorizeUser($financialAccount);

        if ($financialAccount->transactions()->exists()) {
            return Redirect::route('financial-accounts.index')
                ->with('error', 'Tidak dapat menghapus akun yang memiliki transaksi. Hapus transaksi terlebih dahulu.');
        }

        $financialAccount->delete();

        return Redirect::route('financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil dihapus');
    }

    public function exportCsv()
    {
        $accounts = auth()->user()
            ->financialAccounts()
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'financial_accounts_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
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
                    $account->description,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPrint()
    {
        $accounts = auth()->user()
            ->financialAccounts()
            ->orderBy('created_at', 'desc')
            ->get();

        $totalBalance = $accounts->sum('current_balance');

        return view('reports.financial-accounts', compact('accounts', 'totalBalance'));
    }

    private function authorizeUser(FinancialAccount $account): void
    {
        if ((int) $account->user_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
