<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'sometimes|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'avatar'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sport_preferences' => 'nullable|array',
            'sport_preferences.*' => 'string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max'    => 'Le nom ne doit pas dépasser 255 caractères.',
            'avatar.image'=> 'L\'avatar doit être une image.',
            'avatar.max'  => 'L\'avatar ne doit pas dépasser 2 Mo.',
        ];
    }
}
