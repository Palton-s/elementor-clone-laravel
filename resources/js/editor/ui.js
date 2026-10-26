import state from './state.js';
import { apiFetch } from './utils.js';
import { renderMath } from './canvas.js';
import { undo, redo } from './history.js';
import { renderNavigator } from './navigator.js';
import { save, copyStyles, pasteStyles, loadTemplates } from './actions.js';
import { duplicateSelected, deleteSelected, clearMultiSelect } from './multi-select.js';
import { toggleFinder } from './finder.js';
import { selectElement, navigateElements, deselectAll } from './elements.js';

export function setZoom(level) {
    state.zoomLevel = Math.min(200, Math.max(25, level));
    const canvas = document.getElementById('canvas');
    if (canvas) {
        canvas.style.transform = `scale(${state.zoomLevel / 100})`;
        canvas.style.transformOrigin = 'top center';
    }
    const label = document.getElementById('pb-zoom-label');
    if (label) label.textContent = state.zoomLevel + '%';
}

export function toggleFullscreen() {
    state.isFullscreen = !state.isFullscreen;
    const panels = document.querySelectorAll('.pb-panel');
    const layout = document.querySelector('.pb-layout');
    const btn = document.getElementById('pb-fullscreen');
    if (state.isFullscreen) {
        panels.forEach(p => p.style.display = 'none');
        if (layout) layout.style.display = 'block';
        btn.classList.add('active');
    } else {
        panels.forEach(p => p.style.display = '');
        if (layout) layout.style.display = '';
        btn.classList.remove('active');
    }
}

export function setResponsive(mode) {
    const changed = state.responsiveMode !== mode;
    state.responsiveMode = mode;
    state.responsiveTab = mode;
    document.querySelectorAll('.pb-toolbar [data-mode]').forEach(b => b.classList.remove('active'));
    document.querySelector(`.pb-toolbar [data-mode="${mode}"]`).classList.add('active');
    document.querySelectorAll('.pb-resp-tab').forEach(b => b.classList.toggle('active', b.dataset.device === mode));
    if (changed) {
        // Canvas and settings both show the values of the device being edited
        if (state.renderCanvas && state._lastElements) {
            state.renderCanvas(state._lastElements);
            if (state.selectedId) document.querySelector(`.pb-el[data-el-id="${state.selectedId}"]`)?.classList.add('selected');
        }
        import('./controls.js').then(m => {
            if (state.cachedElementId) { m.refreshDeviceView(); m.renderControls(); }
        });
    }
    const canvas = document.getElementById('canvas');
    const frame = document.getElementById('device-frame');
    const notch = document.getElementById('device-notch');
    const label = document.getElementById('device-label');
    canvas.className = 'pb-canvas';
    document.body.classList.remove('responsive-tablet', 'responsive-mobile');
    if (mode !== 'desktop') {
        canvas.classList.add('is-' + mode);
        document.body.classList.add('responsive-' + mode);
        if (notch) notch.style.display = '';
        if (label) {
            label.style.display = '';
            const sizes = { tablet: 'Tablet — 768px', mobile: 'Mobile — 375px' };
            label.textContent = sizes[mode] || '';
        }
        if (frame) frame.className = 'pb-device-frame ' + mode;
    } else {
        if (notch) notch.style.display = 'none';
        if (label) label.style.display = 'none';
        if (frame) frame.className = 'pb-device-frame';
    }
}

export function setResponsiveTab(device) {
    setResponsive(device);
}

