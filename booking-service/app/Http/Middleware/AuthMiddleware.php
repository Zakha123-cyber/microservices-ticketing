<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authenticated user header is required'], 401);
        }

        $request->attributes->set('user_id', (int) $userId);
        $request->attributes->set('user_role', $request->header('X-User-Role', 'user'));

        return $next($request);
    }
}
