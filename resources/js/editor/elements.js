import state from './state.js';
import { structureIcon, apiFetch, showToast, toastError, escHtml } from './utils.js';
import { widgetLabel, elementName } from './i18n.js';
import { renderCanvas, renderMath } from './canvas.js';
import { pushHistory } from './history.js';
import { refreshSortables } from './dragdrop.js';
import { renderNavigator as renderNav, highlightNavItem } from './navigator.js';
import { loadControls } from './controls.js';
import { removeMultiToolbar, refreshMultiSelect } from './multi-select.js';

export function renderStructureWithSelect(elements, parentUl) {
    const ul = parentUl || document.getElementById('structure-tree');
    if (!parentUl) ul.innerHTML = '';
    (elements || []).forEach(el => {
        const li = document.createElement('li');
        li.className = 'pb-structure-item' + (state.selectedId === el.id ? ' active' : '');
        li.dataset.elId = el.id;
        li.innerHTML = `<span class="si-icon">${structureIcon(el.type)}</span><span>${escHtml(elementName(el))}</span><span class="si-type">${escHtml(widgetLabel(el.type))}</span>`;
        li.onclick = (e) => { e.stopPropagation(); selectElement(el.id); };
        ul.appendChild(li);
        if (el.children && el.children.length > 0) {
            const childUl = document.createElement('ul');
            childUl.className = 'pb-structure-children';
            li.appendChild(childUl);
            renderStructureWithSelect(el.children, childUl);
        }
    });
}

export function selectElement(id, ctrlKey) {
    if (ctrlKey) {
        if (state.multiSelected && state.multiSelected.has(id)) {
            state.multiSelected.delete(id);
        } else {
            if (!state.multiSelected) state.multiSelected = new Set();
            state.multiSelected.add(id);
        }
        refreshMultiSelect();
        return;
    }
    state.multiSelected = null;
    state.selectedId = id;
    document.querySelectorAll('.pb-el.selected, .pb-el.multi-selected').forEach(el => {
        el.classList.remove('selected');
        el.classList.remove('multi-selected');
    });
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
    if (el) el.classList.add('selected');
    const si = document.querySelector(`.pb-structure-item[data-el-id="${id}"]`);
    if (si) si.classList.add('active');
    highlightNavItem(id);
    loadControls(id);
    removeMultiToolbar();
}

export function loadElements() {
    return apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
        headers: { 'X-CSRF-TOKEN': state.csrf },
    })
        .then(data => {
            const prevSelected = state.selectedId;
            state._lastElements = data.elements || [];
            renderCanvas(state, state._lastElements);
            refreshSortables(state);
            renderMath();
            renderStructureWithSelect(state._lastElements);
            pushHistory(state, state._lastElements);
            renderNav(state);
            if (prevSelected && document.querySelector(`.pb-el[data-el-id="${prevSelected}"]`)) {
                selectElement(prevSelected);
            }
        })
        .catch(err => toastError('Falha ao carregar elementos: ' + (err.message || err)));
}

export function loadPageData() {
    showToast('Carregando dados da página...', 'info');
    apiFetch(`/page-builder/pages/${state.pageId}/data`)
        .then(data => { window._pageData = data.page; })
        .catch(() => toastError('Falha ao carregar dados da página'));
}

export function deleteElement(id) {
    if (!confirm('Excluir este elemento?')) return;
    apiFetch(`/page-builder/elements/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': state.csrf } })
        .then(() => {
            if (state.selectedId === id) { state.selectedId = null; document.getElementById('settings-empty').style.display = ''; document.getElementById('settings-form').classList.remove('active'); }
            loadElements();
        })
        .catch(() => toastError('Falha ao excluir elemento'));
}

export function duplicateElement(id) {
    apiFetch(`/page-builder/elements/${id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': state.csrf } })
        .then(() => loadElements())
        .catch(() => toastError('Falha ao duplicar elemento'));
}

export function deselectAll() {
    document.querySelectorAll('.pb-el.selected, .pb-el.multi-selected').forEach(el => {
        el.classList.remove('selected');
        el.classList.remove('multi-selected');
    });
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    document.getElementById('settings-empty').style.display = '';
    document.getElementById('settings-form').classList.remove('active');
    document.getElementById('page-settings-form').classList.remove('active');
}

export function navigateElements(direction) {
    const els = Array.from(document.querySelectorAll('#canvas-dropzone .pb-el'));
    if (!els.length) return;
    const currentIdx = els.findIndex(el => el.dataset.elId === String(state.selectedId));
    let nextIdx = currentIdx + direction;
    if (nextIdx < 0) nextIdx = els.length - 1;
    if (nextIdx >= els.length) nextIdx = 0;
    const nextId = els[nextIdx].dataset.elId;
    if (nextId) selectElement(parseInt(nextId));
}
