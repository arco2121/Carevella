<section class="column full-width vertical_center text_center min_height around padding_orizontal_20 gap_60">

    <div class="column gap_20 vertical_center">
        <div class="pill-badge">{{ $name }}</div>
        <h3 class="hero-title">Monitora.<br>Cura.<br>Proteggi.</h3>
        <p class="hero-sub">Piattaforma intelligente per il monitoraggio dei pazienti in tempo reale tramite sensori IoT e dispensatore automatico di farmaci.</p>

        <div class="row gap_20 vertical_center hero-cta">
            @auth
                <a href="/dashboard" class="btn primary">Vai alla Dashboard</a>
            @else
                <a href="/login" class="btn primary">Accedi al sistema</a>
                <a href="/register" class="btn secondary">Crea account</a>
            @endauth
        </div>
    </div>

    <div class="features-grid gap_20 margin_vertical_20">
        <div class="feature-card">
            <span class="feature-icon">🌡️</span>
            <h3>Temperatura & Umidità</h3>
            <p>Monitoraggio continuo dei parametri ambientali del paziente con un box automatizzato</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">💊</span>
            <h3>Dispenser Automatico</h3>
            <p>Erogazione precisa dei farmaci secondo il piano terapeutico prescritto</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">📡</span>
            <h3>Real-time via MQTT</h3>
            <p>Aggiornamenti istantanei tramite WebSocket, senza ricaricare la pagina</p>
        </div>
    </div>

</section>
