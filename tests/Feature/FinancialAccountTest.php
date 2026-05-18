<?php

namespace Tests\Feature;

use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test user can view financial accounts index page
     */
    public function test_user_can_view_financial_accounts_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('financial-accounts.index'));

        $response->assertSuccessful();
    }

    /**
     * Test user can create financial account
     */
    public function test_user_can_create_financial_account(): void
    {
        $data = [
            'name' => 'Bank BCA',
            'type' => 'Bank',
            'initial_balance' => 1000000,
            'description' => 'Main savings account',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('financial-accounts.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_accounts', [
            'user_id' => $this->user->id,
            'name' => 'Bank BCA',
            'current_balance' => 1000000,
        ]);
    }

    /**
     * Test user can view create financial account page
     */
    public function test_user_can_view_create_account_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('financial-accounts.create'));

        $response->assertSuccessful();
    }

    /**
     * Test user can update financial account
     */
    public function test_user_can_update_financial_account(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user)
            ->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put(route('financial-accounts.update', $account), [
                'name' => 'Updated Name',
                'type' => 'Bank',
                'initial_balance' => 1000000,
                'description' => 'Updated description',
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_accounts', [
            'id' => $account->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test user can view edit account page
     */
    public function test_user_can_view_edit_account_page(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user)
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('financial-accounts.edit', $account));

        $response->assertSuccessful();
    }

    /**
     * Test user can delete financial account without transactions
     */
    public function test_user_can_delete_account_without_transactions(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user)
            ->create();

        $response = $this->actingAs($this->user)
            ->delete(route('financial-accounts.destroy', $account));

        $response->assertRedirect();
        $this->assertDatabaseMissing('financial_accounts', ['id' => $account->id]);
    }

    /**
     * Test user cannot delete account with transactions
     */
    public function test_user_cannot_delete_account_with_transactions(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user)
            ->hasTransactions(1)
            ->create();

        $response = $this->actingAs($this->user)
            ->delete(route('financial-accounts.destroy', $account));

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_accounts', ['id' => $account->id]);
    }

    /**
     * Test account name is required
     */
    public function test_account_name_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('financial-accounts.store'), [
                'name' => '',
                'type' => 'Bank',
                'initial_balance' => 1000000,
                'is_active' => true,
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test account type is required
     */
    public function test_account_type_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('financial-accounts.store'), [
                'name' => 'Bank BCA',
                'type' => '',
                'initial_balance' => 1000000,
                'is_active' => true,
            ]);

        $response->assertSessionHasErrors('type');
    }

    /**
     * Test initial balance must be numeric
     */
    public function test_initial_balance_must_be_numeric(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('financial-accounts.store'), [
                'name' => 'Bank BCA',
                'type' => 'Bank',
                'initial_balance' => 'invalid',
                'is_active' => true,
            ]);

        $response->assertSessionHasErrors('initial_balance');
    }

    /**
     * Test user cannot view other users' accounts
     */
    public function test_user_cannot_view_other_users_accounts(): void
    {
        $otherUser = User::factory()->create();
        $account = FinancialAccount::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('financial-accounts.edit', $account));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' accounts
     */
    public function test_user_cannot_update_other_users_accounts(): void
    {
        $otherUser = User::factory()->create();
        $account = FinancialAccount::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->put(route('financial-accounts.update', $account), [
                'name' => 'Hacked Name',
                'type' => 'Bank',
                'initial_balance' => 9999999,
                'is_active' => true,
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' accounts
     */
    public function test_user_cannot_delete_other_users_accounts(): void
    {
        $otherUser = User::factory()->create();
        $account = FinancialAccount::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->delete(route('financial-accounts.destroy', $account));

        $response->assertForbidden();
    }

    /**
     * Test current balance equals initial balance when no transactions
     */
    public function test_current_balance_equals_initial_balance(): void
    {
        $account = FinancialAccount::factory()
            ->for($this->user)
            ->create(['initial_balance' => 1000000, 'current_balance' => 1000000]);

        $this->assertEquals(1000000, $account->current_balance);
        $this->assertEquals(1000000, $account->initial_balance);
    }
}
