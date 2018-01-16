<?php

namespace App\Http\Middleware;

use Closure;

class AreaMiddleware
{
    public function handle($request, Closure $next)
    {
        $areaId = $request->get('area_id');
        if (!$areaId) {
            $areaId = $request->headers->get('area-id');
        }

        if ($areaId) {
            config(['app.area_id' => $areaId]);
        }

        return $next($request);
    }
}
