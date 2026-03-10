<?php
namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Participant;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** POST /api/auth/login */
    public function login(LoginRequest $request): JsonResponse
    {
        $utilisateur = Utilisateur::with(['role', 'entreprise'])
            ->where('email', $request->email)
            ->first();

        if (!$utilisateur || !Hash::check($request->password, $utilisateur->motDePasse)) {
            return response()->json(['message' => 'Email ou mot de passe incorrect.'], 401);
        }

        if (!$utilisateur->actif) {
            return response()->json(['message' => 'Compte désactivé. Contactez l\'administrateur.'], 403);
        }

        // Révoquer les anciens tokens si souhaité (optionnel)
        // $utilisateur->tokens()->delete();

        $token = $utilisateur->createToken('linqora_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'utilisateur'  => $this->formatUser($utilisateur),
        ]);
    }

    /** POST /api/auth/register */
    public function register(RegisterRequest $request): JsonResponse
    {
        $role = Role::where('nomRole', Role::PARTICIPANT)->firstOrFail();

        $utilisateur = Utilisateur::create([
            'nom'        => $request->nom,
            'prenom'     => $request->prenom,
            'email'      => $request->email,
            'motDePasse' => $request->password,
            'idRole'     => $role->idRole,
            'actif'      => true,
        ]);

        Participant::create([
            'telephone'     => $request->telephone,
            'organisation'  => $request->organisation,
            'idUtilisateur' => $utilisateur->idUtilisateur,
        ]);

        $token = $utilisateur->createToken('linqora_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'utilisateur'  => $this->formatUser($utilisateur->load('role')),
        ], 201);
    }

    /** POST /api/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    /** GET /api/auth/me */
    public function me(Request $request): JsonResponse
    {
        return response()->json(
            $this->formatUser($request->user()->load(['role', 'entreprise', 'participant']))
        );
    }

    /** PUT /api/auth/profil */
    public function updateProfil(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nom'          => 'sometimes|string|max:255',
            'prenom'       => 'sometimes|string|max:255',
            'avatar'       => 'nullable|image|max:2048',
            'telephone'    => 'nullable|string|max:20',
            'organisation' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update(array_intersect_key($validated, array_flip(['nom', 'prenom', 'avatar'])));

        if ($user->participant) {
            $user->participant->update(
                array_intersect_key($validated, array_flip(['telephone', 'organisation']))
            );
        }

        return response()->json($this->formatUser($user->load(['role', 'entreprise', 'participant'])));
    }

    /** PUT /api/auth/password */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->motDePasse)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
        }

        $user->update(['motDePasse' => $request->password]);

        return response()->json(['message' => 'Mot de passe mis à jour avec succès.']);
    }

    private function formatUser(Utilisateur $u): array
    {
        return [
            'idUtilisateur' => $u->idUtilisateur,
            'nom'           => $u->nom,
            'prenom'        => $u->prenom,
            'fullName'      => $u->prenom . ' ' . $u->nom,
            'email'         => $u->email,
            'avatar'        => $u->avatar ? asset('storage/' . $u->avatar) : null,
            'actif'         => $u->actif,
            'role'          => $u->role?->nomRole,
            'entreprise'    => $u->entreprise ? [
                'idEntreprise' => $u->entreprise->idEntreprise,
                'nom'          => $u->entreprise->nom,
                'logo'         => $u->entreprise->logo ? asset('storage/' . $u->entreprise->logo) : null,
            ] : null,
            'participant' => $u->participant ? [
                'idParticipant' => $u->participant->idParticipant,
                'telephone'     => $u->participant->telephone,
                'organisation'  => $u->participant->organisation,
            ] : null,
        ];
    }
}
