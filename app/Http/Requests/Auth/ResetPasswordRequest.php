<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => 'required|string',
            'email'    => 'required|string|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'    => 'Le token est obligatoire.',
            'email.required'    => 'L\'email est obligatoire.',
            'email.exists'      => 'Aucun compte n\'est associé à cet email.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed'=> 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
