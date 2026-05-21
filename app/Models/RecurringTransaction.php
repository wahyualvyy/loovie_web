<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'financial_account_id',
        'category_id',
        'title',
        'type',
        'amount',
        'frequency',
        'start_date',
        'next_date',
        'end_date',
        'description',
        'is_active',
        'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'next_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'last_generated_at' => 'datetime',
        ];
    }

    protected $appends = [
        'frequency_label',
        'type_label',
        'status_label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => 'Tidak diketahui',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'income' => 'Pemasukan',
            'expense' => 'Pengeluaran',
            default => 'Tidak diketahui',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function calculateNextDate(?Carbon $fromDate = null): Carbon
    {
        $date = $fromDate
            ? $fromDate->copy()
            : Carbon::parse($this->next_date);

        return match ($this->frequency) {
            'daily' => $date->addDay(),
            'weekly' => $date->addWeek(),
            'monthly' => $date->addMonthNoOverflow(),
            'yearly' => $date->addYearNoOverflow(),
            default => $date->addMonthNoOverflow(),
        };
    }
}
