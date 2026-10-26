import state from './state.js';
import { apiFetch, toastError, toastSuccess } from './utils.js';
import { icon } from './icons.js';

export function loadGlobalSettings() {
    return apiFetch(`/page-builder/pages/${state.pageId}/global-settings`)
        .then(data => { state.globalSettings = data; })
        .catch(() => {});
}

export function saveGlobalSettings(colors, fonts) {
    apiFetch(`/page-builder/pages/${state.pageId}/global-settings`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ global_colors: colors, global_fonts: fonts }),
    })
    .then(data => {
        state.globalSettings.global_colors = data.global_colors;
        state.globalSettings.global_fonts = data.global_fonts;
        toastSuccess('Configurações globais salvas!');
    })
    .catch(() => toastError('Falha ao salvar configurações globais'));
}

export function showSiteSettings() {
    const panel = document.getElementById('settings-empty');
    const form = document.getElementById('settings-form');
    const pageForm = document.getElementById('page-settings-form');
    if (panel) panel.style.display = 'none';
    if (form) form.style.display = 'none';
    if (pageForm) pageForm.style.display = 'none';
    state._prevSelected = state.selectedId;
    if (state.selectedId) { import('./elements.js').then(m => m.deselectAll()); state.selectedId = null; }
    loadGlobalSettings();
    renderSiteSettings();
}

function renderSiteSettings() {
    const body = document.getElementById('settings-body');
    if (!body) return;
    body.innerHTML = '';
    document.getElementById('settings-title').textContent = 'Configurações do Site';
    document.getElementById('settings-type').textContent = 'global';
    const empty = document.getElementById('settings-empty');
    const form = document.getElementById('settings-form');
    if (empty) empty.style.display = 'none';
    if (form) form.style.display = 'block';
    document.getElementById('editor-tabs').style.display = 'none';
    document.getElementById('responsive-tabs').style.display = 'none';

    const colors = state.globalSettings.global_colors || [];
    const fonts = state.globalSettings.global_fonts || [];
    const systemFonts = state.globalSettings.system_fonts || [];

    const colorsSection = document.createElement('div');
    colorsSection.className = 'pb-settings-section';
    colorsSection.innerHTML = '<div class="pb-settings-section-title">Cores Globais</div>';

    const colorList = document.createElement('div');
    colorList.id = 'global-colors-list';
    colorList.style.cssText = 'display:flex;flex-direction:column;gap:6px;margin-bottom:8px;';
    colors.forEach((c, i) => {
        colorList.appendChild(createGlobalColorRow(c, i, colors));
    });
    colorsSection.appendChild(colorList);

    const addColorBtn = document.createElement('button');
    addColorBtn.className = 'pb-btn-add-global';
    addColorBtn.textContent = '+ Adicionar Cor Global';
    addColorBtn.style.cssText = 'width:100%;padding:.4rem;background:rgba(99,102,241,.1);border:1px dashed rgba(99,102,241,.3);color:var(--pb-accent);border-radius:6px;cursor:pointer;font-size:.75rem;';
    addColorBtn.onclick = () => {
        colors.push({ name: 'Nova Cor', value: '#6366f1', system: false });
        saveGlobalSettings(colors, fonts);
        renderSiteSettings();
    };
    colorsSection.appendChild(addColorBtn);
    body.appendChild(colorsSection);

    const fontsSection = document.createElement('div');
    fontsSection.className = 'pb-settings-section';
    fontsSection.innerHTML = '<div class="pb-settings-section-title">Fontes Globais</div>';

    const fontList = document.createElement('div');
    fontList.id = 'global-fonts-list';
    fontList.style.cssText = 'display:flex;flex-direction:column;gap:6px;margin-bottom:8px;';
    fonts.forEach((f, i) => {
        fontList.appendChild(createGlobalFontRow(f, i, fonts, systemFonts));
    });
    fontsSection.appendChild(fontList);

    const addFontBtn = document.createElement('button');
    addFontBtn.className = 'pb-btn-add-global';
    addFontBtn.textContent = '+ Adicionar Fonte Global';
    addFontBtn.style.cssText = 'width:100%;padding:.4rem;background:rgba(99,102,241,.1);border:1px dashed rgba(99,102,241,.3);color:var(--pb-accent);border-radius:6px;cursor:pointer;font-size:.75rem;';
    addFontBtn.onclick = () => {
        fonts.push({ name: 'Nova Fonte', family: 'Inter, sans-serif' });
        saveGlobalSettings(colors, fonts);
        renderSiteSettings();
    };
    fontsSection.appendChild(addFontBtn);
    body.appendChild(fontsSection);
}

