<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : config('app.frontend_url') . '/login/';
    }

    // protected function authenticate($request, array $guards)
    // {
    //     if (empty($guards)) {
    //         $guards = [null];
    //     }

    //     foreach ($guards as $guard) {
    //         if ($this->auth->guard($guard)->check()) {
    //             return $this->auth->shouldUse($guard);
    //         }
    //     }

    //     $this->unauthenticated($request, $guards);
    // }
}
