<?php

// Fix for session management to prevent page expired errors
// This should be called in the bootstrap process

namespace App\Fixes;

class BootstrapFixes
{
    public static function apply()
    {
        // Ensure session configuration is correct
        config([
            'session.secure' => env('FORCE_HTTPS', false) || env('APP_ENV') === 'production',
            'session.same_site' => 'lax',
            'session.expire_on_close' => false,
            'session.cookie' => env('SESSION_COOKIE', str_slug(env('APP_NAME', 'laravel'), '_').'_session'),
        ]);
        
        // Ensure proper CSRF configurations
        config([
            'session.http_only' => true,
        ]);
    }
} 