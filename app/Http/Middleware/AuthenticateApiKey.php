<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Missing API key.'], 401);
        }

        $apiKey = ApiKey::query()
            ->with('customerAccount.activeSubscription.plan')
            ->where('token_hash', ApiKey::hashToken($token))
            ->first();

        if (! $apiKey?->isActive()) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('customer_account', $apiKey->customerAccount);

        return $next($request);
    }
}
