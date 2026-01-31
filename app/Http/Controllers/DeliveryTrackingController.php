<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\DeliveryTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DeliveryTrackingController extends Controller
{
    /**
     * Iniciar rastreamento de uma entrega
     */
    public function start(Order $order)
    {
        try {
            if (!$order->tracking_token) {
                $order->tracking_token = Str::random(32);
            }

            $order->tracking_enabled = true;
            $order->tracking_started_at = now();
            $order->tracking_stopped_at = null;
            $order->save();

            \Log::info('Tracking iniciado', [
                'order_id' => $order->id,
                'token' => $order->tracking_token
            ]);

            return response()->json([
                'success' => true,
                'tracking_url' => route('tracking.show', ['token' => $order->tracking_token]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao iniciar tracking', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao salvar rastreamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parar rastreamento
     */
    public function stop(Order $order)
    {
        $order->tracking_enabled = false;
        $order->tracking_stopped_at = now();
        $order->save();

        return response()->json(['success' => true]);
    }

    /**
     * Salvar localização (chamado pelo app do entregador)
     */
    public function updateLocation(Request $request, Order $order)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
        ]);

        if (!$order->tracking_enabled) {
            return response()->json(['error' => 'Rastreamento não está ativo'], 403);
        }

        DeliveryTracking::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'tracked_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Obter localização atual (para atualização em tempo real)
     */
    public function getLocation($token)
    {
        $order = Order::where('tracking_token', $token)
            ->with(['customer', 'address'])
            ->firstOrFail();

        $latest = DeliveryTracking::where('order_id', $order->id)
            ->orderBy('tracked_at', 'desc')
            ->first();

        return response()->json([
            'tracking_enabled' => $order->tracking_enabled,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer->name ?? 'Cliente',
            'delivery_address' => $order->delivery_address,
            'destination' => $order->address ? [
                'lat' => $order->address->latitude ?? null,
                'lng' => $order->address->longitude ?? null,
            ] : null,
            'current_location' => $latest ? [
                'lat' => (float) $latest->latitude,
                'lng' => (float) $latest->longitude,
                'accuracy' => $latest->accuracy,
                'speed' => $latest->speed,
                'heading' => $latest->heading,
                'time' => $latest->tracked_at->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Página de rastreamento para o cliente
     */
    public function show($token)
    {
        $order = Order::where('tracking_token', $token)
            ->with(['customer', 'address'])
            ->firstOrFail();

        // Tentar obter coordenadas do endereço se não existirem
        $destination = [
            'lat' => $order->address->latitude ?? null,
            'lng' => $order->address->longitude ?? null,
        ];

        // Se não tem coordenadas, tentar usar um padrão da cidade ou do endereço (simulado por enquanto, ideal seria geocoding)
        // Coordenadas padrão de São Paulo se falhar
        if (!$destination['lat'] || !$destination['lng']) {
            // Tenta extrair coordenadas de um cache ou serviço se disponível
            // Por enquanto, manteremos null para que a view trate ou use a localização do entregador como centro
        }

        return view('tracking.show', compact('order', 'token', 'destination'));
    }
}
