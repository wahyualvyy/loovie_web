<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'name', 'type', 'initial_balance', 'current_balance', 'description', 'is_active'])]
class FinancialAccount extends Model
{
    use HasFactory;

    protected $table = 'financial_accounts';

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this account.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calculate and update the current balance based on transactions.
     */
    public function calculateBalance(): void
    {
        $balance = $this->initial_balance;

        $transactions = $this->transactions()->get();

        foreach ($transactions as $transaction) {
            if ($transaction->type === 'income') {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }
        }

        $this->update(['current_balance' => $balance]);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }
}
