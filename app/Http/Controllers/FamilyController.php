<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

require_once base_path('routes/functions.php');

class FamilyController extends Controller
{
    public function index(User $paziente)
    {
        $medico = auth()->user();

        // Verifica che il paziente appartenga a questo medico
        $medico->pazienti()->where('id', $paziente->id)->firstOrFail();

        $familiari        = $paziente->familiari;
        $altriFamiliari   = User::where('role', 'famiglia')
                                ->whereNotIn('id', $familiari->pluck('id'))
                                ->get();

        return renderPage('dashboards.famiglia_paziente', [
            'title'          => 'Gestione Familiari — ' . $paziente->username,
            'paziente'       => $paziente,
            'familiari'      => $familiari,
            'altriFamiliari' => $altriFamiliari,
        ]);
    }

    /**
     * Aggiunge un familiare al paziente.
     */
    public function attach(Request $request, User $paziente)
    {
        $medico = auth()->user();
        $medico->pazienti()->where('id', $paziente->id)->firstOrFail();

        $request->validate([
            'family_id' => ['required', 'exists:users,id'],
        ]);

        $familiare = User::where('id', $request->family_id)
                         ->where('role', 'famiglia')
                         ->firstOrFail();

        $paziente->familiari()->syncWithoutDetaching([$familiare->id]);

        return redirect()
            ->route('family.index', $paziente)
            ->with('success', "'{$familiare->username}' è stato collegato a {$paziente->username}.");
    }

    /**
     * Rimuove un familiare dal paziente.
     */
    public function detach(User $paziente, User $familiare)
    {
        $medico = auth()->user();
        $medico->pazienti()->where('id', $paziente->id)->firstOrFail();

        $paziente->familiari()->detach($familiare->id);

        return redirect()
            ->route('family.index', $paziente)
            ->with('success', "'{$familiare->username}' è stato rimosso da {$paziente->username}.");
    }
}
