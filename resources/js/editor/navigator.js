import Sortable from 'sortablejs';
import { structureIcon, apiFetch, toastError } from './utils.js';
import { icon } from './icons.js';
import { widgetLabel, elementName } from './i18n.js';

let _navSortables = new Map();
// Collapsed node ids survive re-renders of the tree
const _collapsed = new Set();

export function toggleNavigator(state) {
    const navPanel = document.getElementById('panel-navigator');
    const isVisible = navPanel.style.display !== 'none';
    if (isVisible) {
        document.getElementById('panel-widgets').style.display = '';
        document.getElementById('panel-navigator').style.display = 'none';
        document.getElementById('panel-structure').style.display = 'none';
        document.getElementById('panel-layouts').style.display = 'none';
        document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
        document.querySelector('.pb-panel-tab[data-tab="widgets"]').classList.add('active');
    } else {
        document.getElementById('panel-widgets').style.display = 'none';
        document.getElementById('panel-navigator').style.display = '';
        document.getElementById('panel-structure').style.display = 'none';
        document.getElementById('panel-layouts').style.display = 'none';
        document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
        document.querySelector('.pb-panel-tab[data-tab="navigator"]').classList.add('active');
        renderNavigator(state);
    }
}

export function renderNavigator(state) {
    const body = document.getElementById('navigator-body');
    body.innerHTML = '';
    destroyNavSortables();

    const searchBox = document.createElement('div');
    searchBox.className = 'pb-nav-search';
    searchBox.innerHTML = '<input type="text" id="nav-search-input" placeholder="Buscar elementos..." autocomplete="off"><span class="pb-nav-search-icon">' + icon('search', 13) + '</span>';
    body.appendChild(searchBox);

    const treeWrap = document.createElement('div');
    treeWrap.className = 'pb-nav-tree';
    body.appendChild(treeWrap);

    const els = state._lastElements || [];
    _renderNavItems(state, els, treeWrap, 0);
    _initNavSortable(treeWrap, state);

    const searchInput = document.getElementById('nav-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase().trim();
            // A node stays visible when it or any descendant matches
            const filterNode = (node) => {
                const item = node.querySelector(':scope > .pb-nav-item');
                const name = (item?.querySelector('.nav-name')?.textContent || '').toLowerCase();
                const type = (node.dataset.elType || '').toLowerCase();
                let visible = !q || name.includes(q) || type.includes(q);
                const children = node.querySelector(':scope > .pb-nav-children');
                if (children) {
                    let childVisible = false;
                    children.querySelectorAll(':scope > .pb-nav-node').forEach(c => { if (filterNode(c)) childVisible = true; });
                    if (q && childVisible) children.style.display = '';
                    else if (!q) children.style.display = _collapsed.has(parseInt(node.dataset.elId)) ? 'none' : '';
                    visible = visible || childVisible;
                }
                node.style.display = visible ? '' : 'none';
                return visible;
            };
            treeWrap.querySelectorAll(':scope > .pb-nav-node').forEach(filterNode);
        });
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { searchInput.value = ''; searchInput.dispatchEvent(new Event('input')); searchInput.blur(); }
        });
    }
}

