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

        if (!$this->hasVerifiedAccess($phone)) {
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

            $this->markPhoneAsVerified($phone);
            Cache::forget($this->otpCacheKey($phone));
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

        if (!$this->hasVerifiedAccess($normalized)) {
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

        if (!$this->hasVerifiedAccess($normalized)) {
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

        if ($this->hasVerifiedAccess($phone)) {
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
        ];

        Cache::put($this->otpCacheKey($phone), $payload, $expiresAt);
        Cache::put($throttleKey, true, Carbon::now()->addSeconds(self::OTP_THROTTLE_SECONDS));

        $channel = 'log';
        try {
            $whatsApp = new WhatsAppService();
            if ($whatsApp->isEnabled()) {
                $message = "Seu código para acessar os pedidos na Olika é {$code}. Ele expira em "
                    . self::OTP_TTL_MINUTES . " minutos.";
                $whatsApp->sendText($phone, $message);
                $channel = 'whatsapp';
                
                Log::info('OTP enviado com sucesso', [
                    'phone' => $phone,
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
        
        // Validação: deve ter entre 10 e 11 dígitos (com ou sem DDI)
        if (strlen($digits) < 10 || strlen($digits) > 11) {
            return null;
        }
        
        // Se tiver 11 dígitos e começar com 0, remover o 0
        if (strlen($digits) === 11 && $digits[0] === '0') {
            $digits = substr($digits, 1);
        }
        
        // Validação de DDD brasileiro (11-99)
        $ddd = substr($digits, 0, 2);
        if (!preg_match('/^[1-9][1-9]$/', $ddd)) {
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

    private function hasVerifiedAccess(string $phone): bool
    {
        $session = Session::get('customer_orders.auth');
        if (!$session) {
            return false;
        }

        if (($session['phone'] ?? null) !== $phone) {
            return false;
        }

        return ($session['expires_at'] ?? 0) > Carbon::now()->timestamp;
    }

    private function markPhoneAsVerified(string $phone): void
    {
        Session::put('customer_orders.auth', [
            'phone' => $phone,
            'expires_at' => Carbon::now()->addMinutes(self::VERIFIED_SESSION_TTL_MINUTES)->timestamp,
        ]);
    }

    private function verifyOtpCode(string $phone, ?string $otp, ?Request $request = null): bool
    {
        if (!$otp) {
            return false;
        }

        $cacheKey = $this->otpCacheKey($phone);
        $payload = Cache::get($cacheKey);
        if (!$payload) {
            if ($request) {
                Log::warning('Tentativa de verificação OTP sem código válido', [
                    'phone' => $phone,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
            return false;
        }

        if (($payload['expires_at'] ?? 0) <= Carbon::now()->timestamp) {
            Cache::forget($cacheKey);
            if ($request) {
                Log::warning('Tentativa de verificação OTP expirado', [
                    'phone' => $phone,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
            return false;
        }

        // Verificação extra: IP e User-Agent devem corresponder (com tolerância)
        if ($request) {
            $currentIp = $request->ip();
            $currentUserAgent = hash('sha256', $request->userAgent() ?? '');
            $storedIp = $payload['ip'] ?? null;
            $storedUserAgentHash = $payload['user_agent_hash'] ?? null;
            
            // Log de tentativa de verificação
            Log::info('Tentativa de verificação OTP', [
                'phone' => $phone,
                'ip_match' => $currentIp === $storedIp,
                'user_agent_match' => $currentUserAgent === $storedUserAgentHash,
                'ip' => $currentIp,
                'stored_ip' => $storedIp,
            ]);
            
            // Aviso se IP ou User-Agent não corresponderem (mas não bloqueia)
            if ($storedIp && $currentIp !== $storedIp) {
                Log::warning('Tentativa de verificação OTP com IP diferente', [
                    'phone' => $phone,
                    'stored_ip' => $storedIp,
                    'current_ip' => $currentIp,
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        $expected = $payload['hash'] ?? null;
        $provided = hash('sha256', trim($otp));

        if (!$expected || !hash_equals($expected, $provided)) {
            $attempts = (int)($payload['attempts'] ?? 0) + 1;
            
            if ($request) {
                Log::warning('Tentativa de verificação OTP com código inválido', [
                    'phone' => $phone,
                    'attempts' => $attempts,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
            
            if ($attempts >= 5) {
                Cache::forget($cacheKey);
                if ($request) {
                    Log::error('OTP bloqueado após 5 tentativas inválidas', [
                        'phone' => $phone,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }
            } else {
                $payload['attempts'] = $attempts;
                Cache::put($cacheKey, $payload, Carbon::now()->addMinutes(self::OTP_TTL_MINUTES));
            }
            return false;
        }

        // OTP válido
        if ($request) {
            Log::info('OTP verificado com sucesso', [
                'phone' => $phone,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return true;
    }

    private function findCustomerByPhone(string $normalizedPhone): ?Customer
    {
        return Customer::whereRaw('REPLACE(REPLACE(REPLACE(phone, "(", ""), ")", ""), "-", "") = ?', [$normalizedPhone])
            ->orWhere('phone', $normalizedPhone)
            ->first();
    }
}

