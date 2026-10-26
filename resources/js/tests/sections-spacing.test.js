import { describe, it, expect, beforeEach, vi } from 'vitest';
import state from '../editor/state.js';
import { renderCanvas, cssLength, wrapperStyleProps, elementHtml } from '../editor/canvas.js';
import { insertSectionBefore } from '../editor/dragdrop.js';
import { renderControls, refreshDeviceView } from '../editor/controls.js';

let calls;
const flush = (ms = 0) => new Promise(r => setTimeout(r, ms));
function mockFetch(responder = () => ({})) {
    calls = [];
    globalThis.fetch = vi.fn(async (url, opts = {}) => {
        const body = opts.body ? JSON.parse(opts.body) : null;
        calls.push({ url, method: opts.method || 'GET', body });
        return { ok: true, status: 200, redirected: false, json: async () => responder(url, body), text: async () => '' };
    });
}

const sections = () => [
    { id: 1, type: 'section', is_container: true, settings: {}, children: [] },
    { id: 2, type: 'section', is_container: true, settings: {}, children: [] },
    { id: 3, type: 'heading', is_container: false, settings: {} },
    { id: 4, type: 'section', is_container: true, settings: {}, children: [] },
];

describe('"+" between sections', () => {
    beforeEach(() => { document.body.innerHTML = '<div id="canvas-dropzone"></div>'; });

    it('appears only between two consecutive top-level sections', () => {
        renderCanvas({ renderMath() {}, onSelectElement() {}, setInsertTarget() {} }, sections());
        const zones = [...document.querySelectorAll('.pb-section-inserter')];
        expect(zones.map(z => z.dataset.beforeId)).toEqual(['2']);
        expect(zones[0].nextElementSibling.dataset.elId).toBe('2');
    });

    it('clicking it asks for a section before the next one', () => {
        const asked = [];
        renderCanvas({ renderMath() {}, onSelectElement() {}, setInsertTarget() {}, insertSectionBefore: id => asked.push(id) }, sections());
        document.querySelector('.pb-section-inserter-btn').click();
        expect(asked).toEqual([2]);
    });

    it('creates the section in place with a full-width column, then selects it', async () => {
        mockFetch((url, body) => ({ element: { id: body.type === 'section' ? 50 : 51 } }));
        const s = { pageId: 9, csrf: 'x', showToast() {}, loadElements: vi.fn(async () => {}), onSelectElement: vi.fn() };

        await insertSectionBefore(s, 2);

        expect(calls.map(c => c.body)).toEqual([
            { type: 'section', parent_id: null, insert_before_id: 2 },
            { type: 'column', parent_id: 50, settings: { column_width: 100 } },
        ]);
        expect(s.onSelectElement).toHaveBeenCalledWith(50);
    });
});

describe('sections default spacing', () => {
    it('new sections preview with 10px top and bottom margin, customizable', () => {
        expect(elementHtml({ id: 1, type: 'section', settings: {} })).toContain('margin:10px 0 10px 0');
        expect(elementHtml({ id: 1, type: 'section', settings: { margin_top: '30', margin_bottom: '0px' } })).toContain('margin:30px 0 0px 0');
    });
});

describe('units', () => {
    it.each([
        ['margin-top', '10', '10px'], ['padding', '8', '8px'], ['font-size', '16', '16px'], ['border-radius', '4', '4px'],
        ['margin-top', '1.5rem', '1.5rem'], ['width', '50%', '50%'], ['margin-top', '0', '0'], ['margin-top', 'auto', 'auto'],
        ['line-height', '1.5', '1.5'], ['z-index', '10', '10'], ['font-weight', '700', '700'], ['opacity', '0.5', '0.5'],
    ])('%s: %s -> %s', (prop, value, expected) => {
        expect(cssLength(prop, value)).toBe(expected);
    });

    it('wrapper styles become CSS like on the published page', () => {
        expect(wrapperStyleProps({ marginTop: '12', padding_left: '4px', hidden: true, typography: { a: 1 }, empty: '' }))
            .toEqual([['margin-top', '12px'], ['padding-left', '4px']]);
    });
});

