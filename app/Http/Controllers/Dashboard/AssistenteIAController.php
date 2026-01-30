<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Services\AssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssistenteIAController extends Controller
{
    public function __construct(
        private AssistantService $assistant
    ) {}

    public function index(Request $request)
    {
        $assistantName = PaymentSetting::getValue('assistente_ia_nome', 'ChefIA');
        $contextLabels = AssistantService::contextLabels();

        return view('dashboard.assistente-ia.index', [
            'assistantName' => $assistantName,
            'contextLabels' => $contextLabels,
        ]);
    }

    /**
     * POST /assistente-ia/ask — envia pergunta e retorna JSON com resposta do Gemini.
     * Aceita JSON ou form-urlencoded.
     */
    public function ask(Request $request)
    {
        Log::info('AssistenteIA: ask recebido', [
            'has_prompt' => $request->has('prompt'),
            'context' => $request->input('context'),
            'content_type' => $request->header('Content-Type'),
            'has_history' => $request->has('history'),
            'history_raw' => $request->input('history'),
        ]);

        $request->validate([
            'prompt' => 'required|string|max:4000',
            'context' => 'nullable|string|in:default,cardapio,seguranca_alimentar,marketing',
            'history' => 'nullable|string|max:50000', // JSON string com histórico
        ], [
            'prompt.required' => 'Digite sua pergunta.',
            'prompt.max' => 'A pergunta é muito longa.',
        ]);

        $contextType = $request->input('context', 'default');
        
        // Processar histórico se fornecido
        $history = [];
        if ($request->has('history') && !empty($request->input('history'))) {
            try {
                $historyRaw = $request->input('history');
                Log::info('AssistenteIA: processando histórico', [
                    'history_raw_length' => strlen($historyRaw),
                    'history_raw_preview' => substr($historyRaw, 0, 200),
                ]);
                $historyJson = json_decode($historyRaw, true);
                if (is_array($historyJson)) {
                    $history = $historyJson;
                    Log::info('AssistenteIA: histórico processado com sucesso', [
                        'history_count' => count($history),
                        'history_preview' => array_map(function($h) {
                            return ['role' => $h['role'] ?? 'unknown', 'text_preview' => substr($h['text'] ?? '', 0, 50)];
                        }, array_slice($history, 0, 3)),
                    ]);
                } else {
                    Log::warning('AssistenteIA: histórico não é um array válido', ['decoded' => $historyJson]);
                }
            } catch (\Exception $e) {
                Log::warning('AssistenteIA: erro ao processar histórico', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info('AssistenteIA: nenhum histórico fornecido');
        }
        
        $result = $this->assistant->ask(
            $request->input('prompt'),
            $contextType,
            null,
            $history
        );

        if (!$result['ok']) {
            Log::warning('AssistenteIA: ask retornou erro', ['error' => $result['error'] ?? null]);
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Erro ao processar.',
            ], 422);
        }

        $message = $result['message'] ?? '';
        Log::info('AssistenteIA: ask ok', [
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 200) . (strlen($message) > 200 ? '...' : ''),
        ]);
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * GET /assistente-ia/test?prompt=...&context=... — teste de backend (abrir no navegador).
     * Usa a mesma lógica do ask; confirme no log "AssistenteIA: test recebido".
     */
    public function testBackend(Request $request)
    {
        Log::info('AssistenteIA: test recebido', [
            'prompt' => $request->query('prompt'),
            'context' => $request->query('context'),
        ]);

        $prompt = $request->query('prompt', 'Olá');
        $context = $request->query('context', 'default');
        if (!in_array($context, ['default', 'cardapio', 'seguranca_alimentar', 'marketing'], true)) {
            $context = 'default';
        }

        $result = $this->assistant->ask($prompt, $context);

        if (!$result['ok']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Erro'], 422);
        }
        return response()->json(['success' => true, 'message' => $result['message']]);
    }
}
