<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de contrôle d'accès par rôle.
 *
 * Usage dans les routes :
 *   ->middleware('role:super_admin')
 *   ->middleware('role:super_admin,admin_entreprise,gestionnaire')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié. Veuillez vous connecter.',
            ], 401);
        }

        if (!$user->actif) {
            return response()->json([
                'message' => 'Votre compte est désactivé. Contactez l\'administrateur.',
            ], 403);
        }

        if (!empty($roles) && !in_array($user->role->nomRole, $roles)) {
            return response()->json([
                'message'    => 'Accès refusé. Vous n\'avez pas les droits nécessaires.',
                'roles_requis' => $roles,
                'votre_role'   => $user->role->nomRole,
            ], 403);
        }

        return $next($request);
    }
}
