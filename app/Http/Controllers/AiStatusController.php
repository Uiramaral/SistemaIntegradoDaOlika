<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiStatusController extends Controller
{
    /**
     * Verifica se a IA está habilitada para um número de telefone específico
     * 
     * Endpoint: POST /api/ai-status
     * Body: { "phone": "5571987019420" }
     * Headers: X-API-Token: {token}
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        // Autenticação por token
        $token = $request->header('X-API-Token');
        $validToken = env('API_SECRET') ?? env('WEBHOOK_TOKEN') ?? env('WH_API_TOKEN');
        
        if (empty($validToken)) {
            Log::error('AiStatusController: Token de validação não configurado no .env');
            return response()->json([
                'status' => 'disabled',
                'reason' => 'Server_Configuration_Error'
            ], 500);
        }
        
        if ($token !== $validToken) {
            Log::warning('AiStatusController: Tentativa de acesso não autorizado', [
                'ip' => $request->ip(),
                'token_recebido' => $token ? '***' . substr($token, -4) : 'não fornecido'
            ]);
            return response()->json([
                'status' => 'disabled',
                'reason' => 'Unauthorized'
            ], 403);
        }
        
        // Obter número de telefone do corpo da requisição (POST)
        $phone = $request->input('phone');
        
        if (empty($phone)) {
            return response()->json([
                'status' => 'disabled',
                'reason' => 'Phone_Not_Provided'
            ], 400);
        }
        
        // Limpar número (apenas dígitos)
        $phoneDigits = preg_replace('/\D/', '', $phone);
        
        try {
            // 1. Verificar flag global de IA na tabela whatsapp_settings
            $settings = DB::table('whatsapp_settings')->where('active', 1)->first();
            
            if (!$settings) {
                Log::warning('AiStatusController: Nenhuma configuração ativa encontrada');
                return response()->json([
                    'status' => 'disabled',
                    'reason' => 'No_Active_Configuration'
                ]);
            }
            
            // Verificar se existe coluna ai_enabled (pode não existir em instalações antigas)
            $hasAiEnabled = DB::getSchemaBuilder()->hasColumn('whatsapp_settings', 'ai_enabled');
            
            if ($hasAiEnabled) {
                $globalAiEnabled = (bool)($settings->ai_enabled ?? false);
            } else {
                // Se a coluna não existir, assumir que está desabilitado por padrão
                $globalAiEnabled = false;
                Log::info('AiStatusController: Coluna ai_enabled não encontrada, assumindo desabilitado');
            }
            
            // 2. Se o flag global estiver desabilitado, retornar disabled
            if (!$globalAiEnabled) {
                return response()->json([
                    'status' => 'disabled',
                    'reason' => 'Global_Kill_Switch'
                ]);
            }
            
            // 3. Verificar lista de exceções (tabela ai_exceptions se existir)
            $hasExceptionsTable = DB::getSchemaBuilder()->hasTable('ai_exceptions');
            
            if ($hasExceptionsTable) {
                // Limpar exceções expiradas primeiro
                DB::table('ai_exceptions')
                    ->where('expires_at', '<', now())
                    ->update(['active' => false]);
                
                // Verificar se existe exceção ativa e não expirada
                $exception = DB::table('ai_exceptions')
                    ->where('phone', $phoneDigits)
                    ->where('active', true)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->first();
                
                if ($exception) {
                    return response()->json([
                        'status' => 'disabled',
                        'reason' => 'Exception_List',
                        'exception_reason' => $exception->reason ?? 'unknown'
                    ]);
                }
            }
            
            // 4. Se passou todas as verificações, IA está habilitada
            return response()->json([
                'status' => 'enabled'
            ]);
            
        } catch (\Exception $e) {
            Log::error('AiStatusController: Erro ao verificar status da IA', [
                'error' => $e->getMessage(),
                'phone' => $phoneDigits
            ]);
            
            // Em caso de erro, desabilitar por segurança
            return response()->json([
                'status' => 'disabled',
                'reason' => 'Internal_Error'
            ], 500);
        }
    }
}

