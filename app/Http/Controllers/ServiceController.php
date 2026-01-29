<?php

namespace App\Http\Controllers;

use App\Models\Service;
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
        ]);

        if (empty($data['type_service']) && stripos($data['nom_service'], 'courrier') !== false) {
            $data['type_service'] = 'Courrier';
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
        ]);

        if (empty($data['type_service']) && stripos($data['nom_service'], 'courrier') !== false) {
            $data['type_service'] = 'Courrier';
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
