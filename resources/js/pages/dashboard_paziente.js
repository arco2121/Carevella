import { echo } from "../echo.js";

const el  = id => document.getElementById(id);
const now = () => new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

const flash = id => {
    const node = el(id);
    if (!node) return;
    node.classList.remove('value-updated');
    void node.offsetWidth;
    node.classList.add('value-updated');
};

const setBar = (barId, value, min, max) => {
    const bar = el(barId);
    if (!bar) return;
    bar.style.width = Math.min(100, Math.max(0, ((value - min) / (max - min)) * 100)) + '%';
};

const addLog = msg => {
    const log = el('live-log');
    if (!log) return;
    log.querySelector('p')?.remove();
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerHTML = `<span class="log-time">${now()}</span>${msg}`;
    log.prepend(entry);
    while (log.children.length > 30) log.removeChild(log.lastChild);
};

const getCsrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ??
    document.querySelector('input[name="_token"]')?.value ?? '';

const updateConnectionStatus = () => {
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
};

setInterval(updateConnectionStatus, 1500);
updateConnectionStatus();

echo.channel('esp32').listen('MqttMessageReceived', ({ topic, message }) => {
    el('device-dot')?.classList.add('green', 'pulse');
    const devStatus = el('device-status');
    if (devStatus) devStatus.textContent = 'Online';

    let data = {};
    try { data = JSON.parse(message); } catch { data.raw = message; }

    if (data.temperatura !== undefined || data.temp !== undefined) {
        const t = parseFloat(data.temperatura ?? data.temp);
        const node = el('temperatura-value');
        if (node) { node.textContent = t.toFixed(1); flash('temperature-value'); }
        setBar('temp-bar', t, 0, 50);
        if (el('temp-time')) el('temp-time').textContent = now();
    }

    if (data.umidita !== undefined || data.hum !== undefined) {
        const h = parseFloat(data.umidita ?? data.hum);
        const node = el('umidita-value');
        if (node) { node.textContent = h.toFixed(1); flash('umidity-value'); }
        setBar('hum-bar', h, 0, 100);
        if (el('hum-time')) el('hum-time').textContent = now();
    }

    if (data.movimento !== undefined || data.pir !== undefined) {
        const m = data.movimento ?? data.pir;
        const node = el('motion-value');
        if (node) {
            node.textContent = (m === true || m === 1 || m === '1') ? 'SI' : 'NO';
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

const applyTakenState = (row, taken, takenAt) => {
    const check  = row.querySelector('.week-pill-check');
    const metaEl = row.querySelector('.week-pill-taken-at');
    const isTaken = (taken === true || taken === 1 || taken === '1' || taken === 'true');

    row.classList.toggle('is-taken', isTaken);
    if (check) check.textContent = isTaken ? '✓' : '';

    if (metaEl) {
        if (isTaken && takenAt) {
            const d = new Date(takenAt);
            metaEl.textContent = 'Preso alle ' + d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
        } else {
            metaEl.textContent = '';
        }
    }
};

const loadExistingLogs = async () => {
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

        if (!res.ok) return;

        const logs = await res.json();
        const logMap = {};
        logs.forEach(log => {
            const dateStr = (log.date || '').split('T')[0].split(' ')[0];
            logMap[`${log.prescription_id}_${dateStr}`] = log;
        });

        container.querySelectorAll('.week-pill-row').forEach(row => {
            const key = `${row.dataset.prescriptionId}_${row.dataset.date}`;
            if (logMap[key] !== undefined) {
                applyTakenState(row, logMap[key].taken, logMap[key].taken_at);
            }
        });
    } catch (e) {
        console.error('Errore caricamento log settimanali:', e);
    }
};

const handlePillToggle = async row => {
    if (row.classList.contains('is-loading')) return;

    const { prescriptionId, date } = row.dataset;
    if (!prescriptionId || !date) return;

    const csrfToken = getCsrfToken();
    if (!csrfToken) return;

    row.classList.add('is-loading');
    const check = row.querySelector('.week-pill-check');
    const previousContent = check?.textContent ?? '';
    if (check) check.innerHTML = '<span class="spinner"></span>';

    try {
        const res = await fetch(`/paziente/log/${prescriptionId}/${date}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (!res.ok) {
            if (check) check.textContent = previousContent;
            return;
        }

        const data = await res.json();
        applyTakenState(row, data.taken, data.taken_at);
    } catch {
        if (check) check.textContent = previousContent;
    } finally {
        row.classList.remove('is-loading');
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    const container = el('week-tracking-container');
    if (!container) return;

    container.addEventListener('click', e => {
        const row = e.target.closest('.week-pill-row');
        if (row) handlePillToggle(row);
    });

    await loadExistingLogs();
});
