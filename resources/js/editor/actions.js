import state from './state.js';
import { escHtml, apiFetch, showToast, toastSuccess, toastError } from './utils.js';
import { icon } from './icons.js';

export function save(silent) {
    if (state.saving) return;
    state.saving = true;
    let overlay = null;
    if (!silent) {
        overlay = document.createElement('div');
        overlay.className = 'saving-overlay';
        overlay.innerHTML = '<div class="saving-card"><div class="spinner"></div><span class="saving-text">Salvando...</span></div>';
        document.body.appendChild(overlay);
    }
    apiFetch(`/page-builder/pages/${state.pageId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ status: 'draft' }),
    })
    .then(() => {
        state.saving = false;
        state.dirty = false;
        if (overlay) overlay.remove();
        if (!silent) toastSuccess('Página salva!');
    })
    .catch(() => { state.saving = false; if (overlay) overlay.remove(); toastError('Falha ao salvar'); });
}

export function publish() {
    if (!confirm('Publicar esta página?')) return;
    apiFetch(`/page-builder/pages/${state.pageId}/publish`, { method: 'POST', headers: { 'X-CSRF-TOKEN': state.csrf } })
        .then(() => { toastSuccess('Página publicada!'); setTimeout(() => location.reload(), 500); })
        .catch(() => toastError('Falha ao publicar página'));
}

export function autoSave() {
    setInterval(() => {
        if (state.dirty) save(true);
    }, 60000);
}

export function copyStyles() {
    if (!state.selectedId) {
        showToast('Selecione um elemento para copiar estilos', 'error');
        return;
    }
    const styles = state.cachedStyles || {};
    if (Object.keys(styles).length === 0) {
        showToast('Nenhum estilo para copiar', 'error');
        return;
    }
    state._styleClipboard = JSON.parse(JSON.stringify(styles));
    showToast('Estilos copiados! Selecione outro elemento e use Ctrl+Shift+V para colar.', 'success');
}

export function pasteStyles() {
    if (!state.selectedId) {
        showToast('Selecione um elemento para colar estilos', 'error');
        return;
    }
    if (!state._styleClipboard || Object.keys(state._styleClipboard).length === 0) {
        showToast('Nenhum estilo copiado. Use Ctrl+Shift+C primeiro.', 'error');
        return;
    }
    const elId = state.selectedId;
    const styles = state._styleClipboard;
    let applied = 0;
    const applyNext = (keys) => {
        if (keys.length === 0) {
            if (applied > 0) {
                showToast(`${applied} estilo(s) aplicado(s)!`, 'success');
                state.loadElements();
                setTimeout(() => import('./elements.js').then(m => m.selectElement(elId)), 200);
            }
            return;
        }
        const [key, ...rest] = keys;
        const val = styles[key];
        if (val !== undefined && val !== '' && val !== null) {
            applied++;
            apiFetch(`/page-builder/elements/${elId}/styles`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
                body: JSON.stringify({ styles: { [key]: val } }),
            }).then(() => applyNext(rest)).catch(() => applyNext(rest));
        } else {
            applyNext(rest);
        }
    };
    applyNext(Object.keys(styles));
}

export function saveAsTemplate() {
    const name = prompt('Nome do template:', document.title.replace('Editando: ', '').replace(' - PageBuilder', ''));
    if (!name) return;
    apiFetch('/page-builder/pages/' + state.pageId + '/export')
        .then(r => r.blob())
        .then(blob => {
            const reader = new FileReader();
            reader.onload = () => {
                try {
                    const data = JSON.parse(reader.result);
                    data.template_name = name;
                    const blob2 = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob2);
                    const a = document.createElement('a');
                    a.href = url; a.download = name.toLowerCase().replace(/\s+/g, '-') + '.json';
                    document.body.appendChild(a); a.click(); a.remove();
                    URL.revokeObjectURL(url);
                    showToast('Template exportado! Use Importar HTML para reutilizar.', 'success');
                } catch(e) { showToast('Erro ao criar template', 'error'); }
            };
            reader.readAsText(blob);
        })
        .catch(() => showToast('Erro ao exportar template', 'error'));
}

export function copyHtml() {
    fetch('/page-builder/pages/' + state.pageId + '/render?format=inner', { headers: { 'Accept': 'text/html' } })
        .then(r => r.text())
        .then(html => {
            navigator.clipboard.writeText(html).then(() => showToast('HTML copiado!')).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = html;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
                showToast('HTML copiado!');
            });
        })
        .catch(() => toastError('Falha ao copiar HTML'));
}

export function loadTemplates() {
    apiFetch('/page-builder/templates')
        .then(data => {
            const container = document.getElementById('layout-templates');
            container.innerHTML = '<div class="pb-widget-group-title" style="margin-bottom:.75rem">Escolha um modelo de layout</div>';
            for (const [key, tmpl] of Object.entries(data.templates)) {
                const previews = { blank: 'square-dashed', landing: 'layout-template', about: 'user', contact: 'mail', showcase: 'palette' };
                const card = document.createElement('div');
                card.className = 'pb-layout-card';
                card.innerHTML = `<div class="pb-layout-card-preview">${icon(previews[key] || 'square-dashed', 32)}</div><div class="pb-layout-card-info"><h4>${tmpl.name}</h4><p>${tmpl.description}</p></div><button class="pb-apply-btn" data-template="${key}">Aplicar Modelo</button>`;
                card.querySelector('.pb-apply-btn').onclick = (e) => {
                    e.stopPropagation();
                    applyTemplate(key, e.target);
                };
                container.appendChild(card);
            }
        })
        .catch(() => toastError('Falha ao carregar modelos'));
}

function applyTemplate(key, btn) {
    if (!confirm('Aplicar este modelo? Ele vai substituir todo o conteúdo atual da página.')) return;
    btn.disabled = true; btn.textContent = 'Aplicando...';
    apiFetch(`/page-builder/pages/${state.pageId}/apply-template`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ template: key }),
    })
    .then(() => {
        showToast('Modelo aplicado!');
        state.loadElements();
        btn.disabled = false; btn.textContent = 'Aplicar Modelo';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Aplicar Modelo'; });
}

export function saveAsGlobalWidget() {
    const id = state.selectedId;
    if (!id) { showToast('Selecione um elemento primeiro', 'error'); return; }
    const name = prompt('Nome do Global Widget:');
    if (!name) return;
    return apiFetch('/page-builder/elements/' + id)
        .then(data => {
            const el = data.element;
            return apiFetch('/page-builder/global-widgets', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
                body: JSON.stringify({
                    title: name,
                    type: el.type,
                    settings: el.settings || {},
                    content: el.content || {},
                    styles: el.styles || {},
                }),
            });
        })
        .then(data => {
            showToast('Global widget "' + data.global_widget.title + '" criado!', 'success');
        })
        .catch(err => { console.error(err); showToast('Erro ao criar global widget', 'error'); });
}

export function insertGlobalWidget() {
    // apiFetch already resolves to the parsed JSON body
    return apiFetch('/page-builder/global-widgets')
        .then(data => {
            const widgets = data.global_widgets || [];
            if (widgets.length === 0) {
                showToast('Nenhum global widget disponível', 'error');
                return;
            }
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Inserir Global Widget</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">${icon('x', 18)}</button>
                    </div>
                    <div class="modal-body" style="max-height:400px;overflow-y:auto">
                        ${widgets.map(w => `
                            <div class="gw-item" data-id="${Number(w.id)}" style="padding:.75rem;border:1px solid var(--pb-border);border-radius:8px;margin-bottom:.5rem;cursor:pointer;transition:background .15s;display:flex;align-items:center;gap:.75rem" onmouseenter="this.style.background='var(--pb-surface2)'" onmouseleave="this.style.background=''">
                                <span style="display:flex;opacity:.7">${icon('component', 18)}</span>
                                <div>
                                    <div style="font-weight:600;font-size:.85rem;color:var(--pb-text)">${escHtml(w.title)}</div>
                                    <div style="font-size:.72rem;color:var(--pb-text2)">${escHtml(w.type)} · ${w.status === 'published' ? 'publicado' : 'rascunho'}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            // Data is looked up by id instead of being serialized into an onclick attribute
            // (JSON quotes used to close the attribute, so the click never worked)
            overlay.querySelectorAll('.gw-item').forEach(item => {
                item.addEventListener('click', () => {
                    const w = widgets.find(x => Number(x.id) === Number(item.dataset.id));
                    overlay.remove();
                    if (w) _insertGlobalWidget(w.id, w.type, { ...(w.settings || {}) }, w.content || {}, w.styles || {});
                });
            });
            document.body.appendChild(overlay);
        })
        .catch(err => { console.error(err); showToast('Erro ao carregar global widgets', 'error'); });
}

