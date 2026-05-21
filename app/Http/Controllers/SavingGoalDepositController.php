<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\SavingGoal;
use App\Models\SavingGoalDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingGoalDepositController extends Controller
{
    /**
     * Store a new deposit to saving goal.
     */
    public function store(Request $request, SavingGoal $savingGoal)
    {
        $this->authorizeGoal($savingGoal);

        if ($savingGoal->status === 'cancelled') {
            return back()->withErrors([
                'saving_goal_id' => 'Target yang dibatalkan tidak bisa menerima setoran.',
            ]);
        }

        $validated = $request->validate([
            'financial_account_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:1'],
            'deposit_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();

        $account = FinancialAccount::query()
            ->where('user_id', $userId)
            ->where('id', $validated['financial_account_id'])
            ->where('is_active', true)
            ->firstOrFail();

        if ((float) $account->current_balance < (float) $validated['amount']) {
            return back()->withErrors([
                'amount' => 'Saldo akun tidak mencukupi untuk setoran ini.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $savingGoal, $account, $userId) {
            $lockedGoal = SavingGoal::query()
                ->where('user_id', $userId)
                ->where('id', $savingGoal->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAccount = FinancialAccount::query()
                ->where('user_id', $userId)
                ->where('id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $lockedAccount->current_balance < (float) $validated['amount']) {
                throw new \RuntimeException('Saldo akun tidak mencukupi.');
            }

            SavingGoalDeposit::create([
                'user_id' => $userId,
                'saving_goal_id' => $lockedGoal->id,
                'financial_account_id' => $lockedAccount->id,
                'amount' => $validated['amount'],
                'deposit_date' => $validated['deposit_date'],
                'description' => $validated['description'] ?? null,
            ]);

            $lockedAccount->decrement('current_balance', $validated['amount']);
            $lockedGoal->increment('current_amount', $validated['amount']);

            $lockedGoal->refresh();

            if ((float) $lockedGoal->current_amount >= (float) $lockedGoal->target_amount) {
                $lockedGoal->update([
                    'status' => 'completed',
                ]);
            } elseif ($lockedGoal->status === 'completed') {
                $lockedGoal->update([
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('success', 'Setoran target tabungan berhasil ditambahkan.');
    }

    /**
     * Delete deposit and rollback balance + goal progress.
     */
    public function destroy(SavingGoalDeposit $deposit)
    {
        $this->authorizeDeposit($deposit);

        DB::transaction(function () use ($deposit) {
            $lockedDeposit = SavingGoalDeposit::query()
                ->where('id', $deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            $goal = SavingGoal::query()
                ->where('id', $lockedDeposit->saving_goal_id)
                ->lockForUpdate()
                ->firstOrFail();

            $account = FinancialAccount::query()
                ->where('id', $lockedDeposit->financial_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            /**
             * Batal setor:
             * uang dikembalikan ke akun,
             * nominal terkumpul target dikurangi.
             */
            $account->increment('current_balance', $lockedDeposit->amount);

            $newCurrentAmount = max(
                (float) $goal->current_amount - (float) $lockedDeposit->amount,
                0
            );

            $goal->update([
                'current_amount' => $newCurrentAmount,
                'status' => $newCurrentAmount >= (float) $goal->target_amount
                    ? 'completed'
                    : 'active',
            ]);

            $lockedDeposit->delete();
        });

        return back()->with('success', 'Setoran target berhasil dibatalkan.');
    }

    private function authorizeGoal(SavingGoal $savingGoal): void
    {
        if ($savingGoal->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }

    private function authorizeDeposit(SavingGoalDeposit $deposit): void
    {
        if ($deposit->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
