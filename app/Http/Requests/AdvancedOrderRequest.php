<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use App\Models\Coupon;

class AdvancedOrderRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255|min:2',
            'customer_phone' => 'required|string|regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/',
            'customer_email' => 'nullable|email|max:255',
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_type,delivery|string|max:500',
            'delivery_neighborhood' => 'nullable|string|max:100',
            'delivery_complement' => 'nullable|string|max:100',
            'delivery_instructions' => 'nullable|string|max:500',
            'payment_method' => 'required|in:pix,credit_card',
            'observations' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'O nome é obrigatório.',
            'customer_name.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'customer_phone.required' => 'O telefone é obrigatório.',
            'customer_phone.regex' => 'O telefone deve estar no formato (71) 99999-9999.',
            'customer_email.email' => 'O e-mail deve ser válido.',
            'delivery_type.required' => 'Selecione o tipo de entrega.',
            'delivery_type.in' => 'Tipo de entrega inválido.',
            'delivery_address.required_if' => 'O endereço é obrigatório para entrega.',
            'payment_method.required' => 'Selecione a forma de pagamento.',
            'payment_method.in' => 'Forma de pagamento inválida.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar cupom se fornecido
            if ($this->coupon_code) {
                $coupon = Coupon::where('code', $this->coupon_code)->first();
                
                if (!$coupon) {
                    $validator->errors()->add('coupon_code', 'Cupom não encontrado.');
                } elseif (!$coupon->isValid()) {
                    $validator->errors()->add('coupon_code', 'Cupom inválido ou expirado.');
                }
            }

            // Validar telefone único para novos clientes
            if ($this->customer_phone) {
                $existingCustomer = \App\Models\Customer::where('phone', $this->customer_phone)->first();
                if ($existingCustomer && $existingCustomer->email && !$this->customer_email) {
                    $validator->errors()->add('customer_email', 'E-mail é obrigatório para clientes cadastrados.');
                }
            }
        });
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Limpar e formatar dados
        if (isset($validated['customer_phone'])) {
            $validated['customer_phone'] = $this->formatPhone($validated['customer_phone']);
        }
        
        if (isset($validated['customer_name'])) {
            $validated['customer_name'] = ucwords(strtolower(trim($validated['customer_name'])));
        }

        return $validated;
    }

    /**
     * Formatar telefone
     */
    private function formatPhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
