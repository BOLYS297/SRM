<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        return response()->json(
            User::with('service')
                ->where('role', 'agent')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'service_id' => ['required', 'exists:services,id'],
        ]);

        $agent = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'agent',
            'service_id' => $data['service_id'],
            'etudiant_id' => null,
        ]);

        return response()->json($agent, 201);
    }

    public function show(User $agent)
    {
        if ($agent->role !== 'agent') {
            return response()->json(['message' => 'Agent introuvable.'], 404);
        }

        $agent->load('service');

        return response()->json($agent);
    }

    public function update(Request $request, User $agent)
    {
        if ($agent->role !== 'agent') {
            return response()->json(['message' => 'Agent introuvable.'], 404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($agent->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'service_id' => ['required', 'exists:services,id'],
        ]);

        $agent->name = $data['name'];
        $agent->email = $data['email'];
        $agent->service_id = $data['service_id'];
        if (!empty($data['password'])) {
            $agent->password = Hash::make($data['password']);
        }
        $agent->save();

        return response()->json($agent);
    }

    public function destroy(Request $request, User $agent)
    {
        if ($agent->role !== 'agent') {
            return response()->json(['message' => 'Agent introuvable.'], 404);
        }

        $user = $request->user();
        if ($user && $user->id === $agent->id) {
            return response()->json(['message' => 'Impossible de supprimer votre compte.'], 403);
        }

        $agent->delete();

        return response()->json(['message' => 'Agent supprime.']);
    }
}
