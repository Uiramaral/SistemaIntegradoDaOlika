<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        // Buscar configurações do WhatsApp
        $whatsappSettings = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        // Buscar configurações de pagamento
        $paymentSettings = DB::table('payment_settings')->pluck('value', 'key');
        
        // Configurações gerais da loja
        $storeSettings = [
            'store_name' => 'Olika',
            'store_email' => 'contato@olika.com',
            'store_phone' => '(11) 99999-9999',
            'store_cnpj' => '00.000.000/0001-00',
            'service_fee' => 0,
            'min_order' => 0,
        ];
        
        return view('dash.pages.settings.index', compact('whatsappSettings', 'paymentSettings', 'storeSettings'));
    }
    public function whatsapp()
    {
        $row = DB::table('whatsapp_settings')->where('active', 1)->first();

        return view('dash.pages.settings', compact('row'));
    }

    public function whatsappSave(Request $r)
    {
        $data = $r->validate([
            'api_url' => 'required|url',
            'instance_name' => 'required|string',
            'api_key' => 'required|string',
            'sender_name' => 'nullable|string',
            'ai_enabled' => 'nullable|boolean',
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'nullable|string',
            'ai_system_prompt' => 'nullable|string',
            'admin_phone' => 'nullable|string',
        ]);

        $data['ai_enabled'] = (int)($data['ai_enabled'] ?? 0);

        $row = DB::table('whatsapp_settings')->where('active', 1)->first();

        if ($row) {
            DB::table('whatsapp_settings')->where('id', $row->id)->update($data + ['updated_at' => now()]);
        } else {
            DB::table('whatsapp_settings')->insert($data + [
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('ok', 'Configurações do WhatsApp salvas.');
    }

    public function waConnect()
    {
        try{
            $wa = new \App\Services\WhatsAppService();
            $res = $wa->connectInstance();
            
            if(!$res){ 
                return response()->json(['ok'=>false,'msg'=>'Falha ao conectar. Veja os logs.']); 
            }
            
            // Normalize possíveis campos
            $qrBase64 = $res['base64'] ?? $res['qrCode'] ?? null; // algumas builds usam 'base64', outras 'qrCode'
            $pair     = $res['pairingCode'] ?? $res['code'] ?? null;
            
            return response()->json([
                'ok' => true,
                'pairing_code' => $pair,
                'qr_base64' => $qrBase64,  // pode vir 'data:image/png;base64,...' ou só o base64 puro
                'raw' => $res
            ]);
            
        } catch(\Throwable $e){
            \Log::error('waConnect error: '.$e->getMessage());
            return response()->json(['ok'=>false,'msg'=>'Erro interno ao conectar']);
        }
    }

    public function waHealth()
    {
        try{
            $row = \DB::table('whatsapp_settings')->where('active',1)->first();
            if(!$row) return response()->json(['ok'=>false,'msg'=>'Sem configuração.']);

            $url = rtrim($row->api_url,'/')."/instance/health/{$row->instance_name}";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_HTTPHEADER => ["apikey: {$row->api_key}"],
                CURLOPT_TIMEOUT => 15
            ]);
            
            $resp = curl_exec($ch);
            if($resp === false){ return response()->json(['ok'=>false,'msg'=>curl_error($ch)]); }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
            if($code >= 300) return response()->json(['ok'=>false,'msg'=>"HTTP {$code}",'raw'=>$resp]);
            
            $j = json_decode($resp,true);
            return response()->json(['ok'=>true,'data'=>$j]);
            
        }catch(\Throwable $e){
            \Log::error('waHealth error: '.$e->getMessage());
            return response()->json(['ok'=>false,'msg'=>'erro interno']);
        }
    }

    public function mp()
    {
        $keys = DB::table('payment_settings')->pluck('value', 'key');

        return view('dash.pages.settings', ['keys' => $keys]);
    }

    public function mpSave(Request $r)
    {
        $data = $r->validate([
            'mercadopago_access_token' => 'required|string',
            'mercadopago_public_key' => 'required|string',
            'mercadopago_environment' => 'required|string',
            'mercadopago_webhook_url' => 'nullable|url',
        ]);

        foreach ($data as $k => $v) {
            $exists = DB::table('payment_settings')->where('key', $k)->exists();
            
            if ($exists) {
                DB::table('payment_settings')->where('key', $k)->update(['value' => $v]);
            } else {
                DB::table('payment_settings')->insert([
                    'key' => $k,
                    'value' => $v,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('ok', 'Configurações do Mercado Pago salvas.');
    }
}

