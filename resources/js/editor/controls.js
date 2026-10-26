import state from './state.js';
import { escHtml, apiFetch, toastError } from './utils.js';
import { renderMath, valuesForDevice, deviceOverrides, LEGACY_RESPONSIVE_KEYS, applyWrapperStyles, cssLength } from './canvas.js';
import { icon } from './icons.js';
import { widgetLabel, elementName, optionText } from './i18n.js';
import { snapshotHistory } from './history.js';
import { _colorInput } from './color-picker.js';
import { uploadImageFile, uploadVideoFile } from './upload.js';

// Content-tab controls that are layout, so they are also saved per device
const DEVICE_LAYOUT_KEYS = ['align', 'alignment', 'text_align', 'column_width', 'columns', 'column_gap', 'column_direction',
    'gap', 'layout', 'flex_direction', 'flex_wrap', 'justify_content', 'align_items', 'vertical_alignment', 'content_position',
    'icon_size', 'image_size', 'size', 'width', 'height', 'max_width', 'min_height', 'content_width', 'position', 'icon_position'];
// Never per device: identity, behaviour and the visibility flags themselves
const DEVICE_EXCLUDED_KEYS = ['css_id', 'css_classes', 'custom_css', 'custom_js', 'animation', 'scroll_animation', 'visibility',
    'visibility_desktop', 'visibility_tablet', 'visibility_mobile', 'link', 'url', 'link_target', 'link_new_tab', 'name'];

const DEVICE_LABELS = { desktop: 'Computador', tablet: 'Tablet', mobile: 'Celular' };

export function currentDevice() {
    return state.responsiveMode || 'desktop';
}

export function isDeviceScoped(key, isStyle) {
    if (DEVICE_EXCLUDED_KEYS.includes(key)) return false;
    if (isStyle) return true;
    // Decide by the control's own tab (inline text editing saves content while any panel tab is open);
    // sub-keys of compound controls are not in the list, so they follow the open tab
    const ctrl = (state.cachedControls || {})[key];
    const tab = ctrl ? (ctrl.tab || 'content') : state.activeTab;
    return tab !== 'content' || DEVICE_LAYOUT_KEYS.includes(key) || LEGACY_RESPONSIVE_KEYS.includes(key);
}

function currentRaw() {
    return { settings: state.baseSettings || {}, styles: state.baseStyles || {}, responsive_settings: state.cachedResponsive || {} };
}

// cachedSettings/cachedStyles always hold what the controls should show for the active device
export function refreshDeviceView() {
    const view = valuesForDevice(currentRaw(), currentDevice());
    state.cachedSettings = view.settings;
    state.cachedStyles = view.styles;
}

function overriddenBucket(key, device) {
    if (device === 'desktop') return null;
    const o = deviceOverrides(currentRaw(), device);
    if (key in o.styles) return 'styles';
    if (key in o.settings) return 'settings';
    return null;
}

export function loadControls(id) {
    apiFetch(`/page-builder/elements/${id}/controls`)
        .then(data => {
            if (data.error) { console.error('Controls error:', data.error); return; }
            const widget = data.widget;
            const element = data.element;
            document.getElementById('settings-empty').style.display = 'none';
            document.getElementById('settings-form').classList.add('active');
            document.getElementById('settings-title').textContent = elementName({ ...element, type: widget.type });
            document.getElementById('settings-type').textContent = widgetLabel(widget.type);
            state.cachedControls = widget.controls || {};
            state.baseSettings = element.settings || {};
            state.baseStyles = element.styles || {};
            state.cachedResponsive = (element.responsive_settings && !Array.isArray(element.responsive_settings)) ? element.responsive_settings : {};
            refreshDeviceView();
            state.cachedElementId = id;
            state.activeTab = 'content';
            syncEditorTabs();
            renderControls();
            renderMath();
        })
        .catch(err => { console.error('loadControls failed:', err); toastError('Falha ao carregar controles: ' + (err.message || err)); });
}

// settings/styles are already the active device's view (see refreshDeviceView)
export function getResponsiveValue(key, ctrl, settings, styles, tab) {
    if (tab === 'style') {
        return styles[key] !== undefined ? styles[key] : (ctrl.default !== undefined ? ctrl.default : '');
    } else if (tab === 'advanced') {
        return styles[key] !== undefined ? styles[key] : (settings[key] !== undefined ? settings[key] : (ctrl.default !== undefined ? ctrl.default : ''));
    } else {
        return settings[key] !== undefined ? settings[key] : (ctrl.default !== undefined ? ctrl.default : '');
    }
}

export function renderControls() {
    const body = document.getElementById('settings-body');
    body.innerHTML = '';
    const controls = state.cachedControls || {};
    const settings = state.cachedSettings || {};
    const styles = state.cachedStyles || {};
    const elementId = state.cachedElementId;
    const tab = state.activeTab;
    const device = currentDevice();
    if (device !== 'desktop') body.appendChild(deviceBanner(device, tab));
    const filtered = {};
    for (const [key, ctrl] of Object.entries(controls)) {
        const ctrlTab = ctrl.tab || 'content';
        if (ctrlTab === tab) filtered[key] = ctrl;
    }
    const sections = groupControls(filtered);
    for (const [section, ctrls] of Object.entries(sections)) {
        const secDiv = document.createElement('div');
        secDiv.className = 'pb-settings-section';
        if (section !== '_default') {
            const title = document.createElement('div');
            title.className = 'pb-settings-section-title';
            title.textContent = section;
            secDiv.appendChild(title);
        }
        ctrls.forEach(([key, ctrl]) => {
            let val = getResponsiveValue(key, ctrl, settings, styles, tab);
            const control = document.createElement('div');
            control.className = 'pb-control';
            const label = document.createElement('label');
            label.textContent = ctrl.label || key;
            label.htmlFor = 'ctrl-' + key;
            if (device !== 'desktop' && isDeviceScoped(key, (ctrl.tab || 'content') === 'style')) {
                label.appendChild(deviceChip(key, device, elementId));
            }
            control.appendChild(label);
            control.appendChild(createInput(key, ctrl, val, elementId));
            secDiv.appendChild(control);
        });
        body.appendChild(secDiv);
    }
}

export function switchEditorTab(tab) {
    state.activeTab = tab;
    syncEditorTabs();
    renderControls();
}

export function syncEditorTabs() {
    const tab = state.activeTab;
    document.querySelectorAll('#editor-tabs .pb-editor-tab').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.etab === tab);
    });
}

export function groupControls(controls) {
    const sections = { '_default': [] };
    for (const [key, ctrl] of Object.entries(controls)) {
        const section = ctrl.section || '_default';
        if (!sections[section]) sections[section] = [];
        sections[section].push([key, ctrl]);
    }
    return sections;
}

export function _debouncedSetting(key, elementId, fn) {
    const id = `s_${key}_${elementId}`;
    clearTimeout(state._timers[id]);
    state._timers[id] = setTimeout(fn, 300);
}

export function _debouncedStyle(key, elementId, fn) {
    const id = `st_${key}_${elementId}`;
    clearTimeout(state._timers[id]);
    state._timers[id] = setTimeout(fn, 300);
}

function findInTree(list, id) {
    for (const el of list || []) {
        if (el.id === id) return el;
        const found = findInTree(el.children, id);
        if (found) return found;
    }
    return null;
}

