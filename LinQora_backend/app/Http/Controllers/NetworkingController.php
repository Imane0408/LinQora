<?php
namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\NotificationApp;
use App\Models\ProfilNetworking;
use App\Models\Rencontre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NetworkingController extends Controller
{
    /** POST /api/networking/evenements/{evenement}/profil */
    public function creerOuMettreAJourProfil(Request $request, Evenement $evenement): JsonResponse
    {
        $user        = $request->user();
        $participant = $user->participant;

        if (!$participant) {
            return response()->json(['message' => 'Profil participant introuvable.'], 404);
        }

        // Vérifier que le participant est inscrit et confirmé
        $inscription = $evenement->inscriptions()
            ->where('idParticipant', $participant->idParticipant)
            ->where('statut', 'confirme')
            ->first();

        if (!$inscription) {
            return response()->json(['message' => 'Vous devez être inscrit (confirmé) à cet événement.'], 403);
        }

        $validated = $request->validate([
            'poste'          => 'nullable|string|max:255',
            'linkedin'       => 'nullable|url',
            'objectifs'      => 'nullable|string|max:1000',
            'disponibilites' => 'nullable|array',
            'photo'          => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos/networking', 'public');
        }

        $profil = ProfilNetworking::updateOrCreate(
            ['idParticipant' => $participant->idParticipant, 'idEvenement' => $evenement->idEvenement],
            array_merge($validated, ['actif' => true])
        );

        $inscription->update(['networkingActif' => true]);

        return response()->json($profil, 201);
    }

    /** GET /api/networking/evenements/{evenement}/mon-profil */
    public function monProfil(Request $request, Evenement $evenement): JsonResponse
    {
        $participant = $request->user()->participant;
        if (!$participant) return response()->json(null);

        $profil = ProfilNetworking::where('idEvenement', $evenement->idEvenement)
            ->where('idParticipant', $participant->idParticipant)
            ->first();

        return response()->json($profil);
    }

    /** DELETE /api/networking/evenements/{evenement}/profil */
    public function desactiverProfil(Request $request, Evenement $evenement): JsonResponse
    {
        $participant = $request->user()->participant;
        if (!$participant) return response()->json(['message' => 'Profil introuvable.'], 404);

        ProfilNetworking::where('idEvenement', $evenement->idEvenement)
            ->where('idParticipant', $participant->idParticipant)
            ->update(['actif' => false]);

        return response()->json(['message' => 'Profil networking désactivé.']);
    }

    /** GET /api/networking/evenements/{evenement}/participants */
    public function listeParticipants(Request $request, Evenement $evenement): JsonResponse
    {
        $user      = $request->user();
        $monProfil = $user->participant
            ? ProfilNetworking::where('idEvenement', $evenement->idEvenement)
                ->where('idParticipant', $user->participant->idParticipant)->first()
            : null;

        $profils = ProfilNetworking::with(['participant.utilisateur'])
            ->where('idEvenement', $evenement->idEvenement)
            ->where('actif', true)
            ->when($monProfil, fn($q) => $q->where('idProfil', '!=', $monProfil->idProfil))
            ->get()
            ->map(fn($p) => [
                'idProfil'       => $p->idProfil,
                'poste'          => $p->poste,
                'linkedin'       => $p->linkedin,
                'objectifs'      => $p->objectifs,
                'photo'          => $p->photo ? asset('storage/' . $p->photo) : null,
                'disponibilites' => $p->disponibilites,
                'nom'            => $p->participant->utilisateur->nom,
                'prenom'         => $p->participant->utilisateur->prenom,
                'organisation'   => $p->participant->organisation,
            ]);

        return response()->json($profils);
    }

    /** GET /api/networking/profils/{idProfil} */
    public function voirProfil(int $idProfil): JsonResponse
    {
        $profil = ProfilNetworking::with(['participant.utilisateur'])->findOrFail($idProfil);
        return response()->json([
            'idProfil'       => $profil->idProfil,
            'poste'          => $profil->poste,
            'linkedin'       => $profil->linkedin,
            'objectifs'      => $profil->objectifs,
            'photo'          => $profil->photo ? asset('storage/' . $profil->photo) : null,
            'disponibilites' => $profil->disponibilites,
            'nom'            => $profil->participant->utilisateur->nom,
            'prenom'         => $profil->participant->utilisateur->prenom,
            'organisation'   => $profil->participant->organisation,
        ]);
    }

    /** POST /api/networking/rencontres */
    public function proposerRencontre(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'idProfilReceveur' => 'required|exists:profil_networking,idProfil',
            'creaneau'         => 'required|date|after:now',
            'message'          => 'nullable|string|max:500',
            'lieu'             => 'nullable|string|max:255',
        ]);

        $user   = $request->user();
        $profil = ProfilNetworking::whereHas('participant', fn($q) =>
            $q->where('idUtilisateur', $user->idUtilisateur)
        )->first();

        if (!$profil) {
            return response()->json(['message' => 'Vous devez d\'abord créer un profil networking.'], 422);
        }

        if ($profil->idProfil === (int)$validated['idProfilReceveur']) {
            return response()->json(['message' => 'Impossible de proposer une rencontre avec vous-même.'], 422);
        }

        // Vérifier si une rencontre active existe déjà
        $existant = Rencontre::where('idProfilDemandeur', $profil->idProfil)
            ->where('idProfilReceveur', $validated['idProfilReceveur'])
            ->whereIn('statut', ['en_attente', 'accepte'])
            ->exists();

        if ($existant) {
            return response()->json(['message' => 'Une demande de rencontre existe déjà avec ce participant.'], 409);
        }

        $rencontre = Rencontre::create([
            'creaneau'          => $validated['creaneau'],
            'statut'            => 'en_attente',
            'message'           => $validated['message'] ?? null,
            'lieu'              => $validated['lieu'] ?? null,
            'idProfilDemandeur' => $profil->idProfil,
            'idProfilReceveur'  => $validated['idProfilReceveur'],
        ]);

        // Notifier le receveur
        $profilReceveur = ProfilNetworking::find($validated['idProfilReceveur']);
        NotificationApp::create([
            'sujet'         => 'Nouvelle proposition de rencontre',
            'contenu'       => "{$user->prenom} {$user->nom} vous propose une rencontre le " .
                               \Carbon\Carbon::parse($validated['creaneau'])->format('d/m/Y à H:i') . '.',
            'statutEnvoi'   => 'en_attente',
            'type'          => 'interne',
            'dateCreation'  => now(),
            'idUtilisateur' => $profilReceveur->participant->utilisateur->idUtilisateur,
            'idRencontre'   => $rencontre->idRencontre,
        ]);

        return response()->json(
            $rencontre->load(['profilDemandeur.participant.utilisateur', 'profilReceveur.participant.utilisateur']),
            201
        );
    }

    /** PATCH /api/networking/rencontres/{rencontre}/repondre */
    public function repondreRencontre(Request $request, Rencontre $rencontre): JsonResponse
    {
        $request->validate(['action' => ['required', Rule::in(['accepter', 'refuser'])]]);

        $user   = $request->user();
        $profil = ProfilNetworking::whereHas('participant', fn($q) =>
            $q->where('idUtilisateur', $user->idUtilisateur)
        )->where('idProfil', $rencontre->idProfilReceveur)->first();

        if (!$profil) {
            return response()->json(['message' => 'Action non autorisée. Vous n\'êtes pas le destinataire.'], 403);
        }

        $statut = $request->action === 'accepter' ? 'accepte' : 'refuse';
        $rencontre->update(['statut' => $statut]);

        // Notifier le demandeur
        $demandeur = $rencontre->profilDemandeur->participant->utilisateur;
        NotificationApp::create([
            'sujet'         => 'Rencontre ' . ($statut === 'accepte' ? 'acceptée ✅' : 'refusée ❌'),
            'contenu'       => "{$user->prenom} {$user->nom} a " .
                               ($statut === 'accepte' ? 'accepté' : 'refusé') . " votre proposition de rencontre.",
            'statutEnvoi'   => 'en_attente',
            'type'          => 'interne',
            'dateCreation'  => now(),
            'idUtilisateur' => $demandeur->idUtilisateur,
            'idRencontre'   => $rencontre->idRencontre,
        ]);

        return response()->json(['message' => "Rencontre {$statut}.", 'rencontre' => $rencontre]);
    }

    /** PATCH /api/networking/rencontres/{rencontre}/annuler */
    public function annulerRencontre(Request $request, Rencontre $rencontre): JsonResponse
    {
        $user   = $request->user();
        $estConcerne = ProfilNetworking::whereHas('participant', fn($q) =>
            $q->where('idUtilisateur', $user->idUtilisateur)
        )->whereIn('idProfil', [$rencontre->idProfilDemandeur, $rencontre->idProfilReceveur])->exists();

        if (!$estConcerne) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $rencontre->update(['statut' => 'annule']);
        return response()->json(['message' => 'Rencontre annulée.']);
    }

    /** POST /api/networking/rencontres/{rencontre}/feedback */
    public function ajouterFeedback(Request $request, Rencontre $rencontre): JsonResponse
    {
        $request->validate(['feedback' => 'required|string|max:1000']);
        $rencontre->update(['feedback' => $request->feedback, 'statut' => 'termine']);
        return response()->json(['message' => 'Feedback enregistré.', 'rencontre' => $rencontre]);
    }

    /** GET /api/networking/mes-rencontres */
    public function mesRencontres(Request $request): JsonResponse
    {
        $user   = $request->user();
        $profil = ProfilNetworking::whereHas('participant', fn($q) =>
            $q->where('idUtilisateur', $user->idUtilisateur)
        )->first();

        if (!$profil) return response()->json([]);

        $rencontres = Rencontre::with([
            'profilDemandeur.participant.utilisateur',
            'profilReceveur.participant.utilisateur',
        ])
        ->where('idProfilDemandeur', $profil->idProfil)
        ->orWhere('idProfilReceveur', $profil->idProfil)
        ->orderBy('creaneau')
        ->get();

        return response()->json($rencontres);
    }
}