export function _insertGlobalWidget(id, type, settings, content, styles) {
    const section = document.querySelector('.pb-el.selected') || document.querySelector('.pb-canvas .pb-el:first-child');
    let parentId = null;
    if (section && (section.dataset.elType === 'section' || section.dataset.elType === 'column')) {
        parentId = parseInt(section.dataset.elId);
    }
    settings._global_widget_id = id;
    return apiFetch('/page-builder/pages/' + state.pageId + '/elements', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({
            type: type,
            parent_id: parentId,
            settings: settings,
            content: content,
            styles: styles,
            name: 'Global Widget',
        }),
    })
    .then(() => {
        state.loadElements();
        showToast('Global widget inserido!', 'success');
    })
    .catch(() => showToast('Erro ao inserir global widget', 'error'));
}

export function syncGlobalWidgets() {
    const section = document.querySelector('.pb-canvas');
    if (!section) return;
    const elements = section.querySelectorAll('.pb-el[data-el-id]');
    const ids = new Set();
    elements.forEach(el => {
        const elId = parseInt(el.dataset.elId);
        if (elId && !isNaN(elId)) ids.add(elId);
    });
    const promises = [];
    ids.forEach(elId => {
        promises.push(
            apiFetch('/page-builder/elements/' + elId)
                .then(data => {
                    const element = data.element;
                    if (element && element.settings && element.settings._global_widget_id) {
                        return apiFetch('/page-builder/global-widgets/' + element.settings._global_widget_id);
                    }
                    return null;
                })
                .then(gwData => {
                    if (gwData && gwData.global_widget) {
                        const gw = gwData.global_widget;
                        return apiFetch('/page-builder/elements/' + elId + '/settings', {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
                            body: JSON.stringify({
                                settings: Object.assign({}, gw.settings || {}, { _global_widget_id: gw.id }),
                                content: gw.content || {},
                                styles: gw.styles || {},
                            }),
                        });
                    }
                    return null;
                })
                .catch(() => {})
        );
    });
    if (promises.length > 0) {
        Promise.all(promises).then(() => {
            state.loadElements();
            showToast('Global widgets sincronizados!', 'success');
        });
    } else {
        showToast('Nenhum global widget para sincronizar', 'info');
    }
}
