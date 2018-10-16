<?php

namespace App\Http\Middleware;

use Closure;

class SignatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$headerName='X-Name')
    {
        $response= $next($request);

        //as we are acting on response after the response is received.

        $response->headers->set($headerName,config('app.name'));
        return $response;
    }
}
