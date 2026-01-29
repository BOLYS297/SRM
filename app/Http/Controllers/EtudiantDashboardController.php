<?php

namespace App\Http\Controllers;

use App\Models\Requete;
use Illuminate\Http\Request;

class EtudiantDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'etudiant' || !$user->etudiant_id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $stats = [
            'total' => Requete::where('etudiant_id', $user->etudiant_id)->count(),
            'en_attente' => Requete::where('etudiant_id', $user->etudiant_id)->where('statut', 'en_attente')->count(),
            'en_traitement' => Requete::where('etudiant_id', $user->etudiant_id)->where('statut', 'en_traitement')->count(),
            'traitee' => Requete::where('etudiant_id', $user->etudiant_id)->where('statut', 'traitee')->count(),
            'rejetee' => Requete::where('etudiant_id', $user->etudiant_id)->where('statut', 'rejetee')->count(),
        ];

        $recents = Requete::with(['typeRequete', 'decision'])
            ->where('etudiant_id', $user->etudiant_id)
            ->orderByDesc('date_depot')
            ->limit(6)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recents' => $recents,
        ]);
    }
}
