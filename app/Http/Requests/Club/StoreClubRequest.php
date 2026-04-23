<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'sport'        => 'required|string|max:100',
            'description'  => 'nullable|string',
            'address'      => 'required|string|max:255',
            'city'         => 'required|string|max:100',
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

    public function messages(): array
    {
        return [
            'name.required'    => 'Le nom du club est obligatoire.',
            'sport.required'   => 'Le sport est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire.',
            'city.required'    => 'La ville est obligatoire.',
            'logo.image'       => 'Le logo doit être une image.',
            'logo.max'         => 'Le logo ne doit pas dépasser 2 Mo.',
        ];
    }
}
