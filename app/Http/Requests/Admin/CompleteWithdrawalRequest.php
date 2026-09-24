<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompleteWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'transaction_reference' => ['required', 'string', 'min:3', 'max:100'],
            'admin_notes'           => ['nullable', 'string', 'max:500'],
        ];
    }
}