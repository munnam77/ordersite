<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SecureCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        if (App::environment('production') || env('FORCE_HTTPS', false)) {
            $config = config('session');
            
            session()->setSecure(true);
            
            // Set all cookies to secure
            foreach ($response->headers->getCookies() as $cookie) {
                $response->headers->setCookie($cookie->withSecure(true)->withSameSite('lax'));
            }
        }
        
        return $response;
    }
} 