<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Se l'utente non è loggato o non ha il ruolo giusto...
        if (!auth()->check() || auth()->user()->role !== $role) {
            abort(403, 'Azione non autorizzata.');
        }

        return $next($request);
    }
}
