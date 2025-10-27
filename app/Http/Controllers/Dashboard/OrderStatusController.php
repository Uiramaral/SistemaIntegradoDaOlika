<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderStatusController extends Controller
{
    public function index()
    {
        $statuses = DB::table('order_statuses')
            ->orderBy('active', 'desc')
            ->orderBy('id')
            ->get();
            
        $templates = DB::table('whatsapp_templates')
            ->where('active', 1)
            ->orderBy('slug')
            ->get();

        return view('dashboard.statuses', compact('statuses', 'templates'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'  => 'required|alpha_dash|unique:order_statuses,code',
            'name'  => 'required|string|min:2',
            'is_final' => 'nullable|boolean',
            'notify_customer' => 'nullable|boolean',
            'notify_admin' => 'nullable|boolean',
            'whatsapp_template_id' => 'nullable|integer',
        ]);

        $data['is_final'] = (int) ($data['is_final'] ?? 0);
        $data['notify_customer'] = (int) ($data['notify_customer'] ?? 0);
        $data['notify_admin'] = (int) ($data['notify_admin'] ?? 0);
        $data['active'] = 1;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('order_statuses')->insert($data);

        return back()->with('ok', 'Status criado');
    }

    public function updateFlags(Request $r, $id)
    {
        $data = $r->validate([
            'notify_customer' => 'nullable|boolean',
            'notify_admin' => 'nullable|boolean',
            'is_final' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'whatsapp_template_id' => 'nullable|integer',
        ]);

        DB::table('order_statuses')->where('id', $id)->update([
            'notify_customer' => (int) ($data['notify_customer'] ?? 0),
            'notify_admin' => (int) ($data['notify_admin'] ?? 0),
            'is_final' => (int) ($data['is_final'] ?? 0),
            'active' => (int) ($data['active'] ?? 1),
            'whatsapp_template_id' => $data['whatsapp_template_id'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('ok', 'Status atualizado');
    }

    public function destroy($id)
    {
        DB::table('order_statuses')->where('id', $id)->delete();

        return back()->with('ok', 'Status removido');
    }
}

