import { echo } from "../echo.js";

// Gestione invio messaggio tramite form
document.getElementById('messagemqtt')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = document.getElementById("message").value;

    try {
        const response = await fetch('/sendMqtt', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // Recupero del token CSRF dall'input generato da Laravel
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ message })
        });

        const data = await response.json();

        if(data.ok)
            alert("Messaggio inviato correttamente");
        else
            alert("Errore nell'invio");
    } catch (error) {
        console.error("Errore durante la fetch:", error);
        alert(error.message);
    }
});

// Sottoscrizione al canale 'esp32' per ricevere i dati in tempo reale
echo.channel('esp32')
    .listen('MqttMessageReceived', (data) => {
        // Estrazione dati dall'evento
        const { topic, message } = data;

        // Formattazione stringa per debug
        const str = "Topic '" + topic + "' => " + message;

        // 1. Log su console richiesto
        console.log(str);
    });



