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
        $medico   = auth()->user();
        $pazienti = $medico->pazienti()->where('role', 'paziente')->get();

        $selectedPatientId = request('paziente') ?? $pazienti->first()?->id;

        $prescriptionMap = [];
        if ($selectedPatientId) {
            $existing = Prescription::where('patient_id', $selectedPatientId)
                ->with('medicine')
                ->get();
            foreach ($existing as $p) {
                $key = $p->day . '_' . $p->step;
                if (!isset($prescriptionMap[$key])) {
                    $prescriptionMap[$key] = collect();
                }
                $prescriptionMap[$key]->push($p);
            }
        }

        return renderPage('dashboards.prescrizioni', [
            'title'             => 'Gestione Prescrizioni',
            'users'             => $pazienti,
            'medicines'         => Medicine::all(),
            'days'              => ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'],
            'times'             => ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
            'selectedPatientId' => $selectedPatientId,
            'prescriptionMap'   => $prescriptionMap,
        ]);
    }

    public function store(Request $request)
    {
        $medico    = auth()->user();
        $patientId = $request->input('patient_id');
        $medico->pazienti()->where('id', $patientId)->firstOrFail();

        Prescription::where('patient_id', $patientId)->delete();

        $schedule = $request->input('schedule', []);
        foreach ($schedule as $day => $steps) {
            foreach ($steps as $step => $medicineIds) {
                $ids = array_filter((array) $medicineIds);
                foreach ($ids as $medicineId) {
                    $hour          = 8 + (($step - 1) * 2);
                    $scheduledTime = sprintf('%02d:00:00', $hour);

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

        return redirect()
            ->route('prescriptions.index', ['paziente' => $patientId])
            ->with('success', 'Piano terapeutico salvato con successo!');
    }

    public function clear(int $patientId)
    {
        $medico = auth()->user();
        $medico->pazienti()->where('id', $patientId)->firstOrFail();

        Prescription::where('patient_id', $patientId)->delete();

        return redirect()
            ->route('prescriptions.index', ['paziente' => $patientId])
            ->with('success', 'Piano terapeutico cancellato.');
    }
}
