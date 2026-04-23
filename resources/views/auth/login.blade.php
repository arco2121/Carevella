<div class="column min_height around">

    <div class="column gap_20 vertical_center full_width text_center">
        <h1>Accedi al Sistema</h1>
        <h5>Pills Automatic Dispenser - Project 2026</h5>
    </div>

    <div class="column vertical_center full_width">

        @if ($errors->any())
            <div class="padding_vertical_15" style="color: red;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="column gap_20 padding_vertical_15 end box_focus_mode padding_orizontal_10 box">
            @csrf

            <div class="column full_width">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="Inserisci username">
            </div>

            <div class="column full_width">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>

            <div class="row gap_10 vertical_center">
                <input id="remember_me" type="checkbox" name="remember">
                <label for="remember_me">Ricordami</label>
            </div>

            <div class="column gap_10 full_width">
                <button type="submit" class="full_width">Entra</button>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size: 0.8rem; text-align: center;">
                        Password dimenticata?
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
