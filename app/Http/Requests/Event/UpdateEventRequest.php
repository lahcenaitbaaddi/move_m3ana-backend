<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'date'        => 'sometimes|date',
            'location'    => 'nullable|string|max:255',
            'capacity'    => 'nullable|integer|min:1',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
