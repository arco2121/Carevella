<div class="dashboard-wrapper column padding_orizontal_20 padding_vertical_20 min_height gap_40 full_width">

    <div class="dash-header row between vertical_center">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Dashboard Medico</h1>
            <span class="role-badge medico">Dr. {{ auth()->user()->username }}</span>
        </div>
        <div class="row gap_15 vertical_center dash-status">
            <a href="{{ route('medicines.index') }}" class="btn secondary">Gestione Farmaci</a>
            <a href="{{ route('prescriptions.index') }}" class="btn primary">Gestione Prescrizioni</a>
            <div class="row vertical_center gap_10">
                <span class="dot" id="conn-dot"></span>
                <span id="status" class="status-label">Connessione...</span>
            </div>
        </div>
    </div>

    @php $pazienti = auth()->user()->pazienti()->where('role', 'paziente')->get(); @endphp

    @if($pazienti->isEmpty())
        <div class="section-block box column vertical_center text_center gap_20 padding_orizontal_20 padding_vertical_20 empty-state">
            <span class="empty-icon">👥</span>
            <h2 class="font_bold">Nessun paziente assegnato</h2>
            <p class="empty-desc">Non hai ancora pazienti collegati al tuo account. I pazienti si assegnano autonomamente dalla loro pagina profilo.</p>
        </div>
    @else
        <div class="section-block box row between vertical_center gap_20 padding_orizontal_20 padding_vertical_15">
            <span class="font_bold paziente-select-label">Paziente:</span>
            <select id="paziente-select" class="paziente-select font_normal">
                @foreach($pazienti as $p)
                    <option value="{{ $p->id }}" data-username="{{ $p->username }}">
                        {{ $p->username }} ({{ $p->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div id="sensor-section">
            <div class="sensor-grid">
                <div class="sensor-card box" id="card-temp">
                    <div class="sensor-top row between vertical_center">
                        <span class="sensor-label">🌡️ Temperatura</span>
                        <span class="sensor-time" id="temp-time">--</span>
                    </div>
                    <div class="sensor-value" id="temperatura-value">--</div>
                    <div class="sensor-unit">°C</div>
                    <div class="sensor-bar"><div class="bar-fill" id="temp-bar"></div></div>
                </div>

                <div class="sensor-card box" id="card-hum">
                    <div class="sensor-top row between vertical_center">
                        <span class="sensor-label">💧 Umidità</span>
                        <span class="sensor-time" id="hum-time">--</span>
                    </div>
                    <div class="sensor-value" id="umidita-value">--</div>
                    <div class="sensor-unit">%</div>
                    <div class="sensor-bar"><div class="bar-fill blue" id="hum-bar"></div></div>
                </div>

                <div class="sensor-card box" id="card-pir">
                    <div class="sensor-top row between vertical_center">
                        <span class="sensor-label">🚶 Movimento</span>
                        <span class="sensor-time" id="pir-time">--</span>
                    </div>
                    <div class="sensor-value sensor-value--motion" id="motion-value">--</div>
                    <div class="sensor-unit">rilevato</div>
                </div>

                <div class="sensor-card box column gap_10">
                    <span class="sensor-label">📦 Dispositivo</span>
                    <div id="device-mac" class="device-mac">--:--:--:--:--:--</div>
                    <div class="row gap_10 vertical_center device-status-row">
                        <span class="dot" id="device-dot"></span>
                        <span id="device-status" class="device-status-text">In attesa di dati...</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="prescription-section" class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
            <div class="row between vertical_center presc-header">
                <h2 class="font_bold section-title">Piano Terapeutico del Paziente</h2>
                <a id="edit-prescription-btn" href="#" class="btn btn-icon edit secondary btn-sm">✏️ Modifica</a>
            </div>
            <div id="prescription-content">
                <p class="placeholder-text">Seleziona un paziente per vedere le prescrizioni.</p>
            </div>
        </div>

        <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold section-title">Stream in tempo reale</h2>
            <div id="live-log" class="live-log column gap_10">
                <p class="placeholder-text">In attesa di dati dal dispositivo...</p>
            </div>
        </div>
    @endif

</div>

@vite(['resources/js/pages/dashboard_medico.js'])
