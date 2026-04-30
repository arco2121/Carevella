<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionLog;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class PrescriptionLogController extends Controller
{
    /**
     * Toggle lo stato "preso" per una prescrizione in una data specifica.
     * Chiamato via fetch() dalla dashboard paziente.
     */
    public function toggle(Request $request, Prescription $prescription, string $date): \Illuminate\Http\JsonResponse
    {
        $paziente = auth()->user();

        // Verifica che la prescrizione appartenga davvero al paziente loggato
        if ((int) $prescription->patient_id !== (int) $paziente->id) {
            return response()->json(['error' => 'Non autorizzato.'], 403);
        }

        // Valida la data (formato YYYY-MM-DD)
        $request->validate(['date' => 'date_format:Y-m-d'], [], ['date' => $date]);
        $parsedDate = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->startOfDay();

        // updateOrCreate: se esiste il log lo aggiorna, altrimenti lo crea
        $log = PrescriptionLog::updateOrCreate(
            [
                'patient_id'      => $paziente->id,
                'prescription_id' => $prescription->id,
                'date'            => $parsedDate->toDateString(),
            ],
            [
                'taken'    => true,
                'taken_at' => now(),
            ]
        );

        // Se era già "preso", lo togliamo (toggle)
        if (!$log->wasRecentlyCreated) {
            $log->update([
                'taken'    => !$log->getOriginal('taken') ? true : false,
                'taken_at' => $log->taken ? now() : null,
            ]);
            $log->refresh();
        }

        // Pubblica su MQTT per sincronizzare l'ESP32/dispenser fisico
        $this->publishToMqtt($paziente->id, $prescription, $parsedDate->toDateString(), $log->taken);

        return response()->json([
            'taken'    => $log->taken,
            'taken_at' => $log->taken_at?->toIso8601String(),
        ]);
    }

    /**
     * Pubblica lo stato di assunzione su MQTT topic: esp32/assunzione
     * Payload JSON: { patient_id, prescription_id, medicine, date, taken, taken_at }
     */
    private function publishToMqtt(int $patientId, Prescription $prescription, string $date, bool $taken): void
    {
        try {
            $settings = (new ConnectionSettings)
                ->setUseTls(true)
                ->setUsername(env('MQTT_AUTH_USERNAME'))
                ->setPassword(env('MQTT_AUTH_PASSWORD'))
                ->setConnectTimeout(5);

            $mqtt = new MqttClient(
                env('MQTT_HOST'),
                env('MQTT_PORT'),
                uniqid('laravel_log_', true),
            );

            $mqtt->connect($settings, true);

            $payload = json_encode([
                'patient_id'      => $patientId,
                'prescription_id' => $prescription->id,
                'medicine'        => $prescription->medicine->name ?? 'unknown',
                'amount'          => $prescription->amount,
                'date'            => $date,
                'time'            => substr($prescription->scheduled_time, 0, 5),
                'taken'           => $taken,
                'taken_at'        => $taken ? now()->toIso8601String() : null,
            ]);

            // QoS 1 = consegna garantita almeno una volta
            $mqtt->publish('esp32/assunzione', $payload, 1);
            $mqtt->disconnect();

        } catch (\Exception $e) {
            // Non bloccare la risposta HTTP se MQTT fallisce
            \Log::warning('MQTT publish failed in PrescriptionLogController: ' . $e->getMessage());
        }
    }

    /**
     * API per il medico: ritorna i log di assunzione di un paziente
     * per la settimana corrente.
     */
    public function weeklyForPatient(int $patientId): \Illuminate\Http\JsonResponse
    {
        $medico   = auth()->user();
        $paziente = $medico->pazienti()->where('id', $patientId)->firstOrFail();

        $startOfWeek = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek   = $startOfWeek->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $logs = PrescriptionLog::where('patient_id', $paziente->id)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->with('prescription.medicine')
            ->get()
            ->map(fn($log) => [
                'prescription_id' => $log->prescription_id,
                'medicine'        => $log->prescription->medicine->name ?? '?',
                'date'            => $log->date->toDateString(),
                'taken'           => $log->taken,
                'taken_at'        => $log->taken_at?->toIso8601String(),
            ]);

        return response()->json($logs);
    }
}
