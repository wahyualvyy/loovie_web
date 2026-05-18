<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'financial_account_id', 'category_id', 'type', 'amount', 'transaction_date', 'description', 'attachment'])]
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the financial account for this transaction.
     */
    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    /**
     * Get the category for this transaction.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
