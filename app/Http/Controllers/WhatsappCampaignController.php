<?php

namespace App\Http\Controllers;

use App\Models\WhatsappCampaign;
use App\Models\Customer;
use App\Jobs\SendCampaignMessageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsappCampaignController extends Controller
{
    public function index()
    {
        $campaigns = WhatsappCampaign::orderBy('created_at', 'desc')->get();
        return response()->json($campaigns);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'message' => 'required|string',
            'target_audience' => 'required|string', // all, has_orders, no_orders
            'filter_newsletter' => 'nullable|boolean',
            'filter_customer_type' => 'nullable|in:all,new_customers,existing_customers',
            'test_customer_id' => 'nullable|exists:customers,id',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'interval_seconds' => 'required|integer|min:5',
        ]);

        // Se houver cliente de teste, enviar apenas para ele
        if ($request->test_customer_id) {
            $total = 1;
            $scheduledAt = null;
            if ($request->scheduled_date && $request->scheduled_time) {
                $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->scheduled_date . ' ' . $request->scheduled_time);
            }
            
            $campaign = WhatsappCampaign::create([
                'name' => $validated['name'],
                'message' => $validated['message'],
                'target_audience' => $validated['target_audience'],
                'filter_newsletter' => $request->filter_newsletter ?? false,
                'filter_customer_type' => $request->filter_customer_type ?? 'all',
                'test_customer_id' => $validated['test_customer_id'],
                'scheduled_at' => $scheduledAt,
                'scheduled_time' => $request->scheduled_time,
                'interval_seconds' => $validated['interval_seconds'],
                'total_leads' => $total,
                'status' => $scheduledAt && $scheduledAt->isFuture() ? 'scheduled' : 'pending'
            ]);

            // Se não estiver agendada, iniciar imediatamente
            if (!$scheduledAt || $scheduledAt->isPast()) {
                $this->dispatchCampaign($campaign);
            }

            return response()->json([
                'success' => true,
                'message' => $scheduledAt && $scheduledAt->isFuture() 
                    ? "Campanha agendada para {$scheduledAt->format('d/m/Y H:i')}. Enviará apenas para o cliente de teste."
                    : "Campanha criada. Enviando para o cliente de teste.",
                'campaign' => $campaign
            ]);
        }

        // Calcular total de leads baseados nos filtros combinados
        $query = Customer::query();
        
        // Filtro de newsletter
        if ($request->filter_newsletter) {
            $query->where('newsletter', true);
        }
        
        // Filtro de tipo de cliente (novos ou existentes)
        if ($request->filter_customer_type === 'new_customers') {
            $query->doesntHave('orders');
        } elseif ($request->filter_customer_type === 'existing_customers') {
            $query->whereHas('orders');
        }
        
        // Filtro de target_audience (mantido para compatibilidade)
        if ($request->target_audience === 'has_orders') {
            $query->whereHas('orders');
        } elseif ($request->target_audience === 'no_orders') {
            $query->doesntHave('orders');
        }
        
        $total = $query->count();

        // Calcular scheduled_at se fornecido
        $scheduledAt = null;
        if ($request->scheduled_date && $request->scheduled_time) {
            $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->scheduled_date . ' ' . $request->scheduled_time);
        }

        $campaign = WhatsappCampaign::create([
            'name' => $validated['name'],
            'message' => $validated['message'],
            'target_audience' => $validated['target_audience'],
            'filter_newsletter' => $request->filter_newsletter ?? false,
            'filter_customer_type' => $request->filter_customer_type ?? 'all',
            'test_customer_id' => $validated['test_customer_id'] ?? null,
            'scheduled_at' => $scheduledAt,
            'scheduled_time' => $request->scheduled_time,
            'interval_seconds' => $validated['interval_seconds'],
            'total_leads' => $total,
            'status' => $scheduledAt && $scheduledAt->isFuture() ? 'scheduled' : 'pending'
        ]);

        // Se não estiver agendada, iniciar imediatamente
        if (!$scheduledAt || $scheduledAt->isPast()) {
            $this->dispatchCampaign($campaign);
        }

        return response()->json([
            'success' => true,
            'message' => $scheduledAt && $scheduledAt->isFuture()
                ? "Campanha agendada para {$scheduledAt->format('d/m/Y H:i')} com {$total} destinatários."
                : "Campanha criada com {$total} destinatários. O envio começou.",
            'campaign' => $campaign
        ]);
    }

    public function dispatchCampaign(WhatsappCampaign $campaign)
    {
        $campaign->update(['status' => 'processing']);

        // Se houver cliente de teste, enviar apenas para ele
        if ($campaign->test_customer_id) {
            $customer = Customer::find($campaign->test_customer_id);
            if ($customer && $customer->phone) {
                SendCampaignMessageJob::dispatch($campaign, $customer, $campaign->message)
                    ->delay(now()->addSeconds(0));
            }
            return;
        }

        $query = Customer::query();
        
        // Aplicar filtro de newsletter
        if ($campaign->filter_newsletter) {
            $query->where('newsletter', true);
        }
        
        // Aplicar filtro de tipo de cliente
        if ($campaign->filter_customer_type === 'new_customers') {
            $query->doesntHave('orders');
        } elseif ($campaign->filter_customer_type === 'existing_customers') {
            $query->whereHas('orders');
        }
        
        // Aplicar filtro de target_audience (mantido para compatibilidade)
        if ($campaign->target_audience === 'has_orders') {
            $query->whereHas('orders');
        } elseif ($campaign->target_audience === 'no_orders') {
            $query->doesntHave('orders');
        }

        // Chunk para não estourar memória, mas despachar jobs individuais
        $query->chunk(100, function ($customers) use ($campaign) {
            static $count = 0;
            
            foreach ($customers as $customer) {
                if (!$customer->phone) continue;

                // Calcular delay: index * intervalo
                // Ex: 10s intervalo.
                // Cliente 1: 0s
                // Cliente 2: 10s
                // Cliente 3: 20s
                $delay = $count * $campaign->interval_seconds;

                SendCampaignMessageJob::dispatch($campaign, $customer, $campaign->message)
                    ->delay(now()->addSeconds($delay));
                
                $count++;
            }
        });
    }
}








