<?php

return [
    'email_enabled' => env('NOTIFY_EMAIL_ENABLED', true),
    'wa_enabled'    => env('NOTIFY_WA_ENABLED', false),
    'wa_webhook_url'=> env('NOTIFY_WA_WEBHOOK_URL'),
    'wa_token'      => env('NOTIFY_WA_TOKEN'),
    'wa_sender'     => env('NOTIFY_WA_SENDER'),
];
