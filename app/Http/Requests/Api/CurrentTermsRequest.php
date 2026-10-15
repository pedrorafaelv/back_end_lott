<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrentTermsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'docType' => [
                'required',
                'string',
                Rule::in(['terms', 'privacy', 'cookies', 'game-rules']),
            ],
        ];
    }
}