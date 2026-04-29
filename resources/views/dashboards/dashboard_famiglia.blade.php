@php
    $paziente = \App\Models\User::find(auth()->user()->doctor_id);
@endphp

<div class="dashboard-wrapper column padding_orizontal_20 padding_vertical_20 min_height gap_40 full_width">

    <div class="dash-header row between vertical_center">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Vista Familiare</h1>
            @if($paziente)
                <span class="role-badge famiglia">Monitorando: {{ $paziente->username }}</span>
            @else
                <span class="role-badge">Nessun paziente collegato</span>
            @endif
        </div>
        <div class="row vertical_center gap_10">
            <span class="dot" id="conn-dot"></span>
            <span id="status" class="status-label">Connessione...</span>
        </div>
    </div>

    @if(!$paziente)
        <div class="section-block box column vertical_center text_center gap_20 padding_orizontal_20 padding_vertical_20 empty-state">
            <span class="empty-icon">🔗</span>
            <h2 class="font_bold">Nessun paziente collegato</h2>
            <p class="empty-desc">Il tuo account familiare non è ancora stato collegato a nessun paziente. Contatta il medico o l'amministrazione.</p>
        </div>
    @else
        <div class="section-block box row vertical_center gap_20 padding_orizontal_20 padding_vertical_15">
            <div class="avatar-circle">{{ strtoupper(substr($paziente->username, 0, 1)) }}</div>
            <div class="column gap_10">
                <span class="font_bold paziente-name">{{ $paziente->username }}</span>
                <span class="paziente-email">{{ $paziente->email }}</span>
            </div>
            <div class="row gap_10 vertical_center paziente-online">
                <span class="dot green" id="patient-online-dot"></span>
                <span id="patient-online-status" class="status-label">In attesa...</span>
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
                    <span class="dot" id="device-dot"></span>
                    <span id="device-status" class="device-status-text">In attesa di dati...</span>
                </div>
            </div>
        </div>

        @php
            $prescrizioni = $paziente->prescrizioni()->with('medicine')->get()->groupBy('day');
            $giorni = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
        @endphp

        <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold section-title">💊 Piano Terapeutico</h2>
            @if($prescrizioni->isEmpty())
                <div class="empty-state column vertical_center text_center gap_10">
                    <span class="empty-icon">📋</span>
                    <p>Nessuna prescrizione attiva per questo paziente.</p>
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
            <h2 class="font_bold section-title">📡 Stream in tempo reale</h2>
            <div id="live-log" class="live-log column gap_10">
                <p class="placeholder-text">In attesa di dati dal dispositivo...</p>
            </div>
        </div>
    @endif

</div>

@vite(['resources/js/pages/dashboard_paziente.js'])
