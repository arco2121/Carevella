<section class="hero-section column vertical_center text_center min_height around padding_orizontal_20">

    <div class="column gap_20 vertical_center">
        <div class="pill-badge">IOT Project</div>
        <h3 class="hero-title">Monitora.<br>Cura.<br>Proteggi.</h3>
        <p class="hero-sub">Piattaforma intelligente per il monitoraggio dei pazienti in tempo reale tramite sensori IoT e dispensatore automatico di farmaci.</p>

        <div class="row gap_20 vertical_center" style="flex-wrap: wrap; justify-content: center;">
            @auth
                <a href="/dashboard" class="btn primary">Vai alla Dashboard →</a>
            @else
                <a href="/login" class="btn primary">Accedi al sistema →</a>
                <a href="/register" class="btn secondary">Crea account</a>
            @endauth
        </div>
    </div>

    <div class="features-grid gap_20 margin_vertical_20">
        <div class="feature-card" style="animation: fade-in 0.5s 0.1s ease-out both;">
            <div class="feature-icon">🌡️</div>
            <h3>Temperatura & Umidità</h3>
            <p>Monitoraggio continuo dei parametri ambientali del paziente via ESP32</p>
        </div>
        <div class="feature-card" style="animation: fade-in 0.5s 0.2s ease-out both;">
            <div class="feature-icon">💊</div>
            <h3>Dispenser Automatico</h3>
            <p>Erogazione precisa dei farmaci secondo il piano terapeutico prescritto</p>
        </div>
        <div class="feature-card" style="animation: fade-in 0.5s 0.3s ease-out both;">
            <div class="feature-icon">📡</div>
            <h3>Real-time via MQTT</h3>
            <p>Aggiornamenti istantanei tramite WebSocket, senza ricaricare la pagina</p>
        </div>
        <!--div class="feature-card" style="animation: fade-in 0.5s 0.4s ease-out both;">
            <div class="feature-icon">👨‍⚕️</div>
            <h3>Tre ruoli</h3>
            <p>Medico, paziente e familiare: ognuno vede ciò che serve a lui</p>
        </div-->
    </div>

</section>
