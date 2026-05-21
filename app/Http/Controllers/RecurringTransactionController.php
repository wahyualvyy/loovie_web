<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RecurringTransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $recurringTransactions = RecurringTransaction::query()
            ->where('user_id', $user->id)
            ->with(['account', 'category'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('account', function ($accountQuery) use ($search) {
                            $accountQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('frequency'), function ($query) use ($request) {
                $query->where('frequency', $request->frequency);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                }

                if ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderByDesc('is_active')
            ->orderBy('next_date')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total' => RecurringTransaction::where('user_id', $user->id)->count(),
            'active' => RecurringTransaction::where('user_id', $user->id)->where('is_active', true)->count(),
            'inactive' => RecurringTransaction::where('user_id', $user->id)->where('is_active', false)->count(),
            'income_total' => RecurringTransaction::where('user_id', $user->id)->where('type', 'income')->where('is_active', true)->sum('amount'),
            'expense_total' => RecurringTransaction::where('user_id', $user->id)->where('type', 'expense')->where('is_active', true)->sum('amount'),
        ];

        return Inertia::render('RecurringTransactions/Index', [
            'recurringTransactions' => $recurringTransactions,
            'summary' => $summary,
            'filters' => $request->only([
                'search',
                'type',
                'frequency',
                'status',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('RecurringTransactions/Create', [
            'accounts' => $this->getAccounts(),
            'categories' => $this->getCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRecurringTransaction($request);

        $userId = Auth::id();

        $account = FinancialAccount::query()
            ->where('user_id', $userId)
            ->where('id', $validated['financial_account_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $category = Category::query()
            ->where('user_id', $userId)
            ->where('id', $validated['category_id'])
            ->where('type', $validated['type'])
            ->firstOrFail();

        RecurringTransaction::create([
            'user_id' => $userId,
            'financial_account_id' => $account->id,
            'category_id' => $category->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'next_date' => $validated['next_date'] ?? $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('recurring-transactions.index')
            ->with('success', 'Transaksi berulang berhasil dibuat.');
    }

    public function edit(RecurringTransaction $recurringTransaction)
    {
        $this->authorizeRecurringTransaction($recurringTransaction);

        $recurringTransaction->load(['account', 'category']);

        return Inertia::render('RecurringTransactions/Edit', [
            'recurringTransaction' => $recurringTransaction,
            'accounts' => $this->getAccounts(),
            'categories' => $this->getCategories(),
        ]);
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        $this->authorizeRecurringTransaction($recurringTransaction);

        $validated = $this->validateRecurringTransaction($request);

        $userId = Auth::id();

        $account = FinancialAccount::query()
            ->where('user_id', $userId)
            ->where('id', $validated['financial_account_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $category = Category::query()
            ->where('user_id', $userId)
            ->where('id', $validated['category_id'])
            ->where('type', $validated['type'])
            ->firstOrFail();

        $recurringTransaction->update([
            'financial_account_id' => $account->id,
            'category_id' => $category->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'next_date' => $validated['next_date'] ?? $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('recurring-transactions.index')
            ->with('success', 'Transaksi berulang berhasil diperbarui.');
    }

    public function destroy(RecurringTransaction $recurringTransaction)
    {
        $this->authorizeRecurringTransaction($recurringTransaction);

        $recurringTransaction->delete();

        return back()->with('success', 'Transaksi berulang berhasil dihapus.');
    }

    public function generate(RecurringTransaction $recurringTransaction)
    {
        $this->authorizeRecurringTransaction($recurringTransaction);

        if (! $recurringTransaction->is_active) {
            return back()->with('error', 'Transaksi berulang ini sedang nonaktif.');
        }

        if ($recurringTransaction->end_date && Carbon::parse($recurringTransaction->next_date)->gt(Carbon::parse($recurringTransaction->end_date))) {
            return back()->with('error', 'Transaksi berulang ini sudah melewati tanggal akhir.');
        }

        try {
            DB::transaction(function () use ($recurringTransaction) {
                $lockedRecurring = RecurringTransaction::query()
                    ->where('id', $recurringTransaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $account = FinancialAccount::query()
                    ->where('id', $lockedRecurring->financial_account_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedRecurring->type === 'expense' && (float) $account->current_balance < (float) $lockedRecurring->amount) {
                    throw new \RuntimeException('Saldo akun tidak mencukupi untuk membuat transaksi ini.');
                }

                Transaction::create([
                    'user_id' => $lockedRecurring->user_id,
                    'financial_account_id' => $lockedRecurring->financial_account_id,
                    'category_id' => $lockedRecurring->category_id,
                    'type' => $lockedRecurring->type,
                    'amount' => $lockedRecurring->amount,
                    'transaction_date' => $lockedRecurring->next_date,
                    'description' => $lockedRecurring->description
                        ? '[Auto] ' . $lockedRecurring->title . ' - ' . $lockedRecurring->description
                        : '[Auto] ' . $lockedRecurring->title,
                ]);

                if ($lockedRecurring->type === 'income') {
                    $account->increment('current_balance', $lockedRecurring->amount);
                } else {
                    $account->decrement('current_balance', $lockedRecurring->amount);
                }

                $nextDate = $lockedRecurring->calculateNextDate(Carbon::parse($lockedRecurring->next_date));

                $isActive = true;

                if ($lockedRecurring->end_date && $nextDate->gt(Carbon::parse($lockedRecurring->end_date))) {
                    $isActive = false;
                }

                $lockedRecurring->update([
                    'next_date' => $nextDate->toDateString(),
                    'is_active' => $isActive,
                    'last_generated_at' => now(),
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Transaksi berhasil dibuat dari transaksi berulang.');
    }

    public function generateDue()
    {
        $userId = Auth::id();

        $dueTransactions = RecurringTransaction::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereDate('next_date', '<=', now()->toDateString())
            ->orderBy('next_date')
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($dueTransactions as $recurringTransaction) {
            try {
                DB::transaction(function () use ($recurringTransaction) {
                    $lockedRecurring = RecurringTransaction::query()
                        ->where('id', $recurringTransaction->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $account = FinancialAccount::query()
                        ->where('id', $lockedRecurring->financial_account_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedRecurring->type === 'expense' && (float) $account->current_balance < (float) $lockedRecurring->amount) {
                        throw new \RuntimeException('Saldo akun tidak mencukupi.');
                    }

                    Transaction::create([
                        'user_id' => $lockedRecurring->user_id,
                        'financial_account_id' => $lockedRecurring->financial_account_id,
                        'category_id' => $lockedRecurring->category_id,
                        'type' => $lockedRecurring->type,
                        'amount' => $lockedRecurring->amount,
                        'transaction_date' => $lockedRecurring->next_date,
                        'description' => $lockedRecurring->description
                            ? '[Auto] ' . $lockedRecurring->title . ' - ' . $lockedRecurring->description
                            : '[Auto] ' . $lockedRecurring->title,
                    ]);

                    if ($lockedRecurring->type === 'income') {
                        $account->increment('current_balance', $lockedRecurring->amount);
                    } else {
                        $account->decrement('current_balance', $lockedRecurring->amount);
                    }

                    $nextDate = $lockedRecurring->calculateNextDate(Carbon::parse($lockedRecurring->next_date));

                    $isActive = true;

                    if ($lockedRecurring->end_date && $nextDate->gt(Carbon::parse($lockedRecurring->end_date))) {
                        $isActive = false;
                    }

                    $lockedRecurring->update([
                        'next_date' => $nextDate->toDateString(),
                        'is_active' => $isActive,
                        'last_generated_at' => now(),
                    ]);
                });

                $successCount++;
            } catch (\Throwable $exception) {
                $failedCount++;
            }
        }

        return back()->with(
            'success',
            "Generate selesai. Berhasil: {$successCount}, gagal: {$failedCount}."
        );
    }

    private function validateRecurringTransaction(Request $request): array
    {
        return $request->validate([
            'financial_account_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'numeric', 'min:1'],
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'start_date' => ['required', 'date'],
            'next_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);
    }

    private function authorizeRecurringTransaction(RecurringTransaction $recurringTransaction): void
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }

    private function getAccounts()
    {
        return Auth::user()
            ->financialAccounts()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'type',
                'current_balance',
            ]);
    }

    private function getCategories()
    {
        return Auth::user()
            ->categories()
            ->orderBy('type')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'type',
                'color',
                'icon',
            ]);
    }
}
