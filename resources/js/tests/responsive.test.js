import { describe, it, expect, beforeEach, vi } from 'vitest';
import state from '../editor/state.js';
import { valuesForDevice, elementHtml } from '../editor/canvas.js';
import { isDeviceScoped, updateStyle, updateSetting, refreshDeviceView } from '../editor/controls.js';

const heading = () => ({
    id: 7, type: 'heading', is_container: false,
    settings: { title: 'Oi', color: '#111111', alignment: 'left', margin_bottom_tablet: '4px' },
    styles: {},
    responsive_settings: { tablet: { settings: { color: '#ff0000' } }, mobile: { settings: { alignment: 'center' } } },
});

describe('valuesForDevice', () => {
    it('cascades desktop -> tablet -> mobile', () => {
        const el = heading();
        expect(valuesForDevice(el, 'desktop').settings.color).toBe('#111111');
        expect(valuesForDevice(el, 'tablet').settings.color).toBe('#ff0000');
        expect(valuesForDevice(el, 'mobile').settings.color).toBe('#ff0000');
        expect(valuesForDevice(el, 'mobile').settings.alignment).toBe('center');
        expect(valuesForDevice(el, 'tablet').settings.alignment).toBe('left');
    });

    it('reads values saved by the old "<key>_<device>" scheme', () => {
        expect(valuesForDevice(heading(), 'tablet').settings.margin_bottom).toBe('4px');
    });

    it('previews an uploaded video even if the type stayed "youtube"', () => {
        const html = elementHtml({ id: 1, type: 'video', settings: { video_type: 'youtube', video_url: '', video_file: '/storage/v.mp4' }, styles: {} });
        expect(html).toContain('<video src="/storage/v.mp4"');
        expect(html).not.toContain('Nenhum video selecionado');
    });

    it('lets the canvas preview the device being edited', () => {
        expect(elementHtml(heading(), 'desktop')).toContain('#111111');
        expect(elementHtml(heading(), 'tablet')).toContain('#ff0000');
    });
});

describe('device scoped saving', () => {
    let calls;
    beforeEach(() => {
        calls = [];
        globalThis.fetch = vi.fn(async (url, opts) => {
            calls.push({ url, body: JSON.parse(opts.body) });
            return { ok: true, redirected: false, json: async () => ({}) };
        });
        const el = heading();
        Object.assign(state, {
            pageId: 1, csrf: 'x', cachedElementId: 7, _lastElements: [el],
            cachedControls: { title: { type: 'text' }, color: { type: 'color', tab: 'style' }, alignment: { type: 'select' } },
            baseSettings: el.settings, baseStyles: el.styles, cachedResponsive: el.responsive_settings,
            responsiveMode: 'tablet', activeTab: 'style',
        });
        refreshDeviceView();
    });

    it('decides by the control tab, not the open panel tab', () => {
        expect(isDeviceScoped('color', true)).toBe(true);
        expect(isDeviceScoped('alignment', false)).toBe(true); // layout key on the content tab
        expect(isDeviceScoped('title', false)).toBe(false);    // text stays shared even with the Style tab open
        expect(isDeviceScoped('css_id', true)).toBe(false);
    });

    it('sends tablet styles with the device and keeps text global', async () => {
        await updateStyle('color', '#00ff00', 7, false);
        await updateSetting('title', 'Novo', 7, false);

        expect(calls[0].url).toBe('/page-builder/elements/7/styles');
        expect(calls[0].body).toEqual({ styles: { color: '#00ff00' }, device: 'tablet' });
        expect(calls[1].body).toEqual({ settings: { title: 'Novo' } });

        // local caches follow: tablet view changes, desktop untouched, canvas tree updated
        expect(state.cachedStyles.color).toBe('#00ff00');
        expect(state.baseSettings.title).toBe('Novo');
        expect(valuesForDevice(state._lastElements[0], 'desktop').settings.color).toBe('#111111');
        expect(valuesForDevice(state._lastElements[0], 'tablet').styles.color).toBe('#00ff00');
    });

    it('saves desktop values without a device', async () => {
        state.responsiveMode = 'desktop';
        await updateStyle('color', '#123456', 7, false);
        expect(calls[0].body).toEqual({ styles: { color: '#123456' } });
    });
});
