<div class="column min_height around">

    <div class="column gap_20 vertical_center full_width text_center">
        <h1>Recupero Password</h1>
        <h5 class="empty-desc">
            Password dimenticata? Nessun problema. Inserisci il tuo indirizzo email e ti invieremo un link per sceglierne una nuova.
        </h5>
    </div>

    <div class="column vertical_center full_width">

        @if (session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="column gap_10 padding_vertical_15 text_center alert-error">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="column gap_20 padding_vertical_15 end box_focus_mode padding_orizontal_10 box">
            @csrf

            <div class="column full_width gap_10">
                <label for="email">Indirizzo Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Inserisci la tua email">
            </div>

            <div class="column gap_20 vertical_center full_width">
                <button type="submit" class="primary btn full-width">Reset Link</button>

                <a href="{{ route('login') }}" class="text_center profilo-email" style="text-decoration: none;">
                    Torna al login
                </a>
            </div>
        </form>
    </div>
</div>
