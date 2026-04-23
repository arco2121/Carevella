@php
    // Array per la generazione dinamica della tabella delle prescrizioni
    $giorni = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
    $fasce  = ['8:00 / 10:00',  '10:00 / 12:00', '12:00 / 14:00','14:00 / 16:00', '16:00 / 18:00', '18:00 / 20:00','20:00 / 22:00', '22:00 / 8:00',];
@endphp

@csrf

<h1>Gestione Prescrizioni</h1>

<label for="paziente-select">Paziente:</label>
<select id="paziente-select">
    <option value="">— Seleziona paziente —</option>
    @foreach ($pazienti as $paziente)
        <option value="{{ $paziente->id }}">{{ $paziente->username }}</option>
    @endforeach
</select>

<button id="saveBtn">Salva</button>

<table id="tabella-prescrizioni">
    <thead>
    <tr>
        <th>Fascia oraria</th>
        @foreach ($giorni as $giorno)
            <th>{{ $giorno }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($fasce as $orario)
        <tr>
            <td>{{ $orario }}</td>
            @foreach ($giorni as $giorno)
                <td>
                    <select
                        data-giorno="{{ $giorno }}"
                        data-orario="{{ $orario }}"
                        name="prescrizioni[{{ $giorno }}][{{ $orario }}]"
                    >
                        <option value="">--</option>
                        @foreach ($medicine as $medicina)
                            <option value="{{ $medicina->id }}">{{ $medicina->name }}</option>
                        @endforeach
                    </select>
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<div id="stato"></div>

@vite(['resources/js/pages/dashboard_medico.js'])
