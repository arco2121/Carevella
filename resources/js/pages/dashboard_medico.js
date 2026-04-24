import { echo } from "../echo.js";

// ── helpers ──────────────────────────────────────────────────────────────────

const el  = (id) => document.getElementById(id);
const now = () => new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

function flash(id) {
    const node = el(id);
    if (!node) return;
    node.classList.remove('value-updated');
    void node.offsetWidth;
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
    const placeholder = log.querySelector('p');
    if (placeholder) placeholder.remove();

    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerHTML = `<span class="log-time">${now()}</span>${msg}`;
    log.prepend(entry);
    while (log.children.length > 30) log.removeChild(log.lastChild);
}

// ── connessione ───────────────────────────────────────────────────────────────

function updateConnectionStatus() {
    const dot   = el('conn-dot');
    const label = el('status');
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

// ── selezione paziente ────────────────────────────────────────────────────────

const select = el('paziente-select');

function loadPrescriptions(pazienteId) {
    const content = el('prescription-content');
    const editBtn = el('edit-prescription-btn');

    if (editBtn) editBtn.href = `/dashboard/prescrizioni?paziente=${pazienteId}`;

    if (!content) return;
    content.innerHTML = '<p style="opacity:0.4; font-size:0.9rem;">Caricamento...</p>';

    fetch(`/api/paziente/${pazienteId}/prescrizioni`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                content.innerHTML = '<p style="opacity:0.5;">Nessuna prescrizione per questo paziente.</p>';
                return;
            }

            // raggruppa per giorno
            const byDay = {};
            data.forEach(p => {
                if (!byDay[p.day]) byDay[p.day] = [];
                byDay[p.day].push(p);
            });

            const giorni = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
            let html = '<div class="prescription-grid">';
            for (const [day, items] of Object.entries(byDay)) {
                html += `<div class="day-block column gap_10">
                <div class="day-label font_bold">${giorni[day] ?? 'G' + day}</div>`;
                items.sort((a,b) => a.step - b.step).forEach(item => {
                    html += `<div class="pill-item row vertical_center gap_10">
                    <span class="pill-dot"></span>
                    <span>${item.medicine_name}</span>
                    <span class="pill-time">${item.scheduled_time.slice(0,5)}</span>
                </div>`;
                });
                html += '</div>';
            }
            html += '</div>';
            content.innerHTML = html;
        })
        .catch(() => {
            content.innerHTML = '<p style="color:#c0392b; font-size:0.9rem;">Errore nel caricamento prescrizioni.</p>';
        });
}

// reset sensori tra un paziente e l'altro
function resetSensors() {
    ['temperatura-value', 'umidita-value', 'motion-value'].forEach(id => {
        const node = el(id);
        if (node) node.textContent = '--';
    });
    ['temp-bar', 'hum-bar'].forEach(id => {
        const bar = el(id);
        if (bar) bar.style.width = '0%';
    });
    ['temp-time', 'hum-time', 'pir-time'].forEach(id => {
        const node = el(id);
        if (node) node.textContent = '--';
    });
    const mac = el('device-mac');
    if (mac) mac.textContent = '--:--:--:--:--:--';

    const devDot = el('device-dot');
    const devStatus = el('device-status');
    if (devDot) { devDot.classList.remove('green', 'pulse'); }
    if (devStatus) devStatus.textContent = 'In attesa di dati...';

    const log = el('live-log');
    if (log) log.innerHTML = '<p style="opacity:0.4; font-size:0.9rem;">Cambio paziente - in attesa di nuovi dati...</p>';
}

if (select) {
    // carica prescrizioni all'avvio per il primo paziente
    loadPrescriptions(select.value);

    select.addEventListener('change', () => {
        resetSensors();
        loadPrescriptions(select.value);
    });
}

// ── MQTT listener ─────────────────────────────────────────────────────────────

echo.channel('esp32').listen('MqttMessageReceived', ({ topic, message }) => {
    const devDot    = el('device-dot');
    const devStatus = el('device-status');
    if (devDot)    devDot.classList.add('green', 'pulse');
    if (devStatus) devStatus.textContent = 'Online';

    let data = {};
    try { data = JSON.parse(message); } catch { data.raw = message; }

    if (data.temperature !== undefined || data.temp !== undefined) {
        const t = parseFloat(data.temperature ?? data.temp);
        const tempEl = el('temperatura-value');
        if (tempEl) { tempEl.textContent = t.toFixed(1); flash('temperatura-value'); }
        setBar('temp-bar', t, 0, 50);
        if (el('temp-time')) el('temp-time').textContent = now();
    }

    if (data.humidity !== undefined || data.hum !== undefined) {
        const h = parseFloat(data.humidity ?? data.hum);
        const humEl = el('umidita-value');
        if (humEl) { humEl.textContent = h.toFixed(1); flash('umidita-value'); }
        setBar('hum-bar', h, 0, 100);
        if (el('hum-time')) el('hum-time').textContent = now();
    }

    if (data.motion !== undefined || data.pir !== undefined) {
        const m = data.motion ?? data.pir;
        const motionEl = el('motion-value');
        if (motionEl) {
            motionEl.textContent = (m === true || m === 1 || m === '1') ? '🟢' : '⚪';
            flash('motion-value');
        }
        if (el('pir-time')) el('pir-time').textContent = now();
    }

    if (data.mac) {
        const macEl = el('device-mac');
        if (macEl) macEl.textContent = data.mac;
    }

    addLog(`${topic} → ${message.length > 80 ? message.slice(0, 80) + '…' : message}`);
});
