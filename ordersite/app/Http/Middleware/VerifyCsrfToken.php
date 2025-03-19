<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

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
     * Set the CSRF token in a cookie with secure and SameSite attributes.
     *
     * @return void
     */
    protected function newCookie($request, $config)
    {
        $cookie = parent::newCookie($request, $config);
        
        return $cookie->withSecure(true)
                      ->withSameSite('lax');
    }
} 