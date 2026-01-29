<?php

namespace App\Http\Controllers;

use App\Models\TypeRequete;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TypeRequeteController extends Controller
{
    public function index()
    {
        return response()->json(TypeRequete::orderBy('libelle')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle' => ['required', 'string', 'max:255', 'unique:types_requetes,libelle'],
            'delai_cible_hrs' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $type = TypeRequete::create($data);

        return response()->json($type, 201);
    }

    public function show(TypeRequete $typeRequete)
    {
        return response()->json($typeRequete);
    }

    public function update(Request $request, TypeRequete $typeRequete)
    {
        $data = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:255',
                Rule::unique('types_requetes', 'libelle')->ignore($typeRequete->id),
            ],
            'delai_cible_hrs' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $typeRequete->update($data);

        return response()->json($typeRequete);
    }

    public function destroy(TypeRequete $typeRequete)
    {
        $typeRequete->delete();

        return response()->json(['message' => 'Type de requete supprime.']);
    }
}
