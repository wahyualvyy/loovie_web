<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori harus diisi',
            'name.max' => 'Nama kategori maksimal 255 karakter',
            'type.required' => 'Tipe kategori harus dipilih',
            'type.in' => 'Tipe kategori harus income atau expense',
            'color.required' => 'Warna kategori harus dipilih',
            'color.regex' => 'Format warna tidak valid (gunakan format hex: #RRGGBB)',
            'icon.required' => 'Icon kategori harus dipilih',
            'icon.max' => 'Icon maksimal 50 karakter',
        ];
    }
}
