<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20|regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/',
            'customer_email' => 'nullable|email|max:255',
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_type,delivery|string|max:500',
            'delivery_neighborhood' => 'nullable|string|max:255',
            'delivery_complement' => 'nullable|string|max:255',
            'delivery_instructions' => 'nullable|string|max:500',
            'payment_method' => 'required|string|max:30',
            'observations' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:64|exists:coupons,code',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'O nome é obrigatório.',
            'customer_phone.required' => 'O telefone é obrigatório.',
            'customer_phone.regex' => 'O telefone deve estar no formato (71) 99999-9999.',
            'customer_email.email' => 'O e-mail deve ser válido.',
            'delivery_type.required' => 'Selecione o tipo de entrega.',
            'delivery_type.in' => 'Tipo de entrega inválido.',
            'delivery_address.required_if' => 'O endereço é obrigatório para entrega.',
            'payment_method.required' => 'Selecione a forma de pagamento.',
            'coupon_code.exists' => 'Cupom inválido.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove máscara do telefone se necessário
        if ($this->has('customer_phone')) {
            $phone = preg_replace('/\D/', '', $this->customer_phone);
            if (strlen($phone) === 11) {
                $this->merge([
                    'customer_phone' => '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4)
                ]);
            }
        }
    }
}
