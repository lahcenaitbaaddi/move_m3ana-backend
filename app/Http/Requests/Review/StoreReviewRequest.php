<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'club_id' => 'required|exists:clubs,id',
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'club_id.required' => 'Le club est obligatoire.',
            'club_id.exists'   => 'Le club sélectionné n\'existe pas.',
            'rating.required'  => 'La note est obligatoire.',
            'rating.min'       => 'La note minimum est 1.',
            'rating.max'       => 'La note maximum est 5.',
        ];
    }
}
