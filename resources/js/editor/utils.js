import { icon, widgetIcon } from './icons.js';

export function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

export function apiFetch(url, options = {}) {
    const headers = Object.assign({ 'Accept': 'application/json' }, options.headers || {});
    return fetch(url, Object.assign({}, options, { headers, credentials: 'same-origin' })).then(r => {
        if (r.redirected) {
            throw new Error('Sessão expirada. Recarregue a página.');
        }
        if (!r.ok) {
            return r.json().catch(() => r.text()).then(body => {
                // Validation errors come as "errors", controller errors as "message" or "error"
                const msg = typeof body === 'string' ? `HTTP ${r.status}` : (body.message || body.error || (body.errors ? Object.values(body.errors).flat().join(', ') : `HTTP ${r.status}`));
                throw new Error(msg);
            });
        }
        return r.json();
    });
}

export function showToast(msg, type) {
    const existing = document.querySelectorAll('.pb-toast');
    existing.forEach(t => { t.classList.add('pb-toast-out'); setTimeout(() => t.remove(), 300); });
    const t = document.createElement('div');
    t.className = 'pb-toast';
    if (type === 'error') t.style.borderColor = 'rgba(239,68,68,.4)';
    if (type === 'success') t.style.borderColor = 'rgba(34,197,94,.4)';
    const prefix = type === 'error' ? icon('alert', 15) : type === 'success' ? icon('check', 15) : '';
    t.innerHTML = prefix;
    t.appendChild(document.createTextNode(msg));
    document.body.appendChild(t);
    setTimeout(() => { t.classList.add('pb-toast-out'); setTimeout(() => t.remove(), 300); }, 2500);
}

export function toastError(msg) {
    showToast('\u26A0\uFE0F ' + msg, 'error');
}

export function toastSuccess(msg) {
    showToast('\u2705 ' + msg, 'success');
}

export function structureIcon(type) {
    return widgetIcon(type, 14);
}
