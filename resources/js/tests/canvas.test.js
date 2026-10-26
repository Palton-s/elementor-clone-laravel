import { describe, it, expect, beforeEach } from 'vitest';
import { renderCanvas, elementHtml, columnPercent } from '../editor/canvas.js';

const state = (extra = {}) => ({ renderMath() {}, onSelectElement() {}, setInsertTarget() {}, responsiveMode: 'desktop', ...extra });

// Every widget offered in the panel
const WIDGET_TYPES = ['section', 'inner_section', 'column', 'heading', 'text', 'image', 'button', 'video', 'divider', 'spacer',
    'icon', 'gallery', 'form', 'tabs', 'accordion', 'icon_box', 'image_box', 'carousel', 'counter', 'progress_bar',
    'social_icons', 'testimonial', 'price_table', 'countdown', 'google_maps', 'mega_menu', 'lottie', 'callout', 'table', 'math'];

describe('elementHtml', () => {
    it.each(WIDGET_TYPES)('%s renders a preview with and without settings', (type) => {
        expect(() => elementHtml({ id: 1, type, settings: {}, styles: {} })).not.toThrow();
        expect(() => elementHtml({ id: 1, type })).not.toThrow();
        const html = elementHtml({ id: 1, type, settings: {}, styles: {} });
        expect(html).toContain('pb-el-toolbar');
        expect(html).toContain('pb-el-content');
    });

    it('escapes the element name in the toolbar', () => {
        const html = elementHtml({ id: 1, type: 'heading', name: '<img src=x onerror=alert(1)>', settings: {} });
        expect(html).not.toContain('<img src=x');
    });

    it('uses lucide icons for the drag handle and actions', () => {
        const html = elementHtml({ id: 3, type: 'heading', settings: {} });
        expect(html).toMatch(/pb-el-drag[^>]*><svg/);
        expect(html).toContain('editor.duplicateElement(3)');
        expect(html).toContain('editor.deleteElement(3)');
    });
});

describe('columnPercent', () => {
    it.each([
        [{ column_width: 'col-6' }, 50], [{ column_width: 'col-4' }, 33.33], [{ column_width: 25 }, 25],
        [{ column_width: '40' }, 40], [{ column_width: '33.5' }, 33.5], [{ column_width: 500 }, 100], [{}, 33.33],
    ])('%j -> %d%%', (settings, expected) => {
        expect(columnPercent(settings)).toBe(expected);
    });
});

describe('renderCanvas', () => {
    beforeEach(() => { document.body.innerHTML = '<div id="canvas-dropzone"></div>'; });

    const tree = () => [{ id: 1, type: 'section', is_container: true, settings: {}, children: [
        { id: 2, type: 'column', is_container: true, settings: { column_width: 50 }, children: [{ id: 4, type: 'heading', is_container: false, settings: { title: 'Olá' } }] },
        { id: 3, type: 'column', is_container: true, settings: { column_width: 'col-6' }, children: [] },
    ] }];

    it('shows the empty state without elements', () => {
        renderCanvas(state(), []);
        expect(document.getElementById('empty-canvas')).not.toBeNull();
    });

    it('lays columns out in a row with their width on the wrapper', () => {
        renderCanvas(state(), tree());
        const row = document.querySelector('.pb-el-children[data-parent-id="1"]');
        expect(row.classList.contains('pb-row')).toBe(true);
        expect(row.parentElement.classList.contains('pb-section-content')).toBe(true);
        const cols = row.querySelectorAll(':scope > .pb-el[data-el-type="column"]');
        expect([...cols].map(c => c.style.maxWidth)).toEqual(['50%', '50%']);
    });

    it('puts column contents inside the column body', () => {
        renderCanvas(state(), tree());
        const body = document.querySelector('.pb-el-children[data-parent-id="2"]');
        expect(body.parentElement.classList.contains('pb-column-content')).toBe(true);
        expect(body.querySelector(':scope > .pb-el[data-el-id="4"]')).not.toBeNull();
    });

    it('gives every container a "+" that picks it as insert target', () => {
        const picked = [];
        renderCanvas(state({ setInsertTarget: id => picked.push(id) }), tree());
        expect(document.querySelector('.pb-empty-drop[data-parent-id="3"]:not(.pb-add-inside)')).not.toBeNull();
        expect(document.querySelector('.pb-empty-drop.pb-add-inside[data-parent-id="2"]')).not.toBeNull();
        document.querySelector('.pb-empty-drop[data-parent-id="3"]').click();
        expect(picked).toEqual([3]);
    });

    it('stacks columns on mobile unless a mobile width was set', () => {
        const els = tree();
        els[0].children[1].responsive_settings = { mobile: { settings: { column_width: 40 } } };
        renderCanvas(state({ responsiveMode: 'mobile' }), els);
        const [a, b] = document.querySelectorAll('.pb-el[data-el-type="column"]');
        expect(a.style.maxWidth).toBe('100%');
        expect(b.style.maxWidth).toBe('40%');
    });

    it('passes Ctrl/Cmd to selection so multi-select works', () => {
        const calls = [];
        renderCanvas(state({ onSelectElement: (id, ctrl) => calls.push([id, ctrl]) }), tree());
        document.querySelector('.pb-el[data-el-id="4"]').dispatchEvent(new MouseEvent('click', { bubbles: true, ctrlKey: true }));
        expect(calls).toEqual([[4, true]]);
    });
});

import { optionEntries } from '../editor/controls.js';
import { widgetLabel, elementName, optionText } from '../editor/i18n.js';

describe('Portuguese UI', () => {
    it('shows widget types and old English element names in Portuguese', () => {
        expect(widgetLabel('spacer')).toBe('Espaçador');
        expect(elementName({ type: 'section', name: 'Section' })).toBe('Seção');
        expect(elementName({ type: 'column', name: 'Column (copy)' })).toBe('Coluna (cópia)');
        expect(elementName({ type: 'heading', name: 'Meu título' })).toBe('Meu título');
        expect(elementName({ type: 'gallery' })).toBe('Galeria');
    });

    it('select options keep their values and show translated text', () => {
        expect(optionEntries({ options: ['left', 'center'], option_labels: { left: 'Esquerda', center: 'Centro' } }))
            .toEqual([['left', 'Esquerda'], ['center', 'Centro']]);
        // value => text objects used to crash the panel (forEach on an object)
        expect(optionEntries({ options: { row: 'Horizontal', column: 'Vertical' } }))
            .toEqual([['row', 'Horizontal'], ['column', 'Vertical']]);
        expect(optionText('no-repeat')).toBe('Não repetir');
    });

    it('canvas toolbar and previews are in Portuguese', () => {
        const html = elementHtml({ id: 1, type: 'spacer', name: 'Spacer', settings: {} });
        expect(html).toContain('<span class="pb-el-name">Espaçador</span>');
        expect(html).toContain('<span class="pb-el-type">Espaçador</span>');
        expect(elementHtml({ id: 2, type: 'section', settings: {} })).toContain('>Seção<');
    });
});
