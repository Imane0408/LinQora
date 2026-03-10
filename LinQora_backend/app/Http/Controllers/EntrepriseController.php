<?php
namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Inscription;
use App\Models\Participant;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EntrepriseController extends Controller
{
    /** GET /api/entreprises */
    public function index(): JsonResponse
    {
        $entreprises = Entreprise::withCount(['utilisateurs', 'evenements'])
            ->latest()->paginate(15);
        return response()->json($entreprises);
    }

    /** POST /api/entreprises */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'            => 'required|string|max:255',
            'email'          => 'nullable|email|unique:entreprises,email',
            'telephone'      => 'nullable|string|max:20',
            'adresse'        => 'nullable|string',
            'logo'           => 'nullable|image|max:2048',
            // Admin initial de l'entreprise
            'admin_nom'      => 'required|string|max:255',
            'admin_prenom'   => 'required|string|max:255',
            'admin_email'    => 'required|email|unique:utilisateurs,email',
            'admin_password' => 'required|min:8',
        ]);

        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('logos/entreprises', 'public')
            : null;

        $entreprise = Entreprise::create([
            'nom'       => $validated['nom'],
            'email'     => $validated['email'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'adresse'   => $validated['adresse'] ?? null,
            'logo'      => $logoPath,
        ]);

        $roleAdmin = Role::where('nomRole', Role::ADMIN_ENTREPRISE)->firstOrFail();
        Utilisateur::create([
            'nom'          => $validated['admin_nom'],
            'prenom'       => $validated['admin_prenom'],
            'email'        => $validated['admin_email'],
            'motDePasse'   => $validated['admin_password'],
            'idEntreprise' => $entreprise->idEntreprise,
            'idRole'       => $roleAdmin->idRole,
            'actif'        => true,
        ]);

        return response()->json($entreprise->load('utilisateurs'), 201);
    }

    /** GET /api/entreprises/{id} */
    public function show(Entreprise $entreprise): JsonResponse
    {
        $entreprise->load(['utilisateurs.role', 'evenements']);
        $entreprise->loadCount(['utilisateurs', 'evenements']);
        return response()->json($entreprise);
    }

    /** PUT /api/entreprises/{id} */
    public function update(Request $request, Entreprise $entreprise): JsonResponse
    {
        $validated = $request->validate([
            'nom'       => 'sometimes|string|max:255',
            'email'     => ['sometimes', 'nullable', 'email',
                            Rule::unique('entreprises', 'email')->ignore($entreprise->idEntreprise, 'idEntreprise')],
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string',
            'logo'      => 'nullable|image|max:2048',
            'actif'     => 'sometimes|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos/entreprises', 'public');
        }

        $entreprise->update($validated);
        return response()->json($entreprise);
    }

    /** DELETE /api/entreprises/{id} */
    public function destroy(Entreprise $entreprise): JsonResponse
    {
        $entreprise->delete();
        return response()->json(['message' => 'Entreprise supprimée.']);
    }

    /** GET /api/entreprises/{id}/stats */
    public function stats(Entreprise $entreprise): JsonResponse
    {
        $idE = $entreprise->idEntreprise;
        return response()->json([
            'total_evenements'    => $entreprise->evenements()->count(),
            'evenements_publies'  => $entreprise->evenements()->where('statut', 'publie')->count(),
            'total_utilisateurs'  => $entreprise->utilisateurs()->count(),
            'total_inscriptions'  => Inscription::whereHas('evenement', fn($q) => $q->where('idEntreprise', $idE))->count(),
            'total_presents'      => Inscription::whereHas('evenement', fn($q) => $q->where('idEntreprise', $idE))->where('presentGlobal', true)->count(),
        ]);
    }

    /** GET /api/super-admin/stats-globales */
    public function statsGlobales(): JsonResponse
    {
        return response()->json([
            'total_entreprises'  => Entreprise::count(),
            'total_evenements'   => \App\Models\Evenement::count(),
            'total_inscriptions' => Inscription::count(),
            'total_participants' => Participant::count(),
            'total_utilisateurs' => Utilisateur::count(),
        ]);
    }
}
