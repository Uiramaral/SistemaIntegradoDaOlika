<?php

use App\Models\Client;

if (!function_exists('currentClientId')) {
    /**
     * Obtém o ID do cliente (tenant) atual
     * 
     * @return int|null
     */
    function currentClientId(): ?int
    {
        // 1. Request attributes (setado pelo middleware)
        if (app()->bound('request')) {
            $request = app('request');
            
            if ($request->attributes->has('client_id')) {
                return (int) $request->attributes->get('client_id');
            }
            
            if ($request->hasHeader('X-Client-Id')) {
                return (int) $request->header('X-Client-Id');
            }
        }
        
        // 2. Session
        if (session()->has('client_id')) {
            return (int) session('client_id');
        }
        
        // 3. Config default
        $default = config('olika.default_client_id');
        return $default ? (int) $default : null;
    }
}

if (!function_exists('currentClient')) {
    /**
     * Obtém o model Client do tenant atual
     * 
     * @return Client|null
     */
    function currentClient(): ?Client
    {
        // Primeiro verificar se já está no request (cached pelo middleware)
        if (app()->bound('request')) {
            $request = app('request');
            
            if ($request->attributes->has('client')) {
                return $request->attributes->get('client');
            }
        }
        
        // Senão, buscar pelo ID
        $clientId = currentClientId();
        
        if (!$clientId) {
            return null;
        }
        
        return Client::find($clientId);
    }
}

if (!function_exists('setCurrentClient')) {
    /**
     * Define o cliente atual no contexto (útil para Jobs, Commands, etc)
     * 
     * @param int|Client $client ID ou model do cliente
     * @return void
     */
    function setCurrentClient($client): void
    {
        $clientId = $client instanceof Client ? $client->id : (int) $client;
        
        session(['client_id' => $clientId]);
        
        if (app()->bound('request')) {
            $request = app('request');
            $request->attributes->set('client_id', $clientId);
            
            if ($client instanceof Client) {
                $request->attributes->set('client', $client);
            }
        }
    }
}

if (!function_exists('withClient')) {
    /**
     * Executa um callback no contexto de um cliente específico
     * 
     * @param int|Client $client ID ou model do cliente
     * @param callable $callback Função a executar
     * @return mixed
     */
    function withClient($client, callable $callback)
    {
        $previousClientId = currentClientId();
        
        setCurrentClient($client);
        
        try {
            return $callback();
        } finally {
            // Restaurar cliente anterior
            if ($previousClientId) {
                setCurrentClient($previousClientId);
            } else {
                session()->forget('client_id');
            }
        }
    }
}

if (!function_exists('currentClientHasFeature')) {
    /**
     * Verifica se o cliente atual possui determinado recurso/feature habilitado
     * (de acordo com plano e addons da assinatura)
     */
    function currentClientHasFeature(string $feature): bool
    {
        $client = currentClient();

        if (!$client) {
            return true; // fallback: não bloquear em casos antigos
        }

        $subscription = $client->subscription;

        if (!$subscription && method_exists($client, 'subscriptions')) {
            $subscription = $client->subscriptions()
                ->orderByDesc('created_at')
                ->first();
        }

        if (!$subscription) {
            return true;
        }

        $plan = $subscription->plan;

        if (!$plan) {
            return true; // fallback: sem plano, liberar acesso
        }

        // Verificação de features específicas do plano
        switch ($feature) {
            case 'whatsapp':
                // Verifica campo has_whatsapp do plano OU se tem addon de WhatsApp ativo
                $hasPlanWhatsapp = (bool) ($plan->has_whatsapp ?? false);
                
                $hasAddonWhatsapp = false;
                if (method_exists($subscription, 'addons')) {
                    $hasAddonWhatsapp = $subscription->addons()
                        ->active()
                        ->whatsAppInstances()
                        ->exists();
                }

                return $hasPlanWhatsapp || $hasAddonWhatsapp;

            case 'ai':
            case 'ia':
                // Verifica campo has_ai do plano
                return (bool) ($plan->has_ai ?? false);

            default:
                // Para outros features, verificar na lista de features (array de textos)
                $planFeatures = [];
                if (method_exists($plan, 'getFeaturesListAttribute')) {
                    $planFeatures = $plan->features_list ?? [];
                } elseif (is_array($plan->features)) {
                    $planFeatures = $plan->features;
                } else {
                    $decoded = json_decode($plan->features ?? '[]', true);
                    $planFeatures = is_array($decoded) ? $decoded : [];
                }

                return in_array($feature, $planFeatures, true);
        }
    }
}

