import { echo } from "../echo.js";

document.getElementById('messagemqtt').addEventListener('submit', async (e) => {
    e.preventDefault();

    try {
        const response = await fetch('/sendMqtt', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('input[name="_token"]').content
            },
            body: JSON.stringify({ message })
        });

        const data = await response.json();

        if(data.ok) {
            alert("Messaggio inviato correttamente");
        } else {
            alert("Errore");
        }

    } catch (error) {
        console.error(error);
        alert(error.message)
    }
});


echo.channel('esp32')
    .listen('MqttMessageReceived', (data) => {

        console.log("Message");
        let { topic, message } = data;
        message = JSON.parse(message);

        const str = "Topic '" + topic + "' => " + message
    });
