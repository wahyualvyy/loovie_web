<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test user can view notes index page
     */
    public function test_user_can_view_notes_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('notes.index'));

        $response->assertSuccessful();
    }

    /**
     * Test user can create note
     */
    public function test_user_can_create_note(): void
    {
        $data = [
            'title' => 'My First Note',
            'content' => 'This is the content of my note',
            'note_date' => now()->format('Y-m-d'),
            'label' => 'Personal',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('notes.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'title' => 'My First Note',
            'content' => 'This is the content of my note',
        ]);
    }

    /**
     * Test user can view create note page
     */
    public function test_user_can_view_create_note_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('notes.create'));

        $response->assertSuccessful();
    }

    /**
     * Test user can update note
     */
    public function test_user_can_update_note(): void
    {
        $note = Note::factory()
            ->for($this->user)
            ->create(['title' => 'Old Title']);

        $response = $this->actingAs($this->user)
            ->put(route('notes.update', $note), [
                'title' => 'Updated Title',
                'content' => 'Updated content',
                'note_date' => $note->note_date->format('Y-m-d'),
                'label' => 'Updated Label',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ]);
    }

    /**
     * Test user can view edit note page
     */
    public function test_user_can_view_edit_note_page(): void
    {
        $note = Note::factory()
            ->for($this->user)
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('notes.edit', $note));

        $response->assertSuccessful();
    }

    /**
     * Test user can delete note
     */
    public function test_user_can_delete_note(): void
    {
        $note = Note::factory()
            ->for($this->user)
            ->create();

        $response = $this->actingAs($this->user)
            ->delete(route('notes.destroy', $note));

        $response->assertRedirect();
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    /**
     * Test note title is required
     */
    public function test_note_title_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('notes.store'), [
                'title' => '',
                'content' => 'Content',
                'note_date' => now()->format('Y-m-d'),
                'label' => 'Personal',
            ]);

        $response->assertSessionHasErrors('title');
    }

    /**
     * Test note content is required
     */
    public function test_note_content_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('notes.store'), [
                'title' => 'Title',
                'content' => '',
                'note_date' => now()->format('Y-m-d'),
                'label' => 'Personal',
            ]);

        $response->assertSessionHasErrors('content');
    }

    /**
     * Test note date is required
     */
    public function test_note_date_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('notes.store'), [
                'title' => 'Title',
                'content' => 'Content',
                'note_date' => '',
                'label' => 'Personal',
            ]);

        $response->assertSessionHasErrors('note_date');
    }

    /**
     * Test note date must be valid date
     */
    public function test_note_date_must_be_valid_date(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('notes.store'), [
                'title' => 'Title',
                'content' => 'Content',
                'note_date' => 'invalid-date',
                'label' => 'Personal',
            ]);

        $response->assertSessionHasErrors('note_date');
    }

    /**
     * Test user cannot view other users' notes
     */
    public function test_user_cannot_view_other_users_notes(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('notes.edit', $note));

        $response->assertForbidden();
    }

    /**
     * Test user cannot update other users' notes
     */
    public function test_user_cannot_update_other_users_notes(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->put(route('notes.update', $note), [
                'title' => 'Hacked Title',
                'content' => 'Hacked content',
                'note_date' => now()->format('Y-m-d'),
                'label' => 'Hacked',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user cannot delete other users' notes
     */
    public function test_user_cannot_delete_other_users_notes(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->delete(route('notes.destroy', $note));

        $response->assertForbidden();
    }

    /**
     * Test user can create note without label
     */
    public function test_user_can_create_note_without_label(): void
    {
        $data = [
            'title' => 'Note Without Label',
            'content' => 'Content without label',
            'note_date' => now()->format('Y-m-d'),
            'label' => null,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('notes.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'title' => 'Note Without Label',
            'label' => null,
        ]);
    }

    /**
     * Test note title has character limit
     */
    public function test_note_title_has_character_limit(): void
    {
        $longTitle = str_repeat('A', 256);

        $response = $this->actingAs($this->user)
            ->post(route('notes.store'), [
                'title' => $longTitle,
                'content' => 'Content',
                'note_date' => now()->format('Y-m-d'),
                'label' => 'Personal',
            ]);

        $response->assertSessionHasErrors('title');
    }
}
