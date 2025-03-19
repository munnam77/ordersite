<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
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
        // Check if request is already secure OR if X-Forwarded-Proto header is 'https'
        $isSecure = $request->secure() 
                   || $request->header('X-Forwarded-Proto') == 'https'
                   || $request->header('X-Forwarded-Ssl') == 'on';
        
        if (!$isSecure && (App::environment('production') || env('FORCE_HTTPS', false))) {
            // Only redirect if not already on HTTPS
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
} 