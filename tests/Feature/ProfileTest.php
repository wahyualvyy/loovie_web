<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        Storage::fake('public');
    }

    /**
     * Test user can view profile edit page
     */
    public function test_user_can_view_profile_edit_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertSuccessful();
    }

    /**
     * Test user can update name
     */
    public function test_user_can_update_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => 'New Name',
                'email' => $this->user->email,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test user can update email
     */
    public function test_user_can_update_email(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'newemail@example.com',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com',
        ]);
    }

    /**
     * Test user cannot use email that already exists
     */
    public function test_user_cannot_use_email_that_already_exists(): void
    {
        $anotherUser = User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'taken@example.com',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test user can upload profile photo
     */
    public function test_user_can_upload_profile_photo(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'photo' => $file,
            ]);

        $response->assertRedirect();
        $this->user->refresh();
        $this->assertNotNull($this->user->photo_path);
        Storage::disk('public')->assertExists($this->user->photo_path);
    }

    /**
     * Test user can upload PNG photo
     */
    public function test_user_can_upload_png_photo(): void
    {
        $file = UploadedFile::fake()->image('avatar.png', 200, 200);

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'photo' => $file,
            ]);

        $response->assertRedirect();
        $this->user->refresh();
        $this->assertNotNull($this->user->photo_path);
    }

    /**
     * Test user cannot upload non-image file
     */
    public function test_user_cannot_upload_non_image_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'photo' => $file,
            ]);

        $response->assertSessionHasErrors('photo');
    }

    /**
     * Test user cannot upload photo larger than 5MB
     */
    public function test_user_cannot_upload_photo_larger_than_5mb(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg')->size(6000);

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'photo' => $file,
            ]);

        $response->assertSessionHasErrors('photo');
    }

    /**
     * Test old photo is deleted when new photo is uploaded
     */
    public function test_old_photo_is_deleted_when_new_photo_is_uploaded(): void
    {
        $oldFile = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        
        // Upload first photo
        $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'photo' => $oldFile,
            ]);

        $this->user->refresh();
        $oldPhotoPath = $this->user->photo_path;

        // Upload new photo
        $newFile = UploadedFile::fake()->image('avatar2.jpg', 200, 200);
        
        $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'photo' => $newFile,
            ]);

        $this->user->refresh();
        $this->assertNotEquals($oldPhotoPath, $this->user->photo_path);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($this->user->photo_path);
    }

    /**
     * Test user can change password
     */
    public function test_user_can_change_password(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('password.update'), [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $this->user->refresh();
        $this->assertTrue(\Hash::check('newpassword123', $this->user->password));
    }

    /**
     * Test user cannot change password with wrong current password
     */
    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('password.update'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    /**
     * Test password confirmation must match
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('password.update'), [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test new password must be at least 8 characters
     */
    public function test_new_password_must_be_at_least_8_characters(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('password.update'), [
                'current_password' => 'password123',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test user can delete account
     */
    public function test_user_can_delete_account(): void
    {
        $userId = $this->user->id;

        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'));

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertGuest();
    }

    /**
     * Test user photo is deleted when account is deleted
     */
    public function test_user_photo_is_deleted_when_account_is_deleted(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        
        $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'photo' => $file,
            ]);

        $this->user->refresh();
        $photoPath = $this->user->photo_path;

        $this->actingAs($this->user)
            ->delete(route('profile.destroy'));

        Storage::disk('public')->assertMissing($photoPath);
    }

    /**
     * Test name is required
     */
    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => '',
                'email' => $this->user->email,
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test email is required
     */
    public function test_email_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => '',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test email must be valid format
     */
    public function test_email_must_be_valid_format(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test current password is required for password change
     */
    public function test_current_password_is_required_for_password_change(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('password.update'), [
                'current_password' => '',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }
}
