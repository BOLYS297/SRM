<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\FiliereCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::orderBy('nom_service')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_service' => ['required', 'string', 'max:255', 'unique:services,nom_service'],
            'type_service' => ['nullable', 'string', 'max:255'],
            'code_departement' => ['nullable', Rule::in(FiliereCatalog::codes()), 'unique:services,code_departement'],
        ]);

        if (empty($data['type_service']) && stripos($data['nom_service'], 'courrier') !== false) {
            $data['type_service'] = 'Courrier';
        }

        if (($data['type_service'] ?? null) !== 'Departement') {
            $data['code_departement'] = null;
        }

        $service = Service::create($data);

        return response()->json($service, 201);
    }

    public function show(Service $service)
    {
        return response()->json($service);
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'nom_service' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'nom_service')->ignore($service->id),
            ],
            'type_service' => ['nullable', 'string', 'max:255'],
            'code_departement' => [
                'nullable',
                Rule::in(FiliereCatalog::codes()),
                Rule::unique('services', 'code_departement')->ignore($service->id),
            ],
        ]);

        if (empty($data['type_service']) && stripos($data['nom_service'], 'courrier') !== false) {
            $data['type_service'] = 'Courrier';
        }

        if (($data['type_service'] ?? null) !== 'Departement') {
            $data['code_departement'] = null;
        }

        $service->update($data);

        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json(['message' => 'Service supprime.']);
    }
}
