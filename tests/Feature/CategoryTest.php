<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test user can view categories index page
     */
    public function test_user_can_view_categories_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('categories.index'));

        $response->assertSuccessful();
    }

    /**
     * Test user can create income category
     */
    public function test_user_can_create_income_category(): void
    {
        $data = [
            'name' => 'Salary',
            'type' => 'income',
            'color' => '#FF6B6B',
            'icon' => 'briefcase',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('categories.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);
    }

    /**
     * Test user can create expense category
     */
    public function test_user_can_create_expense_category(): void
    {
        $data = [
            'name' => 'Food',
            'type' => 'expense',
            'color' => '#4ECDC4',
            'icon' => 'utensils',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('categories.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'Food',
            'type' => 'expense',
        ]);
    }

    /**
     * Test user can view create category page
     */
    public function test_user_can_view_create_category_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('categories.create'));

        $response->assertSuccessful();
    }

    /**
     * Test user can update category
     */
    public function test_user_can_update_category(): void
    {
        $category = Category::factory()
            ->for($this->user)
            ->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put(route('categories.update', $category), [
                'name' => 'Updated Name',
                'type' => 'income',
                'color' => '#FF6B6B',
                'icon' => 'briefcase',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test user can view edit category page
     */
    public function test_user_can_view_edit_category_page(): void
    {
        $category = Category::factory()
            ->for($this->user)
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('categories.edit', $category));

        $response->assertSuccessful();
    }

    /**
     * Test user can delete category without transactions
     */
    public function test_user_can_delete_category_without_transactions(): void
    {
        $category = Category::factory()
            ->for($this->user)
            ->create();

        $response = $this->actingAs($this->user)
            ->delete(route('categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Test user cannot delete category with transactions
     */
    public function test_user_cannot_delete_category_with_transactions(): void
    {
        $category = Category::factory()
            ->for($this->user)
            ->hasTransactions(1)
            ->create();

        $response = $this->actingAs($this->user)
            ->delete(route('categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /**
     * Test category name is required
     */
    public function test_category_name_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('categories.store'), [
                'name' => '',
                'type' => 'income',
                'color' => '#FF6B6B',
                'icon' => 'briefcase',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test category type is required
     */
    public function test_category_type_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('categories.store'), [
                'name' => 'Salary',
                'type' => '',
                'color' => '#FF6B6B',
                'icon' => 'briefcase',
            ]);

        $response->assertSessionHasErrors('type');
    }

    /**
     * Test category type must be valid
     */
    public function test_category_type_must_be_valid(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('categories.store'), [
                'name' => 'Salary',
                'type' => 'invalid',
                'color' => '#FF6B6B',
                'icon' => 'briefcase',
            ]);

        $response->assertSessionHasErrors('type');
    }

    /**
     * Test category color is required
     */
    public function test_category_color_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('categories.store'), [
                'name' => 'Salary',
                'type' => 'income',
                'color' => '',
                'icon' => 'briefcase',
            ]);

        $response->assertSessionHasErrors('color');
    }

    /**
     * Test category color must be valid hex
     */
    public function test_category_color_must_be_valid_hex(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('categories.store'), [
                'name' => 'Salary',
                'type' => 'income',
                'color' => 'invalid-color',
                'icon' => 'briefcase',
            ]);

        $response->assertSessionHasErrors('color');
    }

    /**
     * Test user cannot view other users' categories
     */
    public function test_user_cannot_view_other_users_categories(): void
    {
        $otherUser = User::factory()->create();
        $category = Category::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('categories.edit', $category));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' categories
     */
    public function test_user_cannot_update_other_users_categories(): void
    {
        $otherUser = User::factory()->create();
        $category = Category::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->put(route('categories.update', $category), [
                'name' => 'Hacked Name',
                'type' => 'income',
                'color' => '#FF6B6B',
                'icon' => 'briefcase',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' categories
     */
    public function test_user_cannot_delete_other_users_categories(): void
    {
        $otherUser = User::factory()->create();
        $category = Category::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->delete(route('categories.destroy', $category));

        $response->assertForbidden();
    }
}
