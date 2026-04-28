<div class="dashboard-wrapper column padding_orizontal_20 padding_vertical_20 min_height gap_20">

    {{-- Flash success --}}
    @if(session('success'))
        <div class="box padding_orizontal_15 padding_vertical_10"
             style="background: #d4edda; border-color: #28a745; color: #155724; border-radius: 12px;">{{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="dash-header row vertical_center">
        <div class="column gap_10">
            <h1 class="font_bold" style="font-size: 2rem; margin: 0;">Il mio Profilo</h1>
            <span class="role-badge {{ $user->role }}">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    {{-- Scheda info base --}}
    <div class="section-block box row vertical_center gap_20 padding_orizontal_20 padding_vertical_20">
        <div class="avatar-circle" style="width: 70px; height: 70px; font-size: 2rem; min-width: 70px;">
            {{ strtoupper(substr($user->username, 0, 1)) }}
        </div>
        <div class="column gap_10">
            <span class="font_bold" style="font-size: 1.5rem;">{{ $user->username }}</span>
            <span style="opacity: 0.6; font-size: 0.95rem;">{{ $user->email }}</span>
            <span class="role-badge {{ $user->role }}" style="width: fit-content; margin-top: 4px;">
                {{ ucfirst($user->role) }}
            </span>
        </div>
    </div>

    @if($user->role === 'paziente')

        {{-- Medico attuale --}}
        <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">👨‍⚕️ Il mio Medico</h2>

            @if($user->medico)
                <div class="box row vertical_center gap_15 padding_orizontal_15 padding_vertical_10" style="width: fit-content;">
                    <div class="avatar-circle" style="background: #b8d4f0; width: 44px; height: 44px; font-size: 1.2rem; min-width: 44px;">
                        {{ strtoupper(substr($user->medico->username, 0, 1)) }}
                    </div>
                    <div class="column gap_5">
                        <span class="font_bold">{{ $user->medico->username }}</span>
                        <span style="opacity: 0.6; font-size: 0.85rem;">{{ $user->medico->email }}</span>
                    </div>
                </div>
            @else
                <div class="row gap_10 vertical_center" style="opacity: 0.5; padding: 10px 0;">
                    <span style="font-size: 1.5rem;">🔍</span>
                    <p style="margin: 0;">Nessun medico assegnato. Selezionane uno qui sotto.</p>
                </div>
            @endif
        </div>

        {{-- Form cambio medico --}}
        <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">
                {{ $user->doctor_id ? '🔄 Cambia Medico' : '➕ Assegna un Medico' }}
            </h2>

            @if($user->doctor_id)
                <div class="box padding_orizontal_15 padding_vertical_10"
                     style="background: #fff3cd; border-color: #ffc107; color: #856404; border-radius: 10px; line-height: 1.5;">
                    Attenzione: cambiando medico le prescrizioni attuali verranno
                    eliminate, poiché il nuovo medico imposterà un nuovo piano terapeutico.
                </div>
            @endif

            @if($medici->isEmpty())
                <p style="opacity: 0.5;">Nessun medico registrato nel sistema al momento.</p>
            @else
                <form method="POST" action="{{ route('profilo.assegna-medico') }}" class="column gap_15">
                    @csrf
                    <div class="column gap_10">
                        <label class="font_bold" for="doctor_id">Seleziona Medico</label>
                        <select name="doctor_id" id="doctor_id" required
                                style="background: var(--background-color1); border: 2px solid currentColor;
                                       border-radius: 10px; padding: 10px 15px; font-size: 1rem;
                                       font-family: 'Fredoka', serif; max-width: 450px;">
                            <option value="">Scegli un medico...</option>
                            @foreach($medici as $medico)
                                <option value="{{ $medico->id }}"
                                    {{ $user->doctor_id == $medico->id ? 'selected' : '' }}>
                                    {{ $medico->username }} — {{ $medico->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                        <span style="color: #dc3545; font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn primary min_height_min">Salva</button>
                </form>
            @endif
        </div>

    @elseif($user->role === 'medico')

        <div class="section-block box column gap_20 padding_orizontal_20 padding_vertical_20">

            <div class="row between vertical_center" style="flex-wrap: wrap; gap: 10px;">
                <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">
                    👥 I miei Pazienti ({{ $pazienti_list->count() }})
                </h2>
                <a href="/dashboard/prescrizioni" class="cta-btn primary" style="text-decoration: none; font-size: 0.9rem;">
                    💊 Gestisci Prescrizioni
                </a>
            </div>

            @if($pazienti_list->isEmpty())
                <div class="empty-state column vertical_center text_center gap_10" style="padding: 30px 0; opacity: 0.5;">
                    <span style="font-size: 2.5rem;">👥</span>
                    <p>Nessun paziente assegnato ancora.<br>
                        I pazienti si assegnano da soli dalla loro pagina profilo.</p>
                </div>
            @else
                <div class="column gap_10">
                    @foreach($pazienti_list as $paz)
                        <div class="box row vertical_center gap_15 padding_orizontal_15 padding_vertical_10"
                             style="transition: transform 0.15s ease;">
                            <div class="avatar-circle" style="width: 42px; height: 42px; font-size: 1.1rem; min-width: 42px;">
                                {{ strtoupper(substr($paz->username, 0, 1)) }}
                            </div>
                            <div class="column gap_5">
                                <span class="font_bold">{{ $paz->username }}</span>
                                <span style="opacity: 0.6; font-size: 0.85rem;">{{ $paz->email }}</span>
                            </div>
                            <span class="role-badge paziente" style="margin-left: auto;">paziente</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    @elseif($user->role === 'famiglia')

        @php $pazienteCollegato = \App\Models\User::find($user->doctor_id); @endphp

        <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
            <h2 class="font_bold" style="margin: 0; font-size: 1.4rem;">👤 Paziente Monitorato</h2>

            @if($pazienteCollegato)
                <div class="box row vertical_center gap_15 padding_orizontal_15 padding_vertical_10" style="width: fit-content;">
                    <div class="avatar-circle" style="width: 44px; height: 44px; font-size: 1.2rem; min-width: 44px;">
                        {{ strtoupper(substr($pazienteCollegato->username, 0, 1)) }}
                    </div>
                    <div class="column gap_5">
                        <span class="font_bold">{{ $pazienteCollegato->username }}</span>
                        <span style="opacity: 0.6; font-size: 0.85rem;">{{ $pazienteCollegato->email }}</span>
                    </div>
                </div>
            @else
                <p style="opacity: 0.5;">Nessun paziente collegato. Contatta l'amministrazione.</p>
            @endif
        </div>

    @endif

</div>