describe('Padding & Margin control', () => {
    function setup(styles = {}) {
        mockFetch(() => ({ html: '<div>video</div>' }));
        document.body.innerHTML = `<div class="pb-settings"><div id="settings-body"></div></div>
            <div id="canvas-dropzone"></div><div id="settings-empty"></div><div id="settings-form" class="active"></div>`;
        const el = { id: 7, type: 'video', is_container: false, settings: {}, styles };
        Object.assign(state, {
            pageId: 1, csrf: 'x', cachedElementId: 7, selectedId: 7, activeTab: 'advanced', responsiveMode: 'desktop',
            cachedControls: { dimensions: { type: 'dimensions', label: 'Espaçamento interno e margem', tab: 'advanced' } },
            baseSettings: el.settings, baseStyles: el.styles, cachedResponsive: {}, _lastElements: [el], _timers: {},
            history: [], historyIndex: -1, renderMath() {}, onSelectElement() {}, setInsertTarget() {},
        });
        renderCanvas(state, state._lastElements);
        refreshDeviceView();
        renderControls();
    }
    const saves = () => calls.filter(c => c.method === 'PUT').map(c => c.body);

    it('shows the saved value of each side (it used to always open empty)', () => {
        setup({ marginTop: '12px', marginBottom: '20px', paddingLeft: '5px' });
        expect(document.getElementById('ctrl-marginTop').value).toBe('12px');
        expect(document.getElementById('ctrl-marginBottom').value).toBe('20px');
        expect(document.getElementById('ctrl-paddingLeft').value).toBe('5px');
        expect(document.getElementById('ctrl-paddingTop').value).toBe('');
    });

    it('a bare number is saved in px and applied to the element in the canvas', async () => {
        setup();
        const top = document.getElementById('ctrl-marginTop');
        top.value = '15';
        top.dispatchEvent(new Event('input'));
        await flush(350);

        // linked by default when empty: all four sides
        expect(saves()).toContainEqual({ styles: { marginTop: '15px' } });
        expect(saves()).toContainEqual({ styles: { marginLeft: '15px' } });
        const box = document.querySelector('.pb-el[data-el-id="7"] > .pb-el-content');
        expect(box.style.marginTop).toBe('15px');
        expect(box.style.marginLeft).toBe('15px');
    });

    it('starts unlinked when sides differ, so one side changes alone', async () => {
        setup({ marginTop: '10px', marginBottom: '30px' });
        const top = document.getElementById('ctrl-marginTop');
        top.value = '5';
        top.dispatchEvent(new Event('input'));
        await flush(350);
        expect(saves()).toEqual([{ styles: { marginTop: '5px' } }]);
    });

    it('the link button does not close the element being edited', async () => {
        setup();
        // the real global handler from index.js
        await import('../editor/index.js');
        const btn = document.querySelector('#settings-body button[title^="Valores"]');
        // users click the icon inside the button; the button then replaces that icon
        btn.querySelector('svg').dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await flush();
        expect(state.selectedId).toBe(7);
        expect(document.getElementById('settings-form').classList.contains('active')).toBe(true);
        expect(btn.title).toBe('Valores independentes');
    });

    it('no panel button that swaps its own icon closes the editor', async () => {
        setup();
        await import('../editor/index.js');
        // any control button that re-renders itself on click, without stopping propagation
        const btn = document.createElement('button');
        btn.innerHTML = '<svg><path/></svg>';
        btn.onclick = () => { btn.innerHTML = '<svg><circle/></svg>'; };
        document.getElementById('settings-body').appendChild(btn);

        btn.querySelector('path').dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await flush();

        expect(state.selectedId).toBe(7);
        expect(document.getElementById('settings-form').classList.contains('active')).toBe(true);
    });
});
