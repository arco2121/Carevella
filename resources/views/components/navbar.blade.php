<nav class="row navbar full_width padding_orizontal_10 padding_vertical_15 gap_20 between vertical_center">

    <picture>
        <source srcset="{{ asset('assets/icon.png')}}">
        <a href="/"><img src="{{ asset('assets/icon.png')}}" width="40" height="40" alt="icon"></a>
    </picture>

    <div class="row around vertical_center gap_20">
        @auth
            <a href="/dashboard">Dashboard</a>
            <a href="{{ route('profilo') }}">Profilo</a>
            <a href="/logout">Logout</a>
        @endauth
        @guest
            <a href="/login">Login/Register</a>
        @endguest
    </div>

</nav>
