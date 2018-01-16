<?php

namespace App\Http\Middleware;

use Closure;

class LanguageMiddleware
{
    public function handle($request, Closure $next)
    {
        $langId = $request->get('language');
        if (!$langId) {
            $langId = $request->headers->get('language');
        }

        if ($langId) {
            config(['app.locale' => $langId]);
        }

        return $next($request);
    }
}
