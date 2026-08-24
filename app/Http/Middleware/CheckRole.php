<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Dipakai kayak: ->middleware('role:Admin|Super Admin')
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!$request->user() || !$request->user()->hasRole($roles)) {
            abort(403, 'Akses Ditolak.');
        }

        return $next($request);
    }
}