<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);
        if ($token === null) {
            return response()->json(['message' => 'Token manquant.'], 401);
        }

        $hashed = hash('sha256', $token);
        $user = User::where('api_token', $hashed)->first();

        if (!$user) {
            return response()->json(['message' => 'Token invalide.'], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $bearer = $request->bearerToken();
        if ($bearer) {
            return $bearer;
        }

        $header = $request->header('X-API-TOKEN');
        if ($header) {
            return $header;
        }

        $query = $request->query('api_token');
        if ($query && is_string($query)) {
            return $query;
        }

        return null;
    }
}
