import { describe, it, expect, beforeEach, vi } from 'vitest';
import state from '../editor/state.js';
import { selectElement } from '../editor/elements.js';
import { setResponsive } from '../editor/ui.js';
import { renderCanvas } from '../editor/canvas.js';

beforeEach(() => {
    globalThis.fetch = vi.fn(async () => ({ ok: true, status: 200, redirected: false, json: async () => ({ widget: { controls: {} }, element: {} }) }));
    document.body.innerHTML = `
        <div class="pb-toolbar"><button data-mode="desktop" class="active"></button><button data-mode="tablet"></button><button data-mode="mobile"></button></div>
        <button class="pb-resp-tab active" data-device="desktop"></button><button class="pb-resp-tab" data-device="tablet"></button>
        <div id="canvas" class="pb-canvas"></div><div id="device-frame"></div><div id="device-notch"></div><div id="device-label"></div>
        <div id="canvas-dropzone"></div><div id="settings-empty"></div><div id="settings-form"></div><div id="settings-body"></div>
        <div id="settings-title"></div><div id="settings-type"></div><div id="editor-tabs"></div>`;
    Object.assign(state, { multiSelected: null, selectedId: null, cachedElementId: null, responsiveMode: 'desktop', renderMath() {}, onSelectElement() {}, setInsertTarget() {} });
});

describe('multi-select (Ctrl+click)', () => {
    it('toggles elements in and out of the selection and shows the bulk toolbar', () => {
        renderCanvas(state, [{ id: 1, type: 'heading', settings: {} }, { id: 2, type: 'text', settings: {} }]);

        selectElement(1, true);
        selectElement(2, true);
        expect([...state.multiSelected]).toEqual([1, 2]);
        expect(document.querySelectorAll('.pb-el.multi-selected')).toHaveLength(2);
        expect(document.getElementById('pb-multi-toolbar').textContent).toContain('2 selecionados');

        selectElement(2, true);
        expect([...state.multiSelected]).toEqual([1]);
        expect(document.getElementById('pb-multi-toolbar')).toBeNull();
    });

    it('a plain click clears the multi-selection', () => {
        renderCanvas(state, [{ id: 1, type: 'heading', settings: {} }, { id: 2, type: 'text', settings: {} }]);
        selectElement(1, true); selectElement(2, true);
        selectElement(2);
        expect(state.multiSelected).toBeNull();
        expect(state.selectedId).toBe(2);
        expect(document.querySelector('.pb-el.selected').dataset.elId).toBe('2');
    });
});

describe('device switch', () => {
    it('syncs both selectors, frames the canvas and re-renders with device values', () => {
        state._lastElements = [{ id: 1, type: 'heading', settings: { color: '#111111' }, responsive_settings: { tablet: { settings: { color: '#ff0000' } } } }];
        state.renderCanvas = (els) => renderCanvas(state, els);

        setResponsive('tablet');

        expect(state.responsiveTab).toBe('tablet');
        expect(document.querySelector('.pb-toolbar [data-mode="tablet"]').classList.contains('active')).toBe(true);
        expect(document.querySelector('.pb-resp-tab[data-device="tablet"]').classList.contains('active')).toBe(true);
        expect(document.getElementById('canvas').classList.contains('is-tablet')).toBe(true);
        expect(document.body.classList.contains('responsive-tablet')).toBe(true);
        expect(document.getElementById('device-label').textContent).toContain('768px');
        expect(document.getElementById('canvas-dropzone').innerHTML).toContain('#ff0000');

        setResponsive('desktop');
        expect(document.body.classList.contains('responsive-tablet')).toBe(false);
        expect(document.getElementById('canvas-dropzone').innerHTML).toContain('#111111');
    });
});
