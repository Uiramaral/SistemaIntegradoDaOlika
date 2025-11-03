<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionalWhatsAppTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Templates adicionais fornecidos
        $extraTemplates = [
            [
                'slug' => 'order_pending',
                'content' => '🚚 Olá {nome}! Seu pedido #{pedido} foi recebido e está sendo processado. Aguardamos seu pagamento para iniciar a preparação!',
                'active' => 1,
            ],
            [
                'slug' => 'order_confirmed',
                'content' => '✅ Olá {nome}! Seu pedido #{pedido} foi confirmado. Estamos preparando seus itens com muito carinho! 🥖',
                'active' => 1,
            ],
            [
                'slug' => 'order_ready',
                'content' => '📦 Olá {nome}! Seu pedido #{pedido} está pronto e está sendo enviado! Chegando em breve!',
                'active' => 1,
            ],
            [
                'slug' => 'order_delivered',
                'content' => '🎉 Olá {nome}! Seu pedido #{pedido} foi entregue! Obrigada por confiar na Olika! Volte sempre!',
                'active' => 1,
            ],
        ];

        foreach ($extraTemplates as $tpl) {
            DB::table('whatsapp_templates')->updateOrInsert(
                ['slug' => $tpl['slug']],
                [
                    'content' => $tpl['content'],
                    'active' => $tpl['active'],
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        // Vincular (se desejar) alguns códigos padrão de status aos novos slugs
        // Ajuste conforme os códigos existentes na sua base (coluna order_statuses.code)
        $map = [
            'pending'           => 'order_pending',
            'paid'              => 'order_confirmed',
            'preparing'         => 'em_preparo',            // já existe no seeder principal
            'out_for_delivery'  => 'saiu_para_entrega',     // já existe no seeder principal
            'delivered'         => 'order_delivered',
        ];

        foreach ($map as $statusCode => $templateSlug) {
            $tplId = DB::table('whatsapp_templates')->where('slug', $templateSlug)->value('id');
            if ($tplId) {
                DB::table('order_statuses')
                    ->where('code', $statusCode)
                    ->update([
                        'whatsapp_template_id' => $tplId,
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}


