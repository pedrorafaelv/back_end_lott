<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcceptTermsRequest extends FormRequest
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
            'version' => ['required', 'string', 'max:20'],
            'sourceUrl' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'docType.required' => 'El tipo de documento es obligatorio.',
            'docType.in' => 'El tipo de documento no es válido.',
            'version.required' => 'La versión del documento es obligatoria.',
            'sourceUrl.url' => 'La URL de origen no es válida.',
        ];
    }
}