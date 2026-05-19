<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->profileRules($this->user()->id);

        // Add photo validation
        $rules['photo'] = ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png'];

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already in use.',
            'photo.image' => 'Photo must be an image file.',
            'photo.max' => 'Photo must not exceed 5MB.',
            'photo.mimes' => 'Photo must be JPG, JPEG, or PNG.',
        ];
    }
}
