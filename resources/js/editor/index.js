import state from './state.js';
import { escHtml, showToast, toastError, toastSuccess, apiFetch } from './utils.js';
import { renderCanvas, renderMath } from './canvas.js';
import { pushHistory, undo, redo, updateUndoButtons } from './history.js';
import { refreshSortables, bindDragDrop, _saveElementOrder, setInsertTarget } from './dragdrop.js';
import { openHtmlImportModal } from './html-import.js';
import { toggleNavigator, renderNavigator as renderNav, _showCanvasContext, _showNavContext, _hideNavContext, _hideCanvasContext, _navMoveElement, _navMoveRelative, _navPasteAfter, _startNavRename } from './navigator.js';
import { loadControls, switchEditorTab } from './controls.js';
import { selectElement, loadElements, loadPageData, deleteElement, duplicateElement, deselectAll, renderStructureWithSelect } from './elements.js';
import { showRevisionHistory } from './revisions.js';
import { collabJoin, collabHeartbeat, bindCollabPresence } from './collaboration.js';
import { toggleFinder, openFindReplace } from './finder.js';
import { initOnboarding } from './onboarding.js';
import { refreshMultiSelect, removeMultiToolbar, duplicateSelected, deleteSelected, clearMultiSelect } from './multi-select.js';
import { showSiteSettings, hideSiteSettings, loadGlobalSettings } from './global-settings.js';
import { showPageSettings, hidePageSettings } from './page-settings.js';
import { save, publish, autoSave, copyStyles, pasteStyles, saveAsTemplate, copyHtml, saveAsGlobalWidget, insertGlobalWidget, syncGlobalWidgets, _insertGlobalWidget } from './actions.js';
import { hydrateIcons } from './icons.js';
import { bindKeyboard, bindZoom, toggleFullscreen, switchTab, bindInlineEditing, bindWidgetSearch, bindResizablePanels, observeCanvas, setZoom, setResponsive, setResponsiveTab } from './ui.js';

const editor = {
    init(pageId, csrfToken) {
        state.pageId = pageId;
        state.csrf = csrfToken;
        hydrateIcons();

        state.renderCanvas = (els) => { renderCanvas(state, els); refreshSortables(state); };
        state.renderMath = () => renderMath();
        state.renderStructure = (els) => renderStructureWithSelect(els);
        state.renderNavigator = (s) => renderNav(s || state);
        // ctrlKey (Ctrl/Cmd+click on the canvas) toggles multi-selection
        state.onSelectElement = (id, ctrlKey) => selectElement(id, ctrlKey);
        state.loadControls = (id) => loadControls(id);
        state.showToast = (msg, type) => showToast(msg, type);
        state.toastError = (msg) => toastError(msg);
        state.loadElements = () => loadElements();
        state.duplicateElement = (id) => duplicateElement(id);
        state.deleteElement = (id) => deleteElement(id);
        state.showCanvasContext = (x, y, elId) => _showCanvasContext(state, x, y, elId);
        state._saveElementOrder = () => _saveElementOrder(state);
        state.setInsertTarget = (parentId) => setInsertTarget(state, parentId);

        loadElements();
        loadPageData();
        bindDragDrop(state);
        bindKeyboard();
        bindInlineEditing();
        bindZoom();
        autoSave();
        observeCanvas();
        collabJoin();
        collabHeartbeat();
        bindCollabPresence();
        bindWidgetSearch();
        bindResizablePanels();
        initOnboarding();
        loadGlobalSettings();
    },

    undo() { undo(state); },
    redo() { redo(state); },
    save(silent) { save(silent); },
    publish() { publish(); },
    setResponsive(mode) { setResponsive(mode); },
    setResponsiveTab(device) { setResponsiveTab(device); },
    switchTab(tab) { switchTab(tab); },
    switchEditorTab(tab) { switchEditorTab(tab); },
    zoomIn() { setZoom(state.zoomLevel + 10); },
    zoomOut() { setZoom(state.zoomLevel - 10); },
    zoomReset() { setZoom(100); },
    toggleFullscreen() { toggleFullscreen(); },
    toggleNavigator() { toggleNavigator(state); },
    selectElement(id) { selectElement(id); },
    duplicateElement(id) { duplicateElement(id); },
    deleteElement(id) { deleteElement(id); },
    deleteSelected() { if (state.selectedId) deleteElement(state.selectedId); },
    showPageSettings() { showPageSettings(); },
    hidePageSettings() { hidePageSettings(); },
    openFindReplace() { openFindReplace(); },
    exportPage() {
        // Elements are persisted on every change, so the zip already matches the canvas.
        // A download link avoids unloading the editor (which would end the collaboration session).
        showToast('Gerando .zip da página...', 'info');
        const a = document.createElement('a');
        a.href = '/page-builder/pages/' + state.pageId + '/export-zip';
        a.download = '';
        document.body.appendChild(a);
        a.click();
        a.remove();
    },
    saveAsTemplate() { saveAsTemplate(); },
    copyHtml() { copyHtml(); },
    importHtml() { openHtmlImportModal(state.csrf); },
    copyStyles() { copyStyles(); },
    pasteStyles() { pasteStyles(); },
    duplicateSelected() { if (state.selectedId) duplicateElement(state.selectedId); },
    showSiteSettings() { showSiteSettings(); },
    hideSiteSettings() { hideSiteSettings(); },
    showRevisionHistory() { showRevisionHistory(); },
    saveAsGlobalWidget() { saveAsGlobalWidget(); },
    insertGlobalWidget() { insertGlobalWidget(); },
    syncGlobalWidgets() { syncGlobalWidgets(); },
};

editor.duplicateSelected = duplicateSelected;
editor.deleteSelected = deleteSelected;
editor.clearMultiSelect = clearMultiSelect;

window._insertGlobalWidget = _insertGlobalWidget;
window.editor = editor;
window.pageBuilderEditor = editor;

const start = () => {
    const pageIdEl = document.querySelector('[data-page-id]');
    const csrfEl = document.querySelector('[data-csrf]');
    if (pageIdEl && csrfEl) {
        editor.init(parseInt(pageIdEl.dataset.pageId), csrfEl.dataset.csrf);
    }
};
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.pb-el') && !e.target.closest('.pb-structure-item') && !e.target.closest('.pb-settings') && !e.target.closest('.pb-toolbar') && !e.target.closest('.pb-nav-context') && !e.target.closest('.pb-multi-toolbar')) {
        document.querySelectorAll('.pb-el.selected, .pb-el.multi-selected').forEach(el => {
            el.classList.remove('selected');
            el.classList.remove('multi-selected');
        });
        document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
        state.selectedId = null;
        state.multiSelected = null;
        removeMultiToolbar();
        document.getElementById('settings-empty').style.display = '';
        document.getElementById('settings-form').classList.remove('active');
    }
});