export function switchTab(tab) {
    document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.pb-panel-tab[data-tab="${tab}"]`).classList.add('active');
    document.getElementById('panel-widgets').style.display = tab === 'widgets' ? '' : 'none';
    document.getElementById('panel-navigator').style.display = tab === 'navigator' ? '' : 'none';
    document.getElementById('panel-structure').style.display = tab === 'structure' ? '' : 'none';
    document.getElementById('panel-layouts').style.display = tab === 'layouts' ? '' : 'none';
    if (tab === 'layouts') loadTemplates();
    if (tab === 'navigator') renderNavigator(state);
}

export function bindKeyboard() {
    document.addEventListener('keydown', e => {
        const isInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT' || e.target.isContentEditable;
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); undo(state); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) { e.preventDefault(); redo(state); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(state); }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); save(); }
        if ((e.ctrlKey || e.metaKey) && e.key === '0') { e.preventDefault(); setZoom(100); }
        if ((e.ctrlKey || e.metaKey) && e.key === '=') { e.preventDefault(); setZoom(state.zoomLevel + 10); }
        if ((e.ctrlKey || e.metaKey) && e.key === '-') { e.preventDefault(); setZoom(state.zoomLevel - 10); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') { e.preventDefault(); duplicateSelected(); }
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') { e.preventDefault(); copyStyles(); }
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'V') { e.preventDefault(); pasteStyles(); }
        if (e.key === 'F11') { e.preventDefault(); toggleFullscreen(); }
        if (e.key === 'Escape' && state.isFullscreen) { toggleFullscreen(); }
        else if (e.key === 'Escape' && state.multiSelected && state.multiSelected.size > 0) { clearMultiSelect(); }
        else if (e.key === 'Escape' && document.getElementById('pb-finder-overlay')) { document.getElementById('pb-finder-overlay').remove(); }
        else if (e.key === 'Escape' && state.selectedId) { deselectAll(); state.selectedId = null; }
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); toggleFinder(); }
        if (e.key === 'Delete' && state.selectedId) { deleteSelected(); }
        if (!isInput && state.selectedId) {
            if (e.key === 'Tab') { e.preventDefault(); navigateElements(e.shiftKey ? -1 : 1); }
        }
    });
}

export function bindZoom() {
    const wrap = document.getElementById('canvas-wrap');
    if (!wrap) return;
    wrap.addEventListener('wheel', (e) => {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -5 : 5;
            setZoom(state.zoomLevel + delta);
        }
    }, { passive: false });
}

export function observeCanvas() {
    const dz = document.getElementById('canvas-dropzone');
    if (!dz) return;
    let timer = null;
    const observer = new MutationObserver(() => {
        clearTimeout(timer);
        timer = setTimeout(() => renderMath(), 150);
    });
    observer.observe(dz, { childList: true, subtree: true, characterData: true });
}

export function bindInlineEditing() {
    const dz = document.getElementById('canvas-dropzone');
    const editableTypes = ['heading', 'text', 'button', 'callout'];
    dz.addEventListener('dblclick', (e) => {
        const el = e.target.closest('.pb-el');
        if (!el || e.target.closest('.pb-el-toolbar')) return;
        const type = el.dataset.elType;
        if (!editableTypes.includes(type)) return;
        let textEl = e.target.closest('h1, h2, h3, h4, h5, h6, p, span, a, button, label');
        if (!textEl) {
            const contentDiv = e.target.closest('.pb-el-content > div');
            if (contentDiv) textEl = contentDiv;
        }
        if (!textEl || el.dataset._editing) return;
        el.dataset._editing = '1';
        el.dataset._origHtml = textEl.innerHTML;
        textEl.contentEditable = 'true';
        textEl.focus();
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(textEl);
        selection.removeAllRanges();
        selection.addRange(range);
        const finish = () => {
            if (!el.dataset._editing) return;
            textEl.contentEditable = 'false';
            delete el.dataset._editing;
            const newHtml = textEl.innerHTML.trim();
            const origHtml = el.dataset._origHtml || '';
            if (newHtml && newHtml !== origHtml) {
                const key = { heading: 'title', text: 'content', button: 'text', callout: 'content' }[type] || 'title';
                const elId = el.dataset.elId;
                import('./controls.js').then(m => m.updateSetting(key, newHtml, elId, false));
                if (state.selectedId == elId) setTimeout(() => import('./controls.js').then(m => m.loadControls(elId)), 100);
            }
        };
        textEl.addEventListener('blur', finish, { once: true });
        textEl.addEventListener('keydown', (k) => {
            if (k.key === 'Enter' && !k.shiftKey) { k.preventDefault(); textEl.blur(); }
            if (k.key === 'Escape') { textEl.innerHTML = el.dataset._origHtml || ''; textEl.blur(); }
        });
    });
}

export function bindWidgetSearch() {
    const input = document.getElementById('widget-search-input');
    if (!input) return;
    const items = document.querySelectorAll('#panel-widgets .pb-widget-item');
    const groups = document.querySelectorAll('#panel-widgets .pb-widget-group');
    const empty = document.getElementById('widget-search-empty');
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        let found = 0;
        items.forEach(item => {
            const search = (item.dataset.search || '') + ' ' + (item.dataset.type || '');
            const match = !q || search.toLowerCase().includes(q);
            item.classList.toggle('pb-widget-hidden', !match);
            if (match) found++;
        });
        groups.forEach(g => {
            const visible = g.querySelectorAll('.pb-widget-item:not(.pb-widget-hidden)').length;
            g.classList.toggle('pb-widget-hidden', visible === 0);
        });
        if (empty) empty.style.display = found === 0 ? '' : 'none';
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { input.value = ''; input.dispatchEvent(new Event('input')); input.blur(); }
    });
}

export function bindResizablePanels() {
    const layout = document.querySelector('.pb-layout');
    if (!layout) return;
    const panels = layout.querySelectorAll('.pb-panel');
    panels.forEach(panel => {
        const handle = document.createElement('div');
        handle.className = 'pb-panel-resize';
        const isRight = panel.classList.contains('pb-panel-right');
        if (!isRight) panel.appendChild(handle);
        else panel.insertBefore(handle, panel.firstChild);
        let startX, startW;
        const onMove = (e) => {
            const dx = e.clientX - startX;
            const newW = isRight ? startW - dx : startW + dx;
            panel.style.width = Math.max(200, Math.min(500, newW)) + 'px';
        };
        const onUp = () => {
            handle.classList.remove('active');
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        };
        handle.addEventListener('mousedown', (e) => {
            e.preventDefault();
            startX = e.clientX;
            startW = panel.offsetWidth;
            handle.classList.add('active');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    });
}

export function showColumnStructurePicker(state, sectionElId) {
    const existing = document.getElementById('pb-col-picker');
    if (existing) existing.remove();
    const sectionEl = document.querySelector(`.pb-el[data-el-id="${sectionElId}"]`);
    if (!sectionEl) return;
    const rect = sectionEl.getBoundingClientRect();
    const picker = document.createElement('div');
    picker.id = 'pb-col-picker';
    picker.style.cssText = `position:fixed;top:${rect.top}px;left:${rect.left}px;width:${rect.width}px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:10px;padding:12px;z-index:99999;box-shadow:0 8px 32px rgba(0,0,0,.3);animation:tourPop .2s`;
    const title = document.createElement('div');
    title.style.cssText = 'font-size:.75rem;font-weight:600;color:var(--pb-text2);margin-bottom:8px;text-align:center';
    title.textContent = 'Estrutura de Colunas';
    picker.appendChild(title);
    const layouts = [
        { cols: [50, 50], label: '50/50' },
        { cols: [33.33, 33.33, 33.33], label: '33/33/33' },
        { cols: [25, 25, 25, 25], label: '25/25/25/25' },
        { cols: [33.33, 66.67], label: '33/67' },
        { cols: [66.67, 33.33], label: '67/33' },
        { cols: [20, 60, 20], label: '20/60/20' },
    ];
    const grid = document.createElement('div');
    grid.style.cssText = 'display:grid;grid-template-columns:repeat(3,1fr);gap:6px';
    layouts.forEach(layout => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.style.cssText = 'padding:8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;cursor:pointer;transition:all .15s;display:flex;gap:2px;height:36px';
        layout.cols.forEach(pct => {
            const col = document.createElement('div');
            col.style.cssText = `flex:${pct};background:var(--pb-accent);border-radius:3px;opacity:.6`;
            btn.appendChild(col);
        });
        btn.onmouseenter = () => { btn.style.borderColor = 'var(--pb-accent)'; btn.style.background = 'var(--pb-primary-light)'; };
        btn.onmouseleave = () => { btn.style.borderColor = 'var(--pb-border)'; btn.style.background = 'var(--pb-surface2)'; };
        btn.onclick = () => {
            picker.remove();
            createColumnsForSection(state, sectionElId, layout.cols);
        };
        grid.appendChild(btn);
    });
    picker.appendChild(grid);
    document.body.appendChild(picker);
    const closeHandler = (e) => { if (!picker.contains(e.target)) { picker.remove(); document.removeEventListener('click', closeHandler); } };
    setTimeout(() => document.addEventListener('click', closeHandler), 10);
}

export function createColumnsForSection(state, sectionId, colPercentages) {
    const promises = [];
    colPercentages.forEach((pct, idx) => {
        promises.push(
            apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
                body: JSON.stringify({ type: 'column', parent_id: sectionId, settings: { column_width: pct } }),
            })
        );
    });
    Promise.all(promises).then(() => state.loadElements());
}
