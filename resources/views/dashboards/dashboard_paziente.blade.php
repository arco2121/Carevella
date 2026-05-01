@php
    use Carbon\Carbon;

    $prescrizioni = auth()->user()->prescrizioni()->with('medicine')->get()->groupBy('day');
    $giorni       = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
    $giorniLungo  = ['', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

    $oggi    = Carbon::now();
    $lunedi  = $oggi->copy()->startOfWeek(Carbon::MONDAY);

    $weekDays = [];
    for ($i = 0; $i < 7; $i++) {
        $weekDays[$i + 1] = $lunedi->copy()->addDays($i)->format('Y-m-d');
    }

    $todayDayNum = (int) $lunedi->diffInDays($oggi) + 1;
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

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

    <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
        <div class="row between vertical_center">
            <h2 class="font_bold section-title">Tracciamento Settimanale</h2>
            <span class="stat-chip">{{ $lunedi->format('d/m') }} — {{ $lunedi->copy()->addDays(6)->format('d/m') }}</span>
        </div>

        @if($prescrizioni->isEmpty())
            <div class="empty-state column vertical_center text_center gap_10">
                <span class="empty-icon">📋</span>
                <p>Nessuna prescrizione attiva.<br>Il tuo medico non ha ancora assegnato farmaci.</p>
            </div>
        @else
            <div class="week-tracking-grid" id="week-tracking-container">
                @for($dayNum = 1; $dayNum <= 7; $dayNum++)
                    @php
                        $dateStr  = $weekDays[$dayNum];
                        $isToday  = ($dayNum === $todayDayNum);
                        $dayItems = $prescrizioni->get($dayNum, collect());
                    @endphp

                    <div class="week-day-card {{ $isToday ? 'week-day-today' : '' }}">
                        <div class="week-day-header">
                            <span class="week-day-name font_bold">
                                {{ $giorniLungo[$dayNum] }}
                                @if($isToday)
                                    &nbsp;<span class="role-badge paziente">Oggi</span>
                                @endif
                            </span>
                            <span class="week-day-date">{{ Carbon::parse($dateStr)->format('d/m') }}</span>
                        </div>

                        <div class="week-pills-list">
                            @if($dayItems->isEmpty())
                                <div class="week-empty-day">Nessun farmaco</div>
                            @else
                                @foreach($dayItems->sortBy('step') as $item)
                                    <div class="week-pill-row"
                                         data-prescription-id="{{ $item->id }}"
                                         data-date="{{ $dateStr }}"
                                         title="Tocca per segnare preso / non preso">
                                        <div class="week-pill-check"></div>
                                        <div class="week-pill-info">
                                            <div class="week-pill-name">{{ $item->medicine->name }}</div>
                                            <div class="week-pill-meta">
                                                <span>{{ $item->amount }} {{ $item->amount == 1 ? 'compressa' : 'compresse' }}</span>
                                                <span class="week-pill-taken-at"></span>
                                            </div>
                                        </div>
                                        <span class="week-pill-time-badge">{{ substr($item->scheduled_time, 0, 5) }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

            <p class="presc-hint">💡 Tocca una pillola per segnare se l'hai presa. I dati vengono sincronizzati con il dispenser fisico via MQTT.</p>
        @endif
    </div>

    <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold section-title">Piano Terapeutico</h2>
        @if($prescrizioni->isEmpty())
            <div class="empty-state column vertical_center text_center gap_10">
                <p>Nessuna prescrizione attiva.</p>
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
                                @if($item->amount != 1)
                                    <span class="pill-time">×{{ $item->amount }}</span>
                                @endif
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
