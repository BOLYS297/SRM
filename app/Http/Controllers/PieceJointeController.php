<?php

namespace App\Http\Controllers;

use App\Models\PieceJointe;
use App\Models\Requete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PieceJointeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role === 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        return response()->json(PieceJointe::orderByDesc('date_ajout')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'requete_id' => ['required', 'exists:requetes,id'],
        ]);

        $requete = Requete::findOrFail($request->input('requete_id'));

        if ($user && $user->role === 'etudiant' && $user->etudiant_id !== $requete->etudiant_id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($request->hasFile('fichier')) {
            $request->validate([
                'fichier' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            ]);

            $file = $request->file('fichier');
            $path = $file->store('pieces_jointes', 'public');
            $pieceJointe = PieceJointe::create([
                'nom_fichier' => $file->getClientOriginalName(),
                'type_piece' => $file->getClientMimeType(),
                'chemin_fichier' => $path,
                'date_ajout' => now(),
                'requete_id' => $requete->id,
            ]);
        } else {
            $data = $request->validate([
                'nom_fichier' => ['required', 'string', 'max:255'],
                'type_piece' => ['nullable', 'string', 'max:255'],
                'chemin_fichier' => ['required', 'string', 'max:255'],
                'date_ajout' => ['nullable', 'date'],
            ]);
            $data['date_ajout'] = $data['date_ajout'] ?? now();
            $data['requete_id'] = $requete->id;
            $pieceJointe = PieceJointe::create($data);
        }

        $pieceJointe->url = Storage::disk('public')->url($pieceJointe->chemin_fichier);

        return response()->json($pieceJointe, 201);
    }

    public function show(Request $request, PieceJointe $pieceJointe)
    {
        $user = $request->user();
        if ($user && $user->role === 'etudiant') {
            $requeteId = $pieceJointe->requete_id;
            if (!$user->etudiant_id || !$requeteId || $user->etudiant_id !== $pieceJointe->requete->etudiant_id) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }
        }

        $pieceJointe->url = Storage::disk('public')->url($pieceJointe->chemin_fichier);

        return response()->json($pieceJointe);
    }

    public function update(Request $request, PieceJointe $pieceJointe)
    {
        $user = $request->user();
        if ($user && $user->role === 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $data = $request->validate([
            'nom_fichier' => ['required', 'string', 'max:255'],
            'type_piece' => ['nullable', 'string', 'max:255'],
            'chemin_fichier' => ['required', 'string', 'max:255'],
            'date_ajout' => ['required', 'date'],
            'requete_id' => ['required', 'exists:requetes,id'],
        ]);

        $pieceJointe->update($data);

        $pieceJointe->url = Storage::disk('public')->url($pieceJointe->chemin_fichier);

        return response()->json($pieceJointe);
    }

    public function destroy(Request $request, PieceJointe $pieceJointe)
    {
        $user = $request->user();
        if ($user && $user->role === 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $pieceJointe->delete();

        return response()->json(['message' => 'Piece jointe supprimee.']);
    }
}
