<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions with filters.
     */
    public function index(): Response
    {
        $user = auth()->user();

        $query = $user->transactions()
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if (request('search')) {
            $search = request('search');

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
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
            $query->whereDate('transaction_date', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('transaction_date', '<=', request('date_to'));
        }

        if (request('month')) {
            $month = request('month');

            if (strlen($month) === 7) {
                $query->whereYear('transaction_date', substr($month, 0, 4))
                    ->whereMonth('transaction_date', substr($month, 5, 2));
            }
        }

        $transactions = $query
            ->paginate(15)
            ->withQueryString();

        $accounts = $user->financialAccounts()
            ->orderBy('name')
            ->get();

        $categories = $user->categories()
            ->orderBy('name')
            ->get();

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
        $user = auth()->user();

        $accounts = $user->financialAccounts()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = $user->categories()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

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
        $user = auth()->user();
        $validated = $request->validated();

        $account = $user->financialAccounts()
            ->where('id', $validated['financial_account_id'])
            ->firstOrFail();

        $user->categories()
            ->where('id', $validated['category_id'])
            ->firstOrFail();

        $validated['user_id'] = $user->id;

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request
                ->file('attachment')
                ->store('transactions', 'public');
        }

        Transaction::create($validated);
        $account->calculateBalance();

        return Redirect::route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction): Response
    {
        $this->authorizeUser($transaction);

        $user = auth()->user();

        $accounts = $user->financialAccounts()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = $user->categories()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return Inertia::render('EditTransaction', [
            'transaction' => $transaction->load(['account', 'category']),
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

        $user = auth()->user();
        $validated = $request->validated();

        $oldAccount = $user->financialAccounts()
            ->where('id', $transaction->financial_account_id)
            ->firstOrFail();

        $newAccount = $user->financialAccounts()
            ->where('id', $validated['financial_account_id'])
            ->firstOrFail();

        $user->categories()
            ->where('id', $validated['category_id'])
            ->firstOrFail();

        if ($request->hasFile('attachment')) {
            if ($transaction->attachment) {
                Storage::disk('public')->delete($transaction->attachment);
            }

            $validated['attachment'] = $request
                ->file('attachment')
                ->store('transactions', 'public');
        }

        $oldAccountId = $transaction->financial_account_id;
        $newAccountId = $validated['financial_account_id'];

        $transaction->update($validated);

        if ((int) $oldAccountId !== (int) $newAccountId) {
            $oldAccount->calculateBalance();
            $newAccount->calculateBalance();
        } else {
            $newAccount->calculateBalance();
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

        $account = auth()->user()
            ->financialAccounts()
            ->where('id', $transaction->financial_account_id)
            ->firstOrFail();

        if ($transaction->attachment) {
            Storage::disk('public')->delete($transaction->attachment);
        }

        $transaction->delete();

        $account->calculateBalance();

        return Redirect::route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus');
    }

    /**
     * Check if user owns the transaction.
     */
    private function authorizeUser(Transaction $transaction): void
    {
        if ((int) $transaction->user_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
