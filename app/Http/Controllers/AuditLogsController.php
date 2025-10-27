<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogsController extends Controller
{
    public function index(Request $req)
    {
        $q = AuditLog::query()->with('user')->latest('created_at');

        if ($m = $req->string('model')->toString()) {
            $q->where('model_type','like',"%{$m}%");
        }

        if ($id = $req->integer('id')) {
            $q->where('model_id',$id);
        }

        if ($a = $req->string('action')->toString()) {
            $q->where('action',$a);
        }

        $logs = $q->paginate(30);

        return view('auditoria.index', compact('logs'));
    }

    public function show(AuditLog $log)
    {
        return view('auditoria.show', compact('log'));
    }
}
