@php
    $paziente = auth()->user()->medico; // il paziente è collegato al "doctor_id" del familiare
    // In realtà la famiglia ha un doctor_id che punta al paziente
    // Adattiamo: famiglia->doctor_id -> paziente
    $paziente = \App\Models\User::find(auth()->user()->doctor_id);
@endphp

<div class="dashboard-wrapper column padding_orizontal_20 padding_vertical_20 min_height gap_20">

    {{-- Header --}}
    <div class="dash-header row between vertical_center" style="animation: fade-in 0.4s ease-out; flex-wrap: wrap; gap: 15px;">
        <div class="column gap_10">
            <h1 class="font_bold" style="font-size: 2rem; margin: 0;">Vista Familiare 👨‍👩‍👧</h1>
            @if($paziente)
                <span class="role-badge famiglia">Monitorando: {{ $paziente->username }}</span>
            @else
                <span class="role-badge" style="background: #eee;">Nessun paziente collegato</span>
            @endif
        </div>
        <div class="status-dot row vertical_center gap_10">
            <span class="dot" id="conn-dot"></span>
            <span id="status" style="font-size: 0.9rem; opacity: 0.7;">Connessione...</span>
        </div>
    </div>

    @if(!$paziente)
        <div class="section-block box column vertical_center text_center gap_20 padding_orizontal_20 padding_vertical_20"
             style="min-height: 350px; justify-content: center; animation: fade-in 0.5s 0.1s ease-out both;">
            <span style="font-size: 4rem;">🔗</span>
            <h2 class="font_bold">Nessun paziente collegato</h2>
            <p style="opacity: 0.6; max-width: 400px; margin: 0 auto;">
                Il tuo account familiare non è ancora stato collegato a nessun paziente. Contatta il medico o l'amministrazione.
            </p>
        </div>
    @else

        {{-- Info paziente --}}
        <div class="section-block box row vertical_center gap_20 padding_orizontal_20 padding_vertical_15"
             style="animation: fade-in 0.4s 0.1s ease-out both;">
            <div class="avatar-circle">{{ strtoupper(substr($paziente->username, 0, 1)) }}</div>
            <div class="column gap_10">
                <span class="font_bold" style="font-size: 1.2rem;">{{ $paziente->username }}</span>
                <span style="opacity: 0.6; font-size: 0.85rem;">{{ $paziente->email }}</span>
            </div>
            <div class="row gap_10 vertical_center" style="margin-left: auto;">
                <span class="dot green" id="patient-online-dot"></span>
                <span id="patient-online-status" style="font-size: 0.85rem; opacity: 0.7;">In attesa...</span>
            </div>
        </div>

        {{-- Sensori --}}
        <div class="sensor-grid" style="animation: fade-in 0.4s 0.15s ease-out both;">

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

        {{-- Piano farmaci (sola lettura) --}}
        @php
            $prescrizioni = $paziente->prescrizioni()->with('medicine')->get()->groupBy('day');
            $giorni = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
        @endphp
        <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20"
             style="animation: fade-in 0.4s 0.2s ease-out both;">
            <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">💊 Piano Terapeutico</h2>
            @if($prescrizioni->isEmpty())
                <div class="empty-state column vertical_center text_center gap_10" style="padding: 20px 0; opacity: 0.5;">
                    <span style="font-size: 2rem;">📋</span>
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

        {{-- Stream --}}
        <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20"
             style="animation: fade-in 0.4s 0.3s ease-out both;">
            <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">📡 Stream in tempo reale</h2>
            <div id="live-log" class="live-log column gap_10">
                <p style="opacity: 0.4; font-size: 0.9rem;">In attesa di dati dal dispositivo...</p>
            </div>
        </div>

    @endif

</div>

@vite(['resources/js/pages/dashboard_paziente.js'])
