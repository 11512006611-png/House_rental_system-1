<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! Auth::check() || ! $user->isAdmin()) {
            abort(403, 'Unauthorized. Admin access only.');
        }

        return $next($request);
    }
}
