<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingGoalDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'saving_goal_id',
        'financial_account_id',
        'amount',
        'deposit_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'deposit_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savingGoal()
    {
        return $this->belongsTo(SavingGoal::class);
    }

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class);
    }
}
