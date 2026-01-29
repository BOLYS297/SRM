<?php

namespace App\Http\Controllers;

use App\Models\Requete;
use App\Models\Service;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function agent(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'agent' || !$user->service_id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $service = Service::find($user->service_id);
        $currentQuery = Requete::query()->whereHas('etapeTraitements', function ($query) use ($user) {
            $query->where('service_id', $user->service_id)
                ->whereNull('date_sortie');
        });

        if ($service && $service->isCourrier()) {
            $currentQuery->orWhereDoesntHave('etapeTraitements');
        }

        $stats = [
            'total' => (clone $currentQuery)->count(),
            'en_attente' => (clone $currentQuery)->where('statut', 'en_attente')->count(),
            'en_traitement' => (clone $currentQuery)->where('statut', 'en_traitement')->count(),
            'traitee' => (clone $currentQuery)->where('statut', 'traitee')->count(),
            'rejetee' => (clone $currentQuery)->where('statut', 'rejetee')->count(),
        ];

        $parService = collect();
        if ($service) {
            $parService = collect([
                [
                    'id' => $service->id,
                    'nom_service' => $service->nom_service,
                    'total_requetes' => $stats['total'],
                ],
            ]);
        }

        $recents = (clone $currentQuery)->with('typeRequete')
            ->orderByDesc('date_depot')
            ->limit(6)
            ->get();

        return response()->json([
            'stats' => $stats,
            'par_service' => $parService,
            'recents' => $recents,
        ]);
    }
}
