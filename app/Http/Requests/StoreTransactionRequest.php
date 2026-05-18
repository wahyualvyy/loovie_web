<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'financial_account_id' => ['required', 'exists:financial_accounts,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'financial_account_id.required' => 'Akun keuangan harus dipilih',
            'financial_account_id.exists' => 'Akun keuangan tidak valid',
            'category_id.required' => 'Kategori harus dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'type.required' => 'Tipe transaksi harus dipilih',
            'type.in' => 'Tipe transaksi harus income atau expense',
            'amount.required' => 'Nominal harus diisi',
            'amount.numeric' => 'Nominal harus berupa angka',
            'amount.min' => 'Nominal minimal 0.01',
            'amount.max' => 'Nominal tidak boleh melebihi 999,999,999.99',
            'transaction_date.required' => 'Tanggal transaksi harus diisi',
            'transaction_date.date' => 'Format tanggal tidak valid',
            'description.max' => 'Deskripsi maksimal 500 karakter',
            'attachment.file' => 'File attachment tidak valid',
            'attachment.mimes' => 'Format file harus jpg, jpeg, png, atau pdf',
            'attachment.max' => 'Ukuran file maksimal 5MB',
        ];
    }
}
