<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Paziente - Monitoraggio IoT</title>
    @vite(['resources/css/app.css', 'resources/js/text.js'])

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: #fff; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .status-indicator { font-weight: bold; padding: 5px 12px; border-radius: 20px; background: #e2e8f0; font-size: 0.9em; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #1a202c; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        .data-value { font-size: 2.5rem; font-weight: 800; color: #3182ce; margin: 20px 0; }
        .unit { font-size: 1rem; color: #718096; }
        /* Rimossi gli stili di input e button in quanto il form è stato eliminato */
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Monitoraggio Paziente</h1>
        <div class="status-indicator">
            Stato: <span id="status">Connessione...</span>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Temperatura</h3>
            <p>Ultimo aggiornamento dal topic <strong>esp32/dati</strong>:</p>
            <div class="data-value">
                <span id="temperatura-value">--</span>
                <span class="unit">°C</span>
            </div>
        </div>

        <div class="card">
            <h3>Umidità</h3>
            <p>Ultimo aggiornamento dal topic <strong>esp32/dati</strong>:</p>
            <div class="data-value">
                <span id="umidita-value">--</span>
                <span class="unit">%</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
