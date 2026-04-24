<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class VerifyLogin extends Command
{
    // Il nome del comando da lanciare nel terminale (es: php artisan mqtt:listen)
    protected $signature = 'mqtt:listen';

    protected $description = 'Ascolta le richieste di login da MQTT, verifica l\'utente e invia le prescrizioni settimanali';

    public function handle()
    {
        // 1. Configurazione della connessione TLS sicura
        $settings = (new ConnectionSettings)
            ->setUseTls(true)
            ->setUsername(env('MQTT_AUTH_USERNAME'))
            ->setPassword(env('MQTT_AUTH_PASSWORD'));

        $mqtt = new MqttClient(
            env('MQTT_HOST'),
            env('MQTT_PORT'),
            uniqid('laravel_', true),
        );

        // 2. Connessione al Broker
        $mqtt->connect($settings, true);
        $this->info("🟢 Connesso al broker HiveMQ!");
        $this->info("👂 In ascolto su 'esp32/login_request'...");

        // 3. Ci mettiamo in ascolto
        $mqtt->subscribe('esp32/login_request', function(string $topic, string $message) use ($mqtt) {

            $this->info("--------------------------------------------------");
            $this->info("📩 Nuova richiesta ricevuta: " . $message);

            try {
                // Decodifica il JSON inviato dall'App Android
                $dati = json_decode($message, true);

                $username = $dati['username'] ?? '';
                $password = $dati['password'] ?? '';
                $replyTo  = $dati['reply_to'] ?? '';

                // Se manca il topic per rispondergli, interrompiamo
                if (empty($replyTo)) {
                    $this->error("❌ Manca il topic di risposta (reply_to)! Impossibile rispondere.");
                    return;
                }

                // 4. Ricerca Utente nel DB
                $utente = User::where('username', $username)->first();

                // 5. Controllo validità Password
                if ($utente && Hash::check($password, $utente->password)) {

                    $this->info("✅ Login CORRETTO per: " . $username);

                    // 6. RECUPERO PRESCRIZIONI DAL DATABASE
                    // Unisce la tabella prescrizioni con quella delle medicine
                    $prescrizioni = DB::table('prescriptions')
                        ->join('medicines', 'prescriptions.medicine_id', '=', 'medicines.id')
                        ->where('patient_id', $utente->id)
                        ->select('prescriptions.day', 'prescriptions.scheduled_time', 'prescriptions.amount', 'medicines.name as nome_pillola')
                        ->get();

                    // 7. COSTRUZIONE DEL JSON SETTIMANALE (Compresi i giorni vuoti)
                    // Creiamo in anticipo tutti i 7 giorni (1 = Lunedì, 7 = Domenica)
                    $p_data = [
                        "1" => (object)[],
                        "2" => (object)[],
                        "3" => (object)[],
                        "4" => (object)[],
                        "5" => (object)[],
                        "6" => (object)[],
                        "7" => (object)[]
                    ];

                    // Riempiamo i giorni con le pillole trovate
                    foreach ($prescrizioni as $p) {
                        $giorno = (string)$p->day;

                        // Tagliamo i secondi (da "08:00:00" a "08:00")
                        $ora = substr($p->scheduled_time, 0, 5);

                        // Inseriamo la pillola nel giorno corretto
                        $p_data[$giorno]->$ora = [
                            'm' => $p->nome_pillola,
                            'q' => $p->amount
                        ];
                    }

                    // Creiamo la risposta finale
                    $risposta = json_encode([
                        'stato' => 1,
                        'p' => $p_data
                    ]);

                } else {

                    $this->error("❌ Credenziali ERRATE per: " . $username);
                    $risposta = json_encode(['stato' => 0]);

                }

                // 8. INVIO DELLA RISPOSTA (QoS a 1 per la consegna garantita)
                $mqtt->publish($replyTo, $risposta, 1);
                $this->info("📤 Risposta inviata al telefono: " . $risposta);

            } catch (\Exception $e) {
                $this->error("⚠️ Errore nell'elaborazione: " . $e->getMessage());
            }

        }, 1);

        // Mantiene il processo in vita all'infinito per ascoltare i messaggi
        $mqtt->loop(true);

        return Command::SUCCESS;
    }
}
