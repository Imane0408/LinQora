<?php
namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\PointageAtelier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvenementController extends Controller
{
    /** GET /api/evenements */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Evenement::with(['entreprise', 'gestionnaire'])
            ->withCount(['inscriptions', 'ateliers']);

        // Filtrer par entreprise selon le rôle
        if ($user && ($user->isAdminEntreprise() || $user->isGestionnaire() || $user->isOperateurScan())) {
            $query->where('idEntreprise', $user->idEntreprise);
        }

        if ($request->filled('statut'))  $query->where('statut', $request->statut);
        if ($request->filled('mode'))    $query->where('mode', $request->mode);
        if ($request->filled('search'))  $query->where('titre', 'like', "%{$request->search}%");
        if ($request->filled('date_debut')) $query->whereDate('dateDebut', '>=', $request->date_debut);

        return response()->json($query->latest()->paginate(15));
    }

    /** POST /api/evenements */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'titre'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'mode'            => ['required', Rule::in(['presentiel', 'en_ligne', 'hybride'])],
            'dateDebut'       => 'required|date',
            'dateFin'         => 'required|date|after:dateDebut',
            'lieu'            => 'nullable|string|max:255',
            'lienEnLigne'     => 'nullable|url',
            'estPayant'       => 'boolean',
            'prix'            => 'nullable|numeric|min:0',
            'networkingActif' => 'boolean',
            'capaciteMax'     => 'nullable|integer|min:1',
            'banniere'        => 'nullable|image|max:5120',
            'paquettePdf'     => 'nullable|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('banniere')) {
            $validated['banniere'] = $request->file('banniere')->store('bannières/evenements', 'public');
        }
        if ($request->hasFile('paquettePdf')) {
            $validated['paquettePdf'] = $request->file('paquettePdf')->store('pdf/evenements', 'public');
        }

        $validated['idEntreprise']   = $user->idEntreprise;
        $validated['idGestionnaire'] = $user->idUtilisateur;

        $evenement = Evenement::create($validated);
        return response()->json($evenement->load(['entreprise', 'gestionnaire']), 201);
    }

    /** GET /api/evenements/{id} */
    public function show(Evenement $evenement): JsonResponse
    {
        $evenement->load(['entreprise', 'gestionnaire', 'ateliers.speakers']);
        $evenement->loadCount(['inscriptions', 'ateliers']);
        return response()->json($evenement);
    }

    /** GET /api/evenements/slug/{slug} — PUBLIC */
    public function showBySlug(string $slug): JsonResponse
    {
        $evenement = Evenement::with(['ateliers.speakers', 'entreprise'])
            ->where('slug', $slug)
            ->where('statut', 'publie')
            ->firstOrFail();
        return response()->json($evenement);
    }

    /** PUT /api/evenements/{id} */
    public function update(Request $request, Evenement $evenement): JsonResponse
    {
        $validated = $request->validate([
            'titre'           => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'mode'            => ['sometimes', Rule::in(['presentiel', 'en_ligne', 'hybride'])],
            'dateDebut'       => 'sometimes|date',
            'dateFin'         => 'sometimes|date|after:dateDebut',
            'lieu'            => 'nullable|string|max:255',
            'lienEnLigne'     => 'nullable|url',
            'estPayant'       => 'boolean',
            'prix'            => 'nullable|numeric|min:0',
            'networkingActif' => 'boolean',
            'capaciteMax'     => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('banniere')) {
            $validated['banniere'] = $request->file('banniere')->store('bannières/evenements', 'public');
        }
        if ($request->hasFile('paquettePdf')) {
            $validated['paquettePdf'] = $request->file('paquettePdf')->store('pdf/evenements', 'public');
        }

        $evenement->update($validated);
        return response()->json($evenement);
    }

    /** PATCH /api/evenements/{id}/statut */
    public function changerStatut(Request $request, Evenement $evenement): JsonResponse
    {
        $request->validate([
            'statut' => ['required', Rule::in(['brouillon', 'publie', 'archive', 'annule'])],
        ]);
        $evenement->update(['statut' => $request->statut]);
        return response()->json([
            'message'   => "Statut de l'événement mis à jour : {$request->statut}",
            'evenement' => $evenement,
        ]);
    }

    /** DELETE /api/evenements/{id} */
    public function destroy(Evenement $evenement): JsonResponse
    {
        $evenement->delete();
        return response()->json(['message' => 'Événement supprimé.']);
    }

    /** GET /api/evenements/{id}/dashboard */
    public function dashboard(Evenement $evenement): JsonResponse
    {
        $evenement->loadCount(['inscriptions', 'ateliers']);

        $confirmes = $evenement->inscriptions()->where('statut', 'confirme')->count();
        $presents  = $evenement->inscriptions()->where('presentGlobal', true)->count();
        $payes     = $evenement->inscriptions()->where('aPaye', true)->count();

        $statsAteliers = $evenement->ateliers()->with('speakers')->get()->map(fn($a) => [
            'idAtelier'  => $a->idAtelier,
            'titre'      => $a->titre,
            'horaire'    => $a->horaire,
            'salle'      => $a->salle,
            'categorie'  => $a->categorie,
            'capacite'   => $a->capacite,
            'inscrits'   => $a->inscriptions()->count(),
            'presents'   => PointageAtelier::where('idAtelier', $a->idAtelier)->where('estPresent', true)->count(),
            'speakers'   => $a->speakers->map(fn($s) => [
                'nom'   => $s->nomComplet,
                'photo' => $s->photo ? asset('storage/' . $s->photo) : null,
            ]),
        ]);

        return response()->json([
            'evenement'          => $evenement,
            'inscrits_confirmes' => $confirmes,
            'inscrits_presents'  => $presents,
            'inscrits_absents'   => $confirmes - $presents,
            'inscrits_payes'     => $payes,
            'taux_participation' => $confirmes > 0 ? round(($presents / $confirmes) * 100, 2) : 0,
            'stats_ateliers'     => $statsAteliers,
        ]);
    }
}
