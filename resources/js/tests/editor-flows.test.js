import { describe, it, expect, beforeEach, vi } from 'vitest';
import state from '../editor/state.js';
import { renderCanvas } from '../editor/canvas.js';
import * as dd from '../editor/dragdrop.js';
import { renderNavigator, _navMoveElement, _navMoveRelative, _navPasteAfter, highlightNavItem } from '../editor/navigator.js';
import { insertGlobalWidget, saveAsGlobalWidget } from '../editor/actions.js';

let posts;
const respond = (body = {}) => ({ ok: true, status: 200, redirected: false, json: async () => body, text: async () => '' });
const flush = () => new Promise(r => setTimeout(r, 0));

function tree() {
    return [{ id: 1, type: 'section', name: 'Seção', is_container: true, settings: {}, children: [
        { id: 2, type: 'column', name: 'Coluna A', is_container: true, settings: {}, children: [] },
        { id: 3, type: 'column', name: 'Coluna B', is_container: true, settings: {}, children: [
            { id: 4, type: 'text', name: 'Texto 1', is_container: false, settings: {} },
            { id: 5, type: 'text', name: 'Texto 2', is_container: false, settings: {} },
        ] },
    ] }, { id: 6, type: 'heading', name: 'Rodapé', is_container: false, settings: {} }];
}

function setup(extraBody = {}) {
    posts = [];
    globalThis.fetch = vi.fn(async (url, opts = {}) => {
        if (opts.method && opts.method !== 'GET') posts.push({ url, method: opts.method, body: opts.body ? JSON.parse(opts.body) : null });
        return respond(typeof extraBody === 'function' ? extraBody(url, opts) : extraBody);
    });
    document.body.innerHTML = `<button class="pb-panel-tab" data-tab="widgets"></button>
        <div id="panel-widgets"><div class="pb-widget-grid"><div class="pb-widget-item" data-type="heading"><span class="pb-widget-icon"></span></div></div></div>
        <div id="canvas-dropzone"></div><div id="navigator-body"></div><ul id="structure-tree"></ul>`;
    Object.assign(state, {
        pageId: 1, csrf: 'x', selectedId: null, insertTarget: null, _lastElements: tree(), responsiveMode: 'desktop',
        renderMath() {}, showToast() {}, onSelectElement: vi.fn(), loadElements: vi.fn(async () => {}),
        renderStructure() {}, duplicateElement() {}, deleteElement() {},
    });
    state.renderCanvas = (els) => { renderCanvas(state, els); dd.refreshSortables(state); };
    state._saveElementOrder = () => dd._saveElementOrder(state);
    state.setInsertTarget = (pid) => dd.setInsertTarget(state, pid);
    state.renderCanvas(state._lastElements);
}

// --------------------------------------------------------------------------- click to insert

describe('click-to-insert', () => {
    beforeEach(() => { setup({ element: { id: 99 } }); dd.bindDragDrop(state); });
    const widget = () => document.querySelector('.pb-widget-item');
    const lastPost = () => posts.at(-1).body;

    it('"+" marks the container and the widget goes inside it', async () => {
        document.querySelector('.pb-empty-drop[data-parent-id="2"]').click();
        expect(document.querySelector('.pb-empty-drop[data-parent-id="2"]').classList.contains('pb-insert-target')).toBe(true);
        widget().click(); await flush();
        expect(lastPost()).toEqual({ type: 'heading', parent_id: 2, insert_before_id: null });
        expect(state.onSelectElement).toHaveBeenCalledWith(99);
        expect(state.insertTarget).toBeNull();
    });

    it('without "+": inside a selected container, after a selected element, or at the end', async () => {
        state.selectedId = 3; widget().click(); await flush();
        expect(lastPost()).toMatchObject({ parent_id: 3, insert_before_id: null });
        state.selectedId = 4; widget().click(); await flush();
        expect(lastPost()).toMatchObject({ parent_id: 3, insert_before_id: 5 });
        state.selectedId = 5; widget().click(); await flush();
        expect(lastPost()).toMatchObject({ parent_id: 3, insert_before_id: null });
        state.selectedId = null; widget().click(); await flush();
        expect(lastPost()).toMatchObject({ parent_id: null, insert_before_id: null });
    });

    it('clicking the same "+" twice or pressing Esc cancels', () => {
        const plus = document.querySelector('.pb-empty-drop[data-parent-id="2"]');
        plus.click(); plus.click();
        expect(state.insertTarget).toBeNull();
        plus.click();
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        expect(state.insertTarget).toBeNull();
        expect(document.querySelector('.pb-insert-target')).toBeNull();
    });

    it('the target survives a canvas re-render', () => {
        document.querySelector('.pb-empty-drop[data-parent-id="2"]').click();
        state.renderCanvas(state._lastElements);
        expect(document.querySelector('.pb-empty-drop[data-parent-id="2"]').classList.contains('pb-insert-target')).toBe(true);
    });

    it('saving the order sends the whole tree with ids only', async () => {
        await dd._saveElementOrder(state);
        expect(posts.at(-1).url).toBe('/page-builder/pages/1/elements/reorder');
        expect(lastPost().order[0]).toEqual({ id: 1, children: [{ id: 2, children: [] }, { id: 3, children: [{ id: 4, children: [] }, { id: 5, children: [] }] }] });
    });
});

