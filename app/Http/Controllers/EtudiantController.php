<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EtudiantController extends Controller
{
    public function index()
    {
        return response()->json(Etudiant::with('user')->orderBy('nom')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'matricule' => ['required', 'string', 'max:255', 'unique:etudiants,matricule'],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'date_naissance' => ['required', 'date'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $etudiant = Etudiant::create($data);

        return response()->json($etudiant, 201);
    }

    public function show(Etudiant $etudiant)
    {
        return response()->json($etudiant);
    }

    public function update(Request $request, Etudiant $etudiant)
    {
        $data = $request->validate([
            'matricule' => [
                'required',
                'string',
                'max:255',
                Rule::unique('etudiants', 'matricule')->ignore($etudiant->id),
            ],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'date_naissance' => ['required', 'date'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $etudiant->update($data);

        return response()->json($etudiant);
    }

    public function destroy(Etudiant $etudiant)
    {
        $etudiant->delete();

        return response()->json(['message' => 'Etudiant supprime.']);
    }

    public function createCompte(Request $request, Etudiant $etudiant)
    {
        if (User::where('etudiant_id', $etudiant->id)->exists()) {
            return response()->json(['message' => 'Compte etudiant deja existant.'], 409);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'] ?? ($etudiant->prenom . ' ' . $etudiant->nom),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'etudiant',
            'etudiant_id' => $etudiant->id,
        ]);

        return response()->json($user, 201);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if (!$user->etudiant_id) {
            return response()->json(['message' => 'Compte etudiant non lie.'], 403);
        }

        $etudiant = $user->etudiant;
        if (!$etudiant) {
            return response()->json(['message' => 'Etudiant introuvable.'], 404);
        }

        return response()->json([
            'etudiant' => $etudiant,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if (!$user->etudiant_id) {
            return response()->json(['message' => 'Compte etudiant non lie.'], 403);
        }

        $etudiant = $user->etudiant;
        if (!$etudiant) {
            return response()->json(['message' => 'Etudiant introuvable.'], 404);
        }

        $data = $request->validate([
            'telephone' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (array_key_exists('telephone', $data)) {
            $etudiant->telephone = $data['telephone'];
        }

        if (array_key_exists('email', $data)) {
            $etudiant->email = $data['email'];
            $user->email = $data['email'];
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $etudiant->save();
        $user->save();

        return response()->json([
            'etudiant' => $etudiant,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }
}
