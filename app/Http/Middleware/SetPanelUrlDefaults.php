<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SetPanelUrlDefaults
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->route()) {
            $panel = $request->route('panel');
            if ($panel) {
                URL::defaults(['panel' => $panel]);
            }
        }

        return $next($request);
    }
}
