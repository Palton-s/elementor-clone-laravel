import state from './state.js';

export function getRecentColors() {
    try { return JSON.parse(localStorage.getItem('pb_recent_colors') || '[]'); } catch { return []; }
}

export function addRecentColor(color) {
    if (!color || color === '#000000' || color === '#ffffff') return;
    let recent = getRecentColors();
    recent = recent.filter(c => c !== color);
    recent.unshift(color);
    if (recent.length > 12) recent = recent.slice(0, 12);
    try { localStorage.setItem('pb_recent_colors', JSON.stringify(recent)); } catch {}
}

export function _colorInput(key, value, saveFn, elementId) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';

    const row1 = document.createElement('div');
    row1.style.cssText = 'display:flex;gap:.35rem;align-items:center';

    const swatch = document.createElement('div');
    swatch.style.cssText = 'width:32px;height:32px;border-radius:6px;border:2px solid var(--pb-border);cursor:pointer;background:' + (value ? value : 'transparent') + ';flex-shrink:0' + (value ? '' : ';background-image:linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%);background-size:8px 8px;background-position:0 0,0 4px,4px -4px,-4px 0px');

    const inp = document.createElement('input');
    inp.type = 'color'; inp.id = 'ctrl-' + key; inp.value = (value && /^#[0-9a-f]{3,8}$/i.test(value)) ? value : '#000000';
    inp.style.cssText = 'width:0;height:0;padding:0;border:none;opacity:0;position:absolute;pointer-events:none';

    const txt = document.createElement('input');
    txt.type = 'text'; txt.value = value || '';
    txt.placeholder = '#000000 ou nome da cor';
    txt.dataset.fk = key;
    txt.style.cssText = 'flex:1;padding:.4rem .55rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:.78rem;font-family:monospace';

    const update = (v) => {
        const color = v === '' ? '' : (v || '#000000');
        if (color === '') {
            inp.value = '#000000';
            txt.value = '';
            swatch.style.background = 'transparent';
            swatch.style.backgroundImage = 'linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%)';
            swatch.style.backgroundSize = '8px 8px';
            swatch.style.backgroundPosition = '0 0, 0 4px, 4px -4px, -4px 0px';
        } else {
            inp.value = color;
            txt.value = color;
            swatch.style.backgroundImage = 'none';
            swatch.style.background = color;
        }
        saveFn(key, color);
        if (color) addRecentColor(color);
    };

    swatch.onclick = () => inp.click();
    inp.oninput = (e) => {
        update(e.target.value);
    };
    txt.oninput = (e) => {
        const v = e.target.value;
        if (/^#[0-9a-f]{3,8}$/i.test(v) || /^rgba?\(|^hsla?\(|^[a-z]+$/i.test(v)) {
            if (v) { swatch.style.backgroundImage = 'none'; swatch.style.background = v; } else { swatch.style.backgroundImage = 'linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%)'; swatch.style.backgroundSize = '8px 8px'; swatch.style.backgroundPosition = '0 0, 0 4px, 4px -4px, -4px 0px'; swatch.style.background = 'transparent'; }
            if (/^#[0-9a-f]{3,8}$/i.test(v)) inp.value = v.length === 4 ? '#' + v[1]+v[1]+v[2]+v[2]+v[3]+v[3] : v;
            saveFn(key, v);
            if (v && /^#[0-9a-f]{6}$/i.test(v)) addRecentColor(v);
        } else {
            swatch.style.backgroundImage = 'linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%)'; swatch.style.backgroundSize = '8px 8px'; swatch.style.backgroundPosition = '0 0, 0 4px, 4px -4px, -4px 0px'; swatch.style.background = 'transparent';
        }
    };

    row1.appendChild(swatch);
    row1.appendChild(inp);
    row1.appendChild(txt);
    container.appendChild(row1);

    const globalColors = (state.globalSettings && state.globalSettings.global_colors) || [];
    const recentColors = getRecentColors();

    if (globalColors.length > 0 || recentColors.length > 0) {
        const paletteDiv = document.createElement('div');
        paletteDiv.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';

        if (globalColors.length > 0) {
            const glLabel = document.createElement('div');
            glLabel.textContent = 'Cores globais';
            glLabel.style.cssText = 'font-size:.65rem;font-weight:600;color:var(--pb-accent);text-transform:uppercase;letter-spacing:.5px';
            paletteDiv.appendChild(glLabel);

            const glGrid = document.createElement('div');
            glGrid.style.cssText = 'display:flex;flex-wrap:wrap;gap:3px';
            globalColors.forEach(c => {
                const colorVal = c.value || c.color || '#6366f1';
                const colorName = c.name || c.label || '';
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.style.cssText = 'width:22px;height:22px;border-radius:4px;border:2px solid transparent;background:' + colorVal + ';cursor:pointer;transition:all .15s';
                dot.title = colorName + ' (' + colorVal + ')';
                dot.onmouseenter = () => { dot.style.borderColor = 'var(--pb-accent)'; dot.style.transform = 'scale(1.15)'; };
                dot.onmouseleave = () => { dot.style.borderColor = 'transparent'; dot.style.transform = 'scale(1)'; };
                dot.onclick = () => update(colorVal);
                glGrid.appendChild(dot);
            });
            paletteDiv.appendChild(glGrid);
        }

        if (recentColors.length > 0) {
            const rcLabel = document.createElement('div');
            rcLabel.textContent = 'Recentes';
            rcLabel.style.cssText = 'font-size:.65rem;font-weight:600;color:var(--pb-text2);text-transform:uppercase;letter-spacing:.5px;margin-top:.2rem';
            paletteDiv.appendChild(rcLabel);

            const rcGrid = document.createElement('div');
            rcGrid.style.cssText = 'display:flex;flex-wrap:wrap;gap:3px';
            recentColors.forEach(c => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.style.cssText = 'width:18px;height:18px;border-radius:3px;border:1px solid var(--pb-border);background:' + c + ';cursor:pointer;transition:all .15s';
                dot.title = c;
                dot.onmouseenter = () => { dot.style.borderColor = 'var(--pb-accent)'; dot.style.transform = 'scale(1.15)'; };
                dot.onmouseleave = () => { dot.style.borderColor = 'var(--pb-border)'; dot.style.transform = 'scale(1)'; };
                dot.onclick = () => update(c);
                rcGrid.appendChild(dot);
            });
            paletteDiv.appendChild(rcGrid);
        }

        container.appendChild(paletteDiv);
    }

    const clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.textContent = 'Limpar';
    clearBtn.style.cssText = 'padding:.2rem .5rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text2);cursor:pointer;font-size:.7rem;align-self:flex-start;margin-top:.15rem';
    clearBtn.onclick = () => update('');
    container.appendChild(clearBtn);

    return container;
}
