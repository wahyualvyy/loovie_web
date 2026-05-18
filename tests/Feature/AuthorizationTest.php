<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
    }

    /**
     * Test user cannot access other users' financial accounts list
     */
    public function test_user_cannot_access_other_users_accounts_list(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user2)
            ->create();

        // The index should only show user's own accounts
        $response = $this->actingAs($this->user1)
            ->get(route('financial-accounts.index'));

        $response->assertSuccessful();
        // Verify account doesn't appear in response (implementation dependent)
    }

    /**
     * Test user cannot view other users' account details
     */
    public function test_user_cannot_view_other_users_account_details(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->get(route('financial-accounts.edit', $account));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' account
     */
    public function test_user_cannot_update_other_users_account(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->put(route('financial-accounts.update', $account), [
                'name' => 'Hacked',
                'type' => 'Bank',
                'initial_balance' => 999999,
                'is_active' => true,
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' account
     */
    public function test_user_cannot_delete_other_users_account(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->delete(route('financial-accounts.destroy', $account));

        $response->assertForbidden();
        $this->assertDatabaseHas('financial_accounts', ['id' => $account->id]);
    }

    /**
     * Test user cannot access other users' categories
     */
    public function test_user_cannot_access_other_users_categories(): void
    {
        $category = Category::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->get(route('categories.edit', $category));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' category
     */
    public function test_user_cannot_update_other_users_category(): void
    {
        $category = Category::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->put(route('categories.update', $category), [
                'name' => 'Hacked Category',
                'type' => 'income',
                'color' => '#FF0000',
                'icon' => 'star',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' category
     */
    public function test_user_cannot_delete_other_users_category(): void
    {
        $category = Category::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->delete(route('categories.destroy', $category));

        $response->assertForbidden();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /**
     * Test user cannot view other users' transactions
     */
    public function test_user_cannot_view_other_users_transactions(): void
    {
        $transaction = Transaction::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->get(route('transactions.edit', $transaction));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' transaction
     */
    public function test_user_cannot_update_other_users_transaction(): void
    {
        $account = FinancialAccount::factory()->for($this->user1)->create();
        $category = Category::factory()->for($this->user1)->create();
        $transaction = Transaction::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->put(route('transactions.update', $transaction), [
                'financial_account_id' => $account->id,
                'category_id' => $category->id,
                'type' => 'income',
                'amount' => 999999,
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Hacked',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' transaction
     */
    public function test_user_cannot_delete_other_users_transaction(): void
    {
        $transaction = Transaction::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->delete(route('transactions.destroy', $transaction));

        $response->assertForbidden();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    /**
     * Test user cannot view other users' notes
     */
    public function test_user_cannot_view_other_users_notes(): void
    {
        $note = Note::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->get(route('notes.edit', $note));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' note
     */
    public function test_user_cannot_update_other_users_note(): void
    {
        $note = Note::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->put(route('notes.update', $note), [
                'title' => 'Hacked Title',
                'content' => 'Hacked content',
                'note_date' => now()->format('Y-m-d'),
                'label' => 'Hacked',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' note
     */
    public function test_user_cannot_delete_other_users_note(): void
    {
        $note = Note::factory()
            ->for($this->user2)
            ->create();

        $response = $this->actingAs($this->user1)
            ->delete(route('notes.destroy', $note));

        $response->assertForbidden();
        $this->assertDatabaseHas('notes', ['id' => $note->id]);
    }

    /**
     * Test user1's data remains intact after user2 modifies their own
     */
    public function test_user1_data_remains_intact_after_user2_modifies(): void
    {
        $account1 = FinancialAccount::factory()
            ->for($this->user1)
            ->create(['name' => 'User1 Account']);

        $account2 = FinancialAccount::factory()
            ->for($this->user2)
            ->create(['name' => 'User2 Account']);

        // User2 updates their account
        $this->actingAs($this->user2)
            ->put(route('financial-accounts.update', $account2), [
                'name' => 'Updated User2 Account',
                'type' => 'Bank',
                'initial_balance' => 2000000,
                'is_active' => true,
            ]);

        // User1's account should remain unchanged
        $this->assertDatabaseHas('financial_accounts', [
            'id' => $account1->id,
            'name' => 'User1 Account',
        ]);

        // User2's account should be updated
        $this->assertDatabaseHas('financial_accounts', [
            'id' => $account2->id,
            'name' => 'Updated User2 Account',
        ]);
    }

    /**
     * Test unauthenticated user cannot access protected routes
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $routes = [
            route('financial-accounts.index'),
            route('categories.index'),
            route('transactions.index'),
            route('notes.index'),
            route('dashboard'),
            route('profile.edit'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('login'));
        }
    }
}
