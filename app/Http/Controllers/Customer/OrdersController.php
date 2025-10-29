<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    /**
     * Lista pedidos do cliente
     */
    public function index(Request $request)
    {
        // Buscar cliente pelo telefone (apenas telefone, sem necessidade de login)
        $phone = $request->get('phone');
        
        // Se não tiver telefone na URL, mostrar tela simples de entrada
        if (!$phone) {
            return view('customer.orders.login');
        }

        // Buscar cliente pelo telefone (normalizar removendo caracteres não numéricos)
        $phoneNormalized = preg_replace('/\D/', '', $phone);
        $customer = Customer::whereRaw('REPLACE(REPLACE(REPLACE(phone, "(", ""), ")", ""), "-", "") = ?', [$phoneNormalized])
            ->orWhere('phone', $phone)
            ->orWhere('phone', $phoneNormalized)
            ->first();

        if (!$customer) {
            return view('customer.orders.login', ['error' => 'Cliente não encontrado. Verifique seu telefone.']);
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
        // Buscar cliente pelo telefone
        $phone = $request->get('phone');
        
        if (!$phone) {
            return redirect()->route('customer.orders.index')->with('error', 'Telefone necessário para acessar.');
        }

        // Buscar cliente pelo telefone (normalizar)
        $phoneNormalized = preg_replace('/\D/', '', $phone);
        $customer = Customer::whereRaw('REPLACE(REPLACE(REPLACE(phone, "(", ""), ")", ""), "-", "") = ?', [$phoneNormalized])
            ->orWhere('phone', $phone)
            ->orWhere('phone', $phoneNormalized)
            ->first();

        if (!$customer) {
            return redirect()->route('customer.orders.index')->with('error', 'Cliente não encontrado.');
        }

        // Buscar pedido
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $customer->id)
            ->with(['customer', 'items.product', 'payment', 'address'])
            ->firstOrFail();

        // Histórico de status
        $statusHistory = \DB::table('order_status_history')
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
            'phone' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Buscar cliente pelo telefone
        $phone = $request->get('phone');
        
        if (!$phone) {
            return redirect()->back()->with('error', 'Telefone necessário.');
        }

        // Buscar cliente pelo telefone (normalizar)
        $phoneNormalized = preg_replace('/\D/', '', $phone);
        $customer = Customer::whereRaw('REPLACE(REPLACE(REPLACE(phone, "(", ""), ")", ""), "-", "") = ?', [$phoneNormalized])
            ->orWhere('phone', $phone)
            ->orWhere('phone', $phoneNormalized)
            ->first();

        if (!$customer) {
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
}

