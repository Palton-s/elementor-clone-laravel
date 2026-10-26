import { describe, it, expect, beforeEach, vi } from 'vitest';
import state from '../editor/state.js';
import { renderControls, refreshDeviceView } from '../editor/controls.js';

let calls;
const flush = (ms = 0) => new Promise(r => setTimeout(r, ms));

function setup(controls, settings = {}, extra = {}) {
    calls = [];
    globalThis.fetch = vi.fn(async (url, opts = {}) => {
        const body = opts.body instanceof FormData ? Object.fromEntries(opts.body.entries()) : (opts.body ? JSON.parse(opts.body) : null);
        calls.push({ url, method: opts.method || 'GET', body });
        const json = url.includes('/upload-video') ? { url: '/storage/v/aula.mp4' }
            : url.includes('/upload') ? { url: '/storage/img/' + (body.image?.name || 'x') }
            : { html: '<div></div>' };
        return { ok: true, status: 200, redirected: false, json: async () => json, text: async () => '' };
    });
    document.body.innerHTML = '<div id="settings-body"></div><div class="pb-el" data-el-id="7" data-el-type="x"><div class="pb-el-content"></div></div>';
    Object.assign(state, {
        pageId: 1, csrf: 'x', cachedElementId: 7, cachedControls: controls, activeTab: 'content',
        baseSettings: settings, baseStyles: {}, cachedResponsive: {}, responsiveMode: 'desktop',
        _lastElements: [{ id: 7, type: 'x', settings, styles: {} }], _timers: {}, history: [], historyIndex: -1, ...extra,
    });
    refreshDeviceView();
    renderControls();
}

const saves = () => calls.filter(c => c.method === 'PUT').map(c => c.body);

describe('icon picker', () => {
    beforeEach(() => setup({ icon: { type: 'icon', label: 'Icon' } }, { icon: 'fas fa-star' }));
    const search = () => document.querySelector('#settings-body input[type="text"]');

    it('typing a search term filters without saving a broken icon', async () => {
        search().value = 'heart';
        search().dispatchEvent(new Event('input'));
        await flush(350);

        expect(saves()).toEqual([]);
        const titles = [...document.querySelectorAll('#settings-body button[title]')].map(b => b.title);
        expect(titles.length).toBeGreaterThan(0);
        expect(titles.every(t => t.includes('heart'))).toBe(true);
    });

    it('picking an icon from the grid saves its full class', async () => {
        document.querySelector('#settings-body button[title="fas fa-heart"]').click();
        await flush();
        expect(saves()).toEqual([{ settings: { icon: 'fas fa-heart' } }]);
    });

    it('a pasted full class is saved', async () => {
        search().value = 'fab fa-github';
        search().dispatchEvent(new Event('input'));
        await flush(350);
        expect(saves()).toEqual([{ settings: { icon: 'fab fa-github' } }]);
    });
});

describe('gallery', () => {
    beforeEach(() => setup({ images: { type: 'gallery', label: 'Images' } }, { images: [{ url: '/a.jpg', alt: '' }] }));

    it('uploads several files in order and appends them', async () => {
        const input = document.querySelector('#settings-body input[type="file"]');
        expect(input.multiple).toBe(true);
        const files = [new File(['1'], 'um.png', { type: 'image/png' }), new File(['2'], 'dois.png', { type: 'image/png' }), new File(['x'], 'nota.txt', { type: 'text/plain' })];
        Object.defineProperty(input, 'files', { value: files, configurable: true });
        input.dispatchEvent(new Event('change'));
        for (let i = 0; i < 10; i++) await flush();

        const uploads = calls.filter(c => c.url === '/page-builder/upload');
        expect(uploads.map(u => u.body.image.name)).toEqual(['um.png', 'dois.png']); // the .txt is skipped
        const last = saves().at(-1).settings.images;
        expect(last.map(i => i.url)).toEqual(['/a.jpg', '/storage/img/um.png', '/storage/img/dois.png']);
        expect(last[1].alt).toBe('um');
    });

    it('still accepts an image by URL', async () => {
        vi.spyOn(window, 'prompt').mockReturnValue('https://x.com/foto.jpg');
        [...document.querySelectorAll('#settings-body button')].find(b => b.textContent.includes('Por URL')).click();
        await flush();
        expect(saves().at(-1).settings.images.at(-1)).toEqual({ url: 'https://x.com/foto.jpg', alt: '' });
    });
});

describe('video upload', () => {
    it('switches the widget type to "upload" before saving the file', async () => {
        setup({ video_type: { type: 'select', options: ['youtube', 'upload'] }, video_file: { type: 'video', label: 'Upload' } }, { video_type: 'youtube' });
        const input = document.querySelector('#settings-body input[type="file"]');
        Object.defineProperty(input, 'files', { value: [new File(['v'], 'aula.mp4', { type: 'video/mp4' })], configurable: true });
        input.dispatchEvent(new Event('change'));
        for (let i = 0; i < 10; i++) await flush();

        expect(saves()).toEqual([{ settings: { video_type: 'upload' } }, { settings: { video_file: '/storage/v/aula.mp4' } }]);
        expect(document.getElementById('ctrl-video_type').value).toBe('upload');
    });

    it('refuses files over the 50 MB limit before uploading', async () => {
        setup({ video_file: { type: 'video', label: 'Upload' } });
        const big = new File(['v'], 'grande.mp4', { type: 'video/mp4' });
        Object.defineProperty(big, 'size', { value: 60 * 1024 * 1024 });
        const input = document.querySelector('#settings-body input[type="file"]');
        Object.defineProperty(input, 'files', { value: [big], configurable: true });
        input.dispatchEvent(new Event('change'));
        await flush();

        expect(calls.filter(c => c.url.includes('upload'))).toEqual([]);
        expect(document.querySelector('.pb-toast').textContent).toContain('50 MB');
    });
});

describe('per-device panel', () => {
    it('shows the device banner and a reset button only for overridden values', async () => {
        setup({ color: { type: 'color', label: 'Cor', tab: 'style' }, margin: { type: 'text', label: 'Margem', tab: 'style' } },
            { color: '#111111' },
            { activeTab: 'style', responsiveMode: 'tablet', cachedResponsive: { tablet: { styles: { color: '#ff0000' } } } });

        expect(document.querySelector('.pb-device-banner').textContent).toContain('Tablet');
        const chips = document.querySelectorAll('.pb-device-chip');
        expect(chips).toHaveLength(2);
        expect(document.querySelectorAll('.pb-device-chip.is-set')).toHaveLength(1);

        document.querySelector('.pb-device-reset').click();
        await flush();
        expect(saves().at(-1)).toEqual({ styles: { color: null }, device: 'tablet' });
    });

    it('desktop shows no device UI', () => {
        setup({ color: { type: 'color', label: 'Cor', tab: 'style' } }, {}, { activeTab: 'style' });
        expect(document.querySelector('.pb-device-banner')).toBeNull();
        expect(document.querySelector('.pb-device-chip')).toBeNull();
    });
});
