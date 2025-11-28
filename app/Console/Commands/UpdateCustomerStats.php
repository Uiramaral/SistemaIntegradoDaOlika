<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\CustomerCashback;
use Illuminate\Support\Facades\DB;

class UpdateCustomerStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-stats 
                            {--customer-id= : Atualizar apenas um cliente específico (ID)}
                            {--dry-run : Apenas mostrar o que seria atualizado, sem salvar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza estatísticas históricas dos clientes (total_orders, total_spent, last_order_at, loyalty_balance)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando atualização de estatísticas dos clientes...');
        $this->newLine();

        $customerId = $this->option('customer-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: Nenhuma alteração será salva no banco de dados.');
            $this->newLine();
        }

        // Query para buscar clientes
        $query = Customer::query();
        
        if ($customerId) {
            $query->where('id', $customerId);
            $customer = $query->first();
            if (!$customer) {
                $this->error("❌ Cliente com ID {$customerId} não encontrado.");
                return 1;
            }
            $customers = collect([$customer]);
        } else {
            $customers = $query->get();
        }

        $totalCustomers = $customers->count();
        $this->info("📊 Encontrados {$totalCustomers} cliente(s) para processar.");
        $this->newLine();

        $bar = $this->output->createProgressBar($totalCustomers);
        $bar->start();

        $updated = 0;
        $errors = 0;
        $summary = [];

        foreach ($customers as $customer) {
            try {
                // Buscar pedidos pagos do cliente
                $paidOrders = $customer->orders()
                    ->whereIn('payment_status', ['approved', 'paid'])
                    ->get();

                $totalOrders = $paidOrders->count();
                $totalSpent = $paidOrders->sum('final_amount');

                // Buscar último pedido pago
                $lastOrder = $customer->orders()
                    ->whereIn('payment_status', ['approved', 'paid'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Calcular saldo de cashback
                $loyaltyBalance = CustomerCashback::getBalance($customer->id);

                // Preparar dados para atualização
                $oldStats = [
                    'total_orders' => $customer->total_orders ?? 0,
                    'total_spent' => $customer->total_spent ?? 0,
                    'last_order_at' => $customer->last_order_at,
                    'loyalty_balance' => $customer->loyalty_balance ?? 0,
                ];

                $newStats = [
                    'total_orders' => $totalOrders,
                    'total_spent' => $totalSpent,
                    'last_order_at' => $lastOrder ? $lastOrder->created_at : null,
                    'loyalty_balance' => $loyaltyBalance,
                ];

                // Verificar se há mudanças
                $hasChanges = false;
                foreach ($oldStats as $key => $oldValue) {
                    if ($oldValue != $newStats[$key]) {
                        $hasChanges = true;
                        break;
                    }
                }

                if ($hasChanges) {
                    if (!$dryRun) {
                        // Atualizar no banco
                        $customer->total_orders = $newStats['total_orders'];
                        $customer->total_spent = $newStats['total_spent'];
                        $customer->last_order_at = $newStats['last_order_at'];
                        $customer->loyalty_balance = $newStats['loyalty_balance'];
                        $customer->save();
                    }

                    $updated++;
                    
                    $summary[] = [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'old' => $oldStats,
                        'new' => $newStats,
                    ];
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("❌ Erro ao processar cliente ID {$customer->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resumo
        $this->info('✅ Processamento concluído!');
        $this->newLine();

        if ($dryRun) {
            $this->warn("📋 Modo DRY-RUN: {$updated} cliente(s) teriam estatísticas atualizadas.");
        } else {
            $this->info("✅ {$updated} cliente(s) atualizado(s) com sucesso.");
        }

        if ($errors > 0) {
            $this->error("❌ {$errors} erro(s) encontrado(s).");
        }

        // Mostrar detalhes se houver atualizações e se for modo verbose ou cliente específico
        if ($updated > 0 && ($this->option('verbose') || $customerId)) {
            $this->newLine();
            $this->info('📊 Detalhes das atualizações:');
            $this->newLine();

            $tableData = [];
            foreach ($summary as $item) {
                $changes = [];
                foreach ($item['old'] as $key => $oldVal) {
                    if ($oldVal != $item['new'][$key]) {
                        $oldFormatted = is_numeric($oldVal) 
                            ? ($key === 'total_spent' || $key === 'loyalty_balance' 
                                ? 'R$ ' . number_format($oldVal, 2, ',', '.') 
                                : number_format($oldVal, 0, ',', '.'))
                            : ($oldVal ? $oldVal->format('d/m/Y H:i') : 'N/A');
                        
                        $newFormatted = is_numeric($item['new'][$key]) 
                            ? ($key === 'total_spent' || $key === 'loyalty_balance' 
                                ? 'R$ ' . number_format($item['new'][$key], 2, ',', '.') 
                                : number_format($item['new'][$key], 0, ',', '.'))
                            : ($item['new'][$key] ? $item['new'][$key]->format('d/m/Y H:i') : 'N/A');
                        
                        $changes[] = "{$key}: {$oldFormatted} → {$newFormatted}";
                    }
                }
                
                $tableData[] = [
                    'ID' => $item['id'],
                    'Nome' => $item['name'],
                    'Mudanças' => implode(' | ', $changes),
                ];
            }

            $this->table(['ID', 'Nome', 'Mudanças'], $tableData);
        }

        return 0;
    }
}

