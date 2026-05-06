<?php
namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Events\MqttMessageReceived;


use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

#[Signature('mqtt:subscribe')]
#[Description('Command description')]
class SubscribeToTopic extends Command
{
    protected $signature = 'mqtt:subscribe';

    protected $description = 'Subscribe To MQTT topic';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $settings = (new ConnectionSettings)
            ->setUseTls(true)
            ->setUsername(env('MQTT_AUTH_USERNAME'))
            ->setPassword(env('MQTT_AUTH_PASSWORD'));

        $mqtt = new MqttClient(
            env('MQTT_HOST'),
            env('MQTT_PORT'),
            uniqid('laravel_', true),
        );

        $mqtt->connect($settings, true);

        //$mqtt = MQTT::connection();
        $this->info("🟢 Connesso al broker HiveMQ Per Il subscribe!");
        
        $mqtt->subscribe('esp32/dati', function(string $topic, string $message) {
            broadcast(new MqttMessageReceived($topic, $message));
        }, 0);

        $mqtt->loop(true);

        return Command::SUCCESS;
    }
}
