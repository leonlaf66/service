<?php

namespace App\Http\Middleware;

use Closure;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $appToken = $request->get('app_token');
        if (!$appToken) {
            $appToken = $request->headers->get('app-token');
        }

        if ($appToken === config('app.token')) {
            config(['app-token' => $appToken]);
            return $next($request);
        }

        return response()->json([
            'code' => 401,
            'message' => 'APP授权失败',
            'app-token' => $appToken
        ]);
    }
}
