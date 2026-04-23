<div class="column min_height around">

    <div class="column gap_20 vertical_center full_width text_center">
        <h1>Crea Account</h1>
        <h6>Hai un accesso? <a href="/login">Loggati</a></h6>
    </div>

    <div class="column vertical_center full_width margin_vertical_20">

        {{-- Messaggi di Errore --}}
        @if ($errors->any())
            <div class="column gap_10 padding_vertical_15 text_center" style="color: #ff4d4d; font-weight: bold;">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="login_form column gap_20 padding_vertical_15 end box_focus_mode padding_orizontal_10 box">
            @csrf

            {{-- Username (Cambiato da Name per il tuo DB) --}}
            <div class="column full_width gap_5">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="Scegli un username">
            </div>

            {{-- Email --}}
            <div class="column full_width gap_5">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="esempio@mail.it">
            </div>

            {{-- Ruolo (Importante per la tua logica Medico/Paziente) --}}
            <div class="column full_width gap_5">
                <label for="role">Tipo di Utente</label>
                <select id="role" name="role" required class="full_width">
                    <option value="patient">Paziente</option>
                    <option value="doctor">Medico</option>
                    <option value="family">Familiare</option>
                </select>
            </div>

            {{-- Password --}}
            <div class="column full_width gap_10">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Minimo 8 caratteri">
            </div>

            {{-- Conferma Password --}}
            <div class="column full_width gap_10">
                <label for="password_confirmation">Conferma Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ripeti la password">
            </div>

            {{-- Azioni --}}
            <div class="column vertical_center gap_20 full_width">
                <button type="submit" class="full-width">Registrati</button>

                <a href="{{ route('login') }}" class="text_center" style="font-size: 0.8rem; text-decoration: none; color: inherit; opacity: 0.7;">
                    Hai già un account? Accedi
                </a>
            </div>
        </form>
    </div>
</div>
