<div class="column padding_orizontal_20 full-width padding_vertical_20 min_height gap_20">

    <div class="row between vertical_center" style="flex-wrap: wrap; gap: 15px;">
        <div class="column gap_10">
            <h1 class="font_bold dash-title">Gestione Farmaci</h1>
            <span class="farm-subtitle">Aggiungi, rinomina o elimina i farmaci disponibili</span>
        </div>
    </div>

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

    <div class="row gap_15 farm-stats">
        <span class="stat-chip">💊 {{ $medicines->count() }} farmac{{ $medicines->count() === 1 ? 'o' : 'i' }}</span>
        <span class="stat-chip">📋 {{ $medicines->sum('prescriptions_count') }} prescrizioni attive</span>
    </div>

    <div class="section-block box column gap_15 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold farm-section-title">Aggiungi nuovo farmaco</h2>
        <form method="POST" action="{{ route('medicines.store') }}" class="column gap_15">
            @csrf
            <div class="add-form-row">
                <div class="farm-field-group">
                    <label class="farm-field-label" for="new-code">Codice ID</label>
                    <input type="text"
                           id="new-code"
                           name="code"
                           class="farm-input farm-input--code"
                           placeholder="Es. PAR500"
                           value="{{ old('code') }}"
                           maxlength="20"
                           required
                           autocomplete="off"
                    >
                </div>
                <div class="farm-field-group farm-field-grow">
                    <label class="farm-field-label" for="new-name">Nome farmaco</label>
                    <input type="text"
                           id="new-name"
                           name="name"
                           class="farm-input"
                           placeholder="Es. Paracetamolo 500mg"
                           value="{{ old('name') }}"
                           required
                           autocomplete="off">
                </div>
                <button type="submit" class="btn primary save-btn farm-add-submit">Aggiungi</button>
            </div>
            <span class="farm-subtitle">Il codice può contenere solo lettere maiuscole, numeri e trattini (es. PAR500, IBU-400).</span>
        </form>
    </div>

    <div class="section-block box column gap_0 padding_orizontal_20 padding_vertical_20">
        <h2 class="font_bold farm-section-title farm-list-title">Farmaci registrati</h2>

        @if($medicines->isEmpty())
            <div class="farm-empty column vertical_center text_center">
                <span class="farm-empty-icon">💊</span>
                <p>Nessun farmaco ancora. Aggiungine uno qui sopra.</p>
            </div>
        @else
            <div class="farm-list">
                @foreach($medicines as $med)
                    <div>
                        <div class="farm-card mobile_row mobile_not_center">
                            <div class="farm-icon">💊</div>
                            <div class="column gap_10 farm-name-col">
                                <span class="farm-name">{{ $med->name }}</span>
                                <span class="farm-code-badge"><kbd>{{ $med->code }}</kbd></span>
                            </div>
                            <span class="farm-badge">
                                {{ $med->prescriptions_count }} prescrizi{{ $med->prescriptions_count === 1 ? 'one' : 'oni' }}
                            </span>
                            <div class="farm-actions">
                                <button type="button"
                                        class="btn-icon edit"
                                        data-toggle-edit="{{ $med->id }}">✏️ Modifica</button>
                                @if($med->prescriptions_count === 0)
                                    <form method="POST" action="{{ route('medicines.destroy', $med) }}"
                                          data-confirm="Eliminare {{ addslashes($med->name) }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon delete">🗑</button>
                                    </form>
                                @else
                                    <button class="btn-icon delete" disabled
                                            title="In uso in {{ $med->prescriptions_count }} prescrizioni">🗑</button>
                                @endif
                            </div>
                        </div>

                        <div class="edit-panel fade_animation" id="edit-{{ $med->id }}">
                            <form method="POST" action="{{ route('medicines.update', $med) }}" class="column gap_15">
                                @csrf @method('PUT')
                                <div class="add-form-row">
                                    <div class="farm-field-group">
                                        <label class="farm-field-label" for="edit-code-{{ $med->id }}">Codice ID</label>
                                        <input type="text"
                                               id="edit-code-{{ $med->id }}"
                                               name="code"
                                               class="farm-input farm-input--code"
                                               value="{{ old('code', $med->code) }}"
                                               maxlength="20"
                                               required
                                               autocomplete="off">
                                    </div>
                                    <div class="farm-field-group farm-field-grow">
                                        <label class="farm-field-label" for="edit-name-{{ $med->id }}">Nome farmaco</label>
                                        <input type="text"
                                               id="edit-name-{{ $med->id }}"
                                               name="name"
                                               class="farm-input"
                                               value="{{ old('name', $med->name) }}"
                                               required
                                               autocomplete="off">
                                    </div>
                                   <div class="row gap_15 vertical_center">
                                       <button type="submit" class="btn primary save-btn">Salva</button>
                                       <button type="button"
                                               class="btn-icon edit"
                                               data-toggle-edit="{{ $med->id }}">Annulla</button>
                                   </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="farm-nav-row">
        <a href="{{ route('prescriptions.index') }}" class="btn primary farm-nav-btn">Vai alle Prescrizioni</a>
    </div>

</div>

@vite(['resources/js/pages/farmaci.js'])
