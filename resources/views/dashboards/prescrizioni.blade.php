<div class="column padding_orizontal_20 padding_vertical_20 min_height">
    <h1 class="text_center font_bold">Gestione Prescrizioni</h1>

    <form action="{{ route('prescriptions.store') }}" method="POST">
        @csrf

        <div class="row vertical_center box padding_orizontal_15 padding_vertical_10 gap_20" style="margin-bottom: 30px; width: fit-content;">
            <h5 class="font_bold">Paziente:</h5>
            <select name="patient_id" class="font_normal" required style="background: transparent; border: none; font-size: 1.1rem;">
                <option value="">Scegli un utente...</option>
                @foreach($users as $user)
                    <option style="color: black" value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="row gap_20" style="overflow-x: auto; padding-bottom: 20px;">
            <div class="column gap_20" style="margin-top: 60px;">
                @foreach($times as $time)
                    <div class="font_bold text_center vertical_center box" style="height: 80px; width: 80px; justify-content: center;">
                        {{ $time }}
                    </div>
                @endforeach
            </div>

            @foreach($days as $index => $dayName)
                @php $dayNum = $index + 1; @endphp
                <div class="column gap_20 box padding_vertical_10" style="min-width: 180px;">
                    <div class="text_center font_bold padding_vertical_10" style="border-bottom: 2px solid var(--font-color);">
                        {{ $dayName }}
                    </div>

                    @for($step = 1; $step <= 6; $step++)
                        <div class="padding_orizontal_10 column vertical_center" style="height: 80px; justify-content: center;">
                            <select name="schedule[{{ $dayNum }}][{{ $step }}]" class="full_width font_light"
                                    style="border-radius: 8px; border: 1px solid #ccc; padding: 5px; background: var(--background-tiles);">
                                <option value="">-- Vuoto --</option>
                                @foreach($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                            <span style="font-size: 0.7rem; margin-top: 5px;" class="font_light">Step {{ $step }}</span>
                        </div>
                    @endfor
                </div>
            @endforeach
        </div>

        <div class="row padding_vertical_20 end">
            <button type="submit" class="font_bold" style="width: 200px; height: 60px; cursor: pointer;">
                SALVA PIANO
            </button>
        </div>
    </form>
</div>
