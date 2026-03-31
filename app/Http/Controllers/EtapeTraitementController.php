<?php

namespace App\Http\Controllers;

use App\Models\EtapeTraitement;
use App\Models\Requete;
use App\Models\Service;
use App\Support\RequeteWorkflow;
use Illuminate\Http\Request;

class EtapeTraitementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role === 'agent') {
            if (!$user->service_id) {
                return response()->json(['message' => 'Agent sans service.'], 403);
            }

            return response()->json(
                EtapeTraitement::with(['service', 'requete'])
                    ->where('service_id', $user->service_id)
                    ->orderBy('requete_id')
                    ->orderBy('ordre_etape')
                    ->get()
            );
        }

        return response()->json(
            EtapeTraitement::with(['service', 'requete'])
                ->orderBy('requete_id')
                ->orderBy('ordre_etape')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'action' => ['required', 'string', 'max:255'],
            'observation' => ['nullable', 'string'],
            'requete_id' => ['required', 'exists:requetes,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'service_suivant_id' => ['nullable', 'exists:services,id'],
        ]);

        if ($user && $user->role === 'agent') {
            if (!$user->service_id) {
                return response()->json(['message' => 'Agent sans service.'], 403);
            }

            $data['service_id'] = $user->service_id;
        } elseif (empty($data['service_id'])) {
            return response()->json(['message' => 'service_id requis.'], 422);
        }

        $service = Service::find($data['service_id']);
        $requete = Requete::findOrFail($data['requete_id']);
        $requete->loadMissing('typeRequete');
        if (!$service) {
            return response()->json(['message' => 'Service introuvable.'], 404);
        }

        $openStep = EtapeTraitement::query()
            ->where('requete_id', $requete->id)
            ->where('service_id', $service->id)
            ->whereNull('date_sortie')
            ->orderByDesc('ordre_etape')
            ->first();

        if ($user && $user->role === 'agent') {
            $hasAnyStep = EtapeTraitement::query()
                ->where('requete_id', $requete->id)
                ->exists();

            if ($service->isCourrier()) {
                if (!$openStep && $hasAnyStep) {
                    return response()->json(['message' => 'Requete deja transmise.'], 403);
                }
            } elseif (!$openStep) {
                return response()->json(['message' => 'Requete non assignee a votre service.'], 403);
            }
        }

        if (!$openStep) {
            $openStep = EtapeTraitement::create([
                'ordre_etape' => $this->nextOrder($requete->id),
                'action' => 'reception',
                'date_entree' => $this->resolveEntryDate($requete),
                'date_sortie' => null,
                'observation' => null,
                'requete_id' => $requete->id,
                'service_id' => $service->id,
            ]);
        }

        $timestampTraitement = now();
        $openStep->action = $data['action'];
        $openStep->observation = $data['observation'] ?? null;
        $openStep->date_sortie = $timestampTraitement;
        $openStep->save();

        $serviceSuivant = $this->resolveNextService(
            $requete,
            $service,
            isset($data['service_suivant_id']) ? (int) $data['service_suivant_id'] : null
        );
        if ($serviceSuivant instanceof \Illuminate\Http\JsonResponse) {
            return $serviceSuivant;
        }

        $this->createNextReceptionStep(
            requeteId: $requete->id,
            serviceSource: $service,
            serviceSuivant: $serviceSuivant,
            timestampEntree: $timestampTraitement->copy()
        );

        $openStep->load(['service', 'requete']);

        return response()->json($openStep, 201);
    }

    public function show(EtapeTraitement $etapeTraitement)
    {
        $etapeTraitement->load(['service', 'requete']);

        return response()->json($etapeTraitement);
    }

    public function update(Request $request, EtapeTraitement $etapeTraitement)
    {
        $user = $request->user();
        if ($user && $user->role === 'agent') {
            if (!$user->service_id || $etapeTraitement->service_id !== $user->service_id) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
        }

        $data = $request->validate([
            'action' => ['required', 'string', 'max:255'],
            'observation' => ['nullable', 'string'],
            'service_suivant_id' => ['nullable', 'exists:services,id'],
        ]);

        $etapeTraitement->action = $data['action'];
        $etapeTraitement->observation = $data['observation'] ?? null;
        if (is_null($etapeTraitement->date_sortie)) {
            $etapeTraitement->date_sortie = now();
        }
        $etapeTraitement->save();

        $serviceSource = Service::find($etapeTraitement->service_id);
        $requete = Requete::find($etapeTraitement->requete_id);
        if ($serviceSource && $requete) {
            $requete->loadMissing('typeRequete');
            $serviceSuivant = $this->resolveNextService(
                $requete,
                $serviceSource,
                isset($data['service_suivant_id']) ? (int) $data['service_suivant_id'] : null
            );
            if ($serviceSuivant instanceof \Illuminate\Http\JsonResponse) {
                return $serviceSuivant;
            }

            $this->createNextReceptionStep(
                requeteId: $etapeTraitement->requete_id,
                serviceSource: $serviceSource,
                serviceSuivant: $serviceSuivant,
                timestampEntree: $etapeTraitement->date_sortie->copy()
            );
        }

        $etapeTraitement->load(['service', 'requete']);

        return response()->json($etapeTraitement);
    }

    public function destroy(Request $request, EtapeTraitement $etapeTraitement)
    {
        $user = $request->user();
        if ($user && $user->role === 'agent') {
            if (!$user->service_id || $etapeTraitement->service_id !== $user->service_id) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
        }

        $etapeTraitement->delete();

        return response()->json(['message' => 'Etape de traitement supprimee.']);
    }

    private function nextOrder(int $requeteId): int
    {
        $max = EtapeTraitement::query()
            ->where('requete_id', $requeteId)
            ->max('ordre_etape');

        return ((int) $max) + 1;
    }

    private function resolveEntryDate(Requete $requete)
    {
        $lastStep = EtapeTraitement::query()
            ->where('requete_id', $requete->id)
            ->orderByDesc('ordre_etape')
            ->orderByDesc('id')
            ->first();

        if ($lastStep) {
            return $lastStep->date_sortie ?: $lastStep->date_entree;
        }

        return $requete->date_depot;
    }

    private function createNextReceptionStep(
        int $requeteId,
        Service $serviceSource,
        ?Service $serviceSuivant,
        $timestampEntree
    ): void {
        if (!$serviceSuivant) {
            return;
        }

        $nextOpen = EtapeTraitement::query()
            ->where('requete_id', $requeteId)
            ->where('service_id', $serviceSuivant->id)
            ->whereNull('date_sortie')
            ->first();
        if ($nextOpen) {
            return;
        }

        EtapeTraitement::create([
            'ordre_etape' => $this->nextOrder($requeteId),
            'action' => 'reception',
            'date_entree' => $timestampEntree,
            'date_sortie' => null,
            'observation' => 'Transmis par ' . $serviceSource->nom_service,
            'requete_id' => $requeteId,
            'service_id' => $serviceSuivant->id,
        ]);
    }

    private function resolveNextService(
        Requete $requete,
        Service $serviceSource,
        ?int $manualNextId
    ) {
        $expected = RequeteWorkflow::nextService($requete, $serviceSource);
        if (!$expected) {
            if ($manualNextId) {
                return response()->json(['message' => 'Aucun service suivant prevu pour ce type de requete.'], 422);
            }

            return null;
        }

        if (!$manualNextId) {
            return $expected;
        }

        $manual = Service::find($manualNextId);
        if (!$manual) {
            return response()->json(['message' => 'Service suivant introuvable.'], 404);
        }

        if (!RequeteWorkflow::isAllowedNextService($requete, $serviceSource, $manual)) {
            return response()->json([
                'message' => 'Service suivant invalide. Attendu: ' . $expected->nom_service . '.',
            ], 422);
        }

        return $manual;
    }
}
