<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
   
  public function rules(): array
    {

        return [
            'id_token'                          => ['required', 'string', 'max:4096'],
            'name'                              => ['required', 'string', 'min:3', 'max:50'],
            // Un solo check del usuario = exactamente terms + privacy
            'accepted_documents'                => ['required', 'array', 'size:2'],
            'accepted_documents.*.doc_type'     => ['required', 'string', 'in:terms,privacy', 'distinct'],
            'accepted_documents.*.version'      => ['required', 'string', 'max:20'],
            'accepted_documents.*.content_hash' => ['required', 'string', 'size:64'],
        ];
    }
    
}
