import { describe, it, expect, beforeEach, vi } from 'vitest';
import { apiFetch, showToast, toastError, toastSuccess, structureIcon } from '../editor/utils.js';
import { icon, widgetIcon, hydrateIcons } from '../editor/icons.js';
import { pushHistory, undo, redo, _findElement, updateUndoButtons } from '../editor/history.js';

const jsonResponse = (body, init = {}) => ({
    ok: init.ok ?? true, status: init.status ?? 200, redirected: init.redirected ?? false,
    json: async () => body, text: async () => JSON.stringify(body),
});

describe('apiFetch', () => {
    beforeEach(() => { globalThis.fetch = vi.fn(); });

    it('returns the parsed body and sends JSON + same-origin credentials', async () => {
        fetch.mockResolvedValue(jsonResponse({ ok: 1 }));
        await expect(apiFetch('/x', { headers: { 'X-A': 'b' } })).resolves.toEqual({ ok: 1 });
        const [, opts] = fetch.mock.calls[0];
        expect(opts.headers).toMatchObject({ Accept: 'application/json', 'X-A': 'b' });
        expect(opts.credentials).toBe('same-origin');
    });

    it('turns Laravel validation errors into a readable message', async () => {
        fetch.mockResolvedValue(jsonResponse({ errors: { title: ['O título é obrigatório'] } }, { ok: false, status: 422 }));
        await expect(apiFetch('/x')).rejects.toThrow('O título é obrigatório');
    });

    it('uses the server message when there is one', async () => {
        fetch.mockResolvedValue(jsonResponse({ message: 'Arquivo grande demais' }, { ok: false, status: 413 }));
        await expect(apiFetch('/x')).rejects.toThrow('Arquivo grande demais');
    });

    it('reports an expired session when the request was redirected to login', async () => {
        fetch.mockResolvedValue(jsonResponse({}, { redirected: true }));
        await expect(apiFetch('/x')).rejects.toThrow(/Sessão expirada/);
    });

    it('falls back to the HTTP status for non-JSON errors', async () => {
        fetch.mockResolvedValue({ ok: false, status: 500, redirected: false, json: async () => { throw new Error('no json'); }, text: async () => 'boom' });
        await expect(apiFetch('/x')).rejects.toThrow('HTTP 500');
    });
});

describe('toasts', () => {
    beforeEach(() => { document.body.innerHTML = ''; });

    it('shows the message as text (no HTML injection) with an icon for errors and successes', () => {
        toastError('<img src=x onerror=alert(1)>');
        const t = document.querySelector('.pb-toast');
        expect(t.textContent).toContain('<img src=x onerror=alert(1)>');
        expect(t.querySelector('img')).toBeNull();
        expect(t.querySelector('svg.pb-icon')).not.toBeNull();

        toastSuccess('ok');
        expect(document.querySelectorAll('.pb-toast').length).toBeGreaterThan(0);
        showToast('neutro');
        expect([...document.querySelectorAll('.pb-toast')].pop().querySelector('svg')).toBeNull();
    });
});

describe('icons', () => {
    it('renders lucide icons as inline svg with the requested size', () => {
        const svg = icon('trash', 20);
        expect(svg).toMatch(/^<svg /);
        expect(svg).toContain('width="20"');
        expect(svg).toContain('stroke="currentColor"');
        expect(svg).toContain('aria-hidden="true"');
        expect(icon('nao-existe')).toBe('');
    });

    it('has a distinct icon per widget and a fallback', () => {
        expect(widgetIcon('section')).not.toBe(widgetIcon('column'));
        expect(widgetIcon('nao-existe')).toMatch(/^<svg/);
        expect(structureIcon('heading')).toMatch(/^<svg/);
    });

    it('hydrates data-icon placeholders and widget panel items', () => {
        document.body.innerHTML = '<button><i data-icon="save" data-icon-size="22"></i> Salvar</button>'
            + '<div class="pb-widget-item" data-type="image"><span class="pb-widget-icon"></span></div>';
        hydrateIcons();
        expect(document.querySelector('[data-icon]')).toBeNull();
        expect(document.querySelector('button svg').getAttribute('width')).toBe('22');
        expect(document.querySelector('.pb-widget-icon svg')).not.toBeNull();
    });
});

describe('history', () => {
    const makeState = () => ({
        history: [], historyIndex: -1, pageId: 1, csrf: 'x', selectedId: null,
        renderCanvas: vi.fn(), renderMath: vi.fn(), renderStructure: vi.fn(), renderNavigator: vi.fn(), loadControls: vi.fn(),
    });
    beforeEach(() => {
        globalThis.fetch = vi.fn().mockResolvedValue(jsonResponse({}));
        document.body.innerHTML = '<button id="pb-undo"></button><button id="pb-redo"></button><div id="settings-empty"></div><div id="settings-form"></div>';
    });

    it('undo/redo walk the snapshots and restore them on the server', () => {
        const s = makeState();
        pushHistory(s, [{ id: 1 }]);
        pushHistory(s, [{ id: 1 }, { id: 2 }]);

        undo(s);
        expect(s._lastElements).toEqual([{ id: 1 }]);
        expect(s.renderCanvas).toHaveBeenLastCalledWith([{ id: 1 }]);
        expect(fetch.mock.calls.at(-1)[0]).toBe('/page-builder/pages/1/elements/restore-snapshot');

        redo(s);
        expect(s._lastElements).toHaveLength(2);
    });

    it('a new change after undo drops the redo branch', () => {
        const s = makeState();
        pushHistory(s, ['a']); pushHistory(s, ['b']); undo(s);
        pushHistory(s, ['c']);
        expect(s.history).toEqual([['a'], ['c']]);
        redo(s);
        expect(s.historyIndex).toBe(1);
    });

    it('snapshots are copies, not live references', () => {
        const s = makeState();
        const els = [{ id: 1, name: 'a' }];
        pushHistory(s, els);
        els[0].name = 'mudou';
        expect(s.history[0][0].name).toBe('a');
    });

    it('undo is a no-op at the start and buttons reflect availability', () => {
        const s = makeState();
        undo(s);
        expect(s.renderCanvas).not.toHaveBeenCalled();
        pushHistory(s, []);
        updateUndoButtons(s);
        expect(parseFloat(document.getElementById('pb-undo').style.opacity)).toBe(0.4);
    });

    it('clears the selection if the selected element no longer exists', () => {
        const s = makeState();
        pushHistory(s, []); pushHistory(s, [{ id: 9 }]);
        s.selectedId = 9;
        undo(s);
        expect(s.selectedId).toBeNull();
        expect(_findElement([{ id: 1, children: [{ id: 2 }] }], 2)).toEqual({ id: 2 });
    });
});
