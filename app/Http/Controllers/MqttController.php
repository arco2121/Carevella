<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpMqtt\Client\Facades\MQTT;

class MqttController extends Controller
{
    public function send(Request $request)
    {
        $message = $request->input('message');

        $topic = 'laravel/dati';
        $message = json_encode([
            'text' => $message,
            'timestamp' => now()
        ]);

        $mqtt = MQTT::connection();

        $mqtt->publish($topic, $message, 0);

        // eventualmente broadcast/evento
        return response()->json(['ok' => true]);
    }
}
