<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderRating;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdersController extends Controller
{
    private const OTP_TTL_MINUTES = 5;
    private const OTP_THROTTLE_SECONDS = 60;
    private const VERIFIED_SESSION_TTL_MINUTES = 15;

    /**
     * Lista pedidos do cliente
     */
    public function index(Request $request)
    {
        $rawPhone = $request->get('phone') ?: $request->cookie('customer_phone');
        $phone = $this->normalizePhone($rawPhone);
        $otpState = $this->consumeOtpFlash();

        if (!$phone) {
            return view('customer.orders.login', array_merge($otpState, [
                'phoneValue' => $rawPhone,
            ]));
        }

        cookie()->queue('customer_phone', $phone, 60 * 24 * 30);

        if (!$this->hasVerifiedAccess($phone, $request)) {
            $otp = $request->get('otp');

            if (!$otp) {
                return view('customer.orders.login', array_merge($otpState, [
                    'phoneValue' => $rawPhone ?? $phone,
                    'needsOtp' => true,
                ]));
            }

            if (!$this->verifyOtpCode($phone, $otp, $request)) {
                Session::flash('customer_orders.otp_error', 'Código inválido ou expirado. Solicite um novo código.');
                return redirect()->route('customer.orders.index', ['phone' => $phone]);
            }

            $this->markPhoneAsVerified($phone, $request);
            // Cache::forget($this->otpCacheKey($phone)); // Now using session
            Session::forget('customer_orders.otp_payload');
        }

        $customer = $this->findCustomerByPhone($phone);

        if (!$customer) {
            cookie()->queue(cookie()->forget('customer_phone'));
            return view('customer.orders.login', array_merge($otpState, [
                'phoneValue' => $rawPhone ?? $phone,
                'error' => 'Cliente não encontrado. Verifique o telefone informado.',
            ]));
        }

        // Buscar pedidos do cliente
        $orders = Order::where('customer_id', $customer->id)
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Buscar avaliações existentes
        $orderIds = $orders->pluck('id');
        $ratings = OrderRating::whereIn('order_id', $orderIds)
            ->pluck('rating', 'order_id')
            ->toArray();

        return view('customer.orders.index', compact('orders', 'customer', 'ratings'));
    }

    /**
     * Visualiza detalhes de um pedido
     */
    public function show(Request $request, $orderNumber)
    {
        // Buscar telefone: primeiro da query string, depois do cookie
        $phone = $request->get('phone') ?: $request->cookie('customer_phone');
        $normalized = $this->normalizePhone($phone);

        if (!$normalized) {
            return redirect()->route('customer.orders.index')->with('error', 'Telefone necessário para acessar.');
        }

        if (!$this->hasVerifiedAccess($normalized, $request)) {
            return redirect()->route('customer.orders.index', ['phone' => $normalized])
                ->with('error', 'Confirme o código enviado para visualizar seus pedidos.');
        }

        $customer = $this->findCustomerByPhone($normalized);

        if (!$customer) {
            cookie()->queue(cookie()->forget('customer_phone'));
            return redirect()->route('customer.orders.index')->with('error', 'Cliente não encontrado.');
        }

        // Buscar pedido
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $customer->id)
            ->with(['customer', 'items.product', 'payment', 'address'])
            ->firstOrFail();

        // Histórico de status
        $statusHistory = DB::table('order_status_history')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Verificar se já foi avaliado
        $rating = OrderRating::where('order_id', $order->id)->first();

        return view('customer.orders.show', compact('order', 'statusHistory', 'customer', 'rating'));
    }

    /**
     * Avalia um pedido
     */
    public function rate(Request $request, $orderNumber)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Buscar telefone: primeiro da query string, depois do cookie
        $phone = $request->get('phone') ?: $request->cookie('customer_phone');
        $normalized = $this->normalizePhone($phone);

        if (!$normalized) {
            return redirect()->back()->with('error', 'Telefone necessário.');
        }

        if (!$this->hasVerifiedAccess($normalized, $request)) {
            return redirect()->route('customer.orders.index', ['phone' => $normalized])
                ->with('error', 'Confirme o código enviado para avaliar seus pedidos.');
        }

        $customer = $this->findCustomerByPhone($normalized);

        if (!$customer) {
            cookie()->queue(cookie()->forget('customer_phone'));
            return redirect()->back()->with('error', 'Cliente não encontrado.');
        }

        // Buscar pedido
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        // Verificar se já foi avaliado
        $existingRating = OrderRating::where('order_id', $order->id)->first();

        if ($existingRating) {
            $existingRating->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
            $message = 'Avaliação atualizada com sucesso!';
        } else {
            OrderRating::create([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
            $message = 'Avaliação enviada com sucesso! Obrigado pelo feedback.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Envia código de acesso via WhatsApp
     */
    public function requestAccessToken(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:10'],
        ]);

        $phone = $this->normalizePhone($data['phone'] ?? null);
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if (!$phone) {
            Log::warning('Tentativa de OTP com telefone inválido', [
                'phone_raw' => $data['phone'] ?? null,
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
            return redirect()->back()->with('error', 'Informe um telefone válido com DDD (ex: (11) 99999-9999).');
        }

        if ($this->hasVerifiedAccess($phone, $request)) {
            Session::flash('customer_orders.status', 'Telefone já autenticado. Listando pedidos.');
            return redirect()->route('customer.orders.index', ['phone' => $phone]);
        }

        $throttleKey = $this->otpThrottleKey($phone);
        if (Cache::has($throttleKey)) {
            Log::warning('Tentativa de OTP com throttle ativo', [
                'phone' => $phone,
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
            Session::flash('customer_orders.otp_error', 'Aguarde alguns segundos antes de solicitar um novo código.');
            return redirect()->route('customer.orders.index', ['phone' => $phone]);
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(self::OTP_TTL_MINUTES);
        // Adicionar verificação extra: IP e User-Agent
        $payload = [
            'hash' => hash('sha256', $code),
            'attempts' => 0,
            'expires_at' => $expiresAt->timestamp,
            'ip' => $ip,
            'user_agent_hash' => hash('sha256', $userAgent ?? ''),
            'created_at' => Carbon::now()->timestamp,
            'client_id' => $request->get('tenant_id') ?? $request->attributes->get('client_id'),
        ];

        // Usar Session para o payload do OTP em vez de Cache (mais confiável em host compartilhado)
        Session::put('customer_orders.otp_payload', $payload);

        // Manter Cache apenas para o throttle (limite de tentativas)
        Cache::put($throttleKey, true, Carbon::now()->addSeconds(self::OTP_THROTTLE_SECONDS));

        $channel = 'log';
        try {
            $whatsApp = new WhatsAppService();
            if ($whatsApp->isEnabled()) {
                // Normalizar telefone antes de enviar
                $phoneNormalized = preg_replace('/\D/', '', $phone);
                if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
                    $phoneNormalized = '55' . $phoneNormalized;
                }

                $message = "Seu código para acessar os pedidos na Olika é {$code}. Ele expira em "
                    . self::OTP_TTL_MINUTES . " minutos.";
                $whatsApp->sendText($phoneNormalized, $message);
                $channel = 'whatsapp';

                Log::info('OTP enviado com sucesso', [
                    'phone_original' => $phone,
                    'phone_normalized' => $phoneNormalized,
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'channel' => $channel,
                ]);
            } else {
                Log::warning('WhatsAppService desabilitado - OTP não enviado', [
                    'phone' => $phone,
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                ]);
                Session::flash('customer_orders.otp_error', 'Não foi possível enviar o código agora. Tente novamente em instantes.');
                return redirect()->route('customer.orders.index', ['phone' => $phone]);
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar OTP via WhatsApp', [
                'phone' => $phone,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Session::flash('customer_orders.otp_error', 'Não foi possível enviar o código. Tente novamente em instantes.');
            return redirect()->route('customer.orders.index', ['phone' => $phone]);
        }

        Session::flash('customer_orders.otp_sent', true);
        Session::flash('customer_orders.otp_channel', $channel);

        return redirect()->route('customer.orders.index', ['phone' => $phone]);
    }

    private function consumeOtpFlash(): array
    {
        return [
            'otpSent' => Session::pull('customer_orders.otp_sent', false),
            'otpChannel' => Session::pull('customer_orders.otp_channel'),
            'otpError' => Session::pull('customer_orders.otp_error'),
            'statusMessage' => Session::pull('customer_orders.status'),
        ];
    }

    /**
     * Normaliza e valida telefone brasileiro
     * Aceita formatos: (11) 99999-9999, 11999999999, 11 99999-9999, etc.
     */
    private function normalizePhone(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        // Remove todos os caracteres não numéricos
        $digits = preg_replace('/\D+/', '', $value);

        // Validação: Aceita 10, 11 (nacional) ou 12, 13 (com DDI 55)
        $len = strlen($digits);
        if ($len < 10 || $len > 13) {
            return null;
        }

        // Se tiver 11 ou 13 dígitos e começar com 0, remover o 0
        if (($len === 11 || $len === 13) && $digits[0] === '0') {
            $digits = substr($digits, 1);
            $len = strlen($digits);
        }

        // Se tiver DDI 55, extrair o DDD e validar
        if ($len >= 12 && str_starts_with($digits, '55')) {
            $ddd = substr($digits, 2, 2);
        } else {
            $ddd = substr($digits, 0, 2);
        }

        // Validação básica de DDD (não pode começar com 0)
        if ($ddd[0] === '0') {
            return null;
        }

        return $digits;
    }

    private function otpCacheKey(string $phone): string
    {
        return 'customer_orders:otp:' . $phone;
    }

    private function otpThrottleKey(string $phone): string
    {
        return 'customer_orders:otp_throttle:' . $phone;
    }

    private function hasVerifiedAccess(string $phone, Request $request): bool
    {
        $clientId = $request->get('tenant_id') ?? $request->attributes->get('client_id');
        $session = Session::get("customer_orders.auth.{$clientId}");
        if (!$session) {
            return false;
        }

        if (($session['phone'] ?? null) !== $phone) {
            return false;
        }

        return ($session['expires_at'] ?? 0) > Carbon::now()->timestamp;
    }

    private function markPhoneAsVerified(string $phone, Request $request): void
    {
        $clientId = $request->get('tenant_id') ?? $request->attributes->get('client_id');
        Session::put("customer_orders.auth.{$clientId}", [
            'phone' => $phone,
            'expires_at' => Carbon::now()->addMinutes(self::VERIFIED_SESSION_TTL_MINUTES)->timestamp,
        ]);
    }

    private function verifyOtpCode(string $phone, ?string $otp, ?Request $request = null): bool
    {
        if (!$otp) {
            return false;
        }

        // Tentar recuperar da Session (novo método)
        $payload = Session::get('customer_orders.otp_payload');

        // Fallback para Cache (para códigos gerados antes da atualização)
        if (!$payload) {
            $cacheKey = $this->otpCacheKey($phone);
            $payload = Cache::get($cacheKey);
        }

        if (!$payload) {
            if ($request) {
                Log::warning('Tentativa de verificação OTP sem payload (Session/Cache vazio)', [
                    'phone' => $phone,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
            return false;
        }

        // Verificar se o OTP pertence ao tenant atual
        if ($request) {
            $currentClientId = $request->get('tenant_id') ?? $request->attributes->get('client_id');
            $storedClientId = $payload['client_id'] ?? null;
            if ($storedClientId && (int) $currentClientId !== (int) $storedClientId) {
                Log::error('Tentativa de verificação OTP de outro estabelecimento', [
                    'phone' => $phone,
                    'current_client' => $currentClientId,
                    'stored_client' => $storedClientId
                ]);
                return false;
            }
        }

        if (($payload['expires_at'] ?? 0) <= Carbon::now()->timestamp) {
            Session::forget('customer_orders.otp_payload');
            Cache::forget($this->otpCacheKey($phone));
            if ($request) {
                Log::warning('Tentativa de verificação OTP expirado', [
                    'phone' => $phone,
                    'ip' => $request->ip(),
                ]);
            }
            return false;
        }

        // Verificação extra: IP deve corresponder
        if ($request) {
            $currentIp = $request->ip();
            $storedIp = $payload['ip'] ?? null;

            if ($storedIp && $currentIp !== $storedIp) {
                Log::warning('Tentativa de verificação OTP com IP diferente', [
                    'phone' => $phone,
                    'stored_ip' => $storedIp,
                    'current_ip' => $currentIp,
                ]);
            }
        }

        $expected = $payload['hash'] ?? null;
        $provided = hash('sha256', trim($otp));

        if (!$expected || !hash_equals($expected, $provided)) {
            $attempts = (int) ($payload['attempts'] ?? 0) + 1;

            if ($attempts >= 5) {
                Session::forget('customer_orders.otp_payload');
                Cache::forget($this->otpCacheKey($phone));
                Log::error('OTP bloqueado após 5 tentativas inválidas', ['phone' => $phone]);
            } else {
                $payload['attempts'] = $attempts;
                Session::put('customer_orders.otp_payload', $payload);
                // No need to update cache as we are migrating
            }
            return false;
        }

        return true;
    }

    private function findCustomerByPhone(string $normalizedPhone): ?Customer
    {
        // Gerar variantes para busca
        $variants = [$normalizedPhone];

        // Se tem 55 no início e 12/13 dígitos, adicionar versão sem 55
        if (str_starts_with($normalizedPhone, '55') && strlen($normalizedPhone) >= 12) {
            $variants[] = substr($normalizedPhone, 2);
        }
        // Se tem 10/11 dígitos (nacional), adicionar versão com 55
        elseif (strlen($normalizedPhone) <= 11) {
            $variants[] = '55' . $normalizedPhone;
        }

        // Adicionar variantes formatadas (ex: se no banco estiver (71) 98175-0546)
        // O MySQL REPLACE cuidará disso na consulta abaixo

        return Customer::whereIn('phone', $variants)
            ->orWhere(function ($query) use ($variants) {
                foreach ($variants as $v) {
                    $query->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(phone, "(", ""), ")", ""), "-", ""), " ", "") = ?', [$v]);
                }
            })
            ->first();
    }
}

