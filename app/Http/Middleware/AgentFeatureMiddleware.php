<?php

namespace App\Http\Middleware;

use App\Support\AgentRoleMatrix;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentFeatureMiddleware
{
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $user = $request->user();
        if (!$user || $user->role !== 'agent') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $user->loadMissing('service');
        if (!$user->service) {
            return response()->json(['message' => 'Agent sans service.'], 403);
        }

        foreach ($features as $feature) {
            if (AgentRoleMatrix::hasFeature($user->service, $feature)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Fonctionnalite non autorisee pour votre service.'], 403);
    }
}

