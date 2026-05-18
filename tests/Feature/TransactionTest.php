<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FinancialAccount $account;
    protected Category $incomeCategory;
    protected Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->account = FinancialAccount::factory()
            ->for($this->user)
            ->create(['current_balance' => 0]);
        
        $this->incomeCategory = Category::factory()
            ->for($this->user)
            ->create(['type' => 'income']);
        
        $this->expenseCategory = Category::factory()
            ->for($this->user)
            ->create(['type' => 'expense']);
    }

    /**
     * Test user can view transactions index page
     */
    public function test_user_can_view_transactions_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('transactions.index'));

        $response->assertSuccessful();
    }

    /**
     * Test user can create income transaction
     */
    public function test_user_can_create_income_transaction(): void
    {
        $data = [
            'financial_account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'type' => 'income',
            'amount' => 500000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Salary',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 500000,
        ]);
    }

    /**
     * Test user can create expense transaction
     */
    public function test_user_can_create_expense_transaction(): void
    {
        $data = [
            'financial_account_id' => $this->account->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Food',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 100000,
        ]);
    }

    /**
     * Test income transaction increases account balance
     */
    public function test_income_transaction_increases_account_balance(): void
    {
        $initialBalance = $this->account->current_balance;

        $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'financial_account_id' => $this->account->id,
                'category_id' => $this->incomeCategory->id,
                'type' => 'income',
                'amount' => 500000,
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Salary',
            ]);

        $this->account->refresh();
        $this->assertEquals($initialBalance + 500000, $this->account->current_balance);
    }

    /**
     * Test expense transaction decreases account balance
     */
    public function test_expense_transaction_decreases_account_balance(): void
    {
        // First add some balance
        $this->account->update(['current_balance' => 1000000]);

        $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'financial_account_id' => $this->account->id,
                'category_id' => $this->expenseCategory->id,
                'type' => 'expense',
                'amount' => 300000,
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Shopping',
            ]);

        $this->account->refresh();
        $this->assertEquals(700000, $this->account->current_balance);
    }

    /**
     * Test updating transaction recalculates balance
     */
    public function test_updating_transaction_recalculates_balance(): void
    {
        $this->account->update(['current_balance' => 1000000]);

        $transaction = Transaction::factory()
            ->for($this->user)
            ->for($this->account)
            ->for($this->expenseCategory)
            ->create(['type' => 'expense', 'amount' => 100000]);

        // Account should now be 900000
        $this->account->refresh();
        $this->assertEquals(900000, $this->account->current_balance);

        // Update transaction amount
        $this->actingAs($this->user)
            ->put(route('transactions.update', $transaction), [
                'financial_account_id' => $this->account->id,
                'category_id' => $this->expenseCategory->id,
                'type' => 'expense',
                'amount' => 200000,
                'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
                'description' => $transaction->description,
            ]);

        // Account should now be 800000 (1000000 - 200000)
        $this->account->refresh();
        $this->assertEquals(800000, $this->account->current_balance);
    }

    /**
     * Test deleting transaction recalculates balance
     */
    public function test_deleting_transaction_recalculates_balance(): void
    {
        $this->account->update(['current_balance' => 1000000]);

        $transaction = Transaction::factory()
            ->for($this->user)
            ->for($this->account)
            ->for($this->expenseCategory)
            ->create(['type' => 'expense', 'amount' => 200000]);

        $this->account->refresh();
        $this->assertEquals(800000, $this->account->current_balance);

        // Delete transaction
        $this->actingAs($this->user)
            ->delete(route('transactions.destroy', $transaction));

        // Balance should be restored
        $this->account->refresh();
        $this->assertEquals(1000000, $this->account->current_balance);
    }

    /**
     * Test updating transaction account moves balance correctly
     */
    public function test_updating_transaction_account_moves_balance(): void
    {
        $account2 = FinancialAccount::factory()
            ->for($this->user)
            ->create(['current_balance' => 0]);

        $this->account->update(['current_balance' => 1000000]);

        $transaction = Transaction::factory()
            ->for($this->user)
            ->for($this->account)
            ->for($this->expenseCategory)
            ->create(['type' => 'expense', 'amount' => 100000]);

        $this->account->refresh();
        $this->assertEquals(900000, $this->account->current_balance);

        // Move transaction to another account
        $this->actingAs($this->user)
            ->put(route('transactions.update', $transaction), [
                'financial_account_id' => $account2->id,
                'category_id' => $this->expenseCategory->id,
                'type' => 'expense',
                'amount' => 100000,
                'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
                'description' => $transaction->description,
            ]);

        // Account 1 should be restored
        $this->account->refresh();
        $this->assertEquals(1000000, $this->account->current_balance);

        // Account 2 should decrease
        $account2->refresh();
        $this->assertEquals(-100000, $account2->current_balance);
    }

    /**
     * Test transaction amount is required
     */
    public function test_transaction_amount_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'financial_account_id' => $this->account->id,
                'category_id' => $this->incomeCategory->id,
                'type' => 'income',
                'amount' => '',
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Salary',
            ]);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test transaction amount must be numeric
     */
    public function test_transaction_amount_must_be_numeric(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'financial_account_id' => $this->account->id,
                'category_id' => $this->incomeCategory->id,
                'type' => 'income',
                'amount' => 'invalid',
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Salary',
            ]);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test transaction amount must be greater than 0
     */
    public function test_transaction_amount_must_be_greater_than_zero(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'financial_account_id' => $this->account->id,
                'category_id' => $this->incomeCategory->id,
                'type' => 'income',
                'amount' => 0,
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Salary',
            ]);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test user cannot view other users' transactions
     */
    public function test_user_cannot_view_other_users_transactions(): void
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('transactions.edit', $transaction));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' transactions
     */
    public function test_user_cannot_update_other_users_transactions(): void
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->put(route('transactions.update', $transaction), [
                'financial_account_id' => $this->account->id,
                'category_id' => $this->incomeCategory->id,
                'type' => 'income',
                'amount' => 999999,
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Hacked',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' transactions
     */
    public function test_user_cannot_delete_other_users_transactions(): void
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->delete(route('transactions.destroy', $transaction));

        $response->assertForbidden();
    }
}
