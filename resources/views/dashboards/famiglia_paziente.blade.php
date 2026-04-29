@php use App\Models\User; @endphp

<div class="column padding_orizontal_20 padding_vertical_20 min_height gap_40 full_width">

    {{-- Header --}}
    <div class="row between vertical_center" style="flex-wrap: wrap; gap: 15px;">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Gestione Familiari</h1>
            <div class="row gap_10 vertical_center">
                <div class="avatar-circle avatar-sm">{{ strtoupper(substr($paziente->username, 0, 1)) }}</div>
                <span class="font_bold" style="font-size: 1.1rem;">{{ $paziente->username }}</span>
                <span class="role-badge paziente">paziente</span>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    {{-- Familiari già collegati --}}
    <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
        <div class="row between vertical_center" style="flex-wrap: wrap; gap: 10px;">
            <h2 class="font_bold section-title">
                Familiari collegati
                <span class="summary-count" style="margin-left: 8px;">{{ $familiari->count() }}</span>
            </h2>
        </div>

        @if($familiari->isEmpty())
            <div class="column vertical_center text_center gap_10" style="padding: 30px 0; opacity: 0.5;">
                <span style="font-size: 2rem;">🔗</span>
                <p>Nessun familiare ancora collegato a questo paziente.</p>
            </div>
        @else
            <div class="column gap_10">
                @foreach($familiari as $fam)
                    <div class="box row vertical_center gap_15 padding_orizontal_15 padding_vertical_10">
                        <div class="avatar-circle avatar-sm" style="background: #f0e2b8;">
                            {{ strtoupper(substr($fam->username, 0, 1)) }}
                        </div>
                        <div class="column gap_10" style="flex: 1;">
                            <span class="font_bold">{{ $fam->username }}</span>
                            <span style="opacity: 0.6; font-size: 0.85rem;">{{ $fam->email }}</span>
                        </div>
                        <span class="role-badge famiglia">famiglia</span>

                        <form method="POST"
                              action="{{ route('family.detach', [$paziente, $fam]) }}"
                              onsubmit="return confirm('Rimuovere {{ addslashes($fam->username) }} da questo paziente?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon delete" style="
                                background: rgba(220,53,69,0.08);
                                border: 2px solid rgba(220,53,69,0.3);
                                color: #c0392b;
                                border-radius: 9px;
                                padding: 6px 13px;
                                font-size: 0.82rem;
                                font-family: 'Fredoka', serif;
                                cursor: pointer;
                                font-weight: bold;
                            ">🗑 Rimuovi</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Aggiungi familiare --}}
    <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold section-title">Collega un familiare</h2>

        @if($altriFamiliari->isEmpty())
            <div style="opacity: 0.55; padding: 10px 0;">
                @if(User::where('role', 'famiglia')->count() === 0)
                    Nessun account con ruolo "famiglia" è registrato nel sistema.
                @else
                    Tutti gli account famiglia disponibili sono già collegati a questo paziente.
                @endif
            </div>
        @else
            <form method="POST" action="{{ route('family.attach', $paziente) }}"
                  class="row vertical_center gap_15" style="flex-wrap: wrap;">
                @csrf
                <select name="family_id" required class="patient-select-wrap" style="
                    background: var(--background-color1);
                    border: 2px solid currentColor;
                    border-radius: 10px;
                    padding: 9px 16px;
                    font-size: 1rem;
                    font-family: 'Fredoka', serif;
                    cursor: pointer;
                    flex: 1;
                    min-width: 200px;
                    appearance: none;
                    -webkit-appearance: none;
                ">
                    <option value="">Seleziona un familiare...</option>
                    @foreach($altriFamiliari as $fam)
                        <option value="{{ $fam->id }}">{{ $fam->username }} — {{ $fam->email }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn primary" style="aspect-ratio: unset; padding: 10px 24px; white-space: nowrap;">
                    Collega
                </button>
            </form>
        @endif
    </div>

</div>
