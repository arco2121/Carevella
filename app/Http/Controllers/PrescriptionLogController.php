<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionLog;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Carbon\Carbon;

class PrescriptionLogController extends Controller
{
    /**
     * Toggle lo stato "preso" per una prescrizione in una data specifica.
     * Chiamato via fetch() dalla dashboard paziente.
     */
    public function toggle(Request $request, Prescription $prescription, string $date): \Illuminate\Http\JsonResponse
    {
        $paziente = auth()->user();

        // Verifica autorizzazione
        if ((int) $prescription->patient_id !== (int) $paziente->id) {
            return response()->json(['error' => 'Non autorizzato.'], 403);
        }

        // Valida formato data
        if (!Carbon::canBeCreatedFromFormat($date, 'Y-m-d')) {
            return response()->json(['error' => 'Data non valida.'], 422);
        }

        $dateString = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');

        // Cerca log esistente
        $log = PrescriptionLog::firstOrNew([
            'patient_id'      => $paziente->id,
            'prescription_id' => $prescription->id,
            'date'            => $dateString,
        ]);

        if ($log->exists) {
            // Inverte lo stato corrente (cast bool esplicito per sicurezza)
            $log->taken    = !((bool) $log->taken);
            $log->taken_at = $log->taken ? now() : null;
            $log->save();
        } else {
            // Prima volta: segna come preso
            $log->taken    = true;
            $log->taken_at = now();
            $log->save();
        }

        // Cast espliciti per la risposta JSON — evita problemi con il cast Eloquent
        $takenFinal   = (bool) $log->taken;
        $takenAtFinal = $log->taken_at ? Carbon::parse($log->taken_at)->toIso8601String() : null;

        // Pubblica su MQTT (non bloccante, gli errori sono loggati ma non bloccano)
        $this->publishToMqtt($paziente->id, $prescription, $dateString, $takenFinal);

        return response()->json([
            'taken'    => $takenFinal,
            'taken_at' => $takenAtFinal,
        ]);
    }

    /**
     * API PAZIENTE: log di assunzione della settimana corrente (se stesso).
     * GET /api/paziente/me/log-settimanale
     */
    public function weeklyForSelf(): \Illuminate\Http\JsonResponse
    {
        $paziente = auth()->user();

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        $logs = PrescriptionLog::where('patient_id', $paziente->id)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get()
            ->map(fn($log) => [
                'prescription_id' => (int) $log->prescription_id,
                'date'            => Carbon::parse($log->date)->format('Y-m-d'),
                'taken'           => (bool) $log->taken,
                'taken_at'        => $log->taken_at ? Carbon::parse($log->taken_at)->toIso8601String() : null,
            ]);

        return response()->json($logs);
    }

    /**
     * API MEDICO: log settimanale di un paziente specifico.
     * GET /api/paziente/{id}/log-settimanale
     */
    public function weeklyForPatient(int $patientId): \Illuminate\Http\JsonResponse
    {
        $medico   = auth()->user();
        $paziente = $medico->pazienti()->where('id', $patientId)->firstOrFail();

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        $logs = PrescriptionLog::where('patient_id', $paziente->id)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->with('prescription.medicine')
            ->get()
            ->map(fn($log) => [
                'prescription_id' => (int) $log->prescription_id,
                'medicine'        => $log->prescription?->medicine?->name ?? '?',
                'date'            => Carbon::parse($log->date)->format('Y-m-d'),
                'taken'           => (bool) $log->taken,
                'taken_at'        => $log->taken_at ? Carbon::parse($log->taken_at)->toIso8601String() : null,
            ]);

        return response()->json($logs);
    }

    /**
     * Pubblica su MQTT topic esp32/assunzione (QoS 1, non bloccante).
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
                'medicine'        => $prescription->medicine?->name ?? 'unknown',
                'amount'          => $prescription->amount,
                'date'            => $date,
                'time'            => substr($prescription->scheduled_time, 0, 5),
                'taken'           => $taken,
                'taken_at'        => $taken ? now()->toIso8601String() : null,
            ]);

            $mqtt->publish('esp32/assunzione', $payload, 1);
            $mqtt->disconnect();

        } catch (\Exception $e) {
            \Log::warning('MQTT publish failed: ' . $e->getMessage());
        }
    }
}
