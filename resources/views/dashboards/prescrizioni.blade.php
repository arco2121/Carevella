<div class="column padding_orizontal_20 padding_vertical_20 full-width min_height gap_60">

    {{-- ── Header ── --}}
    <div class="row between vertical_center" style="flex-wrap: wrap; gap: 15px;">
        <div class="column gap_10">
            <h1 class="font_bold" style="font-size: 2rem; margin: 0;">Gestione Prescrizioni</h1>
            <span style="opacity: 0.6; font-size: 0.9rem;">Dr. {{ auth()->user()->username }}</span>
        </div>
        <div class="row gap_15" style="flex-wrap: wrap;">
            <a href="{{ route('medicines.index') }}" class="btn secondary" style="text-decoration: none; aspect-ratio: unset; padding: 10px 20px;">
                Gestione Farmaci
            </a>
        </div>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
        <div class="alert-success">✅ {{ session('success') }}</div>
    @endif

    @if($users->isEmpty())
        {{-- Empty state --}}
        <div class="section-block box column vertical_center text_center gap_20 padding_orizontal_20 padding_vertical_20"
             style="min-height: 300px; justify-content: center;">
            <span style="font-size: 3.5rem;">👥</span>
            <h2 class="font_bold">Nessun paziente assegnato</h2>
            <p style="opacity: 0.6; max-width: 360px; margin: 0 auto; line-height: 1.6;">
                Non hai pazienti collegati al tuo account. I pazienti si assegnano
                autonomamente dalla loro pagina profilo.
            </p>
        </div>

    @else

        {{-- ── Patient selector ── --}}
        <div class="section-block box padding_orizontal_20 padding_vertical_15 row vertical_center gap_20"
             style="flex-wrap: wrap;">
            <span class="font_bold" style="font-size: 1rem; white-space: nowrap; opacity: 0.8;">👤 Paziente:</span>

            <form method="GET" action="{{ route('prescriptions.index') }}" id="patient-form"
                  class="row vertical_center gap_15" style="flex: 1; min-width: 200px;">
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
                <form method="POST"
                      action="{{ route('prescriptions.clear', $selectedPatientId) }}"
                      onsubmit="return confirm('Cancellare tutte le prescrizioni di questo paziente?')">
                    @csrf
                    <button type="submit" class="danger-btn">🗑 Cancella tutto</button>
                </form>
            @endif
        </div>

        {{-- ── No medicines warning ── --}}
        @if($medicines->isEmpty())
            <div class="alert-warning">
                ⚠️ Nessun farmaco nel database.
                <a href="{{ route('medicines.index') }}" style="font-weight: bold; color: inherit;">
                    Aggiungi almeno un farmaco →
                </a>
            </div>
        @elseif($selectedPatientId)

            {{-- ── Weekly grid ── --}}
            <form method="POST" action="{{ route('prescriptions.store') }}">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $selectedPatientId }}">

                <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">

                    <div class="row between vertical_center" style="flex-wrap: wrap; gap: 12px;">
                        <div class="column gap_5">
                            <h2 class="font_bold" style="margin: 0; font-size: 1.35rem;">Piano Settimanale</h2>
                            <span style="font-size: 0.85rem; opacity: 0.55;">
                                Seleziona un farmaco per ogni slot — lascia vuoto per nessuna somministrazione
                            </span>
                        </div>
                        <button type="submit" class="btn primary presc-save-btn">Salva Piano</button>
                    </div>

                    <div style="overflow-x: auto; width: 100%;">
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
                                            $key      = $day . '_' . $stepNum;
                                            $existing = $prescriptionMap[$key] ?? null;
                                            $hasValue = (bool) $existing;
                                        @endphp
                                        <td>
                                            <select name="schedule[{{ $day }}][{{ $stepNum }}]"
                                                    class="med-select {{ $hasValue ? 'has-value' : '' }}"
                                                    onchange="this.classList.toggle('has-value', !!this.value)">
                                                <option value="">— vuoto —</option>
                                                @foreach($medicines as $med)
                                                    <option value="{{ $med->id }}"
                                                        {{ $existing && $existing->medicine_id == $med->id ? 'selected' : '' }}>
                                                        {{ $med->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            {{-- ── Summary ── --}}
            @php
                $giorni       = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
                $byDay        = collect($prescriptionMap)->groupBy(fn($p) => $p->day)->sortKeys();
                $totalCount   = count($prescriptionMap);
            @endphp

            @if($totalCount > 0)
                <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
                    <div class="row vertical_center gap_15">
                        <h2 class="font_bold" style="margin: 0; font-size: 1.2rem;">Prescrizioni attive</h2>
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
                <div class="section-block box row vertical_center gap_15 padding_orizontal_20 padding_vertical_15"
                     style="opacity: 0.55;">
                    <p style="margin: 0;">Nessuna prescrizione attiva per questo paziente.
                        Usa la griglia sopra per impostare il piano terapeutico.</p>
                </div>
            @endif

        @endif
    @endif
</div>
