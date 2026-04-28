<div class="column min_height around">

    <div class="column gap_20 vertical_center full_width text_center">
        <h1>Recupero Password</h1>
        <h5 style="max-width: 400px; margin: 0 auto; opacity: 0.8;">
            Password dimenticata? Nessun problema. Inserisci il tuo indirizzo email e ti invieremo un link per sceglierne una nuova.
        </h5>
    </div>

    <div class="column vertical_center full_width">

        {{-- Messaggio di successo (Stato della sessione) --}}
        @if (session('status'))
            <div class="box padding_10 mb_20" style="color: #28a745; background-color: #d4edda; border-color: #c3e6cb; text-align: center;">
                {{ session('status') }}
            </div>
        @endif

        {{-- Messaggi di Errore --}}
        @if ($errors->any())
            <div class="column gap_10 padding_vertical_15 text_center" style="color: #ff4d4d; font-weight: bold;">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="column gap_20 padding_vertical_15 end box_focus_mode padding_orizontal_10 box">
            @csrf

            {{-- Email --}}
            <div class="column full_width gap_10">
                <label for="email">Indirizzo Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Inserisci la tua email">
            </div>

            {{-- Azione --}}
            <div class="column gap_20 vertical_center full_width">
                <button type="submit" class="primary btn full-width">Reset Link</button>

                <a href="{{ route('login') }}" class="text_center" style="font-size: 0.8rem; text-decoration: none; color: inherit; opacity: 0.7;">
                    Torna al login
                </a>
            </div>
        </form>
    </div>
</div>
