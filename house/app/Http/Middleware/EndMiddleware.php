<?php

namespace App\Http\Middleware;

use Closure;

class EndMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $result = $next($request);
        app('db')->connection('pgsql2')->disconnect();
        app('db')->disconnect();
        return $result;
    }
}
