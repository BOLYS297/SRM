<?php

namespace App\Http\Controllers;

use App\Models\EtapeTraitement;
use App\Models\Service;
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
            'ordre_etape' => ['required', 'integer', 'min:1'],
            'action' => ['required', 'string', 'max:255'],
            'date_entree' => ['required', 'date'],
            'date_sortie' => ['nullable', 'date', 'after_or_equal:date_entree'],
            'observation' => ['nullable', 'string'],
            'requete_id' => ['required', 'exists:requetes,id'],
            'service_id' => ['required', 'exists:services,id'],
            'service_suivant_id' => ['nullable', 'exists:services,id'],
        ]);

        if ($user && $user->role === 'agent') {
            if (!$user->service_id) {
                return response()->json(['message' => 'Agent sans service.'], 403);
            }
            $data['service_id'] = $user->service_id;

            $service = Service::find($user->service_id);
            $hasOpenStep = EtapeTraitement::where('requete_id', $data['requete_id'])
                ->where('service_id', $user->service_id)
                ->whereNull('date_sortie')
                ->exists();
            $hasAnyStep = EtapeTraitement::where('requete_id', $data['requete_id'])->exists();

            if ($service && $service->isCourrier()) {
                if (!$hasOpenStep && $hasAnyStep) {
                    return response()->json(['message' => 'Requete deja transmise.'], 403);
                }
            } else {
                if (!$hasOpenStep) {
                    return response()->json(['message' => 'Requete non assignee a votre service.'], 403);
                }
            }
        }

        $etape = EtapeTraitement::create($data);

        if (!empty($data['service_suivant_id']) && (int) $data['service_suivant_id'] !== (int) $data['service_id']) {
            $service = Service::find($data['service_id']);
            EtapeTraitement::create([
                'ordre_etape' => $data['ordre_etape'] + 1,
                'action' => 'reception',
                'date_entree' => now(),
                'date_sortie' => null,
                'observation' => $service ? 'Transmis par ' . $service->nom_service : null,
                'requete_id' => $data['requete_id'],
                'service_id' => $data['service_suivant_id'],
            ]);
        }

        return response()->json($etape, 201);
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
            'ordre_etape' => ['required', 'integer', 'min:1'],
            'action' => ['required', 'string', 'max:255'],
            'date_entree' => ['required', 'date'],
            'date_sortie' => ['nullable', 'date', 'after_or_equal:date_entree'],
            'observation' => ['nullable', 'string'],
            'requete_id' => ['required', 'exists:requetes,id'],
            'service_id' => ['required', 'exists:services,id'],
        ]);

        if ($user && $user->role === 'agent') {
            $data['service_id'] = $user->service_id;
        }

        $etapeTraitement->update($data);

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
}
