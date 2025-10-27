<?php

// Webhook público para Evolution API
Route::post('/webhooks/whatsapp/evolution', [\App\Http\Controllers\WhatsAppInboundController::class, 'receive'])
    ->name('webhook.whatsapp.evolution');
