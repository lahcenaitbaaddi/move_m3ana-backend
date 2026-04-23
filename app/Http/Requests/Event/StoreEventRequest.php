<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'club_id'     => 'required|exists:clubs,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'required|date|after:now',
            'location'    => 'nullable|string|max:255',
            'capacity'    => 'nullable|integer|min:1',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'club_id.required' => 'Le club est obligatoire.',
            'club_id.exists'   => 'Le club sélectionné n\'existe pas.',
            'title.required'   => 'Le titre de l\'événement est obligatoire.',
            'date.required'    => 'La date est obligatoire.',
            'date.after'       => 'La date doit être dans le futur.',
            'capacity.min'     => 'La capacité doit être au moins 1.',
        ];
    }
}
