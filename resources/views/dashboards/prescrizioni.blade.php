<div class="column padding_orizontal_20 padding_vertical_20 full-width min_height gap_60">

    <div class="row between vertical_center presc-page-header">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Gestione Prescrizioni</h1>
            <span class="farm-subtitle">Dr. {{ auth()->user()->username }}</span>
        </div>
        <div class="row gap_15 presc-nav">
            <a href="{{ route('medicines.index') }}" class="btn secondary presc-nav-btn">Gestione Farmaci</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($users->isEmpty())
        <div class="section-block box column vertical_center text_center gap_20 padding_orizontal_20 padding_vertical_20 empty-state">
            <span class="empty-icon">👥</span>
            <h2 class="font_bold">Nessun paziente assegnato</h2>
            <p class="empty-desc">Non hai pazienti collegati al tuo account. I pazienti si assegnano autonomamente dalla loro pagina profilo.</p>
        </div>
    @else

        <div class="section-block box padding_orizontal_20 padding_vertical_15 row vertical_center gap_20 presc-patient-row">
            <span class="font_bold presc-patient-label">Paziente:</span>
            <form method="GET" action="{{ route('prescriptions.index') }}" id="patient-form" class="row vertical_center gap_15 presc-patient-form">
                <select name="paziente" class="patient-select-wrap" id="patient-select">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $selectedPatientId == $u->id ? 'selected' : '' }}>
                            {{ $u->username }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </form>

            @if($selectedPatientId)
                <form method="POST" action="{{ route('prescriptions.clear', $selectedPatientId) }}" id="clear-prescriptions-form">
                    @csrf
                    <button type="submit" class="danger-btn">Cancella tutto</button>
                </form>
            @endif
        </div>

        @if($medicines->isEmpty())
            <div class="alert-warning">
                ⚠️ Nessun farmaco nel database.
                <a href="{{ route('medicines.index') }}" class="presc-warning-link">Aggiungi almeno un farmaco →</a>
            </div>
        @elseif($selectedPatientId)

            <form method="POST" action="{{ route('prescriptions.store') }}" id="presc-form">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $selectedPatientId }}">

                <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
                    <div class="row between vertical_center presc-header">
                        <div class="column gap_10">
                            <h2 class="font_bold section-title">Piano Settimanale</h2>
                            <span class="presc-hint">
                                Seleziona i farmaci e imposta la dose per ogni slot. Puoi aggiungere più farmaci per lo stesso orario.
                            </span>
                        </div>
                        <button type="submit" class="btn primary presc-save-btn">Salva Piano</button>
                    </div>

                    <div class="presc-table-wrap">
                        <table class="presc-table">
                            <thead>
                            <tr>
                                <th class="time-col">Orario</th>
                                @foreach($days as $dayName)
                                    <th>{{ $dayName }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($times as $stepIndex => $time)
                                @php $stepNum = $stepIndex + 1; @endphp
                                <tr>
                                    <td class="time-cell">
                                        <span class="time-badge font_bold">{{ $time }}</span>
                                    </td>
                                    @for($day = 1; $day <= 7; $day++)
                                        @php
                                            $slotItems = $prescriptionMap[$day . '_' . $stepNum] ?? null;
                                        @endphp
                                        <td>
                                            <div class="slot-list" id="slot-list-{{ $day }}-{{ $stepNum }}">
                                                @if($slotItems && $slotItems->count() > 0)
                                                    @foreach($slotItems as $existing)
                                                        <div class="med-slot-row">
                                                            <select name="schedule[{{ $day }}][{{ $stepNum }}][medicines][]" class="med-select has-value">
                                                                <option value="">—</option>
                                                                @foreach($medicines as $med)
                                                                    <option value="{{ $med->id }}" {{ $existing->medicine_id == $med->id ? 'selected' : '' }}>
                                                                        {{ $med->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="number" name="schedule[{{ $day }}][{{ $stepNum }}][amounts][]" class="amount-input" value="{{ $existing->amount }}" min="0.5" max="20" step="0.5" title="Dose">
                                                            <button type="button" class="slot-remove-btn">✕</button>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="med-slot-row">
                                                        <select name="schedule[{{ $day }}][{{ $stepNum }}][medicines][]" class="med-select">
                                                            <option value="">—</option>
                                                            @foreach($medicines as $med)
                                                                <option value="{{ $med->id }}">{{ $med->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="number" name="schedule[{{ $day }}][{{ $stepNum }}][amounts][]" class="amount-input" value="1" min="0.5" max="20" step="0.5" title="Dose">
                                                        <button type="button" class="slot-remove-btn">✕</button>
                                                    </div>
                                                @endif
                                            </div>

                                            <button type="button" class="add-slot-btn" data-day="{{ $day }}" data-step="{{ $stepNum }}">
                                                + Farmaco
                                            </button>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            <template id="med-slot-template">
                <div class="med-slot-row">
                    <select class="med-select">
                        <option value="">—</option>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}">{{ $med->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="amount-input" value="1" min="0.5" max="20" step="0.5" title="Dose">
                    <button type="button" class="slot-remove-btn">✕</button>
                </div>
            </template>

            @php
                $giorni     = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
                $allPresc   = collect($prescriptionMap)->flatten(1);
                $byDay      = $allPresc->groupBy('day')->sortKeys();
                $totalCount = $allPresc->count();
            @endphp

            @if($totalCount > 0)
                <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
                    <div class="row vertical_center gap_15">
                        <h2 class="font_bold section-title">Prescrizioni attive</h2>
                        <span class="summary-count">{{ $totalCount }}</span>
                    </div>
                    <div class="prescription-grid">
                        @foreach($byDay as $day => $items)
                            <div class="day-block column gap_10">
                                <div class="day-label font_bold">{{ $giorni[$day] ?? "G$day" }}</div>
                                @foreach($items->sortBy('step') as $item)
                                    <div class="pill-item row vertical_center gap_10">
                                        <span class="pill-dot"></span>
                                        <span>{{ $item->medicine->name }}</span>
                                        <span class="pill-time">×{{ $item->amount }}</span>
                                        <span class="pill-time">{{ substr($item->scheduled_time, 0, 5) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="section-block box row vertical_center gap_15 padding_orizontal_20 padding_vertical_15 presc-empty-note">
                    <p>Nessuna prescrizione attiva per questo paziente. Usa la griglia sopra per impostare il piano terapeutico.</p>
                </div>
            @endif

        @endif
    @endif

</div>

@vite(['resources/js/pages/prescrizioni.js'])