// ----------------------------------------------------------------------------- navigator

describe('layers panel', () => {
    beforeEach(() => setup());
    const node = (id) => document.querySelector(`.pb-nav-node[data-el-id="${id}"]`);

    it('nests nodes with their children and gives empty containers a drop list', () => {
        renderNavigator(state);
        expect(node(4).parentElement.closest('.pb-nav-node')).toBe(node(3));
        expect(node(2).querySelector(':scope > .pb-nav-children')).not.toBeNull();
        expect(node(6).querySelector(':scope > .pb-nav-children')).toBeNull();
        expect(node(1).querySelector('.nav-icon svg')).not.toBeNull();
    });

    it('collapsing a node is remembered across re-renders', () => {
        renderNavigator(state);
        node(3).querySelector('.nav-toggle').click();
        renderNavigator(state);
        expect(node(3).querySelector(':scope > .pb-nav-children').style.display).toBe('none');
        node(3).querySelector('.nav-toggle').click();
    });

    it('search keeps the ancestors of a match visible', () => {
        renderNavigator(state);
        const input = document.getElementById('nav-search-input');
        input.value = 'texto 2';
        input.dispatchEvent(new Event('input'));
        expect(node(5).style.display).toBe('');
        expect(node(3).style.display).toBe('');
        expect(node(2).style.display).toBe('none');
        expect(node(6).style.display).toBe('none');
    });

    it('moves an element into a container and saves the order', async () => {
        _navMoveElement(state, 6, 2);
        expect(state._lastElements[0].children[0].children.map(e => e.id)).toEqual([6]);
        expect(state._lastElements.map(e => e.id)).toEqual([1]);
        await flush();
        expect(posts.at(-1).url).toContain('/elements/reorder');
    });

    it('refuses to move into a non-container or into its own descendant', () => {
        const before = JSON.stringify(state._lastElements);
        _navMoveElement(state, 6, 4);   // text is not a container
        _navMoveElement(state, 1, 3);   // section into its own column
        expect(JSON.stringify(state._lastElements)).toBe(before);
    });

    it('move up / down swaps siblings', () => {
        _navMoveRelative(state, 5, -1);
        expect(state._lastElements[0].children[1].children.map(e => e.id)).toEqual([5, 4]);
        _navMoveRelative(state, 5, -1); // already first: no-op
        expect(state._lastElements[0].children[1].children.map(e => e.id)).toEqual([5, 4]);
    });

    it('paste after posts a snapshot where the copy has no ids', async () => {
        state._clipboard = { id: 1, type: 'section', settings: {}, children: [{ id: 2, type: 'column', settings: {} }] };
        _navPasteAfter(state, 6);
        await flush();
        const snap = posts.at(-1);
        expect(snap.url).toContain('/restore-snapshot');
        const pasted = snap.body.elements[2];
        expect(pasted.id).toBeUndefined();
        expect(pasted.children[0].id).toBeUndefined();
    });

    it('highlights the selected element without re-rendering', () => {
        renderNavigator(state);
        highlightNavItem(5);
        expect(document.querySelector('.pb-nav-item.active').dataset.elId).toBe('5');
    });
});

// ------------------------------------------------------------------------ global widgets

describe('global widgets', () => {
    it('lists widgets and clicking one inserts it (regression: used to fail on JSON quotes)', async () => {
        setup((url) => url === '/page-builder/global-widgets'
            ? { global_widgets: [{ id: 7, title: 'CTA "Promo"', type: 'heading', settings: { title: 'Compre "já"' }, status: 'published' }] }
            : {});
        await insertGlobalWidget();
        const item = document.querySelector('.gw-item');
        expect(item).not.toBeNull();
        expect(item.textContent).toContain('CTA "Promo"');

        item.click(); await flush();
        const body = posts.at(-1).body;
        expect(body.type).toBe('heading');
        expect(body.settings).toEqual({ title: 'Compre "já"', _global_widget_id: 7 });
        expect(document.querySelector('.modal-overlay')).toBeNull();
    });

    it('saving an element as global widget posts its data (regression: double JSON parsing)', async () => {
        setup((url) => url === '/page-builder/elements/4'
            ? { element: { type: 'text', settings: { content: '<p>x</p>' }, content: {}, styles: {} } }
            : { global_widget: { title: 'Meu bloco' } });
        state.selectedId = 4;
        vi.spyOn(window, 'prompt').mockReturnValue('Meu bloco');

        await saveAsGlobalWidget();

        expect(posts.at(-1)).toMatchObject({ url: '/page-builder/global-widgets', method: 'POST' });
        expect(posts.at(-1).body).toMatchObject({ title: 'Meu bloco', type: 'text', settings: { content: '<p>x</p>' } });
    });
});
