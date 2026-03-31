<?php

namespace App\Http\Controllers;

use App\Models\Requete;
use App\Models\Service;
use App\Support\AgentRoleMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function agent(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'agent' || !$user->service_id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $service = Service::find($user->service_id);
        $workspace = AgentRoleMatrix::resolve($service);

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

        $aTraiter = (clone $currentQuery)->with(['typeRequete', 'etudiant', 'piecesJointes'])
            ->orderByDesc('date_depot')
            ->limit(30)
            ->get();
        foreach ($aTraiter as $requete) {
            foreach ($requete->piecesJointes as $piece) {
                $chemin = $piece->chemin_fichier;
                if (str_starts_with($chemin, 'http://') || str_starts_with($chemin, 'https://') || str_starts_with($chemin, '/')) {
                    $piece->url = $chemin;
                } else {
                    $piece->url = Storage::disk('public')->url($chemin);
                }
            }
        }

        $focus = [];
        if ($service && $service->isCourrier()) {
            $focus[] = [
                'label' => 'Nouvelles requetes a enregistrer',
                'value' => Requete::whereDoesntHave('etapeTraitements')->count(),
            ];
        }
        if (AgentRoleMatrix::hasFeature($service, 'decision_finale')) {
            $focus[] = [
                'label' => 'Dossiers sans decision',
                'value' => (clone $currentQuery)->count(),
            ];
        }

        return response()->json([
            'stats' => $stats,
            'par_service' => $parService,
            'a_traiter' => $aTraiter,
            'focus' => $focus,
            'workspace' => [
                'service_id' => $service?->id,
                'service_nom' => $service?->nom_service,
                'service_type' => $service?->type_service,
                'service_key' => $workspace['service_key'],
                'title' => $workspace['title'],
                'description' => $workspace['description'],
                'features' => $workspace['features'],
                'quick_actions' => $workspace['quick_actions'],
            ],
        ]);
    }
}
