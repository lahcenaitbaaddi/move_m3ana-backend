<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'sometimes|string|max:255',
            'sport'        => 'sometimes|string|max:100',
            'description'  => 'nullable|string',
            'address'      => 'sometimes|string|max:255',
            'city'         => 'sometimes|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'tarifs'       => 'nullable|string|max:255',
            'horaires'     => 'nullable|string|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images'       => 'nullable|array',
            'images.*'     => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'social_links' => 'nullable|array',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ];
    }
}
