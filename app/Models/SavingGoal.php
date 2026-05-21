<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'target_amount',
        'current_amount',
        'target_date',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'target_date' => 'date',
        ];
    }

    protected $appends = [
        'progress_percentage',
        'remaining_amount',
        'status_label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        $targetAmount = (float) $this->target_amount;
        $currentAmount = (float) $this->current_amount;

        if ($targetAmount <= 0) {
            return 0;
        }

        return round(min(($currentAmount / $targetAmount) * 100, 100), 2);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max((float) $this->target_amount - (float) $this->current_amount, 0);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Aktif',
        };
    }
}
