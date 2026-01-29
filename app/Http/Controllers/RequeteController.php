<?php

namespace App\Http\Controllers;

use App\Models\Requete;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequeteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Requete::with(['typeRequete', 'decision'])->orderByDesc('date_depot');

        if ($user && $user->role === 'etudiant') {
            if (!$user->etudiant_id) {
                return response()->json(['message' => 'Compte etudiant non lie.'], 403);
            }

            $query->where('etudiant_id', $user->etudiant_id);
        }

        if ($user && $user->role === 'agent') {
            if (!$user->service_id) {
                return response()->json(['message' => 'Agent sans service.'], 403);
            }

            $service = Service::find($user->service_id);
            $query->where(function ($subQuery) use ($user, $service) {
                $subQuery->whereHas('etapeTraitements', function ($etapeQuery) use ($user) {
                    $etapeQuery->where('service_id', $user->service_id)
                        ->whereNull('date_sortie');
                });

                if ($service && $service->isCourrier()) {
                    $subQuery->orWhereDoesntHave('etapeTraitements');
                }
            });
        }

        if ($request->filled('service_id')) {
            $serviceId = (int) $request->query('service_id');
            $query->whereHas('etapeTraitements', function ($subQuery) use ($serviceId) {
                $subQuery->where('service_id', $serviceId);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'date_depot' => ['required', 'date'],
            'objet' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['sometimes', 'in:en_attente,en_traitement,traitee,rejetee'],
            'annee_depot' => ['required', 'string', 'max:255'],
            'filiere_depot' => ['required', 'string', 'max:255'],
            'niveau_depot' => ['required', 'string', 'max:255'],
            'etudiant_id' => ['required', 'exists:etudiants,id'],
            'type_requete_id' => ['required', 'exists:types_requetes,id'],
        ];

        if ($user && $user->role === 'etudiant') {
            if (!$user->etudiant_id) {
                return response()->json(['message' => 'Compte etudiant non lie.'], 403);
            }

            unset($rules['etudiant_id']);
        }

        $data = $request->validate($rules);

        if ($user && $user->role === 'etudiant') {
            $data['etudiant_id'] = $user->etudiant_id;
            $data['statut'] = 'en_attente';
        }

        $requete = Requete::create($data);

        return response()->json($requete, 201);
    }

    public function show(Request $request, Requete $requete)
    {
        $user = $request->user();
        if ($user && $user->role === 'etudiant') {
            if ($requete->etudiant_id !== $user->etudiant_id) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
        }

        if ($user && $user->role === 'agent') {
            if (!$user->service_id) {
                return response()->json(['message' => 'Agent sans service.'], 403);
            }
            $service = Service::find($user->service_id);
            $hasStep = $requete->etapeTraitements()->where('service_id', $user->service_id)->exists();
            $hasAnyStep = $requete->etapeTraitements()->exists();
            $isCourrier = $service && $service->isCourrier();
            if (!$hasStep && !($isCourrier && !$hasAnyStep)) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
        }

        $requete->load(['etudiant', 'typeRequete', 'decision', 'piecesJointes', 'etapeTraitements.service']);

        $requete->piecesJointes->transform(function ($piece) {
            $chemin = $piece->chemin_fichier;
            if (str_starts_with($chemin, 'http://') || str_starts_with($chemin, 'https://') || str_starts_with($chemin, '/')) {
                $piece->url = $chemin;
            } else {
                $piece->url = Storage::disk('public')->url($chemin);
            }
            return $piece;
        });

        return response()->json($requete);
    }

    public function update(Request $request, Requete $requete)
    {
        $user = $request->user();
        if ($user && $user->role === 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $data = $request->validate([
            'date_depot' => ['required', 'date'],
            'objet' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['required', 'in:en_attente,en_traitement,traitee,rejetee'],
            'annee_depot' => ['required', 'string', 'max:255'],
            'filiere_depot' => ['required', 'string', 'max:255'],
            'niveau_depot' => ['required', 'string', 'max:255'],
            'etudiant_id' => ['required', 'exists:etudiants,id'],
            'type_requete_id' => ['required', 'exists:types_requetes,id'],
        ]);

        $requete->update($data);

        return response()->json($requete);
    }

    public function destroy(Request $request, Requete $requete)
    {
        $user = $request->user();
        if ($user && $user->role === 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $requete->delete();

        return response()->json(['message' => 'Requete supprimee.']);
    }
}
