<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\Request;

require_once base_path('routes/functions.php');

class MedicineController extends Controller
{
    public function index()
    {
        return renderPage('dashboards.farmaci', [
            'title'    => 'Gestione Farmaci',
            'medicines' => Medicine::withCount('prescriptions')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:medicines,name'],
        ], [
            'name.required' => 'Il nome del farmaco è obbligatorio.',
            'name.unique'   => 'Esiste già un farmaco con questo nome.',
        ]);

        Medicine::create(['name' => trim($request->name)]);

        return redirect()->route('medicines.index')->with('success', 'Farmaco aggiunto!');
    }

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:medicines,name,' . $medicine->id],
        ], [
            'name.required' => 'Il nome del farmaco è obbligatorio.',
            'name.unique'   => 'Esiste già un farmaco con questo nome.',
        ]);

        $medicine->update(['name' => trim($request->name)]);

        return redirect()->route('medicines.index')->with('success', 'Farmaco aggiornato!');
    }

    public function destroy(Medicine $medicine)
    {
        $count = Prescription::where('medicine_id', $medicine->id)->count();

        if ($count > 0) {
            return redirect()->route('medicines.index')
                ->with('error', "Impossibile eliminare \"{$medicine->name}\": è usato in {$count} prescrizi" . ($count === 1 ? 'one' : 'oni') . '.');
        }

        $medicine->delete();

        return redirect()->route('medicines.index')->with('success', 'Farmaco eliminato.');
    }
}
