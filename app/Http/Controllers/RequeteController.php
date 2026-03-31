<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use App\Models\EtapeTraitement;
use App\Models\Notification;
use App\Models\Requete;
use App\Models\Service;
use App\Support\FiliereCatalog;
use App\Support\RequeteWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RequeteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Requete::with(['typeRequete', 'decision', 'etudiant', 'piecesJointes'])
            ->orderByDesc('date_depot');

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

        $requetes = $query->get();
        $this->appendPieceUrls($requetes);

        return response()->json($requetes);
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
            'filiere_depot' => ['required', Rule::in(FiliereCatalog::codes())],
            'niveau_depot' => ['required', 'string', 'max:255'],
            'etudiant_id' => ['required', 'exists:etudiants,id'],
            'type_requete_id' => ['required', 'exists:types_requetes,id'],
        ];

        if ($user && $user->role === 'etudiant') {
            if (!$user->etudiant_id) {
                return response()->json(['message' => 'Compte etudiant non lie.'], 403);
            }

            unset($rules['etudiant_id']);
            unset($rules['filiere_depot']);
        }

        $data = $request->validate($rules);

        if ($user && $user->role === 'etudiant') {
            $etudiant = $user->etudiant;
            $filiere = FiliereCatalog::normalizeCode($etudiant?->filiere);
            if (!$filiere) {
                return response()->json([
                    'message' => 'La filiere de votre profil est manquante. Contactez un agent pour la renseigner.',
                ], 422);
            }

            $data['etudiant_id'] = $user->etudiant_id;
            $data['filiere_depot'] = $filiere;
            $data['statut'] = 'en_attente';
        }

        $requete = Requete::create($data);
        $requete->load('typeRequete');

        $entryService = RequeteWorkflow::entryService($requete->typeRequete);
        if ($entryService) {
            EtapeTraitement::firstOrCreate(
                [
                    'requete_id' => $requete->id,
                    'service_id' => $entryService->id,
                    'ordre_etape' => 1,
                ],
                [
                    'action' => 'reception',
                    'date_entree' => $requete->date_depot,
                    'date_sortie' => null,
                    'observation' => 'Requete deposee par etudiant',
                ]
            );
        }

        return response()->json($requete, 201);
    }

    public function traiter(Request $request, Requete $requete)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'agent' || !$user->service_id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $service = Service::find($user->service_id);
        if (!$service) {
            return response()->json(['message' => 'Service introuvable.'], 404);
        }

        $data = $request->validate([
            'action' => ['required', 'in:valider,rejeter'],
            'motif' => ['nullable', 'string', 'required_if:action,rejeter'],
        ]);

        $requete->load('typeRequete');

        $openStep = EtapeTraitement::query()
            ->where('requete_id', $requete->id)
            ->where('service_id', $service->id)
            ->whereNull('date_sortie')
            ->orderByDesc('ordre_etape')
            ->first();

        if (!$openStep) {
            return response()->json(['message' => 'Requete non assignee a votre service.'], 403);
        }

        $result = DB::transaction(function () use ($data, $requete, $service, $openStep) {
            $now = now();
            $openStep->action = $data['action'];
            $openStep->observation = $data['motif'] ?? null;
            $openStep->date_sortie = $now;
            $openStep->save();

            if ($data['action'] === 'rejeter') {
                $requete->statut = 'rejetee';
                $requete->save();

                $decision = Decision::updateOrCreate(
                    ['requete_id' => $requete->id],
                    [
                        'date_decision' => $now,
                        'resultat' => 'defavorable',
                        'motif' => $data['motif'],
                        'service_id' => $service->id,
                    ]
                );
                $this->syncNotification($requete, $decision);

                return ['message' => 'Requete rejetee.', 'next_service' => null];
            }

            $nextService = RequeteWorkflow::nextService($requete, $service);
            if ($nextService) {
                $nextOpen = EtapeTraitement::query()
                    ->where('requete_id', $requete->id)
                    ->where('service_id', $nextService->id)
                    ->whereNull('date_sortie')
                    ->first();

                if (!$nextOpen) {
                    EtapeTraitement::create([
                        'ordre_etape' => ((int) EtapeTraitement::where('requete_id', $requete->id)->max('ordre_etape')) + 1,
                        'action' => 'reception',
                        'date_entree' => $now,
                        'date_sortie' => null,
                        'observation' => 'Transmis par ' . $service->nom_service,
                        'requete_id' => $requete->id,
                        'service_id' => $nextService->id,
                    ]);
                }

                $requete->statut = 'en_traitement';
                $requete->save();

                return ['message' => 'Requete validee et transmise.', 'next_service' => $nextService->nom_service];
            }

            $requete->statut = 'traitee';
            $requete->save();

            $decision = Decision::updateOrCreate(
                ['requete_id' => $requete->id],
                [
                    'date_decision' => $now,
                    'resultat' => 'favorable',
                    'motif' => null,
                    'service_id' => $service->id,
                ]
            );
            $this->syncNotification($requete, $decision);

            return ['message' => 'Requete finalisee.', 'next_service' => null];
        });

        $requete->load(['typeRequete', 'decision', 'etudiant', 'piecesJointes', 'etapeTraitements.service']);
        $this->appendPieceUrls(collect([$requete]));

        return response()->json([
            'message' => $result['message'],
            'next_service' => $result['next_service'],
            'requete' => $requete,
        ]);
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
        $this->appendPieceUrls(collect([$requete]));

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
            'filiere_depot' => ['required', Rule::in(FiliereCatalog::codes())],
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

    private function syncNotification(Requete $requete, Decision $decision): void
    {
        Notification::updateOrCreate(
            ['decision_id' => $decision->id],
            [
                'etudiant_id' => $requete->etudiant_id,
                'requete_id' => $requete->id,
                'message' => 'Decision ' . $decision->resultat . ' pour la requete #' . $requete->id . '.',
                'read_at' => null,
            ]
        );
    }

    private function appendPieceUrls($requetes): void
    {
        foreach ($requetes as $requete) {
            foreach ($requete->piecesJointes as $piece) {
                $chemin = $piece->chemin_fichier;
                if (str_starts_with($chemin, 'http://') || str_starts_with($chemin, 'https://') || str_starts_with($chemin, '/')) {
                    $piece->url = $chemin;
                } else {
                    $piece->url = Storage::disk('public')->url($chemin);
                }
            }
        }
    }
}
