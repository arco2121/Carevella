@php use App\Models\User; @endphp

<div class="column padding_orizontal_20 padding_vertical_20 min_height gap_40 full_width">

    <div class="row between vertical_center fam-header">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Gestione Familiari</h1>
            <div class="row gap_10 vertical_center">
                <div class="avatar-circle avatar-sm">{{ strtoupper(substr($paziente->username, 0, 1)) }}</div>
                <span class="font_bold fam-patient-name">{{ $paziente->username }}</span>
                <span class="role-badge paziente">Paziente</span>
            </div>
        </div>
        <a href="{{ route('dashboard-medico') }}" class="btn secondary">Torna alla Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
        <div class="row between vertical_center">
            <h2 class="font_bold section-title">
                Familiari collegati
                <span class="summary-count">{{ $familiari->count() }}</span>
            </h2>
        </div>

        @if($familiari->isEmpty())
            <div class="column vertical_center text_center fam-empty">
                <span class="fam-empty-icon">🔗</span>
                <p>Nessun familiare ancora collegato a questo paziente.</p>
            </div>
        @else
            <div class="column gap_10">
                @foreach($familiari as $fam)
                    <div class="box row nobounce vertical_center gap_15 between padding_orizontal_15 padding_vertical_10">
                        <div class="row vertical_center gap_15">
                            <div class="avatar-circle avatar-sm">{{ strtoupper(substr($fam->username, 0, 1)) }}</div>
                            <div class="column gap_10 fam-card">
                                <span class="font_bold">{{ $fam->username }}</span>
                                <span class="fam-card-email">{{ $fam->email }}</span>
                            </div>
                        </div>
                        <div class="row vertical_center gap_15">
                            <span class="role-badge famiglia">Famiglia</span>
                            <form method="POST"
                                  action="{{ route('family.detach', [$paziente, $fam]) }}"
                                  onsubmit="return confirm('Rimuovere {{ addslashes($fam->username) }} da questo paziente?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-remove">Rimuovi</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold section-title">Collega un familiare</h2>

        @if($altriFamiliari->isEmpty())
            <p class="fam-no-available">
                @if(User::where('role', 'famiglia')->count() === 0)
                    Nessun account con ruolo "famiglia" è registrato nel sistema.
                @else
                    Tutti gli account Famiglia disponibili sono già collegati a questo paziente.
                @endif
            </p>
        @else
            <form method="POST" action="{{ route('family.attach', $paziente) }}"
                  class="row vertical_center gap_15 fam-attach-form">
                @csrf
                <select name="family_id" required class="fam-select">
                    <option value="">Seleziona un familiare...</option>
                    @foreach($altriFamiliari as $fam)
                        <option value="{{ $fam->id }}">{{ $fam->username }} — {{ $fam->email }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn primary fam-attach-btn">Collega</button>
            </form>
        @endif
    </div>

</div>
