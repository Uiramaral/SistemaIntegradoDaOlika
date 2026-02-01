<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ProductionList;
use App\Models\ProductionListItem;
use App\Models\Recipe;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderProductionObserver
{
    /**
     * Quando o pedido é pago ou confirmado, adicionar itens à lista de produção.
     */
    public function updated(Order $order): void
    {
        // Se o pedido foi cancelado, remover os itens da produção
        if ($order->wasChanged('status') && in_array($order->status, ['cancelled', 'canceled'])) {
            $this->removeOrderFromProduction($order);
            return;
        }

        // Se o status do pedido mudou para 'confirmed' ou o status do pagamento mudou para 'paid'/'approved'
        $statusChangedToConfirmed = $order->wasChanged('status') && $order->status === 'confirmed';
        $paymentChangedToPaid = $order->wasChanged('payment_status') && in_array(strtolower($order->payment_status), ['paid', 'approved']);

        if ($statusChangedToConfirmed || $paymentChangedToPaid) {
            $this->syncOrderWithProduction($order);
        }
    }

    /**
     * Sincroniza os itens do pedido com a lista de produção.
     */
    private function syncOrderWithProduction(Order $order): void
    {
        // Segurança: Verificar se a coluna order_item_id existe no banco para evitar erro SQL
        if (!Schema::hasColumn('production_list_items', 'order_item_id')) {
            Log::warning('OrderProductionObserver: Coluna order_item_id não encontrada. Pulei a sincronização para evitar erro.');
            return;
        }

        // Só processar se tiver data de entrega programada
        if (!$order->scheduled_delivery_at) {
            Log::warning('OrderProductionObserver: Pedido sem data de entrega programada.', ['order_id' => $order->id]);
            return;
        }

        // A produção deve ocorrer no dia anterior à entrega (D-1)
        $deliveryDate = Carbon::parse($order->scheduled_delivery_at);
        $productionDate = $deliveryDate->copy()->subDay();

        // Formato Y-m-d para busca no banco
        $productionDateStr = $productionDate->format('Y-m-d');
        $clientId = $order->client_id;

        Log::info("OrderProductionObserver: Sincronizando pedido #{$order->order_number} para produção em {$productionDateStr}");

        $order->loadMissing('items.product');
        Log::info("OrderProductionObserver: Pedido tem " . $order->items->count() . " itens para processar.");

        // 1. Garantir que existe uma lista de produção para essa data
        $productionList = ProductionList::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->whereDate('production_date', $productionDateStr)
            ->first();

        if (!$productionList) {
            $productionList = ProductionList::create([
                'client_id' => $clientId,
                'production_date' => $productionDateStr,
                'status' => 'active',
                'notes' => "Gerada automaticamente a partir de pedidos para entrega em {$deliveryDate->format('d/m/Y')}"
            ]);
        }

        // 2. Processar cada item do pedido
        foreach ($order->items as $item) {
            // Se o item não tem produto_id, ignorar (item avulso sem receita)
            if (!$item->product_id) {
                Log::info("OrderProductionObserver: Item #{$item->id} ignorado (sem product_id)");
                continue;
            }

            // 3. Tentar encontrar uma receita para o produto ou variante
            $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                ->where(function ($q) use ($clientId) {
                    $q->where('client_id', $clientId)->orWhereNull('client_id');
                })
                ->where('product_id', $item->product_id)
                ->where(function ($q) use ($item) {
                    if ($item->variant_id) {
                        $q->where('variant_id', $item->variant_id)->orWhereNull('variant_id');
                    } else {
                        $q->whereNull('variant_id');
                    }
                })
                ->orderByRaw('variant_id IS NOT NULL DESC') // Prefere receita da variante específica
                ->first();

            if (!$recipe) {
                Log::info("OrderProductionObserver: Nenhuma receita encontrada para o produto {$item->product_id}", [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'client_id' => $clientId,
                    'variant_id' => $item->variant_id
                ]);
                continue;
            }

            Log::info("OrderProductionObserver: Receita encontrada '{$recipe->name}' para item #{$item->id}");

            // 4. Verificar se o item já está na produção (idempotência via order_item_id)
            $existingItem = ProductionListItem::where('production_list_id', $productionList->id)
                ->where('order_item_id', $item->id)
                ->first();

            if ($existingItem) {
                // Atualizar se necessário (ex: mudou quantidade no pedido)
                $existingItem->update([
                    'quantity' => $item->quantity,
                    'recipe_name' => $recipe->name,
                    'weight' => $recipe->total_weight,
                    'observation' => "Pedido #{$order->order_number}" . ($item->special_instructions ? " | " . $item->special_instructions : "")
                ]);
                Log::info("OrderProductionObserver: Item de produção atualizado (ID: {$existingItem->id})");
            } else {
                // Criar novo item de produção
                $newItem = ProductionListItem::create([
                    'production_list_id' => $productionList->id,
                    'recipe_id' => $recipe->id,
                    'order_item_id' => $item->id,
                    'recipe_name' => $recipe->name,
                    'quantity' => $item->quantity,
                    'weight' => $recipe->total_weight,
                    'observation' => "Pedido #{$order->order_number}" . ($item->special_instructions ? " | " . $item->special_instructions : ""),
                    'mark_for_print' => true,
                    'sort_order' => ($productionList->items()->max('sort_order') ?? 0) + 1,
                ]);
                Log::info("OrderProductionObserver: Novo item de produção criado (ID: {$newItem->id})");
            }
        }
    }

    /**
     * Remove os itens do pedido da produção se o pedido for cancelado.
     */
    private function removeOrderFromProduction(Order $order): void
    {
        $itemIds = $order->items->pluck('id');

        $deletedCount = ProductionListItem::whereIn('order_item_id', $itemIds)->delete();

        if ($deletedCount > 0) {
            Log::info("OrderProductionObserver: Removidos {$deletedCount} itens de produção devido ao cancelamento do pedido #{$order->order_number}");
        }
    }
}
