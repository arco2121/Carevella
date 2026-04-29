<div class="dashboard-wrapper column padding_orizontal_20 padding_vertical_20 min_height gap_20">

    {{-- Header --}}
    <div class="dash-header row between vertical_center">
        <div class="column gap_10">
            <h1 class="font_bold" style="font-size: 2rem; margin: 0;">Dashboard Medico 🩺</h1>
            <span class="role-badge medico">Dr. {{ auth()->user()->username }}</span>
        </div>
        <div class="row gap_15 vertical_center" style="flex-wrap: wrap;">
            <a href="/dashboard/prescrizioni" class="btn primary" style="text-decoration: none;">
                💊 Gestione Prescrizioni
            </a>
            <div class="status-dot row vertical_center gap_10">
                <span class="dot" id="conn-dot"></span>
                <span id="status" style="font-size: 0.9rem; opacity: 0.7;">Connessione...</span>
            </div>
        </div>
    </div>

    @php
        $pazienti = auth()->user()->pazienti()->where('role', 'paziente')->get();
    @endphp

    @if($pazienti->isEmpty())
        {{-- Stato vuoto --}}
        <div class="section-block box column vertical_center text_center gap_20 padding_orizontal_20 padding_vertical_20"
             style="min-height: 350px; justify-content: center;">
            <span style="font-size: 4rem;">👥</span>
            <h2 class="font_bold">Nessun paziente assegnato</h2>
            <p style="opacity: 0.6; max-width: 400px; margin: 0 auto;">
                Non hai ancora pazienti collegati al tuo account. Contatta l'amministrazione per assegnare pazienti.
            </p>
        </div>
    @else
        {{-- Selezione paziente --}}
        <div class="section-block box row vertical_center gap_20 padding_orizontal_20 padding_vertical_15">
            <span class="font_bold" style="font-size: 1.1rem;">Paziente selezionato:</span>
            <select id="paziente-select" class="paziente-select font_normal"
                    style="background: var(--background-color1); border: 2px solid currentColor; border-radius: 10px; padding: 8px 15px; font-size: 1rem; cursor: pointer; flex: 1; min-width: 200px;">
                @foreach($pazienti as $p)
                    <option value="{{ $p->id }}" data-username="{{ $p->username }}">
                        {{ $p->username }} ({{ $p->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Sensori del paziente selezionato --}}
        <div id="sensor-section">
            <div class="sensor-grid">

                <div class="sensor-card box" id="card-temp">
                    <div class="sensor-top row between vertical_center">
                        <span class="sensor-label">🌡️ Temperatura</span>
                        <span class="sensor-time" id="temp-time">--</span>
                    </div>
                    <div class="sensor-value" id="temperatura-value">--</div>
                    <div class="sensor-unit">°C</div>
                    <div class="sensor-bar"><div class="bar-fill" id="temp-bar" style="width: 0%;"></div></div>
                </div>

                <div class="sensor-card box" id="card-hum">
                    <div class="sensor-top row between vertical_center">
                        <span class="sensor-label">💧 Umidità</span>
                        <span class="sensor-time" id="hum-time">--</span>
                    </div>
                    <div class="sensor-value" id="umidita-value">--</div>
                    <div class="sensor-unit">%</div>
                    <div class="sensor-bar"><div class="bar-fill blue" id="hum-bar" style="width: 0%;"></div></div>
                </div>

                <div class="sensor-card box" id="card-pir">
                    <div class="sensor-top row between vertical_center">
                        <span class="sensor-label">🚶 Movimento</span>
                        <span class="sensor-time" id="pir-time">--</span>
                    </div>
                    <div class="sensor-value" id="motion-value" style="font-size: 2rem;">--</div>
                    <div class="sensor-unit">rilevato</div>
                </div>

                <div class="sensor-card box column gap_10">
                    <span class="sensor-label">📦 Dispositivo</span>
                    <div id="device-mac" style="font-size: 0.85rem; opacity: 0.6; font-family: monospace;">--:--:--:--:--:--</div>
                    <div class="row gap_10 vertical_center" style="margin-top: 8px;">
                        <span class="dot" id="device-dot"></span>
                        <span id="device-status" style="font-size: 0.85rem;">In attesa di dati...</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Piano prescrizioni del paziente --}}
        <div id="prescription-section" class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
            <div class="row between vertical_center" style="flex-wrap: wrap; gap: 10px;">
                <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">💊 Piano Terapeutico del Paziente</h2>
                <a id="edit-prescription-btn" href="#" class="btn secondary" style="text-decoration: none; font-size: 0.85rem; padding: 8px 15px;">
                    ✏️ Modifica
                </a>
            </div>
            <div id="prescription-content">
                <p style="opacity: 0.4; font-size: 0.9rem;">Seleziona un paziente per vedere le prescrizioni.</p>
            </div>
        </div>

        {{-- Stream live --}}
        <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">📡 Stream in tempo reale</h2>
            <div id="live-log" class="live-log column gap_10">
                <p style="opacity: 0.4; font-size: 0.9rem;">In attesa di dati dal dispositivo...</p>
            </div>
        </div>

    @endif

</div>

@vite(['resources/js/pages/dashboard_medico.js'])
