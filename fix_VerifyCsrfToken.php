<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Closure;
use Illuminate\Support\Facades\App;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
    
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Add a fallback for potentially missing CSRF token by checking if it's a GET request
        if ($request->method() == 'GET' || $this->isReading($request) || $this->tokensMatch($request)) {
            return $this->addCookieToResponse($request, $next($request));
        }

        return parent::handle($request, $next);
    }

    /**
     * Set the CSRF token in a cookie with secure and SameSite attributes.
     *
     * @return void
     */
    protected function newCookie($request, $config)
    {
        $cookie = parent::newCookie($request, $config);
        
        $secure = App::environment('production') || env('FORCE_HTTPS', false);
        
        return $cookie->withSecure($secure)
                     ->withSameSite('lax');
    }
} 