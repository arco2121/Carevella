<div class="column padding_orizontal_20 full-width padding_vertical_20 min_height gap_20">

    {{-- Header --}}
    <div class="row between vertical_center" style="flex-wrap: wrap; gap: 15px;">
        <div class="column gap_10">
            <h1 class="font_bold" style="font-size: 2rem; margin: 0;">Gestione Farmaci</h1>
            <span style="opacity:0.6; font-size:0.9rem;">Aggiungi, rinomina o elimina i farmaci disponibili</span>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- Stats --}}
    <div class="row gap_15" style="flex-wrap: wrap;">
        <span class="stat-chip">
            💊 {{ $medicines->count() }} farmac{{ $medicines->count() === 1 ? 'o' : 'i' }}
        </span>
        <span class="stat-chip">
            📋 {{ $medicines->sum('prescriptions_count') }} prescrizioni attive
        </span>
    </div>

    {{-- Add form --}}
    <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold" style="margin:0; font-size:1.2rem;">Aggiungi nuovo farmaco</h2>
        <form method="POST" action="{{ route('medicines.store') }}" class="add-form-row">
            @csrf
            <input type="text" name="name" class="farm-input"
                   placeholder="Es. Paracetamolo 500mg"
                   value="{{ old('name') }}" required autocomplete="off">
            <button type="submit" class="btn primary save-btn">Aggiungi</button>
        </form>
    </div>

    {{-- List --}}
    <div class="section-block box column gap_0 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold" style="margin:0 0 16px; font-size:1.2rem;">Farmaci registrati</h2>

        @if($medicines->isEmpty())
            <div class="farm-empty column vertical_center text_center">
                <span class="farm-empty-icon">💊</span>
                <p style="margin:0;">Nessun farmaco ancora. Aggiungine uno qui sopra.</p>
            </div>
        @else
            <div class="farm-list">
                @foreach($medicines as $med)
                    <div>
                        {{-- Card --}}
                        <div class="farm-card mobile_row">
                            <div class="farm-icon">💊</div>

                            <div class="farm-name">{{ $med->name }}</div>

                            <span class="farm-badge">
                                {{ $med->prescriptions_count }}
                                prescrizi{{ $med->prescriptions_count === 1 ? 'one' : 'oni' }}
                            </span>

                            <div class="farm-actions">
                                <button type="button" class="btn-icon edit"
                                        onclick="toggleEdit({{ $med->id }})">
                                    ✏️ Modifica
                                </button>

                                @if($med->prescriptions_count === 0)
                                    <form method="POST"
                                          action="{{ route('medicines.destroy', $med) }}"
                                          onsubmit="return confirm('Eliminare {{ addslashes($med->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon delete">🗑</button>
                                    </form>
                                @else
                                    <button class="btn-icon delete" disabled
                                            title="In uso in {{ $med->prescriptions_count }} prescrizioni">🗑</button>
                                @endif
                            </div>
                        </div>

                        {{-- Inline edit panel --}}
                        <div class="edit-panel" id="edit-{{ $med->id }}">
                            <form method="POST" action="{{ route('medicines.update', $med) }}"
                                  class="add-form-row">
                                @csrf @method('PUT')
                                <input type="text" name="name" class="farm-input"
                                       value="{{ $med->name }}" required autocomplete="off">
                                <button type="submit" class="btn primary save-btn">Salva</button>
                                <button type="button" class="btn-icon edit"
                                        onclick="toggleEdit({{ $med->id }})">Annulla</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Navigation --}}
    <div class="farm-nav-row">
        <a href="{{ route('prescriptions.index') }}" class="btn primary"
           style="text-decoration:none; aspect-ratio:unset; padding:10px 22px;">Vai alle Prescrizioni</a>
    </div>
</div>

@vite(['resources/js/pages/farmaci.js'])