// Applies a saved value to the local caches (controls view + canvas tree); device null = desktop
function applyLocal(bucket, key, value, device, elementId) {
    if (device) {
        const resp = state.cachedResponsive = (state.cachedResponsive && !Array.isArray(state.cachedResponsive)) ? state.cachedResponsive : {};
        resp[device] = resp[device] || {};
        resp[device][bucket] = { ...(resp[device][bucket] || {}) };
        if (value === null) delete resp[device][bucket][key];
        else resp[device][bucket][key] = value;
        if (value === null) {
            // the server also drops the old "<key>_<device>" value
            delete (state.baseSettings || {})[key + '_' + device];
            delete (state.baseStyles || {})[key + '_' + device];
        }
    } else {
        const base = bucket === 'styles' ? (state.baseStyles = state.baseStyles || {}) : (state.baseSettings = state.baseSettings || {});
        base[key] = value;
    }
    if (state.cachedElementId === elementId) refreshDeviceView();

    const el = findInTree(state._lastElements, elementId);
    if (el && state.cachedElementId === elementId) {
        el.settings = state.baseSettings;
        el.styles = state.baseStyles;
        el.responsive_settings = state.cachedResponsive;
    }
}

function saveValue(bucket, key, value, elementId, reload, scopedDevice) {
    state.dirty = true;
    applyLocal(bucket, key, value, scopedDevice, elementId);
    const body = { [bucket]: { [key]: value } };
    if (scopedDevice) body.device = scopedDevice;
    return apiFetch(`/page-builder/elements/${elementId}/${bucket}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify(body),
    })
    .then(() => {
        const el = findInTree(state._lastElements, elementId);
        // A column's width lives on the canvas wrapper, which only a full canvas render updates
        if (el && el.type === 'column' && state.renderCanvas) {
            state.renderCanvas(state._lastElements);
            document.querySelector(`.pb-el[data-el-id="${elementId}"]`)?.classList.add('selected');
        } else if (reload) {
            reloadElement(elementId);
        }
        // Margins, paddings and other wrapper styles live on the element box, not in the widget HTML
        if (el && bucket === 'styles') {
            applyWrapperStyles(document.querySelector(`.pb-el[data-el-id="${elementId}"]`), valuesForDevice(el, currentDevice()).styles);
        }
        snapshotHistory(state);
    })
    .catch(err => { console.error('save ' + bucket + ' failed:', err); toastError('Falha ao salvar: ' + (err.message || err)); });
}

export function updateSetting(key, value, elementId, reload = true) {
    const device = currentDevice();
    const scoped = device !== 'desktop' && isDeviceScoped(key, false) ? device : null;
    return saveValue('settings', key, value, elementId, reload, scoped);
}

export function updateStyle(key, value, elementId, reload = true) {
    const device = currentDevice();
    const scoped = device !== 'desktop' && isDeviceScoped(key, true) ? device : null;
    return saveValue('styles', key, value, elementId, reload, scoped);
}

// Removes this device's value so it inherits again (mobile -> tablet -> desktop)
export function resetDeviceValue(key, elementId) {
    const device = currentDevice();
    const bucket = overriddenBucket(key, device);
    if (!bucket) return;
    saveValue(bucket, key, null, elementId, true, device).then(() => renderControls());
}

function deviceBanner(device, tab) {
    const b = document.createElement('div');
    b.className = 'pb-device-banner';
    const scope = tab === 'content'
        ? 'Textos e mídias valem para todos os dispositivos; alinhamento e tamanhos marcados com o selo valem só aqui.'
        : 'Os estilos alterados aqui valem só para ' + DEVICE_LABELS[device].toLowerCase() + (device === 'tablet' ? ' (o celular herda, se não tiver valor próprio).' : '.');
    b.innerHTML = `<span class="pb-device-banner-icon">${icon(device === 'tablet' ? 'tablet' : 'smartphone', 15)}</span><div><strong>Editando: ${DEVICE_LABELS[device]}</strong><span></span></div>`;
    b.querySelector('span:last-child').textContent = scope;
    return b;
}

function deviceChip(key, device, elementId) {
    const wrap = document.createElement('span');
    const overridden = !!overriddenBucket(key, device);
    wrap.className = 'pb-device-chip' + (overridden ? ' is-set' : '');
    wrap.title = overridden
        ? 'Valor próprio do ' + DEVICE_LABELS[device].toLowerCase()
        : 'Herdado de ' + (device === 'mobile' ? 'tablet/computador' : 'computador') + ' — alterar cria um valor só para ' + DEVICE_LABELS[device].toLowerCase();
    wrap.innerHTML = icon(device === 'tablet' ? 'tablet' : 'smartphone', 11);
    if (overridden) {
        const reset = document.createElement('button');
        reset.type = 'button';
        reset.className = 'pb-device-reset';
        reset.title = 'Voltar a herdar';
        reset.innerHTML = icon('undo', 11);
        reset.onclick = (e) => { e.preventDefault(); e.stopPropagation(); resetDeviceValue(key, elementId); };
        wrap.appendChild(reset);
    }
    return wrap;
}

export function reloadElement(id) {
    const device = currentDevice();
    apiFetch(`/page-builder/elements/${id}/render` + (device !== 'desktop' ? `?device=${device}` : ''))
        .then(data => {
            const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
            if (el) {
                const elType = el.dataset.elType;
                if (elType === 'section' || elType === 'column') {
                    const wrapper = el.querySelector('.pb-section-editor, .pb-column-editor');
                    if (wrapper) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(`<div>${data.html}</div>`, 'text/html');
                        const newWrapper = doc.body.firstChild.firstElementChild;
                        if (newWrapper) {
                            const existingChildren = el.querySelector('.pb-el-children');
                            const sectionContent = newWrapper.querySelector('.pb-section-content, .pb-column-content');
                            if (existingChildren && sectionContent) {
                                sectionContent.appendChild(existingChildren);
                            }
                            wrapper.replaceWith(newWrapper);
                        }
                    } else {
                        el.innerHTML = data.html;
                    }
                    renderMath();
                } else {
                    const oldContent = el.querySelector('.pb-el-content');
                    if (oldContent) oldContent.innerHTML = data.html;
                    else el.innerHTML = `<div class="pb-el-content">${data.html}</div>`;
                    renderMath();
                }
            }
        })
        .catch(err => { console.error('reloadElement failed:', err); toastError('Falha ao recarregar elemento'); });
}

/**
 * [value, text] pairs for a select. Options come as a list (['left', 'right']) or as a
 * value => text object ({ row: 'Horizontal' }); option_labels holds the translated texts.
 */
export function optionEntries(ctrl) {
    const opts = ctrl.options || [];
    const labels = ctrl.option_labels || {};
    const pairs = Array.isArray(opts) ? opts.map(v => [String(v), String(v)]) : Object.entries(opts).map(([v, t]) => [String(v), String(t)]);
    return pairs.map(([v, t]) => [v, labels[v] ?? t]);
}

function createInput(key, ctrl, value, elementId) {
    const isStyle = ctrl.tab === 'style';
    const saveFn = (k, v) => isStyle ? updateStyle(k, v, elementId) : updateSetting(k, v, elementId);
    const debouncedSave = (k, fn) => isStyle ? _debouncedStyle(k, elementId, fn) : _debouncedSetting(k, elementId, fn);

    const types = {
        text: () => {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;gap:.25rem;align-items:stretch';
            const inp = document.createElement('input');
            inp.type = 'text'; inp.id = `ctrl-${key}`; inp.value = value || '';
            inp.spellcheck = false;
            inp.style.cssText = 'flex:1;min-width:0';
            inp.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
            wrap.appendChild(inp);
            const tagBtn = document.createElement('button');
            tagBtn.type = 'button'; tagBtn.textContent = '{}';
            tagBtn.title = 'Inserir tag dinâmica';
            tagBtn.style.cssText = 'padding:0 6px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-accent);cursor:pointer;font-size:10px;font-weight:700;font-family:monospace';
            tagBtn.onclick = (e) => { e.preventDefault(); showTagPicker(e.target.getBoundingClientRect().left, e.target.getBoundingClientRect().bottom, inp); };
            wrap.appendChild(tagBtn);
            return wrap;
        },
        number: () => {
            const inp = document.createElement('input');
            inp.type = 'number'; inp.id = `ctrl-${key}`; inp.value = value;
            if (ctrl.min !== undefined) inp.min = ctrl.min;
            if (ctrl.max !== undefined) inp.max = ctrl.max;
            inp.oninput = (e) => debouncedSave(key, () => saveFn(key, parseFloat(e.target.value) || 0));
            return inp;
        },
        textarea: () => {
            const ta = document.createElement('textarea');
            ta.id = `ctrl-${key}`; ta.value = typeof value === 'string' ? value : '';
            ta.spellcheck = false;
            ta.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
            return ta;
        },
        select: () => {
            const sel = document.createElement('select');
            sel.id = `ctrl-${key}`;
            optionEntries(ctrl).forEach(([optValue, optLabel]) => {
                const o = document.createElement('option');
                o.value = optValue; o.textContent = optLabel;
                if (String(optValue) === String(value)) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = (e) => saveFn(key, e.target.value);
            return sel;
        },
        color: () => _colorInput(key, value, saveFn, elementId),
        boolean: () => {
            const container = document.createElement('div');
            container.style.cssText = 'display:flex;align-items:center;gap:.5rem';
            const cb = document.createElement('input');
            cb.type = 'checkbox'; cb.id = `ctrl-${key}`; cb.checked = !!value;
            cb.onchange = (e) => saveFn(key, e.target.checked);
            container.appendChild(cb);
            return container;
        },
        url: () => {
            const inp = document.createElement('input');
            inp.type = 'url'; inp.id = `ctrl-${key}`; inp.value = value || '';
            inp.placeholder = 'https://...';
            inp.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
            return inp;
        },
        image: () => createImageInput(key, value, saveFn, elementId),
        video: () => createVideoInput(key, value, saveFn, elementId),
        wysiwyg: () => createWysiwygInput(key, value, saveFn, elementId),
        icon: () => createIconInput(key, value, saveFn, debouncedSave),
        gallery: () => createGalleryInput(key, value, saveFn),
        repeater: () => createRepeaterInput(key, value, ctrl, saveFn),
        typography: () => createTypographyInput(key, value, elementId),
        background: () => createBackgroundInput(key, value, elementId),
        border: () => createBorderInput(key, value, elementId),
        box_shadow: () => createBoxShadowInput(key, value, elementId),
        dimensions: () => createDimensionsInput(key, value, elementId),
        hover: () => createHoverInput(key, value, elementId),
        custom_css: () => {
            const ta = document.createElement('textarea');
            ta.id = `ctrl-${key}`;
            ta.value = typeof value === 'string' ? value : '';
            ta.placeholder = 'Ex: color: red !important;\nbackground: #fff;';
            ta.spellcheck = false;
            ta.style.cssText = 'width:100%;padding:.45rem .6rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:.78rem;min-height:80px;font-family:"SF Mono",Menlo,Monaco,Consolas,monospace;resize:vertical;box-sizing:border-box';
            ta.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
            return ta;
        },
        animation: () => createAnimationInput(key, value, saveFn),
        visibility: () => createVisibilityInput(key, value, saveFn),
        gradient: () => createGradientInput(key, value, elementId),
        scroll_animation: () => createScrollAnimationInput(key, value, saveFn),
        text_shadow: () => createTextShadowInput(key, value, elementId),
        text_stroke: () => createTextStrokeInput(key, value, elementId),
        column_width: () => createColumnWidthInput(key, value, elementId),
    };
    return (types[ctrl.type] || types.text)();
}

function showTagPicker(x, y, inputEl) {
    const existing = document.getElementById('pb-tag-picker');
    if (existing) existing.remove();

    const picker = document.createElement('div');
    picker.id = 'pb-tag-picker';
    picker.style.cssText = `position:fixed;top:${y}px;left:${x}px;z-index:9999;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.15);padding:.5rem;min-width:240px;max-height:320px;overflow-y:auto`;

    fetch('/page-builder/dynamic-tags')
        .then(r => r.json())
        .then(data => {
            const groups = data.groups || {};
            let html = '<div style="font-size:.7rem;font-weight:600;color:var(--pb-text2);padding:.25rem .5rem .5rem;border-bottom:1px solid var(--pb-border);margin-bottom:.25rem">Dynamic Tags</div>';
            for (const [key, group] of Object.entries(groups)) {
                html += `<div style="font-size:.65rem;font-weight:600;color:var(--pb-accent);padding:.35rem .5rem .15rem">${group.label}</div>`;
                group.tags.forEach(t => {
                    html += `<div class="pb-tag-item" data-tag="${t.tag}" style="padding:.35rem .5rem;border-radius:4px;cursor:pointer;font-size:.78rem;display:flex;justify-content:space-between;transition:background .1s" title="${t.description || ''}"><span>{{ ${t.tag} }}</span><span style="font-size:.65rem;color:var(--pb-text2)">${t.label}</span></div>`;
                });
            }
            picker.innerHTML = html;
            picker.querySelectorAll('.pb-tag-item').forEach(item => {
                item.onmouseenter = () => item.style.background = 'var(--pb-surface2)';
                item.onmouseleave = () => item.style.background = 'transparent';
                item.onclick = () => {
                    const tag = '{{ ' + item.dataset.tag + ' }}';
                    if (inputEl) {
                        inputEl.focus();
                        if (inputEl.contentEditable === 'true') {
                            document.execCommand('insertText', false, tag);
                        } else {
                            const start = inputEl.selectionStart;
                            const end = inputEl.selectionEnd;
                            const val = inputEl.value;
                            inputEl.value = val.substring(0, start) + tag + val.substring(end);
                            inputEl.selectionStart = inputEl.selectionEnd = start + tag.length;
                            inputEl.dispatchEvent(new Event('input', {bubbles: true}));
                        }
                    }
                    picker.remove();
                };
            });
        })
        .catch(() => { picker.innerHTML = '<div style="padding:.5rem;color:var(--pb-danger);font-size:.78rem">Não foi possível carregar as tags</div>'; });

    document.body.appendChild(picker);

    document.addEventListener('click', function closePicker(e) {
        if (!picker.contains(e.target)) {
            picker.remove();
            document.removeEventListener('click', closePicker);
        }
    });
}

function createImageInput(key, value, saveFn, elementId) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    const currentUrl = value && value.url ? value.url : '';
    const dropZone = document.createElement('div');
    dropZone.style.cssText = 'border:2px dashed var(--pb-border);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;transition:all .2s;background:var(--pb-bg);position:relative';
    dropZone.innerHTML = `<div style="margin-bottom:.35rem;opacity:.5">${icon('image-plus', 26)}</div><div style="font-size:.72rem;color:var(--pb-text2)"><strong style="color:var(--pb-accent);cursor:pointer">Clique para selecionar</strong><br>ou arraste uma imagem aqui</div><div style="font-size:.65rem;color:var(--pb-text2);margin-top:.3rem">ou cole (Ctrl+V)</div>`;
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
    fileInput.style.display = 'none';
    const preview = document.createElement('div');
    preview.style.cssText = 'border-radius:6px;overflow:hidden;background:var(--pb-bg);min-height:50px;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--pb-text2);border:1px solid var(--pb-border)';
    const urlRow = document.createElement('div');
    urlRow.style.cssText = 'display:flex;gap:.35rem';
    const urlInput = document.createElement('input');
    urlInput.type = 'url'; urlInput.placeholder = 'Ou cole uma URL...';
    urlInput.value = currentUrl;
    urlInput.style.cssText = 'flex:1;padding:.4rem .55rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:.75rem';
    const updatePreview = (url) => {
        if (url) preview.innerHTML = `<img src="${escHtml(url)}" style="width:100%;max-height:100px;object-fit:contain;border-radius:4px">`;
        else preview.textContent = 'Nenhuma imagem';
    };
    const updateSetting = (url) => {
        const alt = (value && value.alt) || '';
        const w = (value && value.width) || 800;
        const h = (value && value.height) || 600;
        saveFn(key, { url, alt, width: w, height: h });
        updatePreview(url);
    };
    if (currentUrl) updatePreview(currentUrl);
    dropZone.appendChild(fileInput);
    dropZone.onclick = () => fileInput.click();
    fileInput.onchange = () => {
        const file = fileInput.files[0];
        if (!file) return;
        uploadImageFile(file, (url) => { updateSetting(url); urlInput.value = url; });
    };
    dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--pb-accent)'; dropZone.style.background = 'var(--pb-primary-light)'; };
    dropZone.ondragleave = () => { dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)'; };
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) uploadImageFile(file, (url) => { updateSetting(url); urlInput.value = url; });
    };
    urlInput.onchange = () => updateSetting(urlInput.value);
    urlRow.appendChild(urlInput);
    container.appendChild(dropZone);
    container.appendChild(preview);
    container.appendChild(urlRow);
    return container;
}

function createVideoInput(key, value, saveFn, elementId) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    const currentUrl = value || '';
    const dropZone = document.createElement('div');
    dropZone.style.cssText = 'border:2px dashed var(--pb-border);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;transition:all .2s;background:var(--pb-bg);position:relative';
    dropZone.innerHTML = `<div style="margin-bottom:.35rem;opacity:.5">${icon('video', 26)}</div><div style="font-size:.72rem;color:var(--pb-text2)"><strong style="color:var(--pb-accent);cursor:pointer">Clique para selecionar</strong><br>ou arraste um vídeo aqui</div><div style="font-size:.65rem;color:var(--pb-text2);margin-top:.3rem">MP4, WebM, OGG (max 50MB)</div>`;
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'video/mp4,video/webm,video/ogg';
    fileInput.style.display = 'none';
    const preview = document.createElement('div');
    preview.style.cssText = 'border-radius:6px;overflow:hidden;background:var(--pb-bg);min-height:50px;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--pb-text2);border:1px solid var(--pb-border)';
    const updatePreview = (url) => {
        if (url) preview.innerHTML = `<video src="${escHtml(url)}" style="width:100%;max-height:120px;object-fit:contain;border-radius:4px" controls></video>`;
        else preview.textContent = 'Nenhum vídeo selecionado';
    };
    if (currentUrl) updatePreview(currentUrl);
    // An upload only plays when the widget type is "upload"; switch it, then save the file
    // (sequentially, so the two saves don't overwrite each other on the server)
    const onUploaded = (url) => {
        updatePreview(url);
        const typeSelect = document.getElementById('ctrl-video_type');
        if (typeSelect) typeSelect.value = 'upload';
        Promise.resolve(key === 'video_file' ? updateSetting('video_type', 'upload', elementId, false) : null)
            .then(() => saveFn(key, url));
    };
    dropZone.appendChild(fileInput);
    dropZone.onclick = () => fileInput.click();
    fileInput.onchange = () => {
        const file = fileInput.files[0];
        if (!file) return;
        uploadVideoFile(file, onUploaded);
        fileInput.value = '';
    };
    dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--pb-accent)'; dropZone.style.background = 'var(--pb-primary-light)'; };
    dropZone.ondragleave = () => { dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)'; };
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('video/')) uploadVideoFile(file, onUploaded);
    };
    container.appendChild(dropZone);
    container.appendChild(preview);
    return container;
}

function createWysiwygInput(key, value, saveFn, elementId) {
    const wrap = document.createElement('div');
    wrap.style.cssText = 'display:flex;flex-direction:column;border:1px solid var(--pb-border);border-radius:6px;overflow:hidden;background:var(--pb-bg)';
    const toolbar = document.createElement('div');
    toolbar.style.cssText = 'display:flex;flex-wrap:wrap;gap:2px;padding:4px 6px;background:var(--pb-surface2);border-bottom:1px solid var(--pb-border)';
    const content = document.createElement('div');
    content.contentEditable = 'true';
    content.id = `ctrl-${key}`;
    content.innerHTML = typeof value === 'string' ? value : '<p></p>';
    content.style.cssText = 'min-height:120px;max-height:400px;overflow-y:auto;padding:8px 10px;font-size:13px;line-height:1.6;color:var(--pb-text);outline:none';
    content.innerHTML = content.innerHTML || '<p></p>';

    const execCmd = (cmd, val) => { content.focus(); document.execCommand(cmd, false, val || null); };
    const makeBtn = (label, title, cmd, val) => {
        const b = document.createElement('button');
        b.type = 'button'; b.innerHTML = label; b.title = title;
        b.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:13px;font-weight:600';
        b.onmouseenter = () => { b.style.background = 'var(--pb-border)'; };
        b.onmouseleave = () => { b.style.background = 'transparent'; b.style.borderColor = 'transparent'; };
        b.onmousedown = (e) => { e.preventDefault(); execCmd(cmd, val); };
        return b;
    };

    toolbar.appendChild(makeBtn('B', 'Negrito', 'bold'));
    toolbar.appendChild(makeBtn('I', 'Itálico', 'italic'));
    toolbar.appendChild(makeBtn('U', 'Sublinhado', 'underline'));
    toolbar.appendChild(makeBtn('S', 'Tachado', 'strikeThrough'));
    const sep = document.createElement('span'); sep.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px'; toolbar.appendChild(sep);
    toolbar.appendChild(makeBtn(icon('heading-2', 14), 'Título H2', 'formatBlock', 'h2'));
    toolbar.appendChild(makeBtn('&#182;', 'Parágrafo', 'formatBlock', 'p'));
    const sep2 = document.createElement('span'); sep2.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px'; toolbar.appendChild(sep2);
    toolbar.appendChild(makeBtn('&#8226;', 'Lista', 'insertUnorderedList'));
    toolbar.appendChild(makeBtn('1.', 'Lista Numerada', 'insertOrderedList'));

    const sep3 = document.createElement('span'); sep3.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px'; toolbar.appendChild(sep3);
    const tagBtn = document.createElement('button');
    tagBtn.type = 'button'; tagBtn.innerHTML = '{}'; tagBtn.title = 'Inserir tag dinâmica';
    tagBtn.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-accent);cursor:pointer;font-size:11px;font-weight:700';
    tagBtn.onmouseenter = () => { tagBtn.style.background = 'var(--pb-border)'; };
    tagBtn.onmouseleave = () => { tagBtn.style.background = 'transparent'; };
    tagBtn.onclick = (e) => {
        e.preventDefault();
        const rect = tagBtn.getBoundingClientRect();
        showTagPicker(rect.left, rect.bottom, content);
    };
    toolbar.appendChild(tagBtn);

    const debounceSave = (() => { let timer; return (html) => { clearTimeout(timer); timer = setTimeout(() => saveFn(key, html), 300); }; })();
    content.oninput = () => { debounceSave(content.innerHTML); };

    wrap.appendChild(toolbar);
    wrap.appendChild(content);
    return wrap;
}

function createIconInput(key, value, saveFn, debouncedSave) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    const icons = ['fas fa-star','fas fa-heart','fas fa-check','fas fa-times','fas fa-plus','fas fa-minus','fas fa-arrow-right','fas fa-arrow-left','fas fa-arrow-up','fas fa-arrow-down','fas fa-chevron-right','fas fa-chevron-left','fas fa-check-circle','fas fa-times-circle','fas fa-exclamation-circle','fas fa-info-circle','fas fa-lightbulb','fas fa-bell','fas fa-envelope','fas fa-phone','fas fa-map-marker-alt','fas fa-user','fas fa-users','fas fa-home','fas fa-cog','fas fa-search','fas fa-lock','fas fa-download','fas fa-upload','fas fa-share','fas fa-link','fas fa-edit','fas fa-trash','fas fa-copy','fas fa-image','fas fa-video','fas fa-book','fas fa-calendar','fas fa-clock','fas fa-flag','fas fa-tag','fas fa-rocket','fas fa-bolt','fas fa-fire','fas fa-sun','fas fa-moon','fas fa-cloud','fas fa-globe','fas fa-code','fas fa-database','fas fa-wifi','fab fa-github','fab fa-google','fab fa-facebook','fab fa-twitter','fab fa-instagram','fab fa-youtube','fab fa-linkedin'];
    const preview = document.createElement('div');
    preview.style.cssText = 'text-align:center;padding:8px;font-size:2rem;color:var(--pb-text)';
    const currentIcon = value || 'fas fa-star';
    preview.innerHTML = `<i class="${escHtml(currentIcon)}"></i>`;
    // The search box only filters; the value changes when an icon is picked or a full class is typed
    const search = document.createElement('input');
    search.type = 'text'; search.value = ''; search.placeholder = 'Buscar ícone (ex: heart) ou colar classe (fas fa-heart)';
    search.style.cssText = 'width:100%;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:12px;font-family:monospace';
    const current = document.createElement('div');
    current.style.cssText = 'font-size:11px;color:var(--pb-text2);font-family:monospace;text-align:center';
    current.textContent = currentIcon;
    const grid = document.createElement('div');
    grid.style.cssText = 'display:grid;grid-template-columns:repeat(8,1fr);gap:2px;max-height:140px;overflow-y:auto;padding:4px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px';
    const renderGrid = (filter) => {
        grid.innerHTML = '';
        const filtered = filter ? icons.filter(i => i.includes(filter.toLowerCase())) : icons;
        filtered.forEach(ic => {
            const btn = document.createElement('button');
            btn.type = 'button'; btn.innerHTML = `<i class="${ic}" style="font-size:14px"></i>`; btn.title = ic;
            btn.style.cssText = 'width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;transition:all .15s';
            btn.onmouseenter = () => { btn.style.background = 'var(--pb-border)'; btn.style.borderColor = 'var(--pb-accent)'; };
            btn.onmouseleave = () => { btn.style.background = 'transparent'; btn.style.borderColor = 'transparent'; };
            btn.onclick = (e) => { e.preventDefault(); preview.innerHTML = `<i class="${ic}"></i>`; current.textContent = ic; saveFn(key, ic); };
            grid.appendChild(btn);
        });
    };
    renderGrid('');
    search.oninput = () => {
        const q = search.value.trim();
        renderGrid(q);
        // Only a complete Font Awesome class (style prefix + fa-name) is saved as the value
        if (/^(fa[srlbdt]?|fa-(solid|regular|light|brands|duotone|thin))\s+fa-[a-z0-9-]+$/i.test(q)) {
            preview.innerHTML = `<i class="${escHtml(q)}"></i>`;
            current.textContent = q;
            debouncedSave(key, () => saveFn(key, q));
        }
    };
    container.appendChild(preview);
    container.appendChild(current);
    container.appendChild(search);
    container.appendChild(grid);
    return container;
}

function createGalleryInput(key, value, saveFn) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    let images = Array.isArray(value) ? [...value] : [];
    const update = () => saveFn(key, images);
    const list = document.createElement('div');
    list.style.cssText = 'display:flex;flex-direction:column;gap:4px;max-height:200px;overflow-y:auto';
    const renderList = () => {
        list.innerHTML = '';
        images.forEach((img, idx) => {
            const item = document.createElement('div');
            item.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:6px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;cursor:grab';
            item.innerHTML = `<img src="${escHtml(img.url||'')}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0"><div style="flex:1;min-width:0"><input type="text" value="${escHtml(img.alt||'')}" placeholder="Texto alternativo" style="width:100%;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px;box-sizing:border-box"></div><button type="button" title="Remover" style="background:none;border:none;color:var(--pb-danger);cursor:pointer;padding:2px;display:flex">${icon('x', 14)}</button>`;
            item.querySelector('input').onchange = (e) => { images[idx].alt = e.target.value; update(); };
            item.querySelector('button').onclick = () => { images.splice(idx, 1); update(); renderList(); };
            list.appendChild(item);
        });
    };
    renderList();

    // Uploads run one after another so the gallery keeps the order the files were chosen in
    const uploadFiles = (files) => {
        const queue = Array.from(files || []).filter(f => f.type.startsWith('image/'));
        if (!queue.length) return;
        const next = () => {
            const file = queue.shift();
            if (!file) return;
            uploadImageFile(file, (url) => {
                images.push({ url, alt: file.name.replace(/\.[^.]+$/, '') });
                update(); renderList();
                next();
            });
        };
        next();
    };

    const fileInput = document.createElement('input');
    fileInput.type = 'file'; fileInput.multiple = true;
    fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
    fileInput.style.display = 'none';
    fileInput.onchange = () => { uploadFiles(fileInput.files); fileInput.value = ''; };

    const btnStyle = 'flex:1;padding:8px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:500;display:flex;align-items:center;justify-content:center;gap:6px';
    const uploadBtn = document.createElement('button');
    uploadBtn.type = 'button';
    uploadBtn.innerHTML = `${icon('upload', 14)} Enviar imagens`;
    uploadBtn.style.cssText = btnStyle + ';background:var(--pb-primary);border:none;color:#fff';
    uploadBtn.onclick = () => fileInput.click();

    const urlBtn = document.createElement('button');
    urlBtn.type = 'button';
    urlBtn.innerHTML = `${icon('link', 14)} Por URL`;
    urlBtn.style.cssText = btnStyle + ';background:var(--pb-surface2);border:1px solid var(--pb-border);color:var(--pb-text)';
    urlBtn.onclick = () => {
        const url = prompt('URL da imagem:');
        if (url) { images.push({ url, alt: '' }); update(); renderList(); }
    };

    const actions = document.createElement('div');
    actions.style.cssText = 'display:flex;gap:.35rem';
    actions.appendChild(uploadBtn);
    actions.appendChild(urlBtn);

    const hint = document.createElement('div');
    hint.style.cssText = 'font-size:.68rem;color:var(--pb-text2);text-align:center';
    hint.textContent = 'Você também pode arrastar imagens do computador para cá';

    container.ondragover = (e) => { if (e.dataTransfer && Array.from(e.dataTransfer.types).includes('Files')) { e.preventDefault(); container.style.outline = '2px dashed var(--pb-accent)'; } };
    container.ondragleave = () => { container.style.outline = ''; };
    container.ondrop = (e) => {
        if (!e.dataTransfer || !e.dataTransfer.files.length) return;
        e.preventDefault(); container.style.outline = '';
        uploadFiles(e.dataTransfer.files);
    };

    container.appendChild(list);
    container.appendChild(actions);
    container.appendChild(hint);
    container.appendChild(fileInput);
    return container;
}

function createRepeaterInput(key, value, ctrl, saveFn) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    let items = Array.isArray(value) ? value.map(v => ({...v})) : [];
    const subFields = ctrl.fields || {};
    const list = document.createElement('div');
    list.style.cssText = 'display:flex;flex-direction:column;gap:4px;max-height:280px;overflow-y:auto';
    const updateRepeater = () => saveFn(key, items);
    const renderItems = () => {
        list.innerHTML = '';
        items.forEach((item, idx) => {
            const card = document.createElement('div');
            card.style.cssText = 'background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;padding:8px;display:flex;flex-direction:column;gap:6px';
            const header = document.createElement('div');
            header.style.cssText = 'display:flex;align-items:center;gap:4px;font-size:.7rem;color:var(--pb-text2);cursor:grab';
            header.innerHTML = `<span style="cursor:grab;display:flex">${icon('grip', 13)}</span><span style="flex:1;font-weight:500;color:var(--pb-text)">${escHtml(item.label||item.type||'Field '+(idx+1))}</span>`;
            const delBtn = document.createElement('button');
            delBtn.type = 'button'; delBtn.innerHTML = icon('x', 14);
            delBtn.style.cssText = 'background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:14px;padding:0 2px';
            delBtn.onclick = () => { items.splice(idx, 1); renderItems(); updateRepeater(); };
            header.appendChild(delBtn);
            card.appendChild(header);
            for (const [fk, fc] of Object.entries(subFields)) {
                const fRow = document.createElement('div');
                fRow.style.cssText = 'display:flex;align-items:center;gap:6px';
                const fLabel = document.createElement('label');
                fLabel.textContent = fc.label || fk;
                fLabel.style.cssText = 'font-size:.65rem;color:var(--pb-text2);min-width:60px';
                fRow.appendChild(fLabel);
                if (fc.type === 'select') {
                    const sel = document.createElement('select');
                    sel.style.cssText = 'flex:1;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px';
                    optionEntries(fc).forEach(([optValue, optLabel]) => {
                        const o = document.createElement('option');
                        o.value = optValue; o.textContent = optLabel;
                        if (String(optValue) === String(item[fk])) o.selected = true;
                        sel.appendChild(o);
                    });
                    sel.onchange = (e) => { items[idx][fk] = e.target.value; updateRepeater(); renderItems(); };
                    fRow.appendChild(sel);
                } else if (fc.type === 'boolean') {
                    const cb = document.createElement('input');
                    cb.type = 'checkbox'; cb.checked = !!item[fk];
                    cb.onchange = (e) => { items[idx][fk] = e.target.checked; updateRepeater(); };
                    fRow.appendChild(cb);
                } else {
                    const inp = document.createElement('input');
                    inp.type = 'text'; inp.value = item[fk] || '';
                    inp.style.cssText = 'flex:1;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px';
                    inp.onchange = (e) => { items[idx][fk] = e.target.value; updateRepeater(); renderItems(); };
                    fRow.appendChild(inp);
                }
                card.appendChild(fRow);
            }
            list.appendChild(card);
        });
    };
    renderItems();
    const addBtn = document.createElement('button');
    addBtn.type = 'button'; addBtn.textContent = '+ Adicionar Item';
    addBtn.style.cssText = 'padding:6px;background:var(--pb-surface2);border:1px dashed var(--pb-border);border-radius:6px;color:var(--pb-text2);cursor:pointer;font-size:11px;text-align:center;transition:all .2s';
    addBtn.onclick = () => {
        const newItem = {};
        for (const [fk, fc] of Object.entries(subFields)) {
            if (fc.type === 'boolean') newItem[fk] = false;
            else if (fc.type === 'select' && optionEntries(fc).length) newItem[fk] = optionEntries(fc)[0][0];
            else newItem[fk] = '';
        }
        items.push(newItem);
        updateRepeater();
        renderItems();
    };
    container.appendChild(list);
    container.appendChild(addBtn);
    return container;
}

function createTypographyInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'typography_font_family', label: 'Fonte', type: 'text' },
        { fk: 'typography_font_size', label: 'Tamanho da fonte', type: 'text' },
        { fk: 'typography_font_weight', label: 'Peso da fonte', type: 'select', options: ['300','400','500','600','700','800','900'] },
        { fk: 'typography_line_height', label: 'Altura da linha', type: 'text' },
        { fk: 'typography_letter_spacing', label: 'Espaçamento entre letras', type: 'text' },
        { fk: 'typography_text_transform', label: 'Maiúsculas/minúsculas', type: 'select', options: ['none','uppercase','lowercase','capitalize'] },
        { fk: 'typography_color', label: 'Cor do texto', type: 'color' },
    ];
    defs.forEach(({fk, label, type, options}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = optionText(opt);
                if (opt === (value || '')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else if (type === 'color') {
            const colorSave = (k, v) => updateStyle(k, v, elementId);
            row.appendChild(_colorInput(fk, value || '', colorSave, elementId));
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || '';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createBackgroundInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'backgroundColor', label: 'Cor de fundo', type: 'color' },
        { fk: 'backgroundImage', label: 'Imagem de fundo', type: 'url' },
        { fk: 'backgroundSize', label: 'Tamanho', type: 'select', options: ['auto','cover','contain'] },
        { fk: 'backgroundRepeat', label: 'Repetição', type: 'select', options: ['no-repeat','repeat','repeat-x','repeat-y'] },
    ];
    defs.forEach(({fk, label, type, options}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = optionText(opt);
                if (opt === (value || '')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else if (type === 'color') {
            const colorSave = (k, v) => updateStyle(k, v, elementId);
            row.appendChild(_colorInput(fk, value || '', colorSave, elementId));
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || '';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createBorderInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'borderWidth', label: 'Espessura da borda', type: 'text', def: '0' },
        { fk: 'borderColor', label: 'Cor da borda', type: 'color', def: '#000000' },
        { fk: 'borderRadius', label: 'Arredondamento', type: 'text', def: '0' },
        { fk: 'borderStyle', label: 'Estilo da borda', type: 'select', options: ['none','solid','dashed','dotted','double'], def: 'solid' },
    ];
    defs.forEach(({fk, label, type, options, def}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = optionText(opt);
                if (opt === (value || def || '')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else if (type === 'color') {
            const colorSave = (k, v) => updateStyle(k, v, elementId);
            row.appendChild(_colorInput(fk, value || def || '', colorSave, elementId));
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || def || '';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createBoxShadowInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'shadowHorizontal', label: 'Horizontal', type: 'text', def: '0' },
        { fk: 'shadowVertical', label: 'Vertical', type: 'text', def: '0' },
        { fk: 'shadowBlur', label: 'Desfoque', type: 'text', def: '0' },
        { fk: 'shadowSpread', label: 'Expansão', type: 'text', def: '0' },
        { fk: 'shadowColor', label: 'Cor', type: 'color', def: 'rgba(0,0,0,0.3)' },
    ];
    const readAll = () => {
        const h = c.querySelector('[data-fk="shadowHorizontal"]')?.value || '0';
        const v = c.querySelector('[data-fk="shadowVertical"]')?.value || '0';
        const b = c.querySelector('[data-fk="shadowBlur"]')?.value || '0';
        const s = c.querySelector('[data-fk="shadowSpread"]')?.value || '0';
        const co = c.querySelector('[data-fk="shadowColor"]')?.value || 'rgba(0,0,0,0.3)';
        return `${h}px ${v}px ${b}px ${s}px ${co}`;
    };
    defs.forEach(({fk, label, type, def}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'color') {
            const colorSave = (k, v) => {
                const inp = c.querySelector('[data-fk="' + fk + '"]');
                if (inp) inp.value = v;
                _debouncedStyle('boxShadow', elementId, () => updateStyle('boxShadow', readAll(), elementId));
            };
            row.appendChild(_colorInput(fk, value || def || '', colorSave, elementId));
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || def || '';
            inp.dataset.fk = fk;
            inp.oninput = () => _debouncedStyle('boxShadow', elementId, () => updateStyle('boxShadow', readAll(), elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createDimensionsInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const SIDE_LABELS = { Top: 'Cima', Right: 'Direita', Bottom: 'Baixo', Left: 'Esquerda' };
    const groups = [
        { prefix: 'padding', label: 'Espaçamento interno', keys: ['Top','Right','Bottom','Left'] },
        { prefix: 'margin', label: 'Margem', keys: ['Top','Right','Bottom','Left'] },
    ];
    // Each side is its own style key (marginTop...): show what is saved for the device being edited
    const saved = (fk) => {
        const v = (state.cachedStyles || {})[fk];
        return v === undefined || v === null ? '' : String(v);
    };
    // Sides start linked only when they are all equal (or empty)
    const isLinked = {};
    groups.forEach(g => { isLinked[g.prefix] = new Set(g.keys.map(k => saved(g.prefix + k))).size <= 1; });
    const save = (fk, raw) => updateStyle(fk, cssLength(fk.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase(), raw), elementId);
    groups.forEach(group => {
        const header = document.createElement('div');
        header.style.cssText = 'display:flex;align-items:center;justify-content:space-between;margin-top:.25rem';
        const hLabel = document.createElement('span');
        hLabel.style.cssText = 'font-size:12px;font-weight:600;color:var(--pb-text2)';
        hLabel.textContent = group.label;
        header.appendChild(hLabel);
        const lockBtn = document.createElement('button');
        lockBtn.type = 'button';
        lockBtn.style.cssText = 'background:none;border:1px solid var(--pb-border);border-radius:4px;padding:2px 6px;cursor:pointer;font-size:12px;transition:all .15s;display:inline-flex;align-items:center;color:var(--pb-text2)';
        const paintLock = () => {
            lockBtn.innerHTML = isLinked[group.prefix] ? icon('link', 12) : icon('unlink', 12);
            lockBtn.title = isLinked[group.prefix] ? 'Valores vinculados: mudar um muda os quatro' : 'Valores independentes';
            lockBtn.style.borderColor = isLinked[group.prefix] ? 'var(--pb-accent)' : 'var(--pb-border)';
        };
        lockBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            isLinked[group.prefix] = !isLinked[group.prefix];
            paintLock();
        };
        paintLock();
        header.appendChild(lockBtn);
        c.appendChild(header);
        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid;grid-template-columns:repeat(4,1fr);gap:3px';
        const inputs = [];
        group.keys.forEach((side, idx) => {
            const fk = group.prefix + side;
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:2px';
            const sideLabel = document.createElement('span');
            sideLabel.style.cssText = 'font-size:10px;color:var(--pb-text2);text-transform:uppercase';
            sideLabel.textContent = SIDE_LABELS[side] || side;
            const inp = document.createElement('input');
            inp.type = 'text'; inp.value = saved(fk); inp.placeholder = '0';
            inp.id = `ctrl-${fk}`;
            inp.title = 'Número em px (ex: 10) ou com unidade (ex: 1rem, 5%)';
            inp.style.cssText = 'width:100%;padding:4px;text-align:center;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:12px;box-sizing:border-box';
            inp.oninput = () => {
                _debouncedStyle(fk, elementId, () => save(fk, inp.value));
                if (isLinked[group.prefix]) {
                    inputs.forEach((otherInp, oi) => {
                        if (oi !== idx) {
                            otherInp.value = inp.value;
                            const otherKey = group.prefix + group.keys[oi];
                            _debouncedStyle(otherKey, elementId, () => save(otherKey, inp.value));
                        }
                    });
                }
            };
            inputs.push(inp);
            wrap.appendChild(sideLabel);
            wrap.appendChild(inp);
            grid.appendChild(wrap);
        });
        c.appendChild(grid);
    });
    return c;
}

function createHoverInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'hoverBackgroundColor', label: 'Cor de fundo', type: 'color' },
        { fk: 'hoverTextColor', label: 'Cor do texto', type: 'color' },
        { fk: 'hoverBorderColor', label: 'Cor da borda', type: 'color' },
        { fk: 'hoverTransform', label: 'Transformação', type: 'select', options: ['none','scale(1.05)','scale(0.98)','translateY(-2px)','translateY(2px)'] },
    ];
    defs.forEach(({fk, label, type, options}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = optionText(opt);
                if (opt === (value || 'none')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else if (type === 'color') {
            const colorSave = (k, v) => updateStyle(k, v, elementId);
            row.appendChild(_colorInput(fk, value || '', colorSave, elementId));
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || '';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createGradientInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const grad = (typeof value === 'object' && value !== null) ? value : { type: 'linear', angle: 180, color1: '#6366f1', color2: '#8b5cf6', position1: 0, position2: 100 };
    const defs = [
        { fk: 'type', label: 'Tipo', type: 'select', options: ['linear', 'radial'] },
        { fk: 'angle', label: 'Ângulo (graus)', type: 'number', min: 0, max: 360 },
        { fk: 'color1', label: 'Cor 1', type: 'color' },
        { fk: 'position1', label: 'Posição 1 (%)', type: 'number', min: 0, max: 100 },
        { fk: 'color2', label: 'Cor 2', type: 'color' },
        { fk: 'position2', label: 'Posição 2 (%)', type: 'number', min: 0, max: 100 },
    ];
    const preview = document.createElement('div');
    preview.style.cssText = 'height:32px;border-radius:6px;margin-bottom:4px;border:1px solid var(--pb-border)';
    const updatePreview = () => {
        if (grad.type === 'radial') preview.style.background = `radial-gradient(circle, ${grad.color1} ${grad.position1}%, ${grad.color2} ${grad.position2}%)`;
        else preview.style.background = `linear-gradient(${grad.angle}deg, ${grad.color1} ${grad.position1}%, ${grad.color2} ${grad.position2}%)`;
    };
    updatePreview();
    c.appendChild(preview);
    defs.forEach(({ fk, label, type, options, min, max }) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = optionText(opt);
                if (opt === grad[fk]) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => { grad[fk] = sel.value; updatePreview(); updateStyle(key, grad, elementId); };
            row.appendChild(sel);
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = grad[fk] || 0;
            if (min !== undefined) inp.min = min;
            if (max !== undefined) inp.max = max;
            if (type === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
            inp.oninput = () => { grad[fk] = type === 'number' ? parseInt(inp.value) || 0 : inp.value; updatePreview(); _debouncedStyle(key, elementId, () => updateStyle(key, grad, elementId)); };
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createScrollAnimationInput(key, value, saveFn) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const row = document.createElement('div');
    row.className = 'pb-control';
    const lbl = document.createElement('label');
    lbl.textContent = 'Animação ao rolar';
    row.appendChild(lbl);
    const sel = document.createElement('select');
    ['none','fade-up','fade-down','fade-left','fade-right','zoom-in','zoom-out','slide-up','slide-down'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = optionText(opt);
        if (opt === (value || 'none')) o.selected = true;
        sel.appendChild(o);
    });
    sel.onchange = () => saveFn(key, sel.value);
    row.appendChild(sel);
    c.appendChild(row);
    const durRow = document.createElement('div');
    durRow.className = 'pb-control';
    durRow.style.display = (value && value !== 'none') ? '' : 'none';
    const durLabel = document.createElement('label');
    durLabel.textContent = 'Duração';
    durRow.appendChild(durLabel);
    const durSel = document.createElement('select');
    ['0.3s','0.6s','0.9s','1.2s'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = optionText(opt);
        if (opt === '0.6s') o.selected = true;
        durSel.appendChild(o);
    });
    durSel.onchange = () => saveFn(key + '_duration', durSel.value);
    durRow.appendChild(durSel);
    c.appendChild(durRow);
    const delayRow = document.createElement('div');
    delayRow.className = 'pb-control';
    delayRow.style.display = (value && value !== 'none') ? '' : 'none';
    const delayLabel = document.createElement('label');
    delayLabel.textContent = 'Atraso';
    delayRow.appendChild(delayLabel);
    const delaySel = document.createElement('select');
    ['0s','0.1s','0.2s','0.3s','0.5s'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = optionText(opt);
        o.selected = opt === '0s';
        delaySel.appendChild(o);
    });
    delaySel.onchange = () => saveFn(key + '_delay', delaySel.value);
    delayRow.appendChild(delaySel);
    c.appendChild(delayRow);
    sel.onchange = () => {
        saveFn(key, sel.value);
        const show = sel.value && sel.value !== 'none';
        durRow.style.display = show ? '' : 'none';
        delayRow.style.display = show ? '' : 'none';
    };
    return c;
}

function createTextShadowInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const shadow = typeof value === 'string' ? value : '';
    const parse = (v) => {
        const m = v.match(/(-?\d+\.?\d*)px\s+(-?\d+\.?\d*)px\s+(-?\d+\.?\d*)px\s+(#[0-9a-fA-F]+|rgba?\(.+?\))/);
        return m ? { x: m[1], y: m[2], blur: m[3], color: m[4] } : { x: '0px', y: '2px', blur: '4px', color: 'rgba(0,0,0,0.3)' };
    };
    const s = parse(shadow);
    const defs = [
        { fk: 'x', label: 'Horizontal', type: 'text' },
        { fk: 'y', label: 'Vertical', type: 'text' },
        { fk: 'blur', label: 'Desfoque', type: 'text' },
        { fk: 'color', label: 'Cor', type: 'color' },
    ];
    const readAll = () => {
        const inputs = c.querySelectorAll('[data-fk]');
        const vals = {};
        inputs.forEach(i => vals[i.dataset.fk] = i.value);
        return `${vals.x || '0px'} ${vals.y || '2px'} ${vals.blur || '4px'} ${vals.color || 'rgba(0,0,0,0.3)'}`;
    };
    defs.forEach(({ fk, label, type }) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        const inp = document.createElement('input');
        inp.type = type; inp.value = s[fk] || '';
        inp.dataset.fk = fk;
        if (fk === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
        inp.oninput = () => _debouncedStyle(key, elementId, () => updateStyle(key, readAll(), elementId));
        row.appendChild(inp);
        c.appendChild(row);
    });
    return c;
}

function createTextStrokeInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const stroke = typeof value === 'string' ? value : '';
    const parse = (v) => {
        const m = v.match(/(-?\d+\.?\d*)px\s+(#[0-9a-fA-F]+|rgba?\(.+?\))/);
        return m ? { width: m[1], color: m[2] } : { width: '0px', color: '#000000' };
    };
    const s = parse(stroke);
    const defs = [
        { fk: 'width', label: 'Espessura', type: 'text' },
        { fk: 'color', label: 'Cor', type: 'color' },
    ];
    const readAll = () => {
        const inputs = c.querySelectorAll('[data-fk]');
        const vals = {};
        inputs.forEach(i => vals[i.dataset.fk] = i.value);
        const w = parseFloat(vals.width) || 0;
        return w > 0 ? `${vals.width} ${vals.color}` : '';
    };
    defs.forEach(({ fk, label, type }) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        const inp = document.createElement('input');
        inp.type = type; inp.value = s[fk] || '';
        inp.dataset.fk = fk;
        if (fk === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
        inp.oninput = () => _debouncedStyle(key, elementId, () => updateStyle(key, readAll(), elementId));
        row.appendChild(inp);
        c.appendChild(row);
    });
    return c;
}

function createColumnWidthInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:4px;';
    const numVal = typeof value === 'number' ? value : (typeof value === 'string' && value.startsWith('col-') ? Math.round(parseInt(value.replace('col-', '')) / 12 * 100) : 33.33);
    const pct = Math.min(100, Math.max(0, numVal));
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;gap:8px;';
    const slider = document.createElement('input');
    slider.type = 'range'; slider.min = '1'; slider.max = '100'; slider.value = pct;
    slider.style.cssText = 'flex:1;accent-color:var(--pb-primary);cursor:pointer;';
    const label = document.createElement('span');
    label.style.cssText = 'font-size:.75rem;color:var(--pb-text);min-width:38px;text-align:right;font-weight:600;';
    label.textContent = pct + '%';
    slider.oninput = () => {
        label.textContent = slider.value + '%';
    };
    slider.onchange = () => {
        const newPct = parseInt(slider.value);
        const colNum = Math.max(1, Math.round(newPct / 100 * 12));
        updateSetting('column_width', `col-${colNum}`, elementId, true);
    };
    row.append(slider, label);
    c.appendChild(row);
    return c;
}

function createAnimationInput(key, value, saveFn) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const animRow = document.createElement('div');
    animRow.className = 'pb-control';
    const animLabel = document.createElement('label');
    animLabel.textContent = 'Animação de entrada';
    animRow.appendChild(animLabel);
    const animSel = document.createElement('select');
    animSel.id = `ctrl-${key}`;
    ['none','fadeIn','fadeInUp','fadeInDown','fadeInLeft','fadeInRight','slideInUp','slideInDown','slideInLeft','slideInRight','zoomIn','bounceIn','rotateIn','lightSpeedIn'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = optionText(opt);
        if (opt === (value || 'none')) o.selected = true;
        animSel.appendChild(o);
    });
    animSel.onchange = () => saveFn(key, animSel.value);
    animRow.appendChild(animSel);
    c.appendChild(animRow);
    const durRow = document.createElement('div');
    durRow.className = 'pb-control';
    durRow.style.display = (value && value !== 'none') ? '' : 'none';
    const durLabel = document.createElement('label');
    durLabel.textContent = 'Duração';
    durRow.appendChild(durLabel);
    const durSel = document.createElement('select');
    ['slow','normal','fast'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = optionText(opt);
        if (opt === 'normal') o.selected = true;
        durSel.appendChild(o);
    });
    durSel.onchange = () => saveFn(key + '_duration', durSel.value);
    durRow.appendChild(durSel);
    c.appendChild(durRow);
    const delayRow = document.createElement('div');
    delayRow.className = 'pb-control';
    delayRow.style.display = (value && value !== 'none') ? '' : 'none';
    const delayLabel = document.createElement('label');
    delayLabel.textContent = 'Atraso (ms)';
    delayRow.appendChild(delayLabel);
    const delayInp = document.createElement('input');
    delayInp.type = 'number'; delayInp.min = 0; delayInp.max = 5000; delayInp.step = 100; delayInp.value = 0;
    delayInp.onchange = () => saveFn(key + '_delay', parseInt(delayInp.value) || 0);
    delayRow.appendChild(delayInp);
    c.appendChild(delayRow);
    animSel.onchange = () => {
        saveFn(key, animSel.value);
        const show = animSel.value && animSel.value !== 'none';
        durRow.style.display = show ? '' : 'none';
        delayRow.style.display = show ? '' : 'none';
    };
    return c;
}

function createVisibilityInput(key, value, saveFn) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
    const defs = [
        { fk: 'visibility_desktop', label: 'Visível no computador', default: true },
        { fk: 'visibility_tablet', label: 'Visível no tablet', default: true },
        { fk: 'visibility_mobile', label: 'Visível no celular', default: true },
    ];
    defs.forEach(({fk, label, default: def}) => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px';
        const lbl = document.createElement('span');
        lbl.style.cssText = 'font-size:12px;color:var(--pb-text)';
        lbl.textContent = label;
        const sw = document.createElement('label');
        sw.style.cssText = 'position:relative;display:inline-block;width:36px;height:20px;cursor:pointer';
        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.checked = value !== undefined ? !!value : def;
        cb.style.cssText = 'opacity:0;width:0;height:0';
        const slider = document.createElement('span');
        slider.style.cssText = 'position:absolute;inset:0;background:var(--pb-border);border-radius:20px;transition:.2s';
        const before = document.createElement('span');
        before.style.cssText = 'position:absolute;height:14px;width:14px;left:3px;bottom:3px;background:white;border-radius:50%;transition:.2s';
        slider.appendChild(before);
        const updateSlider = () => {
            slider.style.background = cb.checked ? 'var(--pb-accent)' : 'var(--pb-border)';
            before.style.transform = cb.checked ? 'translateX(16px)' : '';
        };
        updateSlider();
        cb.onchange = () => { updateSlider(); saveFn(fk, cb.checked); };
        sw.appendChild(cb);
        sw.appendChild(slider);
        row.appendChild(lbl);
        row.appendChild(sw);
        c.appendChild(row);
    });
    return c;
}
