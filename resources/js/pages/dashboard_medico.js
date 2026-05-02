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

const select = el('paziente-select');

const loadPrescriptions = pazienteId => {
    const content = el('prescription-content');
    const editBtn = el('edit-prescription-btn');

    if (editBtn) editBtn.href = `/dashboard/prescrizioni?paziente=${pazienteId}`;
    if (!content) return;

    content.innerHTML = '<p class="placeholder-text">Caricamento...</p>';

    fetch(`/api/paziente/${pazienteId}/prescrizioni`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                content.innerHTML = '<p class="presc-none">Nessuna prescrizione per questo paziente.</p>';
                return;
            }

            const byDay = {};
            data.forEach(p => { (byDay[p.day] ??= []).push(p); });

            const giorni = ['', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
            let html = '<div class="prescription-grid">';

            for (const [day, items] of Object.entries(byDay)) {
                html += `<div class="day-block column gap_10"><div class="day-label font_bold">${giorni[day] ?? 'G' + day}</div>`;
                items.sort((a, b) => a.step - b.step).forEach(item => {
                    html += `<div class="pill-item row vertical_center gap_10">
                        <span class="pill-dot"></span>
                        <span>${item.medicine_name}</span>
                        <kbd class="pill-code">${item.medicine_code}</kbd>
                        <span class="pill-time">x${item.amount}</span>
                        <span class="pill-time">${item.scheduled_time.slice(0, 5)}</span>
                    </div>`;
                });
                html += '</div>';
            }

            content.innerHTML = html + '</div>';
        })
        .catch(() => {
            content.innerHTML = '<p class="presc-error">Errore nel caricamento prescrizioni.</p>';
        });
};

const resetSensors = () => {
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

    el('device-dot')?.classList.remove('green', 'pulse');
    const devStatus = el('device-status');
    if (devStatus) devStatus.textContent = 'In attesa di dati...';

    const log = el('live-log');
    if (log) log.innerHTML = '<p class="placeholder-text">Cambio paziente - in attesa di nuovi dati...</p>';
};

if (select) {
    loadPrescriptions(select.value);
    select.addEventListener('change', () => {
        resetSensors();
        loadPrescriptions(select.value);
    });
}

echo.channel('esp32').listen('MqttMessageReceived', ({ topic, message }) => {
    el('device-dot')?.classList.add('green', 'pulse');
    const devStatus = el('device-status');
    if (devStatus) devStatus.textContent = 'Online';

    let data = {};
    try { data = JSON.parse(message); } catch { data.raw = message; }

    if (data.temperature !== undefined || data.temp !== undefined) {
        const t = parseFloat(data.temperature ?? data.temp);
        const node = el('temperatura-value');
        if (node) { node.textContent = t.toFixed(1); flash('temperatura-value'); }
        setBar('temp-bar', t, 0, 50);
        if (el('temp-time')) el('temp-time').textContent = now();
    }

    if (data.humidity !== undefined || data.hum !== undefined) {
        const h = parseFloat(data.humidity ?? data.hum);
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
