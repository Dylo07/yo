<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     * Validates the API key for external API access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->query('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'API key is required',
                'code' => 'MISSING_API_KEY'
            ], 401);
        }

        $validApiKey = config('services.api.key');

        if (!$validApiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid API key',
                'code' => 'INVALID_API_KEY'
            ], 403);
        }

        return $next($request);
    }
}
