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
        const node = el('temperature-value');
        if (node) { node.textContent = t.toFixed(1); flash('temperature-value'); }
        setBar('temp-bar', t, 0, 50);
        if (el('temp-time')) el('temp-time').textContent = now();
    }

    if (data.umidita !== undefined || data.hum !== undefined) {
        const h = parseFloat(data.umidita ?? data.hum);
        const node = el('umidity-value');
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

    el('patient-online-dot')?.classList.add('green');
    const posEl = el('patient-online-status');
    if (posEl) posEl.textContent = 'Online';

    addLog(`${topic} → ${message.length > 80 ? message.slice(0, 80) + '…' : message}`);
});
