<div class="dashboard-wrapper column padding_orizontal_20 padding_vertical_20 min_height gap_40">

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="dash-header row vertical_center">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Il mio Profilo</h1>
            <span class="role-badge {{ $user->role }}">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    <div class="section-block box row vertical_center gap_20 padding_orizontal_20 padding_vertical_20">
        <div class="avatar-circle avatar-lg">{{ strtoupper(substr($user->username, 0, 1)) }}</div>
        <div class="column gap_10">
            <span class="font_bold profilo-username">{{ $user->username }}</span>
            <span class="profilo-email">{{ $user->email }}</span>
            <span class="role-badge {{ $user->role }}">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    @if($user->role === 'paziente')

        <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold section-title">Il mio Medico</h2>
            @if($user->medico)
                <div class="box row vertical_center gap_15 padding_orizontal_15 padding_vertical_10 full_width">
                    <div class="avatar-circle avatar-md" style="background: #b8d4f0;">
                        {{ strtoupper(substr($user->medico->username, 0, 1)) }}
                    </div>
                    <div class="column gap_10">
                        <span class="font_bold">{{ $user->medico->username }}</span>
                        <span class="profilo-email">{{ $user->medico->email }}</span>
                    </div>
                </div>
            @else
                <div class="row gap_10 vertical_center profilo-no-medico">
                    <span class="empty-icon" style="font-size: 1.5rem;">🔍</span>
                    <p>Nessun medico assegnato. Selezionane uno qui sotto.</p>
                </div>
            @endif
        </div>

        <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold section-title">{{ $user->doctor_id ? 'Cambia Medico' : 'Assegna un Medico' }}</h2>

            @if($user->doctor_id)
                <div class="alert-warning">
                    Attenzione: cambiando medico le prescrizioni attuali verranno eliminate, poiché il nuovo medico imposterà un nuovo piano terapeutico.
                </div>
            @endif

            @if($medici->isEmpty())
                <p class="profilo-no-medico">Nessun medico registrato nel sistema al momento.</p>
            @else
                <form method="POST" action="{{ route('profilo.assegna-medico') }}" class="column gap_15">
                    @csrf
                    <div class="column gap_10">
                        <label class="font_bold" for="doctor_id">Seleziona Medico</label>
                        <select name="doctor_id" id="doctor_id" required class="profilo-doctor-select">
                            <option value="">Scegli un medico...</option>
                            @foreach($medici as $medico)
                                <option value="{{ $medico->id }}" {{ $user->doctor_id == $medico->id ? 'selected' : '' }}>
                                    {{ $medico->username }} — {{ $medico->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <span class="profilo-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn primary min_height_min">Salva</button>
                </form>
            @endif
        </div>

        @elseif($user->role === 'medico')

            <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
                <div class="row between vertical_center presc-header">
                    <h2 class="font_bold section-title">I miei Pazienti ({{ $pazienti_list->count() }})</h2>
                    <a href="/dashboard/prescrizioni" class="btn primary">Gestione Prescrizioni</a>
                </div>

                @if($pazienti_list->isEmpty())
                    <div class="empty-state column vertical_center text_center gap_10">
                        <span class="empty-icon">👥</span>
                        <p>Nessun paziente assegnato ancora.<br>I pazienti si assegnano da soli dalla loro pagina profilo.</p>
                    </div>
                @else
                    <div class="column gap_10">
                        @foreach($pazienti_list as $paz)
                            <div class="box row vertical_center gap_15 padding_orizontal_15 padding_vertical_10">
                                <div class="avatar-circle avatar-sm">{{ strtoupper(substr($paz->username, 0, 1)) }}</div>
                                <div class="column gap_10">
                                    <span class="font_bold">{{ $paz->username }}</span>
                                    <span class="profilo-email">{{ $paz->email }}</span>
                                    @php $famCount = $paz->familiari()->count(); @endphp
                                    @if($famCount > 0)
                                        <span>
                                            {{ $famCount }} familiare{{ $famCount > 1 ? 'i' : '' }} collegat{{ $famCount > 1 ? 'i' : 'o' }}
                                    </span>
                                    @endif
                                </div>
                                <span class="role-badge paziente profilo-badge-right">paziente</span>
                                <a href="{{ route('family.index', $paz) }}"
                                   class="btn secondary">Familiari
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

    @elseif($user->role === 'famiglia')

        @php $pazienteCollegato = \App\Models\User::find($user->doctor_id); @endphp

        <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold section-title">Paziente Monitorato</h2>
            @if($pazienteCollegato)
                <div class="box row vertical_center gap_15 padding_orizontal_15 padding_vertical_10 profilo-paziente-card">
                    <div class="avatar-circle avatar-md">{{ strtoupper(substr($pazienteCollegato->username, 0, 1)) }}</div>
                    <div class="column gap_10">
                        <span class="font_bold">{{ $pazienteCollegato->username }}</span>
                        <span class="profilo-email">{{ $pazienteCollegato->email }}</span>
                    </div>
                </div>
            @else
                <p class="profilo-no-medico">Nessun paziente collegato. Contatta l'amministrazione.</p>
            @endif
        </div>

    @endif

</div>
