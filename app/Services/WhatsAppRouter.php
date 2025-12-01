<?php

namespace App\Services;

use App\Models\WhatsappInstance;
use App\Models\Customer;

use Illuminate\Support\Facades\Cache;

class WhatsAppRouter
{
    /**
     * Obtém a próxima instância disponível em rotação (Round Robin)
     * Útil para campanhas em massa para distribuir a carga
     */
    public static function getRotatedInstance()
    {
        // Buscar IDs de todas as instâncias conectadas
        $instances = WhatsappInstance::where('status', 'CONNECTED')->get();
        
        if ($instances->isEmpty()) {
            // Fallback: tentar qualquer uma com URL configurada
            $instances = WhatsappInstance::whereNotNull('api_url')->get();
        }
        
        if ($instances->isEmpty()) {
            return null;
        }

        // Recuperar o último índice usado do cache
        $lastIndex = Cache::get('wa_rotation_index', -1);
        $currentIndex = ($lastIndex + 1) % $instances->count();
        
        // Salvar novo índice
        Cache::put('wa_rotation_index', $currentIndex, 60 * 60); // 1 hora
        
        return $instances[$currentIndex];
    }

    /**
     * Seleciona instância baseada na lógica de segregação de risco
     * 
     * @param Customer $customer
     * @return WhatsappInstance|null
     */
    public static function selectInstanceByRiskSegregation(Customer $customer)
    {
        // 2. Segregação de Risco:
        // Clientes pagantes (Orders > 0) -> Rota Principal (Segura)
        // Leads frios (Sem orders) -> Rota Secundária (Marketing/Risco)
        
        $hasOrders = $customer->orders()->exists();
        
        if ($hasOrders) {
            // Tenta buscar Principal Conectada
            $instance = WhatsappInstance::where('name', 'like', '%Principal%')
                                       ->where('status', 'CONNECTED')
                                       ->first();
            
            // Se não achar conectada, busca qualquer Principal
            if (!$instance) {
                $instance = WhatsappInstance::where('name', 'like', '%Principal%')->first();
            }
        } else {
            // Tenta buscar Secundario Conectada
            $instance = WhatsappInstance::where('name', 'like', '%Secundario%')
                                       ->where('status', 'CONNECTED')
                                       ->first();
                                       
            // Se não achar conectada, busca qualquer Secundario
            if (!$instance) {
                $instance = WhatsappInstance::where('name', 'like', '%Secundario%')->first();
            }
        }

        // 3. Fallback: Pega qualquer uma online
        if (!$instance) {
            $instance = WhatsappInstance::where('status', 'CONNECTED')->first();
        }
        
        // 4. Último Fallback: Pega qualquer uma que tenha URL configurada
        if (!$instance) {
            $instance = WhatsappInstance::whereNotNull('api_url')->first();
        }
        
        return $instance;
    }

    /**
     * Método principal: retorna a instância adequada para o cliente
     * 
     * @param Customer $customer
     * @return WhatsappInstance|null
     */
    public static function getInstanceForCustomer(Customer $customer)
    {
        // 1. Sticky Session: Se o cliente já tem um "gerente de conta" (número preferido), usa ele.
        if ($customer->preferred_gateway_phone) {
            $instance = WhatsappInstance::where('phone_number', $customer->preferred_gateway_phone)
                                        ->where('status', 'CONNECTED')
                                        ->first();
            
            // Se não estiver conectado, tenta buscar mesmo assim (pode ser bug de status)
            if (!$instance) {
                $instance = WhatsappInstance::where('phone_number', $customer->preferred_gateway_phone)->first();
            }

            if ($instance) {
                return $instance;
            }
        }

        // 2. Segregação de Risco
        return self::selectInstanceByRiskSegregation($customer);
    }
}

