<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BotConversaController extends Controller
{
    /**
     * Endpoint de teste/health check
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function test()
    {
        try {
            // Teste básico - verificar se o Laravel está funcionando
            $status = [
                'status' => 'ok',
                'message' => 'API BotConversa está funcionando',
                'timestamp' => date('Y-m-d H:i:s'),
                'endpoints' => [
                    'POST /api/botconversa/sync-customer' => 'Sincronizar cliente individual',
                    'POST /api/botconversa/sync-customers' => 'Sincronizar múltiplos clientes',
                ],
            ];

            // Testar conexão com banco de dados
            try {
                DB::connection()->getPdo();
                $status['database'] = 'connected';
            } catch (\Exception $e) {
                $status['database'] = 'error: ' . $e->getMessage();
            }

            // Testar modelo Customer
            try {
                $customerCount = Customer::count();
                $status['customer_model'] = 'ok (count: ' . $customerCount . ')';
            } catch (\Exception $e) {
                $status['customer_model'] = 'error: ' . $e->getMessage();
            }

            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar requisição: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Recebe dados de clientes do BotConversa e salva no banco de dados
     * 
     * Esta rota está liberada do CSRF e aceita requisições POST com JSON
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncCustomer(Request $request)
    {
        // Verificar se é POST
        if (!$request->isMethod('post')) {
            return response()->json([
                'success' => false,
                'message' => 'Método não permitido. Use POST.',
                'method_received' => $request->method(),
                'expected_method' => 'POST',
                'hint' => 'Esta rota aceita apenas requisições POST com JSON no body',
            ], 405);
        }

        try {
            // Log da requisição recebida
            Log::info('BotConversa: Sincronização de cliente recebida', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
            ]);

            // Validar dados recebidos
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|max:20',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'newsletter' => 'nullable|boolean',
                
                // Dados opcionais
                'visitor_id' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'neighborhood' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:2',
                'zip_code' => 'nullable|string|max:10',
                'birth_date' => 'nullable|date',
                'cpf' => 'nullable|string|max:14',
                'preferences' => 'nullable|array',
                
                // Datas especiais
                'created_at' => 'nullable|date',
                'last_order_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                Log::warning('BotConversa: Validação falhou', [
                    'errors' => $validator->errors()->toArray(),
                    'payload' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            // Normalizar telefone (remover caracteres especiais)
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);

            // Iniciar transação
            DB::beginTransaction();

            try {
                // Verificar se o cliente já existe pelo telefone
                $customer = Customer::where('phone', $phone)->first();

                if ($customer) {
                    // Atualizar cliente existente
                    $updateData = [
                        'name' => $data['name'],
                        'newsletter' => $data['newsletter'] ?? false,
                    ];

                    // Atualizar campos opcionais se fornecidos
                    if (isset($data['email'])) {
                        $updateData['email'] = $data['email'];
                    }
                    if (isset($data['visitor_id'])) {
                        $updateData['visitor_id'] = $data['visitor_id'];
                    }
                    if (isset($data['address'])) {
                        $updateData['address'] = $data['address'];
                    }
                    if (isset($data['neighborhood'])) {
                        $updateData['neighborhood'] = $data['neighborhood'];
                    }
                    if (isset($data['city'])) {
                        $updateData['city'] = $data['city'];
                    }
                    if (isset($data['state'])) {
                        $updateData['state'] = $data['state'];
                    }
                    if (isset($data['zip_code'])) {
                        $updateData['zip_code'] = $data['zip_code'];
                    }
                    if (isset($data['birth_date'])) {
                        $updateData['birth_date'] = $data['birth_date'];
                    }
                    if (isset($data['cpf'])) {
                        $updateData['cpf'] = $data['cpf'];
                    }
                    if (isset($data['preferences'])) {
                        $updateData['preferences'] = $data['preferences'];
                    }
                    
                    // Atualizar data do último pedido se fornecida
                    if (isset($data['last_order_at'])) {
                        $updateData['last_order_at'] = $data['last_order_at'];
                    }
                    
                    // Atualizar data de cadastro se fornecida (usando DB::table para permitir atualização)
                    $updateCreatedAt = false;
                    $createdAtValue = null;
                    if (isset($data['created_at'])) {
                        $updateCreatedAt = true;
                        $createdAtValue = $data['created_at'];
                    }

                    // Atualizar campos normais
                    $customer->update($updateData);
                    
                    // Atualizar created_at diretamente via DB se fornecido
                    if ($updateCreatedAt) {
                        DB::table('customers')
                            ->where('id', $customer->id)
                            ->update(['created_at' => $createdAtValue]);
                        
                        // Recarregar o modelo para refletir a mudança
                        $customer->refresh();
                    }

                    Log::info('BotConversa: Cliente atualizado com sucesso', [
                        'customer_id' => $customer->id,
                        'phone' => $phone,
                        'updated_fields' => array_keys($updateData),
                    ]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Cliente atualizado com sucesso',
                        'action' => 'updated',
                        'customer' => [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'newsletter' => $customer->newsletter,
                            'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : null,
                            'last_order_at' => $customer->last_order_at ? $customer->last_order_at->format('Y-m-d H:i:s') : null,
                        ],
                    ], 200);
                } else {
                    // Criar novo cliente
                    $createData = [
                        'phone' => $phone,
                        'name' => $data['name'],
                        'newsletter' => $data['newsletter'] ?? false,
                        'is_active' => true,
                    ];

                    // Adicionar campos opcionais se fornecidos
                    if (isset($data['email'])) {
                        $createData['email'] = $data['email'];
                    }
                    if (isset($data['visitor_id'])) {
                        $createData['visitor_id'] = $data['visitor_id'];
                    }
                    if (isset($data['address'])) {
                        $createData['address'] = $data['address'];
                    }
                    if (isset($data['neighborhood'])) {
                        $createData['neighborhood'] = $data['neighborhood'];
                    }
                    if (isset($data['city'])) {
                        $createData['city'] = $data['city'];
                    }
                    if (isset($data['state'])) {
                        $createData['state'] = $data['state'];
                    }
                    if (isset($data['zip_code'])) {
                        $createData['zip_code'] = $data['zip_code'];
                    }
                    if (isset($data['birth_date'])) {
                        $createData['birth_date'] = $data['birth_date'];
                    }
                    if (isset($data['cpf'])) {
                        $createData['cpf'] = $data['cpf'];
                    }
                    if (isset($data['preferences'])) {
                        $createData['preferences'] = $data['preferences'];
                    }
                    
                    // Adicionar data do último pedido se fornecida
                    if (isset($data['last_order_at'])) {
                        $createData['last_order_at'] = $data['last_order_at'];
                    }
                    
                    // Adicionar data de cadastro se fornecida
                    $createdAtValue = null;
                    if (isset($data['created_at'])) {
                        $createdAtValue = $data['created_at'];
                    }

                    // Criar cliente
                    // Se created_at foi fornecido, desabilitar timestamps temporariamente e usar DB::table
                    if ($createdAtValue !== null) {
                        $customer = new Customer($createData);
                        $customer->timestamps = false;
                        $customer->save();
                        
                        // Atualizar created_at diretamente via DB
                        DB::table('customers')
                            ->where('id', $customer->id)
                            ->update(['created_at' => $createdAtValue]);
                        
                        // Restaurar timestamps e recarregar
                        $customer->timestamps = true;
                        $customer->refresh();
                    } else {
                        $customer = Customer::create($createData);
                    }

                    Log::info('BotConversa: Cliente criado com sucesso', [
                        'customer_id' => $customer->id,
                        'phone' => $phone,
                        'created_fields' => array_keys($createData),
                    ]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Cliente criado com sucesso',
                        'action' => 'created',
                        'customer' => [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'newsletter' => $customer->newsletter,
                            'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : null,
                            'last_order_at' => $customer->last_order_at ? $customer->last_order_at->format('Y-m-d H:i:s') : null,
                        ],
                    ], 201);
                }
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('BotConversa: Erro ao salvar cliente', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'payload' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar cliente: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('BotConversa: Erro geral na sincronização', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            // Em desenvolvimento, retornar mais detalhes do erro
            $errorMessage = 'Erro interno do servidor';
            if (config('app.debug')) {
                $errorMessage .= ': ' . $e->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Endpoint para receber múltiplos clientes de uma vez (batch)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncCustomersBatch(Request $request)
    {
        // Verificar se é POST
        if (!$request->isMethod('post')) {
            return response()->json([
                'success' => false,
                'message' => 'Método não permitido. Use POST.',
                'method_received' => $request->method(),
                'expected_method' => 'POST',
                'hint' => 'Esta rota aceita apenas requisições POST com JSON no body',
            ], 405);
        }

        try {
            // Validar que é um array de clientes
            $validator = Validator::make($request->all(), [
                'customers' => 'required|array|min:1|max:100',
                'customers.*.phone' => 'required|string|max:20',
                'customers.*.name' => 'required|string|max:255',
                'customers.*.email' => 'nullable|email|max:255',
                'customers.*.newsletter' => 'nullable|boolean',
                'customers.*.created_at' => 'nullable|date',
                'customers.*.last_order_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $customers = $request->input('customers');
            $results = [
                'created' => 0,
                'updated' => 0,
                'errors' => 0,
                'details' => [],
            ];

            DB::beginTransaction();

            try {
                foreach ($customers as $index => $customerData) {
                    try {
                        // Normalizar telefone
                        $phone = preg_replace('/[^0-9]/', '', $customerData['phone']);

                        // Verificar se existe
                        $customer = Customer::where('phone', $phone)->first();

                        $data = [
                            'name' => $customerData['name'],
                            'newsletter' => $customerData['newsletter'] ?? false,
                        ];

                        if (isset($customerData['email'])) {
                            $data['email'] = $customerData['email'];
                        }
                        if (isset($customerData['visitor_id'])) {
                            $data['visitor_id'] = $customerData['visitor_id'];
                        }
                        if (isset($customerData['last_order_at'])) {
                            $data['last_order_at'] = $customerData['last_order_at'];
                        }
                        
                        $updateCreatedAt = isset($customerData['created_at']);
                        $createdAtValue = $updateCreatedAt ? $customerData['created_at'] : null;

                        if ($customer) {
                            $customer->update($data);
                            
                            // Atualizar created_at se fornecido
                            if ($updateCreatedAt) {
                                DB::table('customers')
                                    ->where('id', $customer->id)
                                    ->update(['created_at' => $createdAtValue]);
                                $customer->refresh();
                            }
                            
                            $results['updated']++;
                            $results['details'][] = [
                                'index' => $index,
                                'phone' => $phone,
                                'action' => 'updated',
                                'customer_id' => $customer->id,
                            ];
                        } else {
                            $data['phone'] = $phone;
                            $data['is_active'] = true;
                            
                            // Criar com created_at se fornecido
                            if ($createdAtValue !== null) {
                                $newCustomer = new Customer($data);
                                $newCustomer->timestamps = false;
                                $newCustomer->save();
                                
                                DB::table('customers')
                                    ->where('id', $newCustomer->id)
                                    ->update(['created_at' => $createdAtValue]);
                                
                                $newCustomer->timestamps = true;
                                $newCustomer->refresh();
                            } else {
                                $newCustomer = Customer::create($data);
                            }
                            
                            $results['created']++;
                            $results['details'][] = [
                                'index' => $index,
                                'phone' => $phone,
                                'action' => 'created',
                                'customer_id' => $newCustomer->id,
                            ];
                        }
                    } catch (\Exception $e) {
                        $results['errors']++;
                        $results['details'][] = [
                            'index' => $index,
                            'phone' => $customerData['phone'] ?? 'unknown',
                            'action' => 'error',
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                DB::commit();

                Log::info('BotConversa: Sincronização em lote concluída', [
                    'total' => count($customers),
                    'created' => $results['created'],
                    'updated' => $results['updated'],
                    'errors' => $results['errors'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Sincronização em lote concluída',
                    'results' => $results,
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar lote: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('BotConversa: Erro na sincronização em lote', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }
}

