<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('API-TOKEN');

        if ($token !== 'IT is to secret you cannot break it :)') {
            return response()->json(['message' => 'Unauthorized Api Token'], 401);
        }

        return $next($request);
    }
}
