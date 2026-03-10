<?php
namespace App\Http\Controllers;

use App\Models\Atelier;
use App\Models\Evenement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AtelierController extends Controller
{
    /** GET /api/evenements/{evenement}/ateliers */
    public function index(Evenement $evenement): JsonResponse
    {
        $ateliers = $evenement->ateliers()
            ->with(['speakers'])
            ->withCount('inscriptions')
            ->orderBy('horaire')
            ->get();
        return response()->json($ateliers);
    }

    /** POST /api/evenements/{evenement}/ateliers */
    public function store(Request $request, Evenement $evenement): JsonResponse
    {
        $validated = $request->validate([
            'titre'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'categorie'    => ['required', Rule::in(['public', 'vip', 'prive', 'autre'])],
            'horaire'      => 'required|date',
            'dureeMinutes' => 'integer|min:15|max:480',
            'salle'        => 'nullable|string|max:100',
            'capacite'     => 'nullable|integer|min:1',
        ]);

        $atelier = $evenement->ateliers()->create($validated);

        // Synchroniser les speakers si fournis
        if ($request->has('speakers')) {
            $syncData = collect($request->speakers)->mapWithKeys(fn($s) => [
                $s['id'] => ['role' => $s['role'] ?? null]
            ])->toArray();
            $atelier->speakers()->sync($syncData);
        }

        return response()->json($atelier->load('speakers'), 201);
    }

    /** GET /api/ateliers/{atelier} */
    public function show(Atelier $atelier): JsonResponse
    {
        return response()->json(
            $atelier->load(['speakers', 'evenement'])->loadCount('inscriptions')
        );
    }

    /** PUT /api/ateliers/{atelier} */
    public function update(Request $request, Atelier $atelier): JsonResponse
    {
        $validated = $request->validate([
            'titre'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'categorie'    => ['sometimes', Rule::in(['public', 'vip', 'prive', 'autre'])],
            'horaire'      => 'sometimes|date',
            'dureeMinutes' => 'integer|min:15|max:480',
            'salle'        => 'nullable|string|max:100',
            'capacite'     => 'nullable|integer|min:1',
        ]);

        $atelier->update($validated);

        if ($request->has('speakers')) {
            $syncData = collect($request->speakers)->mapWithKeys(fn($s) => [
                $s['id'] => ['role' => $s['role'] ?? null]
            ])->toArray();
            $atelier->speakers()->sync($syncData);
        }

        return response()->json($atelier->load('speakers'));
    }

    /** DELETE /api/ateliers/{atelier} */
    public function destroy(Atelier $atelier): JsonResponse
    {
        $atelier->delete();
        return response()->json(['message' => 'Atelier supprimé.']);
    }

    /** POST /api/ateliers/{atelier}/speakers/sync */
    public function syncSpeakers(Request $request, Atelier $atelier): JsonResponse
    {
        $request->validate([
            'speakers'          => 'required|array',
            'speakers.*.id'     => 'required|exists:speakers,idSpeaker',
            'speakers.*.role'   => 'nullable|string',
        ]);

        $syncData = collect($request->speakers)->mapWithKeys(fn($s) => [
            $s['id'] => ['role' => $s['role'] ?? null]
        ])->toArray();

        $atelier->speakers()->sync($syncData);
        return response()->json(['message' => 'Speakers synchronisés.', 'atelier' => $atelier->load('speakers')]);
    }
}
