<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'sport'       => 'nullable|string|max:100',
            'location'    => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:100',
            'date'        => 'nullable|date',
            'capacity'    => 'nullable|integer|min:1',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active'   => 'sometimes|boolean',
        ];
    }
}
