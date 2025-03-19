<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     * Using direct hex value (0x7F = 127) to ensure compatibility with all Laravel versions
     * This value enables all X-Forwarded-* headers
     *
     * @var int
     */
    protected $headers = 0x7F; // Equivalent to all X-Forwarded-* headers
}
