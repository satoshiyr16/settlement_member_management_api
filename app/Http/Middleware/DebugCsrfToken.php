<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugCsrfToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // CSRFトークンをログに出力
        Log::info('CSRF Token Debug', [
            'session_token' => $request->session()->token(),
            'cookies' => $request->cookies->all(),
            'response_cookies' => $response->headers->getCookies(),
        ]);

        return $response;
    }
}
