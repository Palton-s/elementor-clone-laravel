import Sortable from 'sortablejs';
import { toastError, apiFetch } from './utils.js';

const _containerSortables = new Map();

// Shared options for every canvas drop target (root + each container's children list)
const CANVAS_SORTABLE_OPTIONS = {
    animation: 150,
    ghostClass: 'pb-sortable-ghost',
    chosenClass: 'pb-sortable-chosen',
    dragClass: 'pb-sortable-drag',
    handle: '.pb-el-drag',
    draggable: '.pb-el',
    filter: '.pb-empty-drop, .pb-empty-canvas, .pb-section-inserter',
    preventOnFilter: false,
    fallbackOnBody: true,
    swapThreshold: 0.65,
    emptyInsertThreshold: 24,
};

export function bindDragDrop(state) {
    _bindWidgetPanel(state);
}

function _bindWidgetPanel(state) {
    const panelBody = document.getElementById('panel-widgets');
    if (!panelBody) return;

    // A plain click (no drag) inserts the widget; stopPropagation keeps the current selection as reference
    panelBody.addEventListener('click', (e) => {
        const item = e.target.closest('.pb-widget-item[data-type]');
        if (!item) return;
        e.stopPropagation();
        _insertByClick(state, item.dataset.type);
    });

    panelBody.querySelectorAll('.pb-widget-grid').forEach(grid => {
        Sortable.create(grid, {
            group: {
                name: 'widgets',
                pull: 'clone',
                put: false,
            },
            sort: false,
            draggable: '.pb-widget-item',
            ghostClass: 'pb-widget-ghost',
            chosenClass: 'pb-widget-chosen',
            dragClass: 'pb-widget-dragging',
            animation: 150,
            filter: '.pb-widget-group-title',
            onStart() {
                document.body.classList.add('pb-is-dragging');
            },
            onEnd() {
                document.body.classList.remove('pb-is-dragging');
            },
        });
    });
}

function _containerParentId(container) {
    return container.dataset.parentId ? parseInt(container.dataset.parentId) : null;
}

function _handleNewWidgetDrop(state, evt) {
    const item = evt.item;
    const type = item.dataset.type;
    const targetContainer = evt.to;

    // Compute the insertion point before removing the cloned panel item
    let insertBeforeId = null;
    let sib = item.nextElementSibling;
    while (sib) {
        if (sib.classList.contains('pb-el') && sib.dataset.elId) { insertBeforeId = parseInt(sib.dataset.elId); break; }
        sib = sib.nextElementSibling;
    }

    if (item.parentNode) item.parentNode.removeChild(item);
    if (!type) return;

    clearInsertTarget(state);
    createElementAt(state, type, _containerParentId(targetContainer), insertBeforeId);
}

/**
 * Creates a widget inside parentId (null = page root), before insertBeforeId when given,
 * reloads the canvas and selects the new element.
 */
export function createElementAt(state, type, parentId, insertBeforeId) {
    state.showToast('Adicionando ' + type + '...', 'info');

    return apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({
            type: type,
            parent_id: parentId,
            insert_before_id: insertBeforeId,
        }),
    })
    .then(res => Promise.resolve(state.loadElements()).then(() => {
        if (res && res.element && res.element.id) state.onSelectElement(res.element.id);
    }))
    .catch(err => toastError('Falha ao adicionar elemento: ' + (err.message || err)));
}

/**
 * New top-level section before another one (the "+" between two sections), created with a
 * full-width column so widgets can be dropped into it right away.
 */
export function insertSectionBefore(state, beforeId) {
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf };
    const create = (body) => apiFetch(`/page-builder/pages/${state.pageId}/elements`, { method: 'POST', headers, body: JSON.stringify(body) });

    state.showToast('Adicionando seção...', 'info');
    return create({ type: 'section', parent_id: null, insert_before_id: beforeId })
        .then(res => create({ type: 'column', parent_id: res.element.id, settings: { column_width: 100 } }).then(() => res.element.id))
        .then(sectionId => Promise.resolve(state.loadElements()).then(() => state.onSelectElement(sectionId)))
        .catch(err => toastError('Falha ao adicionar seção: ' + (err.message || err)));
}

// ---- Click-to-insert: the "+" inside containers picks a target, a click in the Inserir panel fills it

let _escHandler = null;

export function setInsertTarget(state, parentId) {
    if (state.insertTarget && state.insertTarget.parentId === parentId) {
        clearInsertTarget(state);
        return;
    }
    state.insertTarget = { parentId };
    markInsertTarget(state);

    const tab = document.querySelector('.pb-panel-tab[data-tab="widgets"]');
    if (tab && !tab.classList.contains('active')) tab.click();
    state.showToast('Agora clique em um widget na aba Inserir (Esc cancela)', 'info');

    if (!_escHandler) {
        _escHandler = (e) => { if (e.key === 'Escape') clearInsertTarget(state); };
        document.addEventListener('keydown', _escHandler);
    }
}

