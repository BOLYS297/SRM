<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use App\Models\Notification;
use App\Models\Requete;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DecisionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Decision::orderByDesc('date_decision');

        if ($user && $user->role === 'agent') {
            if (!$user->service_id) {
                return response()->json(['message' => 'Agent sans service.'], 403);
            }
            $query->where('service_id', $user->service_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'date_decision' => ['required', 'date'],
            'resultat' => ['required', 'in:favorable,defavorable,incomplet'],
            'motif' => ['nullable', 'string'],
            'requete_id' => ['required', 'exists:requetes,id', 'unique:decisions,requete_id'],
            'service_id' => ['required', 'exists:services,id'],
        ]);

        if ($user && $user->role === 'agent') {
            if (!$user->service_id) {
                return response()->json(['message' => 'Agent sans service.'], 403);
            }
            $data['service_id'] = $user->service_id;

            $hasAccess = \App\Models\Requete::where('id', $data['requete_id'])
                ->whereHas('etapeTraitements', function ($query) use ($user) {
                    $query->where('service_id', $user->service_id);
                })
                ->exists();
            if (!$hasAccess) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
        }

        $decision = Decision::create($data);
        $this->syncRequeteAndNotification($decision);

        return response()->json($decision, 201);
    }

    public function show(Request $request, Decision $decision)
    {
        $user = $request->user();
        if ($user && $user->role === 'agent') {
            if (!$user->service_id || $decision->service_id !== $user->service_id) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
        }

        return response()->json($decision);
    }

    public function update(Request $request, Decision $decision)
    {
        $user = $request->user();
        $data = $request->validate([
            'date_decision' => ['required', 'date'],
            'resultat' => ['required', 'in:favorable,defavorable,incomplet'],
            'motif' => ['nullable', 'string'],
            'requete_id' => [
                'required',
                'exists:requetes,id',
                Rule::unique('decisions', 'requete_id')->ignore($decision->id),
            ],
            'service_id' => ['required', 'exists:services,id'],
        ]);

        if ($user && $user->role === 'agent') {
            if (!$user->service_id || $decision->service_id !== $user->service_id) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
            $data['service_id'] = $user->service_id;
        }

        $decision->update($data);
        $this->syncRequeteAndNotification($decision);

        return response()->json($decision);
    }

    public function destroy(Decision $decision)
    {
        $decision->delete();

        return response()->json(['message' => 'Decision supprimee.']);
    }

    private function syncRequeteAndNotification(Decision $decision): void
    {
        $requete = Requete::find($decision->requete_id);
        if (!$requete) {
            return;
        }

        $status = match ($decision->resultat) {
            'favorable' => 'traitee',
            'defavorable' => 'rejetee',
            default => 'en_traitement',
        };

        $requete->statut = $status;
        $requete->save();

        $message = sprintf(
            'Decision %s pour la requete #%d.',
            $decision->resultat,
            $requete->id
        );

        Notification::updateOrCreate(
            ['decision_id' => $decision->id],
            [
                'etudiant_id' => $requete->etudiant_id,
                'requete_id' => $requete->id,
                'message' => $message,
                'read_at' => null,
            ]
        );
    }
}
