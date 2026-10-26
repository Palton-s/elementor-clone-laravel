import state from './state.js';
import { apiFetch, toastError } from './utils.js';

export function showPageSettings() {
    state.selectedId = null;
    document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    document.getElementById('settings-empty').style.display = 'none';
    document.getElementById('settings-form').classList.remove('active');
    document.getElementById('settings-form').style.display = '';
    document.getElementById('page-settings-form').classList.add('active');
    document.getElementById('editor-tabs').style.display = '';
    document.getElementById('responsive-tabs').style.display = '';
    renderPageSettings();
}

export function hidePageSettings() {
    document.getElementById('page-settings-form').classList.remove('active');
    document.getElementById('settings-empty').style.display = '';
}

function renderPageSettings() {
    const body = document.getElementById('page-settings-body');
    body.innerHTML = '';
    const currentPage = window._pageData || {};
    const s = currentPage.settings || {};
    const controls = [
        { key: 'container_width', label: 'Largura do Container', type: 'text', default: '1140px' },
        { key: 'page_background', label: 'Fundo da Página', type: 'color', default: '#ffffff' },
        { key: 'content_padding', label: 'Espaçamento Interno', type: 'text', default: '0px' },
        { key: 'custom_css', label: 'CSS Personalizado', type: 'textarea', default: '' },
    ];
    controls.forEach(ctrl => {
        const val = s[ctrl.key] !== undefined ? s[ctrl.key] : ctrl.default;
        const group = document.createElement('div');
        group.className = 'pb-settings-section';
        group.innerHTML = `<div class="pb-control"><label>${ctrl.label}</label></div>`;
        const inputWrap = group.querySelector('.pb-control');
        if (ctrl.type === 'color') {
            const container = document.createElement('div');
            container.style.cssText = 'display:flex;gap:.5rem;align-items:center';
            const inp = document.createElement('input'); inp.type = 'color'; inp.value = val;
            const txt = document.createElement('input'); txt.type = 'text'; txt.value = val; txt.style.cssText = 'flex:1';
            const update = (v) => { inp.value = v; txt.value = v; updatePageSetting(ctrl.key, v); };
            inp.oninput = (e) => update(e.target.value);
            txt.oninput = (e) => { if (/^#[0-9a-f]{3,8}$/i.test(e.target.value)) update(e.target.value); };
            container.appendChild(inp); container.appendChild(txt);
            inputWrap.appendChild(container);
        } else if (ctrl.type === 'textarea') {
            const ta = document.createElement('textarea');
            ta.value = val || '';
            ta.style.cssText = 'width:100%;padding:.45rem .6rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:.8rem;min-height:100px;font-family:monospace';
            ta.onchange = (e) => updatePageSetting(ctrl.key, e.target.value);
            inputWrap.appendChild(ta);
        } else {
            const inp = document.createElement('input');
            inp.type = 'text'; inp.value = val || '';
            inp.onchange = (e) => updatePageSetting(ctrl.key, e.target.value);
            inputWrap.appendChild(inp);
        }
        body.appendChild(group);
    });
}

function updatePageSetting(key, value) {
    state.dirty = true;
    const settings = {};
    settings[key] = value;
    apiFetch(`/page-builder/pages/${state.pageId}/layout`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ settings }),
    }).catch(() => toastError('Falha ao atualizar configuração da página'));
}
