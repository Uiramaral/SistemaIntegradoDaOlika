<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PdvAjaxController extends Controller
{
    public function cep(Request $r){
        $cep = preg_replace('/\D/','', $r->query('cep',''));
        if(strlen($cep) !== 8) return response()->json(['ok'=>false,'message'=>'CEP inválido']);

        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $json = $this->httpGet($url);
        if(!$json) return response()->json(['ok'=>false,'message'=>'Falha na consulta']);

        $d = json_decode($json, true);
        if(!$d || !empty($d['erro'])) return response()->json(['ok'=>false,'message'=>'CEP não encontrado']);

        return response()->json([
            'ok'=>true,
            'address'=>[
                'street'   => $d['logradouro'] ?? '',
                'district' => $d['bairro'] ?? '',
                'city'     => $d['localidade'] ?? '',
                'state'    => $d['uf'] ?? '',
                'cep'      => $cep,
            ]
        ]);
    }

    public function customers(Request $r){
        $q = trim($r->query('q',''));
        if(strlen($q) < 2) return response()->json(['items'=>[]]);

        $rows = DB::table('customers')
            ->select('id','name','phone','email','cep','street','number','complement','district','city','state')
            ->where(function($w) use ($q){
                $w->where('name','like',"%{$q}%")
                  ->orWhere('phone','like',"%{$q}%")
                  ->orWhere('email','like',"%{$q}%");
            })
            ->limit(12)->get();

        $items = $rows->map(function($c){
            return [
                'id'    => $c->id,
                'label' => "{$c->name} · {$c->phone}",
                'name'  => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'address'=>[
                    'cep'=>$c->cep,'street'=>$c->street,'number'=>$c->number,'complement'=>$c->complement,
                    'district'=>$c->district,'city'=>$c->city,'state'=>$c->state,
                ],
            ];
        });

        return response()->json(['items'=>$items]);
    }

    public function products(Request $r){
        $q = trim($r->query('q',''));
        if(strlen($q) < 2) return response()->json(['items'=>[]]);

        $rows = DB::table('products')
            ->select('id','name','price','sku','active')
            ->where(function($w) use ($q){
                $w->where('name','like',"%{$q}%")->orWhere('sku','like',"%{$q}%");
            })
            ->where('active',1)
            ->limit(20)->get();

        $items = $rows->map(function($p){
            return ['id'=>$p->id,'label'=>$p->name,'price'=>$p->price,'meta'=> $p->sku ? "SKU: {$p->sku}" : '' ];
        });

        return response()->json(['items'=>$items]);
    }

    public function eligibleCoupons(Request $r){
        // Simples: retorna cupons ativos; se quiser, aplique regras por customer_id
        $rows = DB::table('coupons')->select('code','name','discount_type','discount_value','active')
            ->where('active',1)->limit(50)->get();

        $items = $rows->map(function($c){
            $v = $c->discount_type==='percent' ? "{$c->discount_value}% off" : 'R$ ' . number_format($c->discount_value, 2, ',', '.');
            return ['code'=>strtoupper($c->code),'label'=>"{$c->name} ({$v})"];
        });

        return response()->json(['items'=>$items]);
    }

    public function deliveryOptions(Request $r){
        // Mock básico: fixa "Retirada" e "Entrega Padrão"
        $items = [
            ['code'=>'pickup', 'label'=>'Retirar na loja (R$ 0,00)', 'value'=>0],
            ['code'=>'std',    'label'=>'Entrega padrão (R$ 8,00)', 'value'=>8.00],
        ];
        return response()->json(['items'=>$items]);
    }

    private function httpGet($url){
        // tenta file_get_contents
        $ctx = stream_context_create(['http'=>['timeout'=>4]]);
        $resp = @file_get_contents($url, false, $ctx);
        if($resp !== false) return $resp;

        // fallback cURL
        if(function_exists('curl_init')){
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>4, CURLOPT_FOLLOWLOCATION=>true]);
            $out = curl_exec($ch);
            curl_close($ch);
            return $out ?: null;
        }
        return null;
    }
}
