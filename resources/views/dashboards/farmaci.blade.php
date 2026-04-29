<style>
    .farm-card {
        background: var(--background-tiles);
        border: 2px solid rgba(0,0,0,0.07);
        border-radius: 14px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .farm-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.07);
    }

    .farm-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--background-color1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .farm-name {
        font-size: 1.05rem;
        font-weight: bold;
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .farm-badge {
        font-size: 0.75rem;
        opacity: 0.5;
        white-space: nowrap;
    }

    .farm-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-edit, .btn-del {
        border: 2px solid transparent;
        border-radius: 9px;
        padding: 6px 13px;
        font-size: 0.82rem;
        font-family: 'Fredoka', serif;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.15s ease;
    }
    .btn-edit {
        background: var(--background-color2);
        border-color: rgba(0,0,0,0.12);
    }
    .btn-edit:hover { border-color: rgba(0,0,0,0.3); }

    .btn-del {
        background: rgba(220,53,69,0.08);
        border-color: rgba(220,53,69,0.3);
        color: #c0392b;
    }
    .btn-del:hover { background: rgba(220,53,69,0.16); border-color: #dc3545; }

    .add-form-row {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .farm-input {
        flex: 1;
        min-width: 200px;
        background: var(--background-tiles);
        border: 2px solid rgba(0,0,0,0.12);
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 1rem;
        font-family: 'Fredoka', serif;
        color: var(--font-color);
        transition: border-color 0.2s ease;
    }
    .farm-input:focus { border-color: var(--background-button); outline: none; }

    .save-btn {
        aspect-ratio: unset !important;
        padding: 10px 22px !important;
        border-radius: 10px !important;
        font-size: 0.95rem !important;
        white-space: nowrap;
    }

    .alert-success { padding:12px 18px; border-radius:12px; background:#d4edda; border:2px solid #28a745; color:#155724; }
    .alert-error   { padding:12px 18px; border-radius:12px; background:#f8d7da; border:2px solid #dc3545; color:#721c24; }

    /* inline edit panel */
    .edit-panel {
        display: none;
        background: var(--background-color2);
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        padding: 14px 18px;
        margin-top: 6px;
        flex-direction: column;
        gap: 10px;
    }
    .edit-panel.open { display: flex; }

    .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--background-color1);
        border-radius: 999px;
        padding: 5px 14px;
        font-size: 0.85rem;
        font-weight: bold;
    }
</style>

<div class="column padding_orizontal_20 padding_vertical_20 min_height gap_20"
     style="max-width: 820px; margin: 0 auto; padding-bottom: 60px;">

    {{-- Header --}}
    <div class="row between vertical_center" style="flex-wrap: wrap; gap: 15px;">
        <div class="column gap_10">
            <h1 class="font_bold" style="font-size: 2rem; margin: 0;">🧪 Gestione Farmaci</h1>
            <span style="opacity:0.6; font-size:0.9rem;">Aggiungi, rinomina o elimina i farmaci disponibili</span>
        </div>
        <a href="/dashboard-medico" class="btn secondary"
           style="text-decoration:none; aspect-ratio:unset; padding:10px 20px;">
            ← Dashboard
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">❌ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $e) <div>❌ {{ $e }}</div> @endforeach
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
        <h2 class="font_bold" style="margin:0; font-size:1.2rem;">➕ Aggiungi nuovo farmaco</h2>
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
        <h2 class="font_bold" style="margin:0 0 16px; font-size:1.2rem;">📦 Farmaci registrati</h2>

        @if($medicines->isEmpty())
            <div class="column vertical_center text_center gap_15" style="padding: 40px 0; opacity:0.45;">
                <span style="font-size:3rem;">💊</span>
                <p style="margin:0;">Nessun farmaco ancora. Aggiungine uno qui sopra.</p>
            </div>
        @else
            <div class="column gap_10">
                @foreach($medicines as $med)
                    <div>
                        {{-- Card --}}
                        <div class="farm-card">
                            <div class="farm-icon">💊</div>

                            <div class="farm-name">{{ $med->name }}</div>

                            <span class="farm-badge">
                                {{ $med->prescriptions_count }}
                                prescrizi{{ $med->prescriptions_count === 1 ? 'one' : 'oni' }}
                            </span>

                            <div class="farm-actions">
                                <button type="button" class="btn-edit"
                                        onclick="toggleEdit({{ $med->id }})">
                                    ✏️ Modifica
                                </button>

                                @if($med->prescriptions_count === 0)
                                    <form method="POST"
                                          action="{{ route('medicines.destroy', $med) }}"
                                          onsubmit="return confirm('Eliminare {{ addslashes($med->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del">🗑</button>
                                    </form>
                                @else
                                    <button class="btn-del" disabled
                                            title="In uso in {{ $med->prescriptions_count }} prescrizioni"
                                            style="opacity:0.35; cursor:not-allowed;">🗑</button>
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
                                <button type="button" class="btn-edit"
                                        onclick="toggleEdit({{ $med->id }})">Annulla</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Navigation --}}
    <div class="row gap_15" style="flex-wrap: wrap;">
        <a href="{{ route('prescriptions.index') }}" class="btn primary"
           style="text-decoration:none; aspect-ratio:unset; padding:10px 22px;">
            💊 Vai alle Prescrizioni →
        </a>
    </div>
</div>

<script>
    function toggleEdit(id) {
        const panel = document.getElementById('edit-' + id);
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) {
            panel.querySelector('input').focus();
        }
    }
</script>
