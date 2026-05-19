<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\FinancialAccount;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions with filters.
     */
    public function index(): Response
    {
        $query = auth()->user()->transactions()
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters from request
        if (request('search')) {
            $query->where('description', 'like', '%' . request('search') . '%');
        }

        if (request('account_id')) {
            $query->where('financial_account_id', request('account_id'));
        }

        if (request('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        if (request('type')) {
            $query->where('type', request('type'));
        }

        if (request('date_from')) {
            $query->where('transaction_date', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->where('transaction_date', '<=', request('date_to'));
        }

        if (request('month')) {
            $month = request('month'); // format: YYYY-MM
            $query->whereYear('transaction_date', substr($month, 0, 4))
                ->whereMonth('transaction_date', substr($month, 5, 2));
        }

        $transactions = $query->paginate(15);

        // Get data for filter dropdowns
        $accounts = auth()->user()->financialAccounts()->orderBy('name')->get();
        $categories = auth()->user()->categories()->orderBy('name')->get();

        return Inertia::render('TransactionsIndex', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'filters' => [
                'search' => request('search'),
                'account_id' => request('account_id'),
                'category_id' => request('category_id'),
                'type' => request('type'),
                'date_from' => request('date_from'),
                'date_to' => request('date_to'),
                'month' => request('month'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(): Response
    {
        $accounts = auth()->user()->financialAccounts()->where('is_active', true)->orderBy('name')->get();
        $categories = auth()->user()->categories()->orderBy('type')->orderBy('name')->get();

        return Inertia::render('CreateTransaction', [
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('transactions', 'public');
            $validated['attachment'] = $path;
        }

        // Create transaction
        $transaction = Transaction::create($validated);

        // Update account balance
        $transaction->account->calculateBalance();

        return Redirect::route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction): Response
    {
        $this->authorizeUser($transaction);

        $accounts = auth()->user()->financialAccounts()->where('is_active', true)->orderBy('name')->get();
        $categories = auth()->user()->categories()->orderBy('type')->orderBy('name')->get();

        return Inertia::render('EditTransaction', [
            'transaction' => $transaction,
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeUser($transaction);

        $validated = $request->validated();

        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($transaction->attachment) {
                Storage::disk('public')->delete($transaction->attachment);
            }

            $file = $request->file('attachment');
            $path = $file->store('transactions', 'public');
            $validated['attachment'] = $path;
        }

        // If account changed, recalculate both accounts
        $oldAccountId = $transaction->financial_account_id;
        $newAccountId = $validated['financial_account_id'];

        $transaction->update($validated);

        // Recalculate balances for affected accounts
        if ($oldAccountId !== $newAccountId) {
            FinancialAccount::find($oldAccountId)->calculateBalance();
            FinancialAccount::find($newAccountId)->calculateBalance();
        } else {
            $transaction->account->calculateBalance();
        }

        return Redirect::route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeUser($transaction);

        $accountId = $transaction->financial_account_id;

        // Delete attachment if exists
        if ($transaction->attachment) {
            Storage::disk('public')->delete($transaction->attachment);
        }

        $transaction->delete();

        // Recalculate balance
        FinancialAccount::find($accountId)->calculateBalance();

        return Redirect::route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus');
    }

    /**
     * Check if user owns the transaction.
     */
    private function authorizeUser(Transaction $transaction): void
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