export function clearInsertTarget(state) {
    state.insertTarget = null;
    markInsertTarget(state);
    if (_escHandler) {
        document.removeEventListener('keydown', _escHandler);
        _escHandler = null;
    }
}

// Re-applied after every canvas render, since rendering rebuilds the DOM
export function markInsertTarget(state) {
    document.querySelectorAll('.pb-insert-target, .pb-insert-parent').forEach(el => el.classList.remove('pb-insert-target', 'pb-insert-parent'));
    document.body.classList.toggle('pb-has-insert-target', !!state.insertTarget);
    if (!state.insertTarget) return;
    const id = state.insertTarget.parentId;
    const adder = document.querySelector(`#canvas-dropzone .pb-empty-drop[data-parent-id="${id}"]`);
    if (!adder) { state.insertTarget = null; document.body.classList.remove('pb-has-insert-target'); return; }
    adder.classList.add('pb-insert-target');
    adder.closest('.pb-el')?.classList.add('pb-insert-parent');
}

function _locate(list, id, parent = null) {
    for (let i = 0; i < (list || []).length; i++) {
        if (list[i].id === id) return { el: list[i], list, index: i, parent };
        const found = _locate(list[i].children, id, list[i]);
        if (found) return found;
    }
    return null;
}

function _insertByClick(state, type) {
    let parentId = null;
    let beforeId = null;

    if (state.insertTarget) {
        parentId = state.insertTarget.parentId;
    } else if (state.selectedId) {
        // No "+" chosen: go inside the selected container, or right after the selected element
        const found = _locate(state._lastElements, state.selectedId);
        if (found && found.el.is_container) {
            parentId = found.el.id;
        } else if (found) {
            const next = found.list[found.index + 1];
            parentId = found.parent ? found.parent.id : null;
            if (next) beforeId = next.id;
        }
    }

    clearInsertTarget(state);
    createElementAt(state, type, parentId, beforeId);
}

function _handleCanvasMove(state) {
    const order = _buildOrderFromDOM();
    apiFetch(`/page-builder/pages/${state.pageId}/elements/reorder`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ order }),
    })
    .catch(() => toastError('Falha ao reordenar'))
    .finally(() => state.loadElements());
}

export function initContainerSortables(state) {
    destroyAllContainerSortables();

    const dz = document.getElementById('canvas-dropzone');
    if (dz) _initSortableForContainer(dz, state);

    document.querySelectorAll('#canvas-dropzone .pb-el-children').forEach(childContainer => {
        _initSortableForContainer(childContainer, state);
    });

    markInsertTarget(state);
}

function _initSortableForContainer(container, state) {
    if (_containerSortables.has(container)) {
        _containerSortables.get(container).destroy();
    }

    const sortable = Sortable.create(container, {
        ...CANVAS_SORTABLE_OPTIONS,
        group: {
            name: 'canvas',
            pull: true,
            put: ['widgets', 'canvas'],
        },
        onStart() {
            document.body.classList.add('pb-is-dragging');
        },
        onEnd() {
            document.body.classList.remove('pb-is-dragging');
        },
        onAdd(evt) {
            if (evt.item.dataset.type && !evt.item.dataset.elId) {
                _handleNewWidgetDrop(state, evt);
            } else {
                _handleCanvasMove(state);
            }
        },
        onUpdate() {
            _handleCanvasMove(state);
        },
    });

    _containerSortables.set(container, sortable);
}

export function destroyAllContainerSortables() {
    _containerSortables.forEach(s => s.destroy());
    _containerSortables.clear();
}

export function refreshSortables(state) {
    initContainerSortables(state);
}

export function _saveElementOrder(state) {
    const buildOrder = (elements) => {
        return elements.filter(el => el.id).map(el => ({
            id: el.id,
            children: el.children ? buildOrder(el.children) : [],
        }));
    };
    const order = buildOrder(state._lastElements || []);
    return apiFetch(`/page-builder/pages/${state.pageId}/elements/reorder`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ order }),
    }).catch(() => toastError('Falha ao reordenar'));
}

function _buildOrderFromDOM() {
    function buildContainer(container) {
        const items = [];
        container.querySelectorAll(':scope > .pb-el').forEach(child => {
            const childId = parseInt(child.dataset.elId);
            if (!childId) return;
            const childItem = { id: childId, children: [] };
            const own = child.querySelector(`.pb-el-children[data-parent-id="${childId}"]`);
            if (own) childItem.children = buildContainer(own);
            items.push(childItem);
        });
        return items;
    }
    const dz = document.getElementById('canvas-dropzone');
    return dz ? buildContainer(dz) : [];
}
