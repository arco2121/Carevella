<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index()
    {
        $users = User::all();
        $medicines = Medicine::all();
        $days = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
        $times = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'];

        // Definiamo le variabili richieste dal tuo layout
        $title = "Gestione Prescrizioni";
        $page = "dashboards.prescrizioni"; // Questo caricherà 'resources/css/test.css' (come visto nel tuo codice)
        $params = [];   // Un array vuoto per ora, per evitare l'errore undefined

        return view('layouts.app', [
            'users' => User::all(),
            'medicines' => Medicine::all(),
            'days' => ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'],
            'times' => ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
            'title' => "Gestione Prescrizioni",
            'page' => "dashboards.prescrizioni", // Questo dice al layout cosa includere
            'params' => []
        ]);
    }

    public function store(Request $request)
    {
        // 1. Recuperiamo l'ID del paziente dal modulo
        $patientId = $request->input('patient_id');
        $schedule = $request->input('schedule'); // L'array [giorno][step]

        // 2. CANCELLAZIONE RECORD VECCHI
        // Prima di salvare il nuovo piano, eliminiamo tutto quello che esiste per questo utente
        \App\Models\Prescription::where('patient_id', $patientId)->delete();

        // 3. CREAZIONE NUOVI RECORD
        // Se l'utente ha selezionato almeno una medicina, procediamo
        if ($schedule) {
            foreach ($schedule as $day => $steps) {
                foreach ($steps as $step => $medicineId) {
                    // Salviamo solo se è stata selezionata una medicina (non vuoto)
                    if ($medicineId) {
                        // Calcolo orario (Step 1 = 08:00, Step 2 = 10:00, ecc.)
                        $baseTime = 8 + (($step - 1) * 2);
                        $scheduledTime = sprintf('%02d:00:00', $baseTime);

                        \App\Models\Prescription::create([
                            'patient_id' => $patientId,
                            'medicine_id' => $medicineId,
                            'day' => $day,
                            'step' => $step,
                            'scheduled_time' => $scheduledTime,
                            'amount' => 1
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Piano prescrizioni aggiornato con successo!');
    }
}