export function _renderNavItems(state, elements, container, depth) {
    (elements || []).forEach(el => {
        // Each node wraps its row and its children so they move together when dragged
        const node = document.createElement('div');
        node.className = 'pb-nav-node';
        node.dataset.elId = el.id;
        node.dataset.elType = el.type;

        const item = document.createElement('div');
        item.className = 'pb-nav-item' + (state.selectedId === el.id ? ' active' : '');
        item.dataset.elId = el.id;
        item.dataset.elType = el.type;

        const hasChildren = el.children && el.children.length > 0;
        const collapsed = _collapsed.has(el.id);

        const toggle = document.createElement('span');
        toggle.className = 'nav-toggle' + (hasChildren && !collapsed ? ' expanded' : '');
        toggle.innerHTML = hasChildren ? icon('chevron-right', 12) : '';
        toggle.style.visibility = hasChildren ? 'visible' : 'hidden';
        toggle.onclick = (e) => {
            e.stopPropagation();
            const ch = node.querySelector(':scope > .pb-nav-children');
            if (ch) {
                const visible = ch.style.display !== 'none';
                ch.style.display = visible ? 'none' : '';
                toggle.classList.toggle('expanded', !visible);
                if (visible) _collapsed.add(el.id); else _collapsed.delete(el.id);
            }
        };

        const iconEl = document.createElement('span');
        iconEl.className = 'nav-icon';
        iconEl.innerHTML = structureIcon(el.type);

        const name = document.createElement('span');
        name.className = 'nav-name';
        name.textContent = elementName(el);

        const type = document.createElement('span');
        type.className = 'nav-type';
        type.textContent = widgetLabel(el.type);

        item.appendChild(toggle);
        item.appendChild(iconEl);
        item.appendChild(name);
        item.appendChild(type);

        item.onclick = (e) => {
            e.stopPropagation();
            state.onSelectElement(el.id);
            highlightNavItem(el.id);
        };

        item.ondblclick = (e) => {
            e.stopPropagation();
            _startNavRename(state, name, el);
        };

        item.oncontextmenu = (e) => {
            e.preventDefault();
            e.stopPropagation();
            state.onSelectElement(el.id);
            highlightNavItem(el.id);
            _showNavContext(state, e.clientX, e.clientY, el);
        };

        node.appendChild(item);
        container.appendChild(node);

        // Containers always get a children list, even when empty, so items can be dropped into them
        if (el.is_container) {
            const childDiv = document.createElement('div');
            childDiv.className = 'pb-nav-children';
            childDiv.dataset.parentId = el.id;
            if (hasChildren && collapsed) childDiv.style.display = 'none';
            node.appendChild(childDiv);
            _renderNavItems(state, el.children || [], childDiv, depth + 1);
            _initNavSortable(childDiv, state);
        }
    });
}

export function highlightNavItem(id) {
    document.querySelectorAll('#navigator-body .pb-nav-item.active').forEach(i => i.classList.remove('active'));
    const item = document.querySelector(`#navigator-body .pb-nav-item[data-el-id="${id}"]`);
    if (item) item.classList.add('active');
}

function _initNavSortable(container, state) {
    if (_navSortables.has(container)) {
        _navSortables.get(container).destroy();
    }

    const sortable = Sortable.create(container, {
        group: 'navigator',
        animation: 150,
        ghostClass: 'pb-nav-ghost',
        chosenClass: 'pb-nav-chosen',
        draggable: '.pb-nav-node',
        handle: '.pb-nav-item',
        fallbackOnBody: true,
        swapThreshold: 0.65,
        emptyInsertThreshold: 12,
        onEnd(evt) {
            if (evt.from === evt.to && evt.oldIndex === evt.newIndex) return;
            // Defer so Sortable finishes its own cleanup before the tree is rebuilt
            setTimeout(() => _applyNavTreeFromDOM(state), 0);
        },
    });

    _navSortables.set(container, sortable);
}

// Rebuilds state._lastElements from the navigator DOM after a drag, then re-renders and saves
function _applyNavTreeFromDOM(state) {
    const byId = new Map();
    const index = (list) => (list || []).forEach(e => { byId.set(e.id, e); index(e.children); });
    index(state._lastElements || []);

    const build = (container) => {
        const out = [];
        container.querySelectorAll(':scope > .pb-nav-node').forEach(node => {
            const el = byId.get(parseInt(node.dataset.elId));
            if (!el) return;
            const childList = node.querySelector(':scope > .pb-nav-children');
            if (childList) el.children = build(childList);
            out.push(el);
        });
        return out;
    };

    const tree = document.querySelector('#navigator-body .pb-nav-tree');
    if (!tree) return;
    state._lastElements = build(tree);
    _rerenderAll(state);
    state._saveElementOrder();
}

