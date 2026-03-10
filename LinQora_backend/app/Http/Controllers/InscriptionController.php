<?php
namespace App\Http\Controllers;

use App\Jobs\EnvoyerEmailInscription;
use App\Models\Atelier;
use App\Models\Evenement;
use App\Models\Inscription;
use App\Models\Participant;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InscriptionController extends Controller
{
    /** POST /api/evenements/{evenement}/inscrire — PUBLIC */
    public function inscrire(Request $request, Evenement $evenement): JsonResponse
    {
        if ($evenement->statut !== 'publie') {
            return response()->json(['message' => 'Cet événement n\'est pas ouvert aux inscriptions.'], 403);
        }

        $validated = $request->validate([
            'nom'             => 'required|string|max:255',
            'prenom'          => 'required|string|max:255',
            'email'           => 'required|email',
            'telephone'       => 'nullable|string|max:20',
            'organisation'    => 'nullable|string|max:255',
            'networkingActif' => 'boolean',
            'ateliers'        => 'nullable|array',
            'ateliers.*'      => 'integer|exists:ateliers,idAtelier',
        ]);

        // Créer ou récupérer l'utilisateur participant
        $utilisateur = Utilisateur::firstOrCreate(
            ['email' => $validated['email']],
            [
                'nom'        => $validated['nom'],
                'prenom'     => $validated['prenom'],
                'motDePasse' => bcrypt(Str::random(12)),
                'idRole'     => Role::where('nomRole', Role::PARTICIPANT)->value('idRole'),
                'actif'      => true,
            ]
        );

        $participant = Participant::firstOrCreate(
            ['idUtilisateur' => $utilisateur->idUtilisateur],
            [
                'telephone'    => $validated['telephone'] ?? null,
                'organisation' => $validated['organisation'] ?? null,
            ]
        );

        // Vérifier doublon
        if (Inscription::where('idEvenement', $evenement->idEvenement)
            ->where('idParticipant', $participant->idParticipant)
            ->whereNotIn('statut', ['annule', 'refuse'])
            ->exists()) {
            return response()->json(['message' => 'Vous êtes déjà inscrit à cet événement.'], 409);
        }

        // Vérifier capacité max
        if ($evenement->capaciteMax) {
            $nbInscrits = $evenement->inscriptions()->where('statut', 'confirme')->count();
            if ($nbInscrits >= $evenement->capaciteMax) {
                return response()->json(['message' => 'L\'événement a atteint sa capacité maximale.'], 409);
            }
        }

        $codeQr = 'LQ-' . strtoupper(Str::random(16));

        $inscription = Inscription::create([
            'dateInscr'       => now(),
            'aPaye'           => !$evenement->estPayant,
            'presentGlobal'   => false,
            'codeQr'          => $codeQr,
            'statut'          => $evenement->estPayant ? 'en_attente' : 'confirme',
            'networkingActif' => $validated['networkingActif'] ?? false,
            'idEvenement'     => $evenement->idEvenement,
            'idParticipant'   => $participant->idParticipant,
        ]);

        // Associer les ateliers choisis (valider qu'ils appartiennent à l'événement)
        if (!empty($validated['ateliers'])) {
            $idsValides = Atelier::whereIn('idAtelier', $validated['ateliers'])
                ->where('idEvenement', $evenement->idEvenement)
                ->pluck('idAtelier');
            $inscription->ateliers()->attach($idsValides);
        }

        // Envoyer email de confirmation en arrière-plan (queue)
        EnvoyerEmailInscription::dispatch($inscription->load(['evenement', 'ateliers']), $utilisateur);

        return response()->json([
            'message'     => 'Inscription réussie ! Un email de confirmation vous a été envoyé.',
            'inscription' => $inscription->load(['evenement', 'ateliers']),
            'codeQr'      => $codeQr,
        ], 201);
    }

    /** GET /api/evenements/{evenement}/inscriptions */
    public function index(Evenement $evenement, Request $request): JsonResponse
    {
        $query = $evenement->inscriptions()
            ->with(['participant.utilisateur', 'ateliers'])
            ->latest('dateInscr');

        if ($request->filled('statut'))  $query->where('statut', $request->statut);
        if ($request->filled('present')) $query->where('presentGlobal', (bool)$request->present);
        if ($request->filled('search')) {
            $query->whereHas('participant.utilisateur', fn($q) =>
                $q->where('nom', 'like', "%{$request->search}%")
                  ->orWhere('prenom', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            );
        }

        return response()->json($query->paginate(20));
    }

    /** GET /api/inscriptions/{inscription} */
    public function show(Inscription $inscription): JsonResponse
    {
        return response()->json(
            $inscription->load([
                'participant.utilisateur',
                'evenement',
                'ateliers',
                'pointageEv',
                'pointagesAtelier.atelier',
            ])
        );
    }

    /** GET /api/inscriptions/qr/{code} — PUBLIC */
    public function parCodeQr(string $code): JsonResponse
    {
        $inscription = Inscription::with(['participant.utilisateur', 'evenement', 'ateliers'])
            ->where('codeQr', $code)
            ->firstOrFail();
        return response()->json($inscription);
    }

    /** PATCH /api/inscriptions/{inscription}/valider */
    public function valider(Inscription $inscription): JsonResponse
    {
        $inscription->update(['statut' => 'confirme']);
        return response()->json(['message' => 'Inscription validée manuellement.', 'inscription' => $inscription]);
    }

    /** PATCH /api/inscriptions/{inscription}/annuler */
    public function annuler(Inscription $inscription): JsonResponse
    {
        $inscription->update(['statut' => 'annule']);
        return response()->json(['message' => 'Inscription annulée.', 'inscription' => $inscription]);
    }

    /** PATCH /api/inscriptions/{inscription}/paiement */
    public function marquerPaye(Request $request, Inscription $inscription): JsonResponse
    {
        $request->validate(['methode' => 'nullable|string|max:100']);

        $inscription->update([
            'aPaye'           => true,
            'datePaiement'    => now(),
            'methodePaiement' => $request->methode ?? 'manuel',
            'statut'          => 'confirme',
        ]);

        return response()->json(['message' => 'Paiement enregistré et inscription confirmée.', 'inscription' => $inscription]);
    }
}