function createGlobalColorRow(color, index, colors) {
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;gap:6px;background:var(--pb-surface2);border-radius:6px;padding:6px 8px;';
    const swatch = document.createElement('input');
    swatch.type = 'color'; swatch.value = color.value || '#6366f1';
    swatch.style.cssText = 'width:28px;height:28px;border:none;border-radius:4px;cursor:pointer;padding:0;';
    swatch.onchange = () => { colors[index].value = swatch.value; saveGlobalSettings(colors, state.globalSettings.global_fonts); };
    const nameInput = document.createElement('input');
    nameInput.value = color.name || '';
    nameInput.placeholder = 'Nome';
    nameInput.style.cssText = 'flex:1;background:var(--pb-surface3);border:1px solid var(--pb-border);border-radius:4px;padding:4px 8px;color:var(--pb-text);font-size:.75rem;';
    nameInput.onchange = () => { colors[index].name = nameInput.value; saveGlobalSettings(colors, state.globalSettings.global_fonts); };
    const del = document.createElement('button');
    del.innerHTML = icon('x', 14); del.title = 'Remover';
    del.style.cssText = 'background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1rem;padding:0 4px;';
    del.onclick = () => { colors.splice(index, 1); saveGlobalSettings(colors, state.globalSettings.global_fonts); renderSiteSettings(); };
    row.append(swatch, nameInput, del);
    return row;
}

function createGlobalFontRow(font, index, fonts, systemFonts) {
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;gap:6px;background:var(--pb-surface2);border-radius:6px;padding:6px 8px;';
    const nameInput = document.createElement('input');
    nameInput.value = font.name || '';
    nameInput.placeholder = 'Nome';
    nameInput.style.cssText = 'flex:1;background:var(--pb-surface3);border:1px solid var(--pb-border);border-radius:4px;padding:4px 8px;color:var(--pb-text);font-size:.75rem;';
    nameInput.onchange = () => { fonts[index].name = nameInput.value; saveGlobalSettings(state.globalSettings.global_colors, fonts); };
    const sel = document.createElement('select');
    sel.style.cssText = 'flex:1;background:var(--pb-surface3);border:1px solid var(--pb-border);border-radius:4px;padding:4px 8px;color:var(--pb-text);font-size:.75rem;';
    systemFonts.forEach(sf => {
        const opt = document.createElement('option');
        opt.value = sf.family; opt.textContent = sf.name;
        if (sf.family === font.family) opt.selected = true;
        sel.appendChild(opt);
    });
    sel.onchange = () => { fonts[index].family = sel.value; saveGlobalSettings(state.globalSettings.global_colors, fonts); };
    const del = document.createElement('button');
    del.innerHTML = icon('x', 14); del.title = 'Remover';
    del.style.cssText = 'background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1rem;padding:0 4px;';
    del.onclick = () => { fonts.splice(index, 1); saveGlobalSettings(state.globalSettings.global_colors, fonts); renderSiteSettings(); };
    row.append(nameInput, sel, del);
    return row;
}

export function hideSiteSettings() {
    const form = document.getElementById('settings-form');
    const empty = document.getElementById('settings-empty');
    const pageForm = document.getElementById('page-settings-form');
    const tabs = document.getElementById('editor-tabs');
    const respTabs = document.getElementById('responsive-tabs');
    if (form) { form.style.display = ''; form.classList.remove('active'); }
    if (pageForm) pageForm.classList.remove('active');
    if (tabs) tabs.style.display = '';
    if (respTabs) respTabs.style.display = '';
    if (state._prevSelected) { import('./elements.js').then(m => m.selectElement(state._prevSelected)); }
    else { if (form) form.style.display = 'none'; if (empty) empty.style.display = ''; }
}
