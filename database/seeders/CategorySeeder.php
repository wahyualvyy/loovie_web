<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $incomeCategories = [
            ['name' => 'Salary', 'icon' => 'Briefcase', 'color' => '#10b981'],
            ['name' => 'Freelance', 'icon' => 'Code', 'color' => '#3b82f6'],
            ['name' => 'Investment', 'icon' => 'TrendingUp', 'color' => '#f59e0b'],
            ['name' => 'Bonus', 'icon' => 'Gift', 'color' => '#ec4899'],
            ['name' => 'Other Income', 'icon' => 'Plus', 'color' => '#6366f1'],
        ];

        $expenseCategories = [
            ['name' => 'Food & Dining', 'icon' => 'UtensilsCrossed', 'color' => '#ef4444'],
            ['name' => 'Transportation', 'icon' => 'Car', 'color' => '#f97316'],
            ['name' => 'Shopping', 'icon' => 'ShoppingCart', 'color' => '#ec4899'],
            ['name' => 'Entertainment', 'icon' => 'Popcorn', 'color' => '#8b5cf6'],
            ['name' => 'Utilities', 'icon' => 'Zap', 'color' => '#eab308'],
            ['name' => 'Healthcare', 'icon' => 'Heart', 'color' => '#ef4444'],
            ['name' => 'Education', 'icon' => 'BookOpen', 'color' => '#3b82f6'],
            ['name' => 'Insurance', 'icon' => 'Shield', 'color' => '#06b6d4'],
            ['name' => 'Rent', 'icon' => 'Home', 'color' => '#8b5cf6'],
            ['name' => 'Other Expense', 'icon' => 'Minus', 'color' => '#6366f1'],
        ];

        // Get the test user if it exists, or use the first user
        $user = User::where('email', 'test@example.com')->first() ?? User::first();

        if ($user) {
            // Create income categories
            foreach ($incomeCategories as $category) {
                Category::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $category['name'],
                        'type' => 'income',
                    ],
                    [
                        'icon' => $category['icon'],
                        'color' => $category['color'],
                    ]
                );
            }

            // Create expense categories
            foreach ($expenseCategories as $category) {
                Category::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $category['name'],
                        'type' => 'expense',
                    ],
                    [
                        'icon' => $category['icon'],
                        'color' => $category['color'],
                    ]
                );
            }
        }
    }
}
