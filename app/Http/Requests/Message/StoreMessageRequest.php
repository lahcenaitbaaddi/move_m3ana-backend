<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'club_id' => 'required|exists:clubs,id',
            'message' => 'required|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'club_id.required' => 'Le club destinataire est obligatoire.',
            'club_id.exists'   => 'Le club sélectionné n\'existe pas.',
            'message.required' => 'Le message est obligatoire.',
            'message.max'      => 'Le message ne doit pas dépasser 2000 caractères.',
        ];
    }
}
