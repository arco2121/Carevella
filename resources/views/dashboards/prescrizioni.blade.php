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
            <form method="GET" action="{{ route('prescriptions.index') }}" id="patient-form"
                  class="row vertical_center gap_15 presc-patient-form">
                <select name="paziente" class="patient-select-wrap"
                        onchange="document.getElementById('patient-form').submit()">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $selectedPatientId == $u->id ? 'selected' : '' }}>
                            {{ $u->username }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </form>

            @if($selectedPatientId)
                <form method="POST" action="{{ route('prescriptions.clear', $selectedPatientId) }}"
                      onsubmit="return confirm('Cancellare tutte le prescrizioni di questo paziente?')">
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

            <form method="POST" action="{{ route('prescriptions.store') }}">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $selectedPatientId }}">

                <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
                    <div class="row between vertical_center presc-header">
                        <div class="column gap_10">
                            <h2 class="font_bold section-title">Piano Settimanale</h2>
                            <span class="presc-hint">
                                Scegli il Piano Settimanale per il <kbd>Paziente</kbd>
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
                                            $hasValue  = $slotItems && $slotItems->count() > 0;
                                            $selected  = $slotItems ? $slotItems->pluck('medicine_id')->toArray() : [];
                                        @endphp
                                        <td>
                                            <div class="med-select med-select-multi {{ $hasValue ? 'has-value' : '' }}">
                                                @foreach($medicines as $med)
                                                    @php
                                                        $isSelected = in_array($med->id, $selected);
                                                        $uniqueId = "med_{$day}_{$stepNum}_{$med->id}";
                                                    @endphp

                                                    <label for="{{ $uniqueId }}" class="pill-item row vertical_center gap_10 {{ $isSelected ? 'is-selected' : '' }}">
                                                        <input
                                                            type="checkbox"
                                                            name="schedule[{{ $day }}][{{ $stepNum }}][]"
                                                            value="{{ $med->id }}"
                                                            id="{{ $uniqueId }}"
                                                            class="hidden-checkbox"
                                                            {{ $isSelected ? 'checked' : '' }}
                                                            onchange="
                                                            this.closest('.pill-item').classList.toggle('is-selected', this.checked);
                                                            this.closest('.med-select-multi').classList.toggle('has-value', this.closest('.med-select-multi').querySelectorAll('input:checked').length > 0);">
                                                        <span class="pill-dot"></span>
                                                        <span class="med-name">{{ $med->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            @php
                $giorni          = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
                $allPresc        = collect($prescriptionMap)->flatten(1);
                $byDay           = $allPresc->groupBy('day')->sortKeys();
                $totalCount      = $allPresc->count();
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
