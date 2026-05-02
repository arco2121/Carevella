<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpMqtt\Client\Facades\MQTT;

class MqttController extends Controller
{
    public function send(Request $request)
    {
        $message = $request->input('message');

        $topic = $request->input('topic') ?? "esp32/comandi";
        $message = [
            'text' => $message,
            'timestamp' => now()
        ];

        $mqtt = MQTT::connection();

        $mqtt->publish($topic, $message['text'], 0);

        return response()->json(['ok' => true, 'topic' => $topic]);
    }
}
