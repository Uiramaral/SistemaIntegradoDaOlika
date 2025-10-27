<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsAppTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'slug' => 'pedido_recebido',
                'content' => "🧾 *Pedido recebido!*\nOlá, {nome}! Recebemos seu pedido *#{pedido}*. Assim que o pagamento for confirmado, começamos o preparo. 💛",
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'pagamento_aprovado',
                'content' => "✅ *Pagamento confirmado!*\n{nome}, seu pedido *#{pedido}* está confirmado.\nTotal: R$ {valor}\nEm breve avisaremos sobre a entrega. 🥖",
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'em_preparo',
                'content' => "👩‍🍳 *Seu pedido está em preparo!*\nPedido *#{pedido}* já está sendo produzido com carinho.",
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'saiu_para_entrega',
                'content' => "🛵 *Saiu para entrega!*\nPedido *#{pedido}* está a caminho. Fique atento ao telefone 😉",
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'entregue',
                'content' => "🎉 *Pedido entregue!*\nEsperamos que você goste! Qualquer feedback é muito bem-vindo 🙌",
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'cancelado',
                'content' => "⚠️ *Pedido cancelado.*\nPedido *#{pedido}* foi cancelado. Se foi engano, chame a gente por aqui.",
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('whatsapp_templates')->insertOrIgnore($template);
        }

        // Buscar IDs dos templates criados
        $pedidoPagoId = DB::table('whatsapp_templates')->where('slug', 'pagamento_aprovado')->value('id');
        $emPreparoId = DB::table('whatsapp_templates')->where('slug', 'em_preparo')->value('id');
        $saiuId = DB::table('whatsapp_templates')->where('slug', 'saiu_para_entrega')->value('id');
        $entregueId = DB::table('whatsapp_templates')->where('slug', 'entregue')->value('id');
        $canceladoId = DB::table('whatsapp_templates')->where('slug', 'cancelado')->value('id');

        // Criar status padrão
        $statuses = [
            [
                'code' => 'pending',
                'name' => 'Aguardando Revisão',
                'is_final' => 0,
                'notify_customer' => 0,
                'notify_admin' => 1,
                'whatsapp_template_id' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'waiting_payment',
                'name' => 'Aguardando Pagamento',
                'is_final' => 0,
                'notify_customer' => 0,
                'notify_admin' => 0,
                'whatsapp_template_id' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'paid',
                'name' => 'Pago/Confirmado',
                'is_final' => 0,
                'notify_customer' => 1,
                'notify_admin' => 1,
                'whatsapp_template_id' => $pedidoPagoId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'preparing',
                'name' => 'Em Preparo',
                'is_final' => 0,
                'notify_customer' => 1,
                'notify_admin' => 0,
                'whatsapp_template_id' => $emPreparoId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'out_for_delivery',
                'name' => 'Saiu para Entrega',
                'is_final' => 0,
                'notify_customer' => 1,
                'notify_admin' => 0,
                'whatsapp_template_id' => $saiuId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'delivered',
                'name' => 'Entregue',
                'is_final' => 1,
                'notify_customer' => 1,
                'notify_admin' => 0,
                'whatsapp_template_id' => $entregueId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'cancelled',
                'name' => 'Cancelado',
                'is_final' => 1,
                'notify_customer' => 1,
                'notify_admin' => 1,
                'whatsapp_template_id' => $canceladoId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('order_statuses')->insertOrIgnore($status);
        }
    }
}

