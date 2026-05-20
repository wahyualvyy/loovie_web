<?php

namespace App\Http\Controllers;

use App\Models\AccountTransfer;
use App\Models\FinancialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccountTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $transfers = AccountTransfer::query()
            ->where('user_id', $user->id)
            ->with(['fromAccount', 'toAccount'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhereHas('fromAccount', function ($accountQuery) use ($search) {
                            $accountQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('toAccount', function ($accountQuery) use ($search) {
                            $accountQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('transfer_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('transfer_date', '<=', $request->date_to);
            })
            ->when($request->filled('month'), function ($query) use ($request) {
                [$year, $month] = explode('-', $request->month);

                $query->whereYear('transfer_date', $year)
                    ->whereMonth('transfer_date', $month);
            })
            ->latest('transfer_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Transfers/Index', [
            'transfers' => $transfers,
            'filters' => $request->only([
                'search',
                'date_from',
                'date_to',
                'month',
            ]),
        ]);
    }

    public function create()
    {
        $accounts = FinancialAccount::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'type',
                'current_balance',
            ]);

        return Inertia::render('Transfers/Create', [
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => ['required', 'integer', 'different:to_account_id'],
            'to_account_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();

        $fromAccount = FinancialAccount::where('user_id', $userId)
            ->where('id', $validated['from_account_id'])
            ->firstOrFail();

        $toAccount = FinancialAccount::where('user_id', $userId)
            ->where('id', $validated['to_account_id'])
            ->firstOrFail();

        if ((float) $fromAccount->current_balance < (float) $validated['amount']) {
            return back()->withErrors([
                'amount' => 'Saldo akun sumber tidak mencukupi.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $userId, $fromAccount, $toAccount) {
            AccountTransfer::create([
                'user_id' => $userId,
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $validated['amount'],
                'transfer_date' => $validated['transfer_date'],
                'description' => $validated['description'] ?? null,
            ]);

            $fromAccount->decrement('current_balance', $validated['amount']);
            $toAccount->increment('current_balance', $validated['amount']);
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Transfer antar akun berhasil dibuat.');
    }

    public function edit(AccountTransfer $transfer)
    {
        $this->authorizeTransfer($transfer);

        $accounts = FinancialAccount::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'type',
                'current_balance',
            ]);

        $transfer->load(['fromAccount', 'toAccount']);

        return Inertia::render('Transfers/Edit', [
            'transfer' => $transfer,
            'accounts' => $accounts,
        ]);
    }

    public function update(Request $request, AccountTransfer $transfer)
    {
        $this->authorizeTransfer($transfer);

        $validated = $request->validate([
            'from_account_id' => ['required', 'integer', 'different:to_account_id'],
            'to_account_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();

        $newFromAccount = FinancialAccount::where('user_id', $userId)
            ->where('id', $validated['from_account_id'])
            ->firstOrFail();

        $newToAccount = FinancialAccount::where('user_id', $userId)
            ->where('id', $validated['to_account_id'])
            ->firstOrFail();

        DB::transaction(function () use ($transfer, $validated, $newFromAccount, $newToAccount) {
            $oldFromAccount = FinancialAccount::where('id', $transfer->from_account_id)->lockForUpdate()->firstOrFail();
            $oldToAccount = FinancialAccount::where('id', $transfer->to_account_id)->lockForUpdate()->firstOrFail();

            /**
             * Balikkan efek transfer lama.
             */
            $oldFromAccount->increment('current_balance', $transfer->amount);
            $oldToAccount->decrement('current_balance', $transfer->amount);

            $newFromAccountLocked = FinancialAccount::where('id', $newFromAccount->id)->lockForUpdate()->firstOrFail();
            $newToAccountLocked = FinancialAccount::where('id', $newToAccount->id)->lockForUpdate()->firstOrFail();

            if ((float) $newFromAccountLocked->current_balance < (float) $validated['amount']) {
                throw new \RuntimeException('Saldo akun sumber tidak mencukupi.');
            }

            /**
             * Terapkan efek transfer baru.
             */
            $newFromAccountLocked->decrement('current_balance', $validated['amount']);
            $newToAccountLocked->increment('current_balance', $validated['amount']);

            $transfer->update([
                'from_account_id' => $newFromAccountLocked->id,
                'to_account_id' => $newToAccountLocked->id,
                'amount' => $validated['amount'],
                'transfer_date' => $validated['transfer_date'],
                'description' => $validated['description'] ?? null,
            ]);
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Transfer antar akun berhasil diperbarui.');
    }

    public function destroy(AccountTransfer $transfer)
    {
        $this->authorizeTransfer($transfer);

        DB::transaction(function () use ($transfer) {
            $fromAccount = FinancialAccount::where('id', $transfer->from_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $toAccount = FinancialAccount::where('id', $transfer->to_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            /**
             * Hapus transfer = kembalikan saldo seperti sebelum transfer.
             */
            $fromAccount->increment('current_balance', $transfer->amount);
            $toAccount->decrement('current_balance', $transfer->amount);

            $transfer->delete();
        });

        return back()->with('success', 'Transfer antar akun berhasil dihapus.');
    }

    private function authorizeTransfer(AccountTransfer $transfer): void
    {
        if ($transfer->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
