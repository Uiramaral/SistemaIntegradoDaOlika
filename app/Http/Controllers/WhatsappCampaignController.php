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
            'interval_seconds' => 'required|integer|min:5',
        ]);

        // Calcular total de leads baseados no filtro
        $query = Customer::query();
        
        if ($request->target_audience === 'has_orders') {
            $query->whereHas('orders');
        } elseif ($request->target_audience === 'no_orders') {
            $query->doesntHave('orders');
        }
        
        $total = $query->count();

        $campaign = WhatsappCampaign::create([
            'name' => $validated['name'],
            'message' => $validated['message'],
            'target_audience' => $validated['target_audience'],
            'interval_seconds' => $validated['interval_seconds'],
            'total_leads' => $total,
            'status' => 'pending'
        ]);

        // Iniciar o processo de despacho (Dispatch)
        $this->dispatchCampaign($campaign);

        return response()->json([
            'success' => true,
            'message' => "Campanha criada com {$total} destinatários. O envio começou.",
            'campaign' => $campaign
        ]);
    }

    private function dispatchCampaign(WhatsappCampaign $campaign)
    {
        $campaign->update(['status' => 'processing']);

        $query = Customer::query();
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



