<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enregistrement du middleware de rôle
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Sanctum stateful pour le frontend React
        $middleware->statefulApi();
    
         $middleware->validateCsrfTokens(except: [
          'api/*',
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 401 — Non authentifié
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            return response()->json(['message' => 'Non authentifié. Veuillez vous connecter.'], 401);
        });

        // 422 — Validation échouée
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'Données invalides.',
                'errors'  => $e->errors(),
            ], 422);
        });

        // 404 — Modèle introuvable
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            $model = class_basename($e->getModel());
            return response()->json(['message' => "{$model} introuvable."], 404);
        });

        // 404 — Route introuvable
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            return response()->json(['message' => 'Route introuvable.'], 404);
        });

        // 403 — Accès interdit
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        });
    })->create();
