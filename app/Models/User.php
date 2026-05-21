<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo_path',
        'role',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    public function financialAccounts()
    {
        return $this->hasMany(FinancialAccount::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function accountTransfers()
    {
        return $this->hasMany(AccountTransfer::class);
    }

    public function savingGoals()
    {
        return $this->hasMany(SavingGoal::class);
    }

    public function savingGoalDeposits()
    {
        return $this->hasMany(SavingGoalDeposit::class);
    }

    public function recurringTransactions()
    {
        return $this->hasMany(RecurringTransaction::class);
    }
}