function _rerenderAll(state) {
    const els = state._lastElements || [];
    state.renderCanvas(els);
    state.renderStructure(els);
    renderNavigator(state);
    state.renderMath();
}

function destroyNavSortables() {
    _navSortables.forEach(s => s.destroy());
    _navSortables.clear();
}

export function _startNavRename(state, nameEl, el) {
    const input = document.createElement('input');
    input.className = 'pb-nav-rename-input';
    input.value = elementName(el);
    nameEl.replaceWith(input);
    input.focus();
    input.select();

    const save = () => {
        const newName = input.value.trim() || el.type;
        el.name = newName;
        nameEl.textContent = newName;
        input.replaceWith(nameEl);
        apiFetch(`/page-builder/elements/${el.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
            body: JSON.stringify({ name: newName }),
        }).catch(() => {});
    };

    input.onblur = save;
    input.onkeydown = (e) => {
        if (e.key === 'Enter') save();
        if (e.key === 'Escape') { input.replaceWith(nameEl); }
    };
}

export function _showNavContext(state, x, y, el) {
    _hideNavContext();
    const ctx = document.createElement('div');
    ctx.className = 'pb-nav-context';
    ctx.style.left = x + 'px';
    ctx.style.top = y + 'px';

    const items = [
        { icon: 'pencil', label: 'Renomear', action: () => { const item = document.querySelector(`.pb-nav-item[data-el-id="${el.id}"] .nav-name`); if (item) _startNavRename(state, item, el); } },
        { icon: 'copy', label: 'Duplicar', action: () => state.duplicateElement(el.id) },
        { sep: true },
        { icon: 'arrow-up', label: 'Mover para cima', action: () => _navMoveRelative(state, el.id, -1) },
        { icon: 'arrow-down', label: 'Mover para baixo', action: () => _navMoveRelative(state, el.id, 1) },
        { sep: true },
        { icon: 'clipboard-copy', label: 'Copiar', action: () => { state._clipboard = JSON.parse(JSON.stringify(el)); state.showToast('Elemento copiado', 'success'); } },
        { icon: 'clipboard-paste', label: 'Colar (ap\u00F3s)', action: () => _navPasteAfter(state, el.id) },
        { sep: true },
        { icon: 'paintbrush', label: 'Copiar estilos', action: () => { state.onSelectElement(el.id); setTimeout(() => { state._styleClipboard = JSON.parse(JSON.stringify(state.cachedStyles || {})); state.showToast('Estilos copiados!', 'success'); }, 100); } },
        { icon: 'paint-roller', label: 'Colar estilos', action: () => { state.onSelectElement(el.id); setTimeout(() => { if (!state._styleClipboard) { state.showToast('Nenhum estilo copiado', 'error'); return; } const elId = el.id; const styles = state._styleClipboard; let applied = 0; const keys = Object.keys(styles); const applyNext = (idx) => { if (idx >= keys.length) { if (applied > 0) { state.showToast(`${applied} estilo(s) aplicado(s)!`, 'success'); state.loadElements(); setTimeout(() => state.onSelectElement(elId), 200); } return; } const k = keys[idx]; const v = styles[k]; if (v !== undefined && v !== '' && v !== null) { applied++; fetch(`/page-builder/elements/${elId}/styles`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf }, body: JSON.stringify({ styles: { [k]: v } }) }).then(() => applyNext(idx + 1)).catch(() => applyNext(idx + 1)); } else { applyNext(idx + 1); } }; applyNext(0); }, 100); } },
        { sep: true },
        { icon: 'trash', label: 'Excluir', cls: 'danger', action: () => state.deleteElement(el.id) },
    ];

    items.forEach(m => {
        if (m.sep) {
            const sep = document.createElement('div');
            sep.className = 'pb-nav-context-sep';
            ctx.appendChild(sep);
            return;
        }
        const btn = document.createElement('div');
        btn.className = 'pb-nav-context-item' + (m.cls ? ' ' + m.cls : '');
        btn.innerHTML = (m.icon ? icon(m.icon, 14) : '') + '<span></span>';
        btn.lastChild.textContent = m.label;
        btn.onclick = (e) => { e.stopPropagation(); _hideNavContext(); m.action(); };
        ctx.appendChild(btn);
    });

    document.body.appendChild(ctx);
    const closeCtx = (e) => {
        if (!ctx.contains(e.target)) { _hideNavContext(); document.removeEventListener('click', closeCtx); }
    };
    setTimeout(() => document.addEventListener('click', closeCtx), 10);
}

export function _hideNavContext() {
    document.querySelectorAll('.pb-nav-context').forEach(c => c.remove());
}

export function _showCanvasContext(state, x, y, elId) {
    _hideCanvasContext();
    const ctx = document.createElement('div');
    ctx.className = 'pb-canvas-context';
    ctx.style.left = x + 'px';
    ctx.style.top = y + 'px';

    const items = [
        { icon: 'pencil', label: 'Editar', action: () => state.onSelectElement(elId) },
        { icon: 'copy', label: 'Duplicar', action: () => state.duplicateElement(elId) },
        { sep: true },
        { icon: 'arrow-up', label: 'Mover para cima', action: () => _navMoveRelative(state, elId, -1) },
        { icon: 'arrow-down', label: 'Mover para baixo', action: () => _navMoveRelative(state, elId, 1) },
        { sep: true },
        { icon: 'clipboard-copy', label: 'Copiar', action: () => { const els = state._lastElements || []; const find = (list) => { for (const e of list) { if (e.id === elId) return e; if (e.children) { const f = find(e.children); if (f) return f; } } return null; }; const found = find(els); if (found) { state._clipboard = JSON.parse(JSON.stringify(found)); state.showToast('Elemento copiado', 'success'); } } },
        { icon: 'clipboard-paste', label: 'Colar (ap\u00F3s)', action: () => _navPasteAfter(state, elId) },
        { sep: true },
        { icon: 'paintbrush', label: 'Copiar estilos', action: () => { state.onSelectElement(elId); setTimeout(() => { state._styleClipboard = JSON.parse(JSON.stringify(state.cachedStyles || {})); state.showToast('Estilos copiados!', 'success'); }, 100); } },
        { icon: 'paint-roller', label: 'Colar estilos', action: () => { state.onSelectElement(elId); setTimeout(() => { if (!state._styleClipboard) { state.showToast('Nenhum estilo copiado', 'error'); return; } const styles = state._styleClipboard; let applied = 0; const keys = Object.keys(styles); const applyNext = (idx) => { if (idx >= keys.length) { if (applied > 0) { state.showToast(`${applied} estilo(s) aplicado(s)!`, 'success'); state.loadElements(); setTimeout(() => state.onSelectElement(elId), 200); } return; } const k = keys[idx]; const v = styles[k]; if (v !== undefined && v !== '' && v !== null) { applied++; fetch(`/page-builder/elements/${elId}/styles`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf }, body: JSON.stringify({ styles: { [k]: v } }) }).then(() => applyNext(idx + 1)).catch(() => applyNext(idx + 1)); } else { applyNext(idx + 1); } }; applyNext(0); }, 100); } },
        { sep: true },
        { icon: 'component', label: 'Salvar como Global Widget', action: () => { state.onSelectElement(elId); setTimeout(() => { if (typeof saveAsGlobalWidget === 'function') saveAsGlobalWidget(); else if (window.editor && window.editor.saveAsGlobalWidget) window.editor.saveAsGlobalWidget(); }, 50); } },
        { icon: 'refresh', label: 'Sincronizar Global Widget', action: () => { if (typeof syncGlobalWidgets === 'function') syncGlobalWidgets(); else if (window.editor && window.editor.syncGlobalWidgets) window.editor.syncGlobalWidgets(); } },
        { sep: true },
        { icon: 'trash', label: 'Excluir', cls: 'danger', action: () => state.deleteElement(elId) },
    ];

    items.forEach(m => {
        if (m.sep) {
            const sep = document.createElement('div');
            sep.className = 'pb-canvas-context-sep';
            ctx.appendChild(sep);
            return;
        }
        const btn = document.createElement('div');
        btn.className = 'pb-canvas-context-item' + (m.cls ? ' ' + m.cls : '');
        btn.innerHTML = (m.icon ? icon(m.icon, 14) : '') + '<span></span>';
        btn.lastChild.textContent = m.label;
        btn.onclick = (e) => { e.stopPropagation(); _hideCanvasContext(); m.action(); };
        ctx.appendChild(btn);
    });

    document.body.appendChild(ctx);
    const rect = ctx.getBoundingClientRect();
    if (rect.right > window.innerWidth) ctx.style.left = (x - rect.width) + 'px';
    if (rect.bottom > window.innerHeight) ctx.style.top = (y - rect.height) + 'px';
    const closeCtx = (e) => {
        if (!ctx.contains(e.target)) { _hideCanvasContext(); document.removeEventListener('click', closeCtx); }
    };
    setTimeout(() => document.addEventListener('click', closeCtx), 10);
}

export function _hideCanvasContext() {
    document.querySelectorAll('.pb-canvas-context').forEach(c => c.remove());
}

function findInTree(list, id) {
    for (const el of list) {
        if (el.id === id) return el;
        if (el.children) {
            const found = findInTree(el.children, id);
            if (found) return found;
        }
    }
    return null;
}

function findParentList(list, id) {
    for (const el of list) {
        if (el.id === id) return list;
        if (el.children) {
            const found = findParentList(el.children, id);
            if (found) return found;
        }
    }
    return null;
}

export function _navMoveElement(state, dragId, newParentId) {
    const els = state._lastElements || [];
    const dragEl = findInTree(els, dragId);
    if (!dragEl) return;

    const targetEl = newParentId ? findInTree(els, newParentId) : null;
    if (newParentId && (!targetEl || !targetEl.is_container || findInTree([dragEl], newParentId))) return;

    const dragParent = findParentList(els, dragId);
    if (dragParent) dragParent.splice(dragParent.findIndex(e => e.id === dragId), 1);

    if (targetEl) (targetEl.children = targetEl.children || []).push(dragEl);
    else els.push(dragEl);

    _rerenderAll(state);
    state._saveElementOrder();
}

export function _navMoveRelative(state, elId, direction) {
    const els = state._lastElements || [];
    const findAndMove = (list) => {
        const idx = list.findIndex(e => e.id === elId);
        if (idx !== -1) {
            const newIdx = idx + direction;
            if (newIdx >= 0 && newIdx < list.length) {
                [list[idx], list[newIdx]] = [list[newIdx], list[idx]];
                return true;
            }
            return false;
        }
        for (const el of list) {
            if (el.children && findAndMove(el.children)) return true;
        }
        return false;
    };

    if (findAndMove(els)) {
        _rerenderAll(state);
        state._saveElementOrder();
    }
}

export function _navPasteAfter(state, targetId) {
    if (!state._clipboard) return;
    const els = JSON.parse(JSON.stringify(state._lastElements || []));
    const clone = JSON.parse(JSON.stringify(state._clipboard));
    const stripIds = (el) => { delete el.id; (el.children || []).forEach(stripIds); };
    stripIds(clone);

    const findAndInsert = (list) => {
        const idx = list.findIndex(e => e.id === targetId);
        if (idx !== -1) {
            list.splice(idx + 1, 0, clone);
            return true;
        }
        for (const el of list) {
            if (el.children && findAndInsert(el.children)) return true;
        }
        return false;
    };

    if (!findAndInsert(els)) return;

    // New elements have no id yet, so persist the whole tree through the snapshot endpoint
    apiFetch(`/page-builder/pages/${state.pageId}/elements/restore-snapshot`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ elements: els }),
    })
    .then(() => { state.showToast('Elemento colado', 'success'); state.loadElements(); })
    .catch(() => toastError('Falha ao colar elemento'));
}
