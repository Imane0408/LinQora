<?php
namespace App\Http\Controllers;

use App\Models\Atelier;
use App\Models\Inscription;
use App\Models\PointageAtelier;
use App\Models\PointageEv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointageController extends Controller
{
    /** POST /api/pointage/evenement — Scan QR entrée événement */
    public function scanEvenement(Request $request): JsonResponse
    {
        $request->validate(['codeQr' => 'required|string']);

        $inscription = Inscription::with(['participant.utilisateur', 'evenement', 'ateliers'])
            ->where('codeQr', $request->codeQr)
            ->first();

        if (!$inscription) {
            return response()->json([
                'success' => false,
                'type'    => 'inconnu',
                'alerte'  => '❌ QR code inconnu. Participant non trouvé.',
            ], 404);
        }

        if ($inscription->statut !== 'confirme') {
            return response()->json([
                'success' => false,
                'type'    => 'non_confirme',
                'alerte'  => "⚠️ Inscription non confirmée (statut : {$inscription->statut}).",
                'participant' => $this->formatParticipant($inscription),
            ], 422);
        }

        // Vérifier doublon
        $pointageExistant = PointageEv::where('idInscription', $inscription->idInscription)->first();
        if ($pointageExistant) {
            return response()->json([
                'success'     => false,
                'type'        => 'doublon',
                'alerte'      => '⚠️ Participant déjà scanné à ' . $pointageExistant->heureEntree->format('H:i'),
                'participant' => $this->formatParticipant($inscription),
                'heureEntree' => $pointageExistant->heureEntree,
            ], 409);
        }

        $pointage = PointageEv::create([
            'heureEntree'   => now(),
            'estPresent'    => true,
            'idInscription' => $inscription->idInscription,
            'idOperateur'   => $request->user()->idUtilisateur,
        ]);

        $inscription->update(['presentGlobal' => true]);

        return response()->json([
            'success'     => true,
            'message'     => '✅ Présence enregistrée !',
            'participant' => $this->formatParticipant($inscription),
            'heureEntree' => $pointage->heureEntree,
            'ateliers'    => $inscription->ateliers->map(fn($a) => [
                'titre'   => $a->titre,
                'horaire' => $a->horaire->format('H:i'),
                'salle'   => $a->salle,
            ]),
        ]);
    }

    /** POST /api/pointage/atelier — Scan QR pour un atelier */
    public function scanAtelier(Request $request): JsonResponse
    {
        $request->validate([
            'codeQr'    => 'required|string',
            'idAtelier' => 'required|exists:ateliers,idAtelier',
        ]);

        $inscription = Inscription::with(['participant.utilisateur', 'ateliers'])
            ->where('codeQr', $request->codeQr)
            ->first();

        if (!$inscription) {
            return response()->json([
                'success' => false,
                'alerte'  => '❌ QR code inconnu.',
            ], 404);
        }

        $atelier    = Atelier::findOrFail($request->idAtelier);
        $estInscrit = $inscription->ateliers()->where('idAtelier', $request->idAtelier)->exists();

        $pointageExistant = PointageAtelier::where('idInscription', $inscription->idInscription)
            ->where('idAtelier', $request->idAtelier)
            ->first();

        if ($pointageExistant) {
            return response()->json([
                'success'     => false,
                'alerte'      => '⚠️ Déjà scanné pour cet atelier à ' . $pointageExistant->heureScan->format('H:i'),
                'participant' => $this->formatParticipant($inscription),
            ], 409);
        }

        $pointage = PointageAtelier::create([
            'heureScan'     => now(),
            'estPresent'    => true,
            'idInscription' => $inscription->idInscription,
            'idAtelier'     => $request->idAtelier,
            'idOperateur'   => $request->user()->idUtilisateur,
        ]);

        return response()->json([
            'success'           => true,
            'message'           => '✅ Présence atelier enregistrée !',
            'participant'       => $this->formatParticipant($inscription),
            'atelier'           => $atelier->titre,
            'estInscritAtelier' => $estInscrit,
            'heureScan'         => $pointage->heureScan,
        ]);
    }

    /** GET /api/evenements/{idEvenement}/pointage/stats */
    public function statsPresence(int $idEvenement): JsonResponse
    {
        $inscrits = Inscription::where('idEvenement', $idEvenement)->where('statut', 'confirme')->count();
        $presents = Inscription::where('idEvenement', $idEvenement)->where('presentGlobal', true)->count();

        return response()->json([
            'inscrits'           => $inscrits,
            'presents'           => $presents,
            'absents'            => $inscrits - $presents,
            'taux_participation' => $inscrits > 0 ? round(($presents / $inscrits) * 100, 2) : 0,
        ]);
    }

    /** GET /api/evenements/{idEvenement}/absents */
    public function listeAbsents(int $idEvenement): JsonResponse
    {
        $absents = Inscription::with(['participant.utilisateur'])
            ->where('idEvenement', $idEvenement)
            ->where('statut', 'confirme')
            ->where('presentGlobal', false)
            ->get()
            ->map(fn($i) => [
                'idInscription' => $i->idInscription,
                'nom'           => $i->participant->utilisateur->nom,
                'prenom'        => $i->participant->utilisateur->prenom,
                'email'         => $i->participant->utilisateur->email,
                'organisation'  => $i->participant->organisation,
                'telephone'     => $i->participant->telephone,
            ]);

        return response()->json($absents);
    }

    /** GET /api/ateliers/{idAtelier}/pointage/stats */
    public function statsAtelier(int $idAtelier): JsonResponse
    {
        $atelier  = Atelier::findOrFail($idAtelier);
        $inscrits = $atelier->inscriptions()->count();
        $presents = PointageAtelier::where('idAtelier', $idAtelier)->where('estPresent', true)->count();

        return response()->json([
            'atelier'            => $atelier->titre,
            'inscrits'           => $inscrits,
            'presents'           => $presents,
            'absents'            => $inscrits - $presents,
            'taux_participation' => $inscrits > 0 ? round(($presents / $inscrits) * 100, 2) : 0,
        ]);
    }

    /** GET /api/pointage/historique */
    public function historique(Request $request): JsonResponse
    {
        $query = PointageEv::with(['inscription.participant.utilisateur', 'operateur'])
            ->when($request->filled('idEvenement'), fn($q) =>
                $q->whereHas('inscription', fn($q2) =>
                    $q2->where('idEvenement', $request->idEvenement)
                )
            )
            ->latest('heureEntree');

        return response()->json($query->paginate(30));
    }

    private function formatParticipant(Inscription $inscription): array
    {
        $u = $inscription->participant->utilisateur;
        return [
            'nom'          => $u->nom,
            'prenom'       => $u->prenom,
            'email'        => $u->email,
            'organisation' => $inscription->participant->organisation,
        ];
    }
}
