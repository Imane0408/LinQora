<?php
namespace App\Http\Controllers;

use App\Models\NotificationApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** GET /api/notifications */
    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationApp::where('idUtilisateur', $request->user()->idUtilisateur)
            ->latest('dateCreation')
            ->paginate(20);
        return response()->json($notifications);
    }

    /** GET /api/notifications/non-lues */
    public function nonLues(Request $request): JsonResponse
    {
        $userId = $request->user()->idUtilisateur;
        $count  = NotificationApp::where('idUtilisateur', $userId)->where('lu', false)->count();
        $items  = NotificationApp::where('idUtilisateur', $userId)
            ->where('lu', false)
            ->latest('dateCreation')
            ->take(10)
            ->get();

        return response()->json(['count' => $count, 'notifications' => $items]);
    }

    /** PATCH /api/notifications/{notification}/lire */
    public function marquerLu(Request $request, NotificationApp $notification): JsonResponse
    {
        if ($notification->idUtilisateur !== $request->user()->idUtilisateur) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
        $notification->marquerCommeLu();
        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    /** PATCH /api/notifications/tout-lire */
    public function toutMarquerLu(Request $request): JsonResponse
    {
        NotificationApp::where('idUtilisateur', $request->user()->idUtilisateur)
            ->where('lu', false)
            ->update(['lu' => true, 'dateLecture' => now()]);
        return response()->json(['message' => 'Toutes les notifications marquées comme lues.']);
    }

    /** DELETE /api/notifications/{notification} */
    public function destroy(Request $request, NotificationApp $notification): JsonResponse
    {
        if ($notification->idUtilisateur !== $request->user()->idUtilisateur) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
        $notification->delete();
        return response()->json(['message' => 'Notification supprimée.']);
    }
}
