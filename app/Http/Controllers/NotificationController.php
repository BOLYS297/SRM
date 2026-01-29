<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if (!$user->etudiant_id) {
            return response()->json(['message' => 'Compte etudiant non lie.'], 403);
        }

        $notifications = Notification::where('etudiant_id', $user->etudiant_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }

    public function update(Request $request, Notification $notification)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'etudiant') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($notification->etudiant_id !== $user->etudiant_id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $notification->read_at = now();
        $notification->save();

        return response()->json($notification);
    }
}
