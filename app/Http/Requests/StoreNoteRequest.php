<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'note_date' => ['required', 'date'],
            'label' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.max' => 'Title must not exceed 255 characters.',
            'content.required' => 'Note content is required.',
            'note_date.required' => 'Date is required.',
            'note_date.date' => 'Date must be a valid date.',
            'label.max' => 'Label must not exceed 50 characters.',
        ];
    }
}
