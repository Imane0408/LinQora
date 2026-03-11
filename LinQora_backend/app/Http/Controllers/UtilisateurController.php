<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UtilisateurController extends Controller
{
    /** GET /api/utilisateurs */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Utilisateur::with(['role', 'entreprise']);

        if ($user->isAdminEntreprise()) {
            $query->where('idEntreprise', $user->idEntreprise);
        } elseif (!$user->isSuperAdmin()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        if ($request->filled('role')) {
            $query->whereHas('role', fn($q) => $q->where('nomRole', $request->role));
        }
        if ($request->filled('search')) {
            $query->where(fn($q) =>
                $q->where('nom', 'like', "%{$request->search}%")
                  ->orWhere('prenom', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            );
        }

        return response()->json($query->latest()->paginate(20));
    }

    /** POST /api/utilisateurs */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nom'          => 'required|string|max:255',
            'prenom'       => 'required|string|max:255',
            'email'        => 'required|email|unique:utilisateurs,email',
            'password'     => 'required|min:8',
            'idRole'       => 'required|exists:roles,idRole',
            'idEntreprise' => 'nullable|exists:entreprises,idEntreprise',
        ]);

        if (!$user->isSuperAdmin()) {
            $role = Role::find($validated['idRole']);
            if ($role->nomRole === Role::SUPER_ADMIN) {
                return response()->json(['message' => 'Vous ne pouvez pas créer un Super Admin.'], 403);
            }
            $validated['idEntreprise'] = $user->idEntreprise;
        }

        $utilisateur = Utilisateur::create([
            'nom'          => $validated['nom'],
            'prenom'       => $validated['prenom'],
            'email'        => $validated['email'],
            'motDePasse'   => $validated['password'],
            'idRole'       => $validated['idRole'],
            'idEntreprise' => $validated['idEntreprise'] ?? null,
            'actif'        => true,
        ]);

        return response()->json($utilisateur->load('role'), 201);
    }

    /** GET /api/utilisateurs/{id} */
    public function show(Utilisateur $utilisateur): JsonResponse
    {
        return response()->json($utilisateur->load(['role', 'entreprise', 'participant']));
    }

    /** PUT /api/utilisateurs/{id} */
    public function update(Request $request, Utilisateur $utilisateur): JsonResponse
    {
        $validated = $request->validate([
            'nom'    => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email'  => ['sometimes', 'email',
                         Rule::unique('utilisateurs', 'email')->ignore($utilisateur->idUtilisateur, 'idUtilisateur')],
            'actif'  => 'sometimes|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $utilisateur->update($validated);
        return response()->json($utilisateur->load('role'));
    }

    /** DELETE /api/utilisateurs/{id} */
    public function destroy(Request $request, Utilisateur $utilisateur): JsonResponse
    {
        if ($utilisateur->idUtilisateur === $request->user()->idUtilisateur) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }
        $utilisateur->delete();
        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    /** PATCH /api/utilisateurs/{id}/toggle-actif */
    public function toggleActif(Utilisateur $utilisateur): JsonResponse
    {
        $utilisateur->update(['actif' => !$utilisateur->actif]);
        return response()->json([
            'message' => $utilisateur->actif ? 'Compte activé.' : 'Compte désactivé.',
            'actif'   => $utilisateur->actif,
        ]);
    }

    /** PATCH /api/utilisateurs/{id}/reset-password */
    public function resetPassword(Utilisateur $utilisateur): JsonResponse
    {
        $newPassword = Str::random(10);
        $utilisateur->update(['motDePasse' => $newPassword]);

        // En production, envoyer par email — ici on retourne pour l'admin
        return response()->json([
            'message'      => 'Mot de passe réinitialisé.',
            'new_password' => $newPassword,
        ]);
    }
}
