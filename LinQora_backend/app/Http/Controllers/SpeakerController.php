<?php
namespace App\Http\Controllers;

use App\Models\Speaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpeakerController extends Controller
{
    /** GET /api/speakers */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Speaker::withCount('ateliers');

        if (!$user->isSuperAdmin()) {
            $query->where('idEntreprise', $user->idEntreprise);
        }
        if ($request->filled('search')) {
            $query->where('nomComplet', 'like', "%{$request->search}%")
                  ->orWhere('organisation', 'like', "%{$request->search}%");
        }

        return response()->json($query->latest()->paginate(20));
    }

    /** POST /api/speakers */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomComplet'   => 'required|string|max:255',
            'bio'          => 'nullable|string',
            'titre'        => 'nullable|string|max:255',
            'organisation' => 'nullable|string|max:255',
            'linkedin'     => 'nullable|url',
            'twitter'      => 'nullable|string|max:100',
            'email'        => 'nullable|email',
            'photo'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos/speakers', 'public');
        }

        $validated['idEntreprise'] = $request->user()->idEntreprise;
        $speaker = Speaker::create($validated);

        return response()->json($speaker, 201);
    }

    /** GET /api/speakers/{speaker} */
    public function show(Speaker $speaker): JsonResponse
    {
        return response()->json($speaker->load('ateliers.evenement'));
    }

    /** PUT /api/speakers/{speaker} */
    public function update(Request $request, Speaker $speaker): JsonResponse
    {
        $validated = $request->validate([
            'nomComplet'   => 'sometimes|string|max:255',
            'bio'          => 'nullable|string',
            'titre'        => 'nullable|string|max:255',
            'organisation' => 'nullable|string|max:255',
            'linkedin'     => 'nullable|url',
            'twitter'      => 'nullable|string|max:100',
            'email'        => 'nullable|email',
            'photo'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos/speakers', 'public');
        }

        $speaker->update($validated);
        return response()->json($speaker);
    }

    /** DELETE /api/speakers/{speaker} */
    public function destroy(Speaker $speaker): JsonResponse
    {
        $speaker->delete();
        return response()->json(['message' => 'Speaker supprimé.']);
    }
}
