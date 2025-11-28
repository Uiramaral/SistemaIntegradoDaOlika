<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) abort(403);
        
        if (empty($roles) || in_array($user->role, $roles)) return $next($request);
        
        abort(403);
    }
}
