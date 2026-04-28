<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\Request;

require_once base_path('routes/functions.php');

class PrescriptionController extends Controller
{
    public function index()
    {
        // Mostra solo i pazienti assegnati al medico loggato
        $medico = auth()->user();

        return renderPage('dashboards.prescrizioni', [
            'users'     => $medico->pazienti()->where('role', 'paziente')->get(),
            'medicines' => Medicine::all(),
            'days'      => ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'],
            'times'     => ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
            'title'     => 'Gestione Prescrizioni',
        ]);
    }

    public function store(Request $request)
    {
        $medico    = auth()->user();
        $patientId = $request->input('patient_id');

        // Verifica che il paziente appartenga davvero a questo medico
        $medico->pazienti()->where('id', $patientId)->firstOrFail();

        $schedule = $request->input('schedule');

        // Cancella il piano precedente per questo paziente
        Prescription::where('patient_id', $patientId)->delete();

        // Salva il nuovo piano
        if ($schedule) {
            foreach ($schedule as $day => $steps) {
                foreach ($steps as $step => $medicineId) {
                    if ($medicineId) {
                        $baseTime      = 8 + (($step - 1) * 2);
                        $scheduledTime = sprintf('%02d:00:00', $baseTime);

                        Prescription::create([
                            'patient_id'     => $patientId,
                            'medicine_id'    => $medicineId,
                            'day'            => $day,
                            'step'           => $step,
                            'scheduled_time' => $scheduledTime,
                            'amount'         => 1,
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Piano prescrizioni aggiornato con successo!');
    }
}
