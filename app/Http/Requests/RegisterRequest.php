<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nom'          => 'required|string|max:255',
            'prenom'       => 'required|string|max:255',
            'email'        => 'required|email|unique:utilisateurs,email',
            'password'     => 'required|min:8|confirmed',
            'telephone'    => 'nullable|string|max:20',
            'organisation' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'         => 'Le nom est obligatoire.',
            'prenom.required'      => 'Le prénom est obligatoire.',
            'email.required'       => 'L\'adresse email est obligatoire.',
            'email.unique'         => 'Cette adresse email est déjà utilisée.',
            'password.min'         => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'   => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
