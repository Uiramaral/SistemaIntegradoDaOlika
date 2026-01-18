<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStatusController extends Controller
{
    /**
     * Exibe a página de gerenciamento de status e templates
     */
    public function index()
    {
        $clientId = currentClientId();
        
        $statuses = DB::table('order_statuses')
            ->leftJoin('whatsapp_templates', 'order_statuses.whatsapp_template_id', '=', 'whatsapp_templates.id')
            ->select(
                'order_statuses.*',
                'whatsapp_templates.slug as template_slug',
                'whatsapp_templates.content as template_content'
            )
            ->where(function($query) use ($clientId) {
                $query->where('order_statuses.client_id', $clientId)
                      ->orWhereNull('order_statuses.client_id');
            })
            ->orderBy('order_statuses.created_at')
            ->get();

        $templates = DB::table('whatsapp_templates')
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->orderBy('slug')
            ->get();

        return view('dashboard.settings.status-templates', compact('statuses', 'templates'));
    }

    /**
     * Atualiza um status de pedido
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_final' => 'nullable|boolean',
            'notify_customer' => 'nullable|boolean',
            'notify_admin' => 'nullable|boolean',
            'whatsapp_template_id' => 'nullable|exists:whatsapp_templates,id',
            'active' => 'nullable|boolean',
        ]);

        DB::table('order_statuses')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'is_final' => $request->has('is_final') && $request->is_final ? 1 : 0,
                'notify_customer' => $request->has('notify_customer') && $request->notify_customer ? 1 : 0,
                'notify_admin' => $request->has('notify_admin') && $request->notify_admin ? 1 : 0,
                'whatsapp_template_id' => $validated['whatsapp_template_id'] ?? null,
                'active' => $request->has('active') && $request->active ? 1 : 0,
                'updated_at' => now(),
            ]);

        return redirect()->route('dashboard.status-templates.index')
            ->with('success', 'Status atualizado com sucesso!');
    }

    /**
     * Salva ou atualiza um template de WhatsApp
     */
    public function saveTemplate(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:whatsapp_templates,id',
            'slug' => 'required|string|max:100',
            'content' => 'required|string',
            'active' => 'nullable|boolean',
        ]);

        $clientId = currentClientId();
        
        $data = [
            'slug' => $validated['slug'],
            'content' => $validated['content'],
            'active' => $request->has('active') && $request->active ? 1 : 0,
            'updated_at' => now(),
        ];

        if (isset($validated['id']) && $validated['id']) {
            // Atualizar
            DB::table('whatsapp_templates')
                ->where('id', $validated['id'])
                ->where(function($query) use ($clientId) {
                    $query->where('client_id', $clientId)
                          ->orWhereNull('client_id');
                })
                ->update($data);
        } else {
            // Criar novo
            $data['client_id'] = $clientId;
            $data['created_at'] = now();
            DB::table('whatsapp_templates')->insert($data);
        }

        return redirect()->back()
            ->with('success', 'Template salvo com sucesso!');
    }

    /**
     * Deleta um template
     */
    public function deleteTemplate($id)
    {
        $clientId = currentClientId();
        
        // Verificar se está sendo usado por algum status
        $inUse = DB::table('order_statuses')
            ->where('whatsapp_template_id', $id)
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->exists();

        if ($inUse) {
            return redirect()->back()
                ->with('error', 'Este template está sendo usado por um ou mais status. Remova a associação antes de excluir.');
        }

        DB::table('whatsapp_templates')
            ->where('id', $id)
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->delete();

        return redirect()->back()
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Retorna um template para edição (AJAX)
     */
    public function getTemplate($id)
    {
        $clientId = currentClientId();
        
        $template = DB::table('whatsapp_templates')
            ->where('id', $id)
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->first();
        
        if (!$template) {
            return response()->json(['error' => 'Template não encontrado'], 404);
        }

        return response()->json($template);
    }
}
