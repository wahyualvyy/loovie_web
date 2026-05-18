<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:cash,bank,digital_wallet,investment,credit_card'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama akun harus diisi',
            'name.max' => 'Nama akun maksimal 255 karakter',
            'type.required' => 'Tipe akun harus dipilih',
            'type.in' => 'Tipe akun tidak valid',
            'initial_balance.required' => 'Saldo awal harus diisi',
            'initial_balance.numeric' => 'Saldo awal harus berupa angka',
            'initial_balance.min' => 'Saldo awal tidak boleh negatif',
            'description.max' => 'Deskripsi maksimal 500 karakter',
        ];
    }
}
