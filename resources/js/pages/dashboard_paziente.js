/**
 * dashboard_paziente.js
 * Tracciamento settimanale assunzioni + sensori real-time MQTT.
 *
 * Fix applicati:
 * - CSRF token letto dal meta tag (aggiunto nel blade)
 * - Toggle robusto: aggiornamento UI basato sulla risposta del server, non su ottimismo
 * - Rimossa progress bar giornaliera
 * - loadExistingLogs usa cast esplicito (bool) per evitare "0"/"1" come stringa
 */

import { echo } from "../echo.js";

console.log(import.meta.env);

// ── Helpers ───────────────────────────────────────────────────────────────────

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

/**
 * Legge il CSRF token dal meta tag inserito nel blade.
 * Fallback sull'input hidden nel caso fosse presente in pagina.
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        ?? document.querySelector('input[name="_token"]')?.value
        ?? '';
}

// ── Connessione WebSocket ─────────────────────────────────────────────────────

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

// ── MQTT/WebSocket listener ───────────────────────────────────────────────────

echo.channel('esp32').listen('MqttMessageReceived', ({ topic, message }) => {
    const devDot    = el('device-dot');
    const devStatus = el('device-status');
    if (devDot)    devDot.classList.add('green', 'pulse');
    if (devStatus) devStatus.textContent = 'Online';

    let data = {};
    try { data = JSON.parse(message); } catch { data.raw = message; }

    if (data.temperatura !== undefined || data.temp !== undefined) {
        const t = parseFloat(data.temperatura ?? data.temp);
        const node = el('temperatura-value');
        if (node) { node.textContent = t.toFixed(1); flash('temperatura-value'); }
        setBar('temp-bar', t, 0, 50);
        if (el('temp-time')) el('temp-time').textContent = now();
    }
    if (data.umidita !== undefined || data.hum !== undefined) {
        const h = parseFloat(data.umidita ?? data.hum);
        const node = el('umidita-value');
        if (node) { node.textContent = h.toFixed(1); flash('umidita-value'); }
        setBar('hum-bar', h, 0, 100);
        if (el('hum-time')) el('hum-time').textContent = now();
    }
    if (data.motion !== undefined || data.pir !== undefined) {
        const m = data.motion ?? data.pir;
        const node = el('motion-value');
        if (node) {
            node.textContent = (m === true || m === 1 || m === '1') ? '🟢' : '⚪';
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

// ── Tracciamento settimanale ──────────────────────────────────────────────────

/**
 * Applica lo stato visivo "preso / non preso" a una riga.
 * @param {HTMLElement} row
 * @param {boolean|string|number} taken
 * @param {string|null} takenAt  - ISO 8601 string o null
 */
function applyTakenState(row, taken, takenAt) {
    const check  = row.querySelector('.week-pill-check');
    const metaEl = row.querySelector('.week-pill-taken-at');

    // FIX: Gestisce valori di ritorno dal JSON/DB sia come stringa che come numero/booleano
    const isTaken = (taken === true || taken === 1 || taken === '1' || taken === 'true');

    row.classList.toggle('is-taken', isTaken);

    if (check) {
        check.textContent = isTaken ? '✓' : '';
    }

    if (metaEl) {
        if (isTaken && takenAt) {
            const d = new Date(takenAt);
            metaEl.textContent = 'Preso alle ' + d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
        } else {
            metaEl.textContent = '';
        }
    }
}

/**
 * Carica i log esistenti dal server e aggiorna la UI al caricamento pagina.
 */
async function loadExistingLogs() {
    const container = el('week-tracking-container');
    if (!container) return;

    try {
        const res = await fetch('/api/paziente/me/log-settimanale', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });

        if (!res.ok) {
            console.warn('Log settimanale: risposta non ok', res.status);
            return;
        }

        const logs = await res.json();

        const logMap = {};
        logs.forEach(log => {
            // FIX: Taglia l'eventuale orario (es. "T12:00:00" o " 12:00:00") lasciando solo YYYY-MM-DD
            const logDateStr = (log.date || '').split('T')[0].split(' ')[0];
            const key = `${log.prescription_id}_${logDateStr}`;

            logMap[key] = log;
        });

        // Aggiorna ogni riga in base alla mappa
        container.querySelectorAll('.week-pill-row').forEach(row => {
            const prescId = row.dataset.prescriptionId;
            const date    = row.dataset.date;
            const key     = `${prescId}_${date}`;

            if (logMap[key] !== undefined) {
                applyTakenState(row, logMap[key].taken, logMap[key].taken_at);
            }
        });

    } catch (e) {
        console.error('Errore caricamento log settimanali:', e);
    }
}

/**
 * Gestisce il click su una riga-pillola: invia il toggle al server
 * e aggiorna la UI SOLO con la risposta del server (niente ottimismo).
 * @param {HTMLElement} row
 */
async function handlePillToggle(row) {
    if (row.classList.contains('is-loading')) return;

    const prescriptionId = row.dataset.prescriptionId;
    const date           = row.dataset.date;

    if (!prescriptionId || !date) return;

    const csrfToken = getCsrfToken();
    if (!csrfToken) {
        console.error('CSRF token non trovato! Verifica che il meta tag csrf-token sia presente nel blade.');
        return;
    }

    // Mostra stato di caricamento
    row.classList.add('is-loading');
    const check = row.querySelector('.week-pill-check');
    const previousContent = check?.textContent ?? '';
    if (check) {
        check.innerHTML = '<span class="spinner"></span>';
    }

    try {
        const res = await fetch(`/paziente/log/${prescriptionId}/${date}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     csrfToken,
            },
        });

        if (!res.ok) {
            // Ripristina stato precedente in caso di errore
            if (check) check.textContent = previousContent;
            const errData = await res.json().catch(() => ({}));
            console.error('Errore toggle:', res.status, errData);
            return;
        }

        const data = await res.json();
        // Aggiorna UI con i dati confermati dal server
        applyTakenState(row, data.taken, data.taken_at);

    } catch (e) {
        // Errore di rete: ripristina
        if (check) check.textContent = previousContent;
        console.error('Errore di rete nel toggle:', e);
    } finally {
        row.classList.remove('is-loading');
    }
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', async () => {
    const container = el('week-tracking-container');
    if (!container) return;

    // Event delegation: un solo listener per tutti i .week-pill-row
    container.addEventListener('click', (e) => {
        const row = e.target.closest('.week-pill-row');
        if (row) handlePillToggle(row);
    });

    // Carica i log salvati in DB per questa settimana
    await loadExistingLogs();
});
