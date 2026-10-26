import state from './state.js';
import { apiFetch, toastError } from './utils.js';
import { icon } from './icons.js';

export function refreshMultiSelect() {
    document.querySelectorAll('.pb-el').forEach(el => {
        el.classList.remove('multi-selected');
    });
    if (state.multiSelected && state.multiSelected.size > 0) {
        state.multiSelected.forEach(id => {
            const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
            if (el) el.classList.add('multi-selected');
        });
        showMultiToolbar();
    } else {
        removeMultiToolbar();
    }
}

export function showMultiToolbar() {
    removeMultiToolbar();
    const count = state.multiSelected ? state.multiSelected.size : 0;
    if (count < 2) return;
    const bar = document.createElement('div');
    bar.className = 'pb-multi-toolbar';
    bar.id = 'pb-multi-toolbar';
    bar.innerHTML = `
        <span class="pb-multi-count">${count} selecionados</span>
        <button class="pb-multi-btn" onclick="window.pageBuilderEditor.duplicateSelected()">${icon('copy', 14)} Duplicar</button>
        <button class="pb-multi-btn danger" onclick="window.pageBuilderEditor.deleteSelected()">${icon('trash', 14)} Excluir</button>
        <button class="pb-multi-btn" onclick="window.pageBuilderEditor.clearMultiSelect()">${icon('x', 14)} Limpar</button>
    `;
    document.body.appendChild(bar);
}

export function removeMultiToolbar() {
    const bar = document.getElementById('pb-multi-toolbar');
    if (bar) bar.remove();
}

export function duplicateSelected() {
    if (!state.multiSelected || state.multiSelected.size === 0) return;
    const ids = [...state.multiSelected];
    Promise.all(ids.map(id =>
        apiFetch(`/page-builder/elements/${id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': state.csrf } })
    )).then(() => { state.multiSelected = null; removeMultiToolbar(); state.loadElements(); })
     .catch(() => toastError('Falha ao duplicar elementos'));
}

export function deleteSelected() {
    if (state.multiSelected && state.multiSelected.size > 0) {
        if (!confirm(`Excluir ${state.multiSelected.size} elementos?`)) return;
        const ids = [...state.multiSelected];
        Promise.all(ids.map(id =>
            apiFetch(`/page-builder/elements/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': state.csrf } })
        )).then(() => { state.multiSelected = null; removeMultiToolbar(); state.loadElements(); })
         .catch(() => toastError('Falha ao excluir elementos'));
    } else if (state.selectedId) {
        import('./elements.js').then(m => m.deleteElement(state.selectedId));
    }
}

export function clearMultiSelect() {
    state.multiSelected = null;
    document.querySelectorAll('.pb-el.multi-selected').forEach(el => el.classList.remove('multi-selected'));
    removeMultiToolbar();
}
