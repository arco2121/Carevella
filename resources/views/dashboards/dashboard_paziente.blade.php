<div class="dashboard-wrapper column padding_orizontal_20 padding_vertical_20 min_height gap_40 full_width">

    <div class="dash-header row between vertical_center margin_vertical_20">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Buongiorno, {{ auth()->user()->username }}</h1>
            <span class="role-badge paziente">Paziente</span>
        </div>
        <div class="row vertical_center gap_10">
            <span class="dot" id="conn-dot"></span>
            <span id="status" class="status-label">Connessione...</span>
        </div>
    </div>

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
                <span class="dot green" id="device-dot"></span>
                <span id="device-status" class="device-status-text">In attesa di dati...</span>
            </div>
        </div>
    </div>

    @php
        $prescrizioni = auth()->user()->prescrizioni()->with('medicine')->get()->groupBy('day');
        $giorni = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
    @endphp

    <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold section-title">Piano Terapeutico</h2>
        @if($prescrizioni->isEmpty())
            <div class="empty-state column vertical_center text_center gap_10">
                <p>Nessuna prescrizione attiva.<br>Il tuo medico non ha ancora assegnato farmaci.</p>
            </div>
        @else
            <div class="prescription-grid">
                @foreach($prescrizioni as $day => $items)
                    <div class="day-block column gap_10">
                        <div class="day-label font_bold">{{ $giorni[$day] ?? "G$day" }}</div>
                        @foreach($items->sortBy('step') as $item)
                            <div class="pill-item row vertical_center gap_10">
                                <span class="pill-dot"></span>
                                <span>{{ $item->medicine->name }}</span>
                                <span class="pill-time">{{ substr($item->scheduled_time, 0, 5) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold section-title">Stream in tempo reale</h2>
        <div id="live-log" class="live-log column gap_10">
            <p class="placeholder-text">In attesa di dati dal dispositivo...</p>
        </div>
    </div>

</div>

@vite(['resources/js/pages/dashboard_paziente.js'])
