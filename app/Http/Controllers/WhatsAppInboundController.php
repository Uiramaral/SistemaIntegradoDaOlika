<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\AIResponderService;
use App\Services\WhatsAppService;

class WhatsAppInboundController extends Controller
{
    public function receive(Request $r)
    {
        // 1) autenticação simples opcional: um secret no query ou header vindo da Evolution (se configurado)
        // if($r->header('X-Webhook-Secret') !== env('WA_WEBHOOK_SECRET')) return response()->json(['ok'=>false],403);

        $payload = $r->all();
        Log::info('WA inbound', ['payload'=>$payload]);

        // Evolution costuma enviar 'message' com 'from' e 'text' (pode variar por versão)
        $from = data_get($payload, 'data.key.remoteJid') ?: data_get($payload,'message.from') ?: data_get($payload,'from');
        $text = data_get($payload, 'data.message.conversation') ?: data_get($payload,'message.text') ?: data_get($payload,'text');

        if(!$from || !$text) return response()->json(['ok'=>true]); // nada a fazer

        // extrai dígitos do número
        $digits = preg_replace('/\D/','', $from);

        // PALAVRAS-CHAVE para handoff manual
        $cmd = strtolower(trim($text));
        if(in_array($cmd, ['humano','atendente','suporte','parar','stop'])){
            // marcar cliente p/ atendimento humano, opcionalmente avisar admin
            $admin = DB::table('whatsapp_settings')->where('active',1)->value('admin_phone');
            if($admin){
                (new WhatsAppService())->sendText($admin, "📣 Handoff solicitado por +{$digits}. Atender manualmente.");
            }
            return response()->json(['ok'=>true]);
        }

        $ai = new AIResponderService();
        if(!$ai->isEnabled()) return response()->json(['ok'=>true]); // IA desligada

        // 2) contexto (cliente + último pedido)
        $context = $ai->buildContextForPhone($digits);

        // 3) gera resposta
        $answer = $ai->reply($text, $context);
        if(!$answer) return response()->json(['ok'=>true]);

        // 4) envia resposta
        (new WhatsAppService())->sendText($digits, $answer);

        return response()->json(['ok'=>true]);
    }
}
