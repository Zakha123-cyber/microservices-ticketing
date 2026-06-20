<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('token')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
