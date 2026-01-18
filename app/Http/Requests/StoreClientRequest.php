<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * StoreClientRequest - Validação para criação de novo cliente
 */
class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Cadastro é público (onboarding)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'slug' => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9-]+$/',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $normalized = Str::slug($value);
                        if (!Client::isSlugAvailable($normalized)) {
                            $fail('Este endereço já está em uso ou é reservado.');
                        }
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // requer password_confirmation
            ],
            'admin_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'plan' => [
                'nullable',
                'in:basic,ia',
            ],
            'terms_accepted' => [
                'required',
                'accepted',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do estabelecimento é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'name.max' => 'O nome não pode ter mais de 100 caracteres.',
            
            'slug.min' => 'O endereço deve ter pelo menos 3 caracteres.',
            'slug.max' => 'O endereço não pode ter mais de 30 caracteres.',
            'slug.regex' => 'O endereço só pode conter letras, números e hífens.',
            
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Digite um email válido.',
            'email.unique' => 'Este email já está cadastrado.',
            
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            
            'terms_accepted.required' => 'Você precisa aceitar os termos de uso.',
            'terms_accepted.accepted' => 'Você precisa aceitar os termos de uso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do estabelecimento',
            'slug' => 'endereço',
            'email' => 'email',
            'password' => 'senha',
            'admin_name' => 'nome do administrador',
            'phone' => 'telefone',
            'plan' => 'plano',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalizar slug se fornecido
        if ($this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->slug),
            ]);
        }

        // Limpar telefone
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9]/', '', $this->phone),
            ]);
        }
    }
}
