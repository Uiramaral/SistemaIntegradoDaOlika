<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Olika Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações específicas do sistema Olika
    |
    */

    'business' => [
        'name' => env('OLIKA_BUSINESS_NAME', 'Olika'),
        'description' => env('OLIKA_BUSINESS_DESCRIPTION', 'Pães artesanais feitos com amor'),
        'phone' => env('OLIKA_BUSINESS_PHONE', '(71) 98701-9420'),
        'email' => env('OLIKA_BUSINESS_EMAIL', 'contato@olika.com.br'),
        'address' => env('OLIKA_BUSINESS_ADDRESS', 'Salvador, BA'),
    ],

    'delivery' => [
        'min_value' => env('OLIKA_MIN_DELIVERY_VALUE', 100.00),
        'free_threshold' => env('OLIKA_FREE_DELIVERY_THRESHOLD', 100.00),
        'fee_per_km' => env('OLIKA_DELIVERY_FEE_PER_KM', 2.50),
        'max_distance' => env('OLIKA_MAX_DELIVERY_DISTANCE', 15.00),
    ],

    'payment' => [
        'mercadopago' => [
            'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
            'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
            'environment' => env('MERCADOPAGO_ENV', 'sandbox'),
        ],
    ],

    'whatsapp' => [
        'api_url' => env('WHATSAPP_API_URL'),
        'api_key' => env('WHATSAPP_API_KEY'),
    ],

    'loyalty' => [
        'enabled' => env('OLIKA_LOYALTY_ENABLED', true),
        'points_per_real' => env('OLIKA_POINTS_PER_REAL', 1.00),
        'cashback_percentage' => env('OLIKA_CASHBACK_PERCENTAGE', 5.00),
    ],
];
