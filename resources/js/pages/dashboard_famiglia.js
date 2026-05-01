import { echo } from "../echo.js";

// ── helpers ──────────────────────────────────────────────────────────────────

const el  = (id) => document.getElementById(id);
const now = () => new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

function flash(id) {
    const node = el(id);
    if (!node) return;
    node.classList.remove('value-updated');
    void node.offsetWidth; // reflow
    node.classList.add('value-updated');
}

function setBar(barId, value, min, max) {
    const bar = el(barId);
    if (!bar) return;
    const pct = Math.min(100, Math.max(0, ((value - min) / (max - min)) * 100));
    bar.style.width = pct + '%';
}

function addLog(msg) {
    const log = el('live-log');
    if (!log) return;

    // rimuovi placeholder
    const placeholder = log.querySelector('p');
    if (placeholder) placeholder.remove();

    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerHTML = `<span class="log-time">${now()}</span>${msg}`;
    log.prepend(entry);

    // tieni max 30 voci
    while (log.children.length > 30) log.removeChild(log.lastChild);
}

// ── connessione ───────────────────────────────────────────────────────────────

function updateConnectionStatus() {
    const dot    = el('conn-dot');
    const label  = el('status');
    if (!dot || !label) return;

    const state = echo.connector?.pusher?.connection?.state ?? 'unknown';
    dot.classList.remove('green', 'red', 'orange', 'pulse');

    if (state === 'connected') {
        dot.classList.add('green', 'pulse');
        label.textContent = 'Connesso';
    } else if (state === 'connecting') {
        dot.classList.add('orange');
        label.textContent = 'Connessione...';
    } else {
        dot.classList.add('red');
        label.textContent = 'Disconnesso';
    }
}

setInterval(updateConnectionStatus, 1500);
updateConnectionStatus();

// ── MQTT listener ─────────────────────────────────────────────────────────────

echo.channel('esp32').listen('MqttMessageReceived', ({ topic, message }) => {

    // aggiorna device status
    const devDot    = el('device-dot');
    const devStatus = el('device-status');
    if (devDot)    devDot.classList.add('green', 'pulse');
    if (devStatus) devStatus.textContent = 'Online';

    // parse JSON se possibile
    let data = {};
    try { data = JSON.parse(message); } catch { data.raw = message; }

    // temperatura
    if (data.temperatura !== undefined || data.temp !== undefined) {
        const t = parseFloat(data.temperatura ?? data.temp);
        const tempEl = el('temperatura-value');
        if (tempEl) { tempEl.textContent = t.toFixed(1); flash('temperatura-value'); }
        setBar('temp-bar', t, 0, 50);
        if (el('temp-time')) el('temp-time').textContent = now();
    }

    // umidità
    if (data.umidita !== undefined || data.hum !== undefined) {
        const h = parseFloat(data.umidita ?? data.hum);
        const humEl = el('umidita-value');
        if (humEl) { humEl.textContent = h.toFixed(1); flash('umidita-value'); }
        setBar('hum-bar', h, 0, 100);
        if (el('hum-time')) el('hum-time').textContent = now();
    }

    // PIR
    if (data.motion !== undefined || data.pir !== undefined) {
        const m = data.motion ?? data.pir;
        const motionEl = el('motion-value');
        if (motionEl) {
            motionEl.textContent = (m === true || m === 1 || m === '1') ? '🟢' : '⚪';
            flash('motion-value');
        }
        if (el('pir-time')) el('pir-time').textContent = now();
    }

    // MAC address
    if (data.mac) {
        const macEl = el('device-mac');
        if (macEl) macEl.textContent = data.mac;
    }

    // paziente-online dot (solo nella vista famiglia)
    const polEl = el('patient-online-dot');
    const posEl = el('patient-online-status');
    if (polEl) { polEl.classList.add('green'); }
    if (posEl) { posEl.textContent = 'Online'; }

    addLog(`${topic} → ${message.length > 80 ? message.slice(0, 80) + '…' : message}`);
});
