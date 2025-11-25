<?php

return [
    'email_enabled'      => env('NOTIFY_EMAIL_ENABLED', true),
    'wa_enabled'         => env('NOTIFY_WA_ENABLED', env('WHATSAPP_WEBHOOK_URL') ? true : false),
    'wa_webhook_url'     => env('WHATSAPP_WEBHOOK_URL', env('NOTIFY_WA_WEBHOOK_URL')),
    'wa_token'           => env('WHATSAPP_WEBHOOK_TOKEN', env('NOTIFY_WA_TOKEN')),
    'wa_sender'          => env('NOTIFY_WA_SENDER'),
    'wa_default_country' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '55'),
    'wa_timeout'         => env('WHATSAPP_WEBHOOK_TIMEOUT', 10),
];
