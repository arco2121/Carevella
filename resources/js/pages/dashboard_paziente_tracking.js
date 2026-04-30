/**
 * dashboard_paziente_tracking.js
 * Gestisce il tracciamento settimanale assunzioni farmaci.
 * Carica i log esistenti e permette al paziente di segnare "preso".
 */

import { echo } from "../echo.js";


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


echo.channel('esp32').listen('MqttMessageReceived', ({ topic, message }) => {
    const devDot    = el('device-dot');
    const devStatus = el('device-status');
    if (devDot)    devDot.classList.add('green', 'pulse');
    if (devStatus) devStatus.textContent = 'Online';

    let data = {};
    try { data = JSON.parse(message); } catch { data.raw = message; }

    if (data.temperatura !== undefined || data.temp !== undefined) {
        const t = parseFloat(data.temperatura ?? data.temp);
        const tempEl = el('temperatura-value');
        if (tempEl) { tempEl.textContent = t.toFixed(1); flash('temperatura-value'); }
        setBar('temp-bar', t, 0, 50);
        if (el('temp-time')) el('temp-time').textContent = now();
    }
    if (data.umidita !== undefined || data.hum !== undefined) {
        const h = parseFloat(data.umidita ?? data.hum);
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


/**
 * Recupera la data ISO (YYYY-MM-DD) per un giorno della settimana (0=lun, 6=dom)
 * relativo alla settimana corrente.
 */
function getWeekDate(dayIndex) {
    const today = new Date();
    // getDay(): 0=dom, 1=lun ... 6=sab
    const jsDay = today.getDay(); // 0-6
    // convertiamo in indice lun=0...dom=6
    const currentDayIndex = (jsDay + 6) % 7;
    const diff = dayIndex - currentDayIndex;
    const target = new Date(today);
    target.setDate(today.getDate() + diff);
    return target.toISOString().slice(0, 10);
}

function getTodayIndex() {
    return (new Date().getDay() + 6) % 7;
}

/**
 * Aggiorna la progress bar di un giorno card.
 */
function updateDayProgress(dayCard) {
    const rows  = dayCard.querySelectorAll('.week-pill-row');
    const taken = dayCard.querySelectorAll('.week-pill-row.is-taken').length;
    const total = rows.length;
    const fill  = dayCard.querySelector('.week-progress-fill');
    if (fill && total > 0) {
        fill.style.width = Math.round((taken / total) * 100) + '%';
    }
}

/**
 * Carica i log esistenti dall'API e aggiorna lo stato della UI.
 */
async function loadExistingLogs() {
    const container = el('week-tracking-container');
    if (!container) return;

    try {
        const res  = await fetch('/api/paziente/me/log-settimanale', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const logs = await res.json();

        // Mappa: prescription_id + date → log
        const logMap = {};
        logs.forEach(log => {
            logMap[`${log.prescription_id}_${log.date}`] = log;
        });

        // Aggiorna ogni riga
        container.querySelectorAll('.week-pill-row').forEach(row => {
            const prescId = row.dataset.prescriptionId;
            const date    = row.dataset.date;
            const key     = `${prescId}_${date}`;
            if (logMap[key]) {
                applyTakenState(row, logMap[key].taken, logMap[key].taken_at);
            }
        });

        // Aggiorna progress bar di ogni giorno
        container.querySelectorAll('.week-day-card').forEach(updateDayProgress);

    } catch (e) {
        console.error('Errore caricamento log:', e);
    }
}

/**
 * Applica lo stato visivo "preso / non preso" a una riga.
 */
function applyTakenState(row, taken, takenAt) {
    const check   = row.querySelector('.week-pill-check');
    const metaEl  = row.querySelector('.week-pill-taken-at');

    row.classList.toggle('is-taken', taken);

    if (check) {
        check.innerHTML = taken ? '✓' : '';
    }

    if (metaEl) {
        if (taken && takenAt) {
            const d = new Date(takenAt);
            metaEl.textContent = 'Preso alle ' + d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
        } else {
            metaEl.textContent = '';
        }
    }
}

/**
 * Gestisce il click su una riga-pillola (toggle assunzione).
 */
async function handlePillToggle(row) {
    if (row.classList.contains('is-loading')) return;

    const prescriptionId = row.dataset.prescriptionId;
    const date           = row.dataset.date;
    const csrfToken      = document.querySelector('meta[name="csrf-token"]')?.content
        ?? document.querySelector('input[name="_token"]')?.value
        ?? '';

    // Mostra spinner
    row.classList.add('is-loading');
    const check = row.querySelector('.week-pill-check');
    const originalContent = check?.innerHTML ?? '';
    if (check) check.innerHTML = '<span class="spinner"></span>';

    try {
        const res = await fetch(`/paziente/log/${prescriptionId}/${date}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     csrfToken,
            },
        });

        if (!res.ok)
            throw new Error(`HTTP ${res.status}`);

        const data = await res.json();
        applyTakenState(row, data.taken, data.taken_at);

        // Aggiorna progress bar del giorno padre
        const dayCard = row.closest('.week-day-card');
        if (dayCard) updateDayProgress(dayCard);

    } catch (e) {
        console.error('Errore toggle assunzione:', e);
        if (check) check.innerHTML = originalContent;
    } finally {
        row.classList.remove('is-loading');
    }
}


document.addEventListener('DOMContentLoaded', () => {
    const container = el('week-tracking-container');
    if (!container) return;
    container.addEventListener('click', (e) => {
        const row = e.target.closest('.week-pill-row');
        if (row) handlePillToggle(row);
    });
    loadExistingLogs();
});
