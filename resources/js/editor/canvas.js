import { escHtml } from './utils.js';
import { icon } from './icons.js';
import { widgetLabel, elementName } from './i18n.js';

// Mirrors Element::valuesForDevice() in PHP: desktop -> tablet -> mobile cascade
export const RESPONSIVE_DEVICES = ['tablet', 'mobile'];
export const LEGACY_RESPONSIVE_KEYS = ['padding_top', 'padding_bottom', 'padding_left', 'padding_right', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'font_size', 'line_height', 'letter_spacing', 'width', 'max_width', 'height', 'border_radius', 'gap'];

export function deviceOverrides(el, device) {
    const saved = (el.responsive_settings && el.responsive_settings[device]) || {};
    const out = { settings: { ...(saved.settings || {}) }, styles: { ...(saved.styles || {}) } };
    for (const bucket of ['settings', 'styles']) {
        const source = el[bucket] || {};
        for (const key of LEGACY_RESPONSIVE_KEYS) {
            const legacy = key + '_' + device;
            if (source[legacy] !== undefined && source[legacy] !== '' && source[legacy] !== null && !(key in out[bucket])) {
                out[bucket][key] = source[legacy];
            }
        }
    }
    return out;
}

export function valuesForDevice(el, device = 'desktop') {
    let settings = { ...(el.settings || {}) };
    let styles = { ...(el.styles || {}) };
    if (device === 'desktop') return { settings, styles };
    for (const step of RESPONSIVE_DEVICES) {
        const o = deviceOverrides(el, step);
        settings = { ...settings, ...o.settings };
        styles = { ...styles, ...o.styles };
        if (step === device) break;
    }
    return { settings, styles };
}

// CSS properties that take a length: a bare number means pixels
const LENGTH_PROP = /^(margin|padding)(-(top|right|bottom|left))?$|(^|-)(width|height|radius|gap|size|top|left|right|bottom|spacing|indent)$/;

export function cssLength(prop, value) {
    const v = String(value).trim();
    // line-height also ends in "height" but a bare number there is a valid multiplier
    return prop !== 'line-height' && LENGTH_PROP.test(prop) && /^-?\d+(\.\d+)?$/.test(v) && v !== '0' ? v + 'px' : v;
}

/**
 * The inline style the published page puts on the element wrapper (Renderer::buildStyleString):
 * every plain style value, with camelCase / snake_case keys turned into CSS properties.
 */
export function wrapperStyleProps(styles) {
    const out = [];
    for (const [key, value] of Object.entries(styles || {})) {
        if (value === null || value === '' || typeof value === 'object' || typeof value === 'boolean') continue;
        const prop = key.replace(/([a-z0-9])([A-Z])/g, '$1-$2').replace(/_/g, '-').toLowerCase();
        out.push([prop, cssLength(prop, value)]);
    }
    return out;
}

// Applied to the element's content box so the editor shows margins/paddings like the published page
export function applyWrapperStyles(div, styles) {
    const box = div && div.querySelector(':scope > .pb-el-content');
    if (!box) return;
    box.removeAttribute('style');
    for (const [prop, value] of wrapperStyleProps(styles)) {
        if (/^[a-z-]+$/.test(prop)) box.style.setProperty(prop, value);
    }
}

// A thin hover zone between two consecutive top-level sections; its "+" creates a new section there
function addSectionInserters(state, dz) {
    const roots = [...dz.querySelectorAll(':scope > .pb-el')];
    roots.forEach((el, i) => {
        const next = roots[i + 1];
        if (!next || el.dataset.elType !== 'section' || next.dataset.elType !== 'section') return;
        const zone = document.createElement('div');
        zone.className = 'pb-section-inserter';
        zone.dataset.beforeId = next.dataset.elId;
        zone.innerHTML = `<button type="button" class="pb-section-inserter-btn" title="Adicionar uma seção aqui">${icon('plus', 16)}</button>`;
        zone.querySelector('button').onclick = (e) => {
            e.stopPropagation();
            if (state.insertSectionBefore) state.insertSectionBefore(parseInt(next.dataset.elId));
        };
        dz.insertBefore(zone, next);
    });
}

function currentDevice(state) {
    return (state && state.responsiveMode) || 'desktop';
}

export function renderCanvas(state, elements, parentEl) {
    const dz = document.getElementById('canvas-dropzone');
    if (!parentEl) {
        dz.innerHTML = '';
        if (!elements || elements.length === 0) {
            dz.innerHTML = `<div class="pb-empty-canvas" id="empty-canvas"><div class="pb-empty-icon">${icon('pointer', 44)}</div><p><strong>Arraste widgets do painel esquerdo</strong><br>para comecar a construir sua página</p></div>`;
            return;
        }
    }
    (elements || []).forEach(el => {
        const div = document.createElement('div');
        div.className = 'pb-el';
        div.dataset.elId = el.id;
        div.dataset.elType = el.type;
        div.dataset.isContainer = el.is_container ? 'true' : 'false';
        const device = currentDevice(state);
        const values = valuesForDevice(el, device);
        div.innerHTML = elementHtml(el, device);
        applyWrapperStyles(div, values.styles);
        div.onclick = (e) => { e.stopPropagation(); state.onSelectElement(el.id, e.ctrlKey || e.metaKey); };
        if (el.type === 'column') {
            // The .pb-el wrapper is the flex item inside the parent row, so it carries the width
            let pct = columnPercent({ ...values.settings, ...values.styles });
            // Same default as the published page: phones stack columns unless a phone width was chosen
            if (device === 'mobile' && valuesForDevice(el, 'mobile').settings.column_width === (el.settings || {}).column_width) pct = 100;
            div.style.flex = `0 1 ${pct}%`;
            div.style.maxWidth = `${pct}%`;
        }
        if (parentEl) parentEl.appendChild(div);
        else dz.appendChild(div);
        if (el.is_container) {
            const childContainer = document.createElement('div');
            childContainer.className = 'pb-el-children';
            childContainer.dataset.parentId = el.id;
            const s = { ...values.settings, ...values.styles };
            if (el.type === 'section') {
                childContainer.classList.add('pb-row');
                if (s.align_items) childContainer.style.alignItems = s.align_items;
                if (s.justify_content) childContainer.style.justifyContent = s.justify_content;
                if (s.flex_wrap) childContainer.style.flexWrap = s.flex_wrap;
                if (s.gap && s.gap !== 'default') childContainer.style.gap = s.gap;
            } else if (el.type === 'inner_section') {
                childContainer.classList.add('pb-row');
                const dir = s.column_direction || 'row';
                childContainer.style.flexDirection = dir;
                childContainer.style.flexWrap = 'nowrap';
                childContainer.style.gap = s.column_gap || '20px';
            }
            const host = div.querySelector(
                el.type === 'section' ? '.pb-section-content'
                : el.type === 'column' ? '.pb-column-content'
                : el.type === 'inner_section' ? '.pb-inner-section-editor'
                : ':scope > .pb-el-content'
            );
            (host || div).appendChild(childContainer);
            const hasChildren = el.children && el.children.length > 0;
            if (hasChildren) renderCanvas(state, el.children, childContainer);
            // Empty containers get a big "+", filled ones a small one at the end; both pick this container as insert target
            const adder = document.createElement('div');
            adder.className = hasChildren ? 'pb-empty-drop pb-add-inside' : 'pb-empty-drop';
            adder.dataset.parentId = el.id;
            adder.title = 'Clique e escolha um widget na aba Inserir para adicioná-lo aqui';
            adder.innerHTML = icon('plus', hasChildren ? 14 : 20);
            adder.onclick = (e) => { e.stopPropagation(); state.setInsertTarget(el.id); };
            childContainer.appendChild(adder);
        }
    });
    if (!parentEl) {
        addSectionInserters(state, dz);
        state.renderMath();
        setTimeout(() => initCanvasFeatures(), 100);
    }
}

const COL_CLASS_MAP = { 'col-1': 8.33, 'col-2': 16.67, 'col-3': 25, 'col-4': 33.33, 'col-5': 41.67, 'col-6': 50, 'col-7': 58.33, 'col-8': 66.67, 'col-9': 75, 'col-10': 83.33, 'col-11': 91.67, 'col-12': 100 };

export function columnPercent(s) {
    const colWidth = s.column_width || 'col-4';
    if (typeof colWidth === 'number') return Math.max(1, Math.min(100, colWidth));
    if (typeof colWidth === 'string' && COL_CLASS_MAP[colWidth]) return COL_CLASS_MAP[colWidth];
    if (typeof colWidth === 'string' && !colWidth.startsWith('col-') && !isNaN(parseFloat(colWidth))) {
        return Math.max(1, Math.min(100, parseFloat(colWidth) || 33));
    }
    return 33.33;
}

export function renderMath() {
    if (typeof katex === 'undefined') return;
    document.querySelectorAll('#canvas-dropzone .pb-math, #settings-body .pb-math').forEach(el => {
        if (el.closest('[contenteditable="true"]')) return;
        if (el.dataset.katexRendered) return;
        try {
            katex.render(el.getAttribute('data-formula'), el, {
                displayMode: el.getAttribute('data-display') === 'true',
                throwOnError: false
            });
            el.dataset.katexRendered = '1';
        } catch (e) {
            el.textContent = el.getAttribute('data-formula');
        }
    });
}

export function elementHtml(el, device = 'desktop') {
    let name = elementName(el);
    const values = valuesForDevice(el, device);
    const s = { ...values.settings, ...values.styles };
    let preview = '';
    switch (el.type) {
        case 'heading': {
            const tagSizeMap = {h1:'2.2em',h2:'1.8em',h3:'1.4em',h4:'1.15em',h5:'1em',h6:'.85em'};
            const sizeMap = {small:'1.2em',medium:'2.5em',large:'3em',xl:'3.5em',xxl:'4.5em'};
            const fs = s.size && sizeMap[s.size] ? sizeMap[s.size] : (tagSizeMap[s.tag] || '1.8em');
            preview = `<${s.tag || 'h2'} style="text-align:${s.alignment||'left'};color:${s.color||'#333'};font-size:${fs};font-weight:${s.font_weight||'700'};line-height:${s.line_height||'1.4'}">${escHtml(s.title||'Título')}</${s.tag || 'h2'}>`;
            break;
        }
        case 'text': preview = `<div style="text-align:${s.alignment||'left'};color:${s.color||'#666'};font-size:${s.font_size||'16px'};font-weight:${s.font_weight||'400'};line-height:${s.line_height||'1.7'}">${s.content||'<p>Conteúdo de texto</p>'}</div>`; break;
        case 'image':
            if (s.image && s.image.url) preview = `<div style="text-align:${s.alignment||'center'}"><img src="${escHtml(s.image.url)}" alt="${escHtml(s.image.alt||'')}" style="width:${s.width||'100%'};max-width:${s.max_width||'100%'};height:${s.height||'auto'};object-fit:${s.object_fit||'cover'};border-radius:${s.border_radius||'0px'};opacity:${s.opacity||1}"></div>`;
            else preview = `<div class="pb-image-placeholder" style="text-align:center;padding:2rem;color:#999">Nenhuma imagem selecionada</div>`;
            break;
        case 'button': {
            const sizeMap = {small:{p:'8px 16px',f:'14px'},medium:{p:'12px 24px',f:'16px'},large:{p:'16px 32px',f:'18px'},xl:{p:'20px 40px',f:'20px'}};
            const sz = sizeMap[s.size]||sizeMap.medium;
            const btn = `<button style="background-color:${s.background_color||'#007bff'};color:${s.text_color||'#fff'};border:${s.border_width||'0px'} solid ${s.border_color||'transparent'};border-radius:${s.border_radius||'4px'};padding:${sz.p};font-size:${sz.f};font-weight:${s.font_weight||'500'};cursor:pointer;display:inline-block">${escHtml(s.text||'Botão')}</button>`;
            preview = s.alignment !== 'stretch' ? `<div style="text-align:${s.alignment||'left'}">${btn}</div>` : btn;
            break;
        }
        case 'callout': {
            const typeStyles = {info:{bg:'#eff6ff',border:'#3b82f6',icon:'&#9432;',text:'#1e3a5f',title:'#1e40af'},success:{bg:'#f0fdf4',border:'#22c55e',icon:'&#10004;',text:'#14532d',title:'#166534'},warning:{bg:'#fffbeb',border:'#f59e0b',icon:'&#9888;',text:'#78350f',title:'#92400e'},danger:{bg:'#fef2f2',border:'#ef4444',icon:'&#10060;',text:'#7f1d1d',title:'#991b1b'},tip:{bg:'#f0f9ff',border:'#0ea5e9',icon:'&#128161;',text:'#0c3547',title:'#0c4a6e'},definition:{bg:'#faf5ff',border:'#a855f7',icon:'&#128214;',text:'#581c87',title:'#6b21a8'},theorem:{bg:'#fff7ed',border:'#f97316',icon:'&#9878;',text:'#7c2d12',title:'#9a3412'},exercise:{bg:'#ecfdf5',border:'#10b981',icon:'&#9998;',text:'#064e3b',title:'#065f46'},note:{bg:'#f8fafc',border:'#64748b',icon:'&#128221;',text:'#475569',title:'#334155'}};
            const st = typeStyles[s.type]||typeStyles.info;
            const borderStyle = s.border_style==='none'?'border-left:none;':s.border_style==='full'?`border:2px solid ${st.border};`:`border-left:4px solid ${st.border};`;
            const titleHtml = s.title ? `<div style="font-weight:700;font-size:1rem;margin-bottom:6px;color:${st.title}">${escHtml(s.title)}</div>` : '';
            preview = `<div style="background:${st.bg};${borderStyle}padding:${s.padding||'16px 20px'};border-radius:${s.border_radius||'8px'};color:${st.text}"><div style="display:flex;align-items:flex-start;gap:10px"><span style="font-size:1.2em;flex-shrink:0">${escHtml(s.icon)||st.icon}</span><div style="flex:1">${titleHtml}${s.content||'<p>Conteúdo do callout</p>'}</div></div></div>`;
            break;
        }
        case 'table': {
            const rows = parseInt(s.rows)||3;
            const cols = parseInt(s.cols)||3;
            const hd = s.has_header!==false;
            const bw = s.border_width||'1px';
            const bc = s.border_color||'#e2e8f0';
            let html = `<table style="width:${s.width||'100%'};border-collapse:collapse;font-size:${s.font_size||'14px'}"><tbody>`;
            for (let r=0;r<rows;r++) {
                html += '<tr>';
                for (let c=0;c<cols;c++) {
                    if (r===0&&hd) html += `<th style="background:#f1f5f9;border:${bw} solid ${bc};padding:${s.cell_padding||'10px 14px'};font-weight:600;text-align:left">Cabecalho ${c+1}</th>`;
                    else html += `<td style="border:${bw} solid ${bc};padding:${s.cell_padding||'10px 14px'}">Conteúdo</td>`;
                }
                html += '</tr>';
            }
            html += '</tbody></table>';
            preview = `<div style="overflow-x:auto">${html}</div>`;
            break;
        }
        case 'math': {
            const formula = s.formula||'x^2 + y^2 = z^2';
            const mode = s.display_mode===false?'inline':'block';
            preview = mode==='block'
                ? `<div style="text-align:${s.alignment||'center'};padding:16px 0"><span class="pb-math" data-formula="${escHtml(formula)}" data-display="true" style="font-size:${s.font_size||'24px'};color:${s.color||'#333'}"></span>${s.label?`<div style="margin-top:6px;font-size:0.8em;color:#666;font-style:italic">${escHtml(s.label)}</div>`:''}</div>`
                : `<span class="pb-math" data-formula="${escHtml(formula)}" data-display="false" style="font-size:${s.font_size||'16px'};color:${s.color||'#333'}"></span>`;
            break;
        }
        case 'video': {
            // Same rule as VideoWidget::fileSource(): uploaded file, or a direct video URL, plays in <video>
            const vFile = (s.video_file || '').trim();
            const vUrl = (s.video_url || '').trim();
            const fileSrc = vFile && (s.video_type === 'upload' || !vUrl) ? vFile
                : (s.video_type !== 'upload' && /\.(mp4|webm|ogv|ogg|mov|m4v)(\?.*)?$/i.test(vUrl) ? vUrl : '');
            if (fileSrc) {
                const ratioMap = {'16:9':'56.25%','4:3':'75%','1:1':'100%','21:9':'42.86%'};
                const pad = ratioMap[s.aspect_ratio]||'56.25%';
                const wStyle = `width:${s.width||'100%'};max-width:${s.max_width||'100%'};margin:0`;
                const mAlign = s.alignment==='center'?'margin-left:auto;margin-right:auto':s.alignment==='right'?'margin-left:auto':'';
                preview = `<div style="${wStyle};${mAlign}"><div style="position:relative;padding-bottom:${pad};height:0;overflow:hidden;border-radius:8px;background:#000"><video src="${escHtml(fileSrc)}" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;border:0"${s.controls!==false?' controls':''}${s.loop?' loop':''}${s.mute?' muted':''} playsinline preload="metadata"></video></div></div>`;
            } else if (s.video_url) {
                const ratioMap = {'16:9':'56.25%','4:3':'75%','1:1':'100%','21:9':'42.86%'};
                const pad = ratioMap[s.aspect_ratio]||'56.25%';
                let embedUrl = s.video_url;
                const ytMatch = s.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
                if (ytMatch) {
                    const params = ['rel=0'];
                    params.push(s.controls!==false?'controls=1':'controls=0');
                    params.push(s.autoplay?'autoplay=1':'autoplay=0');
                    params.push(s.loop?'loop=1':'loop=0');
                    params.push(s.mute?'mute=1':'mute=0');
                    if (s.loop) params.push('playlist='+ytMatch[1]);
                    if (s.start_time>0) params.push('start='+s.start_time);
                    if (s.end_time>0) params.push('end='+s.end_time);
                    embedUrl = 'https://www.youtube.com/embed/'+ytMatch[1]+'?'+params.join('&');
                } else {
                    const vmMatch = s.video_url.match(/vimeo\.com\/(\d+)/);
                    if (vmMatch) {
                        const params = [];
                        params.push(s.autoplay?'autoplay=1':'autoplay=0');
                        params.push(s.loop?'loop=1':'loop=0');
                        params.push(s.mute?'muted=1':'muted=0');
                        params.push('title=0','byline=0','portrait=0');
                        embedUrl = 'https://player.vimeo.com/video/'+vmMatch[1]+'?'+params.join('&');
                    }
                }
                const wStyle = `width:${s.width||'100%'};max-width:${s.max_width||'100%'};margin:0`;
                const mAlign = s.alignment==='center'?'margin-left:auto;margin-right:auto':s.alignment==='right'?'margin-left:auto':'';
                preview = `<div style="${wStyle};${mAlign}"><div style="position:relative;padding-bottom:${pad};height:0;overflow:hidden;border-radius:8px"><iframe src="${escHtml(embedUrl)}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" title="video"></iframe></div></div>`;
            } else {
                preview = '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">Nenhum vídeo selecionado</div>';
            }
            break;
        }
        case 'divider': {
            const w = s.width != null ? s.width : 100;
            const t = s.thickness || 1;
            const st = s.style || 'solid';
            const c = s.color || '#e2e8f0';
            const mt = s.space_before != null ? s.space_before : 20;
            const mb = s.space_after != null ? s.space_after : 20;
            preview = `<hr style="border:none;border-top:${t}px ${st} ${c};width:${w}%;margin:${mt}px auto ${mb}px">`;
            break;
        }
        case 'spacer': {
            const sp = s.space != null ? s.space : 50;
            preview = `<div style="height:${sp}px;background:repeating-linear-gradient(45deg,transparent,transparent 5px,rgba(99,102,241,.06) 5px,rgba(99,102,241,.06) 10px);border:1px dashed rgba(99,102,241,.25);border-radius:4px;position:relative"><span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:.7rem;color:rgba(99,102,241,.6);pointer-events:none">${sp}px</span></div>`;
            break;
        }
        case 'icon': {
            const ic = s.icon || 'fas fa-star';
            const isz = s.icon_size || 48;
            const icc = s.color || '#6366f1';
            const ica = s.align || 'center';
            let icHtml = `<i class="${ic}" style="font-size:${isz}px;color:${icc};line-height:1"></i>`;
            if (s.link) {
                const icTarget = s.link_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
                icHtml = `<a href="${s.link}"${icTarget} style="text-decoration:none;display:inline-block">${icHtml}</a>`;
            }
            preview = `<div style="text-align:${ica};padding:8px 0">${icHtml}</div>`;
            break;
        }
        case 'gallery': {
            const imgs = Array.isArray(s.images) ? s.images : [];
            const cols = s.columns || 3;
            const gGap = s.gap != null ? s.gap : 10;
            const br = s.border_radius != null ? s.border_radius : 4;
            if (imgs.length === 0) {
                preview = '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">Nenhuma imagem selecionada</div>';
            } else {
                let ghtml = `<div style="display:grid;grid-template-columns:repeat(${cols},1fr);gap:${gGap}px">`;
                imgs.slice(0, 12).forEach(img => {
                    ghtml += `<div style="overflow:hidden;border-radius:${br}px;aspect-ratio:1;background:#f1f5f9"><img src="${escHtml(img.url||'')}" alt="${escHtml(img.alt||'')}" style="width:100%;height:100%;object-fit:cover;border-radius:${br}px"></div>`;
                });
                if (imgs.length > 12) ghtml += `<div style="display:flex;align-items:center;justify-content:center;aspect-ratio:1;background:#f1f5f9;border-radius:${br}px;font-size:.75rem;color:#666">+${imgs.length - 12}</div>`;
                ghtml += '</div>';
                preview = ghtml;
            }
            break;
        }
        case 'form': {
            const fields = Array.isArray(s.fields) ? s.fields : [];
            const fbr = s.field_radius != null ? s.field_radius : 6;
            const fsp = s.field_spacing != null ? s.field_spacing : 12;
            const bc = s.button_color || '#6366f1';
            const btc = s.button_text_color || '#fff';
            const bw = s.button_width === 'full' ? 'width:100%;' : '';
            let fhtml = '';
            fields.forEach(f => {
                const req = f.required ? ' <span style="color:#ef4444">*</span>' : '';
                fhtml += `<div style="margin-bottom:${fsp}px"><label style="display:block;margin-bottom:4px;font-size:13px;font-weight:500;color:#374151">${escHtml(f.label||'')}${req}</label>`;
                if (f.type === 'textarea') {
                    fhtml += `<div style="width:100%;min-height:50px;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#f9fafb;font-size:12px;color:#9ca3af">Texto longo</div>`;
                } else if (f.type === 'select') {
                    fhtml += `<div style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#fff;font-size:12px;color:#9ca3af;display:flex;justify-content:space-between"><span>Selecione...</span><span>&#9660;</span></div>`;
                } else if (f.type === 'checkbox' || f.type === 'radio') {
                    fhtml += `<div style="display:flex;align-items:center;gap:6px"><input type="${f.type}" disabled style="width:auto"><span style="font-size:12px;color:#374151">${escHtml(f.label||'')}</span></div>`;
                } else {
                    fhtml += `<div style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#f9fafb;font-size:12px;color:#9ca3af">${escHtml(f.type||'text')}</div>`;
                }
                fhtml += '</div>';
            });
            const btnLabel = escHtml(s.button_text || 'Enviar');
            fhtml += `<button type="button" style="padding:10px 24px;background:${bc};color:${btc};border:none;border-radius:${fbr}px;font-size:13px;font-weight:500;cursor:default;${bw}">${btnLabel}</button>`;
            preview = fhtml;
            break;
        }
        case 'tabs': {
            const tabs = Array.isArray(s.tabs) ? s.tabs : [];
            const tc = s.tab_color || '#6366f1';
            const bc2 = s.border_color || '#e2e8f0';
            const ati = s.active_tab || 0;
            if (tabs.length === 0) { preview = '<div style="text-align:center;padding:1rem;color:#999">Nenhuma aba</div>'; break; }
            let thead = '<div style="display:flex;border-bottom:2px solid ' + bc2 + '">';
            let tbody = '';
            tabs.forEach((t, i) => {
                const active = i === ati;
                thead += `<button type="button" style="padding:8px 16px;font-size:13px;border:none;border-bottom:3px solid ${active?tc:'transparent'};margin-bottom:-2px;background:${active?'#fff':'transparent'};color:${active?tc:'#6b7280'};font-weight:${active?'600':'400'};cursor:default">${escHtml(t.title||'Aba '+(i+1))}</button>`;
                tbody += `<div style="display:${active?'block':'none'};padding:16px;font-size:13px;color:#6b7280">${t.content?escHtml(String(t.content).substring(0,100)):'...'}</div>`;
            });
            thead += '</div>';
            preview = thead + tbody;
            break;
        }
        case 'accordion': {
            const items = Array.isArray(s.items) ? s.items : [];
            const ac = s.tab_color || '#6366f1';
            const ab = s.border_color || '#e2e8f0';
            if (items.length === 0) { preview = '<div style="text-align:center;padding:1rem;color:#999">Nenhum item</div>'; break; }
            let ahtml = '';
            items.forEach((item, i) => {
                const isOpen = item.open;
                ahtml += `<div style="border:1px solid ${ab};border-radius:8px;overflow:hidden;margin-bottom:2px">`;
                ahtml += `<div style="display:flex;align-items:center;padding:10px 14px;font-size:13px;font-weight:500;background:${isOpen?ac:'#f9fafb'};color:${isOpen?'#fff':'#374151'}"><span style="display:inline-block;transform:rotate(${isOpen?'90':'0'}deg);margin-right:8px;font-size:10px">&#9654;</span>${escHtml(item.title||'Seção '+(i+1))}</div>`;
                if (isOpen) ahtml += `<div style="padding:12px;font-size:12px;color:#6b7280;background:#fff">${item.content?escHtml(String(item.content).substring(0,80)):'...'}</div>`;
                ahtml += '</div>';
            });
            preview = ahtml;
            break;
        }
        case 'section': {
            const bgColor = s.background_color || 'transparent';
            // "20" typed without a unit means 20px (SectionWidget does the same)
            const pt = cssLength('padding-top', s.padding_top || '40px');
            const pr = cssLength('padding-right', s.padding_right || '0px');
            const pb = cssLength('padding-bottom', s.padding_bottom || '40px');
            const pl = cssLength('padding-left', s.padding_left || '0px');
            const mt = cssLength('margin-top', s.margin_top || '10px');
            const mb = cssLength('margin-bottom', s.margin_bottom || '10px');
            const br = s.border_radius || '0px';
            const cw = s.content_width || '1140px';
            const layout = s.layout || 'boxed';
            const bgImage = s.background_image && s.background_image.url ? s.background_image.url : '';
            const bgType = s.background_type || 'none';
            const bgGradient = s.background_gradient;
            let secStyle = `padding:${pt} ${pr} ${pb} ${pl};margin:${mt} 0 ${mb} 0;border-radius:${br};position:relative;`;
            if (bgType === 'classic') {
                if (bgColor && bgColor !== 'transparent') secStyle += `background-color:${bgColor};`;
                if (bgImage) secStyle += `background-image:url('${escHtml(bgImage)}');background-position:center center;background-size:cover;background-repeat:no-repeat;`;
            } else if (bgType === 'gradient' && bgGradient) {
                const gType = bgGradient.type || 'linear';
                const angle = bgGradient.angle || 180;
                const c1 = bgGradient.color1 || '#6366f1';
                const c2 = bgGradient.color2 || '#8b5cf6';
                const p1 = bgGradient.position1 || 0;
                const p2 = bgGradient.position2 || 100;
                if (gType === 'radial') secStyle += `background:radial-gradient(circle,${c1} ${p1}%,${c2} ${p2}%);`;
                else secStyle += `background:linear-gradient(${angle}deg,${c1} ${p1}%,${c2} ${p2}%);`;
            }
            const minH = s.min_height && s.min_height !== 'auto' ? `min-height:${s.min_height};` : '';
            if (minH) secStyle += minH;
            const innerStyle = layout === 'boxed' ? `max-width:${cw};margin:0 auto;position:relative;z-index:2;` : 'position:relative;z-index:2;';

            let videoHtml = '';
            if (bgType === 'video' && s.video_background) {
                const vUrl = escHtml(s.video_background);
                const loop = s.video_bg_loop ? 'loop' : '';
                const mute = s.video_bg_mute ? 'muted' : '';
                videoHtml = `<video autoplay ${loop} ${mute} playsinline style="position:absolute;top:50%;left:50%;min-width:100%;min-height:100%;width:auto;height:auto;transform:translate(-50%,-50%);object-fit:cover;z-index:0;pointer-events:none" src="${vUrl}"></video>`;
            }

            let overlayHtml = '';
            const ovColor = s.background_overlay_color || '#000000';
            const ovOpacity = parseInt(s.background_overlay_opacity) || 0;
            const ovBlend = s.background_overlay_blend || 'normal';
            if (ovOpacity > 0) {
                const alpha = ovOpacity / 100;
                const r = parseInt(ovColor.slice(1, 3), 16) || 0;
                const g = parseInt(ovColor.slice(3, 5), 16) || 0;
                const b = parseInt(ovColor.slice(5, 7), 16) || 0;
                overlayHtml = `<div style="position:absolute;top:0;left:0;right:0;bottom:0;background-color:rgba(${r},${g},${b},${alpha});mix-blend-mode:${ovBlend};pointer-events:none;z-index:1;"></div>`;
            }

            const shapePaths = {
                tilt: 'M0,0L1200,0L1200,120L0,120Z',
                waves: 'M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z',
                mountains: 'M0,0L1200,0L1200,120L0,120Z M0,60L200,20L400,80L600,10L800,70L1000,30L1200,60L1200,120L0,120Z',
                clouds: 'M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z',
                triangles: 'M0,0L1200,0L1200,120L0,120Z M0,0L600,120L1200,0L1200,120L0,120Z',
                drip: 'M0,0V120H1200V0C1200,0,1100,60,900,60S500,0,300,60,0,0,0,0Z',
                'clouds-dramatic': 'M0,0V60c0,0,200-40,400,0s400,60,400,0,200-40,400,0V0Z',
                'tilt-opacity': 'M0,0L1200,0L1200,120L0,120Z',
                'mountains-peak': 'M0,0L1200,0L1200,120L0,120Z M0,80L150,30L300,90L500,10L700,70L900,20L1100,60L1200,40L1200,120L0,120Z',
            };

            let shapeTopHtml = '';
            const topType = s.shape_divider_top || 'none';
            if (topType !== 'none' && shapePaths[topType]) {
                const topColor = s.shape_divider_top_color || '#ffffff';
                const topHeight = parseInt(s.shape_divider_top_height) || 100;
                shapeTopHtml = `<div style="position:absolute;top:0;left:0;width:100%;height:${topHeight}px;z-index:1;transform:rotate(180deg);"><svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="position:absolute;bottom:0;left:0;width:100%;height:100%;"><path d="${shapePaths[topType]}" style="fill:${topColor};"></path></svg></div>`;
            }

            let shapeBottomHtml = '';
            const bottomType = s.shape_divider_bottom || 'none';
            if (bottomType !== 'none' && shapePaths[bottomType]) {
                const bottomColor = s.shape_divider_bottom_color || '#ffffff';
                const bottomHeight = parseInt(s.shape_divider_bottom_height) || 100;
                shapeBottomHtml = `<div style="position:absolute;bottom:0;left:0;width:100%;height:${bottomHeight}px;z-index:1;"><svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="position:absolute;bottom:0;left:0;width:100%;height:100%;"><path d="${shapePaths[bottomType]}" style="fill:${bottomColor};"></path></svg></div>`;
            }

            preview = `<div class="pb-section-editor" style="${secStyle}">${videoHtml}${overlayHtml}${shapeTopHtml}<div class="pb-section-header">Seção</div><div class="pb-section-content" style="${innerStyle}"></div>${shapeBottomHtml}</div>`;
            break;
        }
        case 'column': {
            const vAlign = s.vertical_alignment || 'stretch';
            const cPos = s.content_position || 'top';
            const tAlign = s.text_align || '';
            const cBg = s.background_color || 'transparent';
            const cpt = s.padding_top || '10px';
            const cpr = s.padding_right || '10px';
            const cpb = s.padding_bottom || '10px';
            const cpl = s.padding_left || '10px';
            const cm = s.margin || '0px';
            const cbr = s.border_radius || '0px';
            let colStyle = `padding:${cpt} ${cpr} ${cpb} ${cpl};border-radius:${cbr};display:flex;flex-direction:column;align-self:${vAlign};justify-content:${cPos};`;
            if (tAlign) colStyle += `text-align:${tAlign};`;
            if (cm) colStyle += `margin:${cm};`;
            if (cBg && cBg !== 'transparent') colStyle += `background-color:${cBg};`;
            // Width lives on the .pb-el wrapper (see renderCanvas); the inner box fills it
            colStyle += 'width:100%;height:100%;box-sizing:border-box;';
            preview = `<div class="pb-column-editor" style="${colStyle}"><div class="pb-column-header">Coluna</div><div class="pb-column-content"></div></div>`;
            break;
        }
        case 'inner_section': {
            const cols = parseInt(s.columns) || 2;
            const iBg = s.background_color || 'transparent';
            const ipt = s.padding_top || '0px';
            const ipr = s.padding_right || '0px';
            const ipb = s.padding_bottom || '0px';
            const ipl = s.padding_left || '0px';
            const ibr = s.border_radius || '0px';
            let isStyle = `padding:${ipt} ${ipr} ${ipb} ${ipl};min-height:60px;border:1px dashed rgba(99,102,241,.3);border-radius:4px;position:relative;`;
            if (iBg && iBg !== 'transparent') isStyle += `background-color:${iBg};`;
            preview = `<div class="pb-inner-section-editor" style="${isStyle}"><div style="position:absolute;top:2px;left:4px;font-size:.55rem;color:var(--pb-text2);text-transform:uppercase;letter-spacing:.5px;">Seção Interna (${cols} colunas)</div></div>`;
            break;
        }
        case 'counter': {
            const num = parseInt(s.number) || 0;
            const prefix = escHtml(s.prefix || '');
            const suffix = escHtml(s.suffix || '');
            const sep = s.separator !== false;
            const dur = s.duration || 2000;
            const fs = s.font_size || '2.5em';
            const c = s.color || '#6366f1';
            const al = s.alignment || 'center';
            preview = `<div class="pb-counter" data-count="${num}" data-duration="${dur}" data-separator="${sep}" style="text-align:${al}"><div style="font-size:${fs};font-weight:700;color:${c}"><span class="pb-counter-prefix">${prefix}</span><span class="pb-counter-num">0</span><span class="pb-counter-suffix">${suffix}</span></div>${s.title?`<div style="font-size:.85em;color:#666;margin-top:4px">${escHtml(s.title)}</div>`:''}</div>`;
            break;
        }
        case 'progress_bar': {
            const pct = Math.min(100, Math.max(0, parseInt(s.percentage) || 70));
            const h = s.height || '20px';
            const c = s.color || '#6366f1';
            const bg = s.background_color || '#e2e8f0';
            const br = s.border_radius || '10px';
            const showPct = s.show_percentage !== false;
            preview = `<div style="margin:8px 0">${s.title?`<div style="font-size:.8em;font-weight:600;margin-bottom:4px;color:#333">${escHtml(s.title)}</div>`:''}<div style="height:${h};background:${bg};border-radius:${br};overflow:hidden"><div class="pb-progress-fill" style="height:100%;width:${pct}%;background:${c};border-radius:${br};transition:width 1s ease"></div></div>${showPct?`<div style="text-align:right;font-size:.75em;color:#666;margin-top:2px">${pct}%</div>`:''}</div>`;
            break;
        }
        case 'social_icons': {
            const icons = Array.isArray(s.icons) ? s.icons : [];
            const cols = s.columns || 4;
            const isz = s.icon_size || 32;
            const gap = s.gap || 10;
            const al = s.alignment || 'center';
            const platformIcons = { facebook: 'fab fa-facebook-f', twitter: 'fab fa-twitter', instagram: 'fab fa-instagram', youtube: 'fab fa-youtube', linkedin: 'fab fa-linkedin-in', github: 'fab fa-github', email: 'fas fa-envelope', phone: 'fas fa-phone' };
            const platformColors = { facebook: '#1877F2', twitter: '#1DA1F2', instagram: '#E4405F', youtube: '#FF0000', linkedin: '#0A66C2', github: '#333', email: '#EA4335', phone: '#25D366' };
            let html = `<div style="display:flex;flex-wrap:wrap;gap:${gap}px;justify-content:${al==='left'?'flex-start':al==='right'?'flex-end':'center'}">`;
            icons.forEach(ic => {
                const faIcon = platformIcons[ic.platform] || 'fas fa-link';
                const color = ic.color || platformColors[ic.platform] || '#666';
                html += `<a href="${escHtml(ic.url || '#')}" target="_blank" rel="noopener" style="width:${isz}px;height:${isz}px;border-radius:50%;background:${color};color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:${Math.round(isz*0.45)}px"><i class="${faIcon}"></i></a>`;
            });
            html += '</div>';
            preview = html;
            break;
        }
        case 'icon_box': {
            const ic = s.icon || 'fas fa-star';
            const isz = s.icon_size || 48;
            const icc = s.icon_color || '#6366f1';
            const tc = s.title_color || '#333';
            const dc = s.description_color || '#666';
            const pos = s.icon_position || 'left';
            const al = s.alignment || 'left';
            const dir = pos === 'top' ? 'flex-direction:column;align-items:center;text-align:center' : 'flex-direction:row;align-items:flex-start';
            preview = `<div style="display:flex;gap:16px;${dir};text-align:${al}"><div style="font-size:${isz}px;color:${icc};flex-shrink:0;line-height:1"><i class="${ic}"></i></div><div><div style="font-size:1.1em;font-weight:600;color:${tc};margin-bottom:4px">${escHtml(s.title || 'Título da caixa')}</div><div style="font-size:.9em;color:${dc};line-height:1.5">${escHtml(s.description || 'Escreva a descrição aqui')}</div></div></div>`;
            break;
        }
        case 'image_box': {
            const img = s.image && s.image.url ? s.image.url : '';
            const tag = s.title_tag || 'h3';
            const tc = s.title_color || '#333';
            const dc = s.description_color || '#666';
            const al = s.alignment || 'center';
            const br = s.border_radius || '8px';
            const iw = s.image_width || '100%';
            let html = `<div style="text-align:${al}">`;
            if (img) html += `<img src="${escHtml(img)}" alt="${escHtml(s.image?.alt||'')}" style="width:${iw};border-radius:${br};margin-bottom:8px">`;
            html += `<${tag} style="font-size:1.1em;font-weight:600;color:${tc};margin-bottom:4px">${escHtml(s.title || 'Caixa com Imagem')}</${tag}>`;
            html += `<div style="font-size:.9em;color:${dc};line-height:1.5">${escHtml(s.description || 'Texto de descrição')}</div></div>`;
            preview = html;
            break;
        }
        case 'testimonial': {
            const tc = s.text_color || '#555';
            const nc = s.name_color || '#333';
            const rating = parseInt(s.rating) || 0;
            let stars = '';
            for (let i = 0; i < 5; i++) stars += i < rating ? '&#9733;' : '&#9734;';
            const avatar = s.avatar && s.avatar.url ? `<img src="${escHtml(s.avatar.url)}" style="width:50px;height:50px;border-radius:50%;object-fit:cover">` : `<div style="width:50px;height:50px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#999">&#128100;</div>`;
            preview = `<div style="text-align:center;padding:16px;max-width:500px;margin:0 auto"><div style="color:#f59e0b;font-size:1.1em;margin-bottom:8px">${stars}</div><div style="color:${tc};font-style:italic;margin-bottom:12px;line-height:1.6">&ldquo;${escHtml(s.content || 'Escreva aqui o depoimento.')}&rdquo;</div><div style="display:flex;align-items:center;justify-content:center;gap:10px">${avatar}<div><div style="font-weight:600;color:${nc};font-size:.9em">${escHtml(s.name || 'Nome')}</div><div style="font-size:.75em;color:#999">${escHtml((s.position||'')+(s.company?', '+s.company:''))}</div></div></div></div>`;
            break;
        }
        case 'price_table': {
            const feat = Array.isArray(s.features) ? s.features : [];
            const fc = s.featured_color || '#6366f1';
            const isFeat = s.featured;
            const border = isFeat ? `border:2px solid ${fc};transform:scale(1.03)` : 'border:1px solid #e2e8f0';
            let html = `<div style="border-radius:12px;${border};padding:24px;text-align:center;max-width:320px;margin:0 auto;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.06)">`;
            if (isFeat) html += `<div style="background:${fc};color:#fff;padding:4px 12px;border-radius:20px;font-size:.7rem;font-weight:600;display:inline-block;margin-bottom:8px">POPULAR</div>`;
            html += `<div style="font-size:1.1em;font-weight:600;margin-bottom:8px">${escHtml(s.title || 'Plano')}</div>`;
            html += `<div style="font-size:2em;font-weight:700;color:${fc}">${escHtml(s.currency||'R$')}${escHtml(s.price||'29')}</div>`;
            if (s.period) html += `<div style="font-size:.8em;color:#999;margin-bottom:16px">${escHtml(s.period)}</div>`;
            html += '<ul style="list-style:none;padding:0;margin:16px 0;text-align:left">';
            feat.forEach(f => {
                const icon = f.included ? '<span style="color:#22c55e">&#10003;</span>' : '<span style="color:#ef4444">&#10007;</span>';
                html += `<li style="padding:6px 0;font-size:.85em;border-bottom:1px solid #f1f5f9">${icon} ${escHtml(f.text || '')}</li>`;
            });
            html += '</ul>';
            html += `<button style="background:${isFeat?fc:'#fff'};color:${isFeat?'#fff':fc};border:2px solid ${fc};padding:10px 24px;border-radius:8px;font-weight:600;cursor:pointer;width:100%">${escHtml(s.button_text || 'Escolher')}</button></div>`;
            preview = html;
            break;
        }
        case 'countdown': {
            const c = s.color || '#6366f1';
            const bg = s.background_color || '#f1f5f9';
            const al = s.alignment || 'center';
            const boxStyle = `display:inline-flex;flex-direction:column;align-items:center;padding:12px 16px;background:${bg};border-radius:8px;min-width:60px;margin:4px`;
            preview = `<div class="pb-countdown" data-target="${escHtml(s.target_date||'2026-12-31')}" style="text-align:${al}"><div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px"><div style="${boxStyle}"><div class="pb-cd-days" style="font-size:1.8em;font-weight:700;color:${c}">00</div><div style="font-size:.7em;color:#999">${escHtml(s.days_label||'Dias')}</div></div><div style="${boxStyle}"><div class="pb-cd-hours" style="font-size:1.8em;font-weight:700;color:${c}">00</div><div style="font-size:.7em;color:#999">${escHtml(s.hours_label||'Horas')}</div></div><div style="${boxStyle}"><div class="pb-cd-mins" style="font-size:1.8em;font-weight:700;color:${c}">00</div><div style="font-size:.7em;color:#999">${escHtml(s.minutes_label||'Min')}</div></div><div style="${boxStyle}"><div class="pb-cd-secs" style="font-size:1.8em;font-weight:700;color:${c}">00</div><div style="font-size:.7em;color:#999">${escHtml(s.seconds_label||'Seg')}</div></div></div></div>`;
            break;
        }
        case 'google_maps': {
            const lat = s.latitude || '-15.7975';
            const lng = s.longitude || '-47.8919';
            const zm = s.zoom || 15;
            const h = s.height || '400px';
            const br = s.border_radius || '8px';
            const mt = s.map_type || 'roadmap';
            preview = `<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=${lng-0.01},${lat-0.01},${lng+0.01},${lat+0.01}&layer=mapnik&marker=${lat},${lng}" style="width:100%;height:${h};border:none;border-radius:${br}" loading="lazy" title="Map"></iframe>`;
            break;
        }
        case 'carousel': {
            const imgs = Array.isArray(s.images) ? s.images : [];
            const cols = s.columns || 1;
            const br = s.border_radius || '8px';
            if (imgs.length === 0) {
                preview = '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">Nenhuma imagem no carrossel</div>';
            } else {
                let html = `<div class="pb-carousel" style="display:flex;overflow-x:auto;scroll-snap-type:x mandatory;gap:10px;padding:4px 0;-webkit-overflow-scrolling:touch">`;
                imgs.forEach(img => {
                    html += `<div style="flex:0 0 ${100/cols}%;scroll-snap-align:start;min-width:0"><img src="${escHtml(img.url||'')}" alt="${escHtml(img.alt||'')}" style="width:100%;height:200px;object-fit:cover;border-radius:${br}"></div>`;
                });
                html += '</div>';
                preview = html;
            }
            break;
        }
        case 'mega_menu': {
            const items = Array.isArray(s.menu_items) ? s.menu_items : [];
            const dir = s.layout === 'vertical' ? 'column' : 'row';
            let mhtml = `<div style="display:flex;flex-direction:${dir};gap:${s.item_spacing || 0}px;background:${s.background_color || '#fff'};padding:0">`;
            if (items.length === 0) {
                mhtml += '<div style="padding:8px 16px;color:#999;font-size:12px">Nenhum item de menu</div>';
            } else {
                items.forEach(item => {
                    const ic = item.icon ? `<i class="${item.icon}" style="margin-right:4px"></i>` : '';
                    mhtml += `<a href="${item.link || '#'}" style="padding:${s.item_padding?.top||'12px'} ${s.item_padding?.right||'20px'} ${s.item_padding?.bottom||'12px'} ${s.item_padding?.left||'20px'};text-decoration:none;color:#333;font-size:13px;display:flex;align-items:center;gap:6px;border-radius:4px">${ic}${item.label || 'Item do menu'}</a>`;
                });
            }
            mhtml += '</div>';
            preview = mhtml;
            break;
        }
        case 'lottie': {
            if (s.lottie_url) {
                preview = `<div style="display:flex;justify-content:${s.alignment||'center'};padding:8px 0"><lottie-player src="${escHtml(s.lottie_url)}" ${s.loop ? 'loop' : ''} ${s.autoplay ? 'autoplay' : ''} speed="${s.speed||1}" direction="${s.direction||1}" style="width:${s.width||300}px;height:${s.height||300}px;${s.background ? 'background:'+s.background+';' : ''}" renderer="${s.renderer||'svg'}"></lottie-player></div>`;
            } else {
                preview = '<div style="display:flex;justify-content:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">🎬 Lottie Animation</div>';
            }
            break;
        }
        default: preview = `<div class="pb-el-placeholder">${escHtml(widgetLabel(el.type))}</div>`;
    }
    return `<div class="pb-el-drag" title="Arrastar para reordenar">${icon('grip', 14)}</div><div class="pb-el-toolbar"><span class="pb-el-name">${escHtml(name)}</span><span class="pb-el-type">${escHtml(widgetLabel(el.type))}</span><span style="flex:1"></span><button class="pb-el-action" onclick="event.stopPropagation();editor.duplicateElement(${el.id})" title="Duplicar">${icon('copy', 14)}</button><button class="pb-el-action" onclick="event.stopPropagation();editor.deleteElement(${el.id})" title="Excluir">${icon('trash', 14)}</button></div><div class="pb-el-content">${preview}</div>`;
}

export function initCanvasFeatures() {
    initParallax();
    initScrollAnimations();
    initCounters();
    initCountdowns();
    initLightbox();
}

function initParallax() {
    const sections = document.querySelectorAll('#canvas-dropzone .pb-parallax');
    if (!sections.length) return;
    const onScroll = () => {
        sections.forEach(sec => {
            const speed = parseFloat(sec.dataset.parallaxSpeed) || 0.5;
            const rect = sec.getBoundingClientRect();
            const offset = rect.top * speed;
            const video = sec.querySelector('.pb-section-video');
            if (video) {
                video.style.transform = `translate(-50%, calc(-50% + ${offset}px))`;
            }
            const inner = sec.querySelector('.pb-section-inner');
            if (inner) {
                inner.style.transform = `translateY(${offset * 0.3}px)`;
            }
        });
    };
    const wrap = document.getElementById('canvas-wrap');
    if (wrap) wrap.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

function initScrollAnimations() {
    const animated = document.querySelectorAll('#canvas-dropzone [data-scroll-animation]');
    if (!animated.length) return;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const anim = el.dataset.scrollAnimation;
                const dur = el.dataset.scrollDuration || '0.6s';
                const delay = el.dataset.scrollDelay || '0s';
                el.style.transition = `opacity ${dur} ${delay} ease, transform ${dur} ${delay} ease`;
                el.classList.add('pb-scroll-animated', `pb-scroll-${anim}`);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.15 });
    animated.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        observer.observe(el);
    });
}

function initCounters() {
    document.querySelectorAll('#canvas-dropzone .pb-counter').forEach(el => {
        const target = parseInt(el.dataset.count) || 0;
        const duration = parseInt(el.dataset.duration) || 2000;
        const useSep = el.dataset.separator === 'true';
        const numEl = el.querySelector('.pb-counter-num');
        if (!numEl) return;
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                animateCounter(numEl, target, duration, useSep);
                observer.unobserve(el);
            }
        }, { threshold: 0.5 });
        observer.observe(el);
    });
}

function animateCounter(el, target, duration, useSep) {
    const start = performance.now();
    const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        let val = Math.round(eased * target);
        if (useSep) val = val.toLocaleString('pt-BR');
        el.textContent = val;
        if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

function initCountdowns() {
    document.querySelectorAll('#canvas-dropzone .pb-countdown').forEach(el => {
        const target = el.dataset.target;
        if (!target) return;
        const targetDate = new Date(target).getTime();
        const update = () => {
            const now = Date.now();
            const diff = Math.max(0, targetDate - now);
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            const days = el.querySelector('.pb-cd-days');
            const hours = el.querySelector('.pb-cd-hours');
            const mins = el.querySelector('.pb-cd-mins');
            const secs = el.querySelector('.pb-cd-secs');
            if (days) days.textContent = String(d).padStart(2, '0');
            if (hours) hours.textContent = String(h).padStart(2, '0');
            if (mins) mins.textContent = String(m).padStart(2, '0');
            if (secs) secs.textContent = String(s).padStart(2, '0');
        };
        update();
        setInterval(update, 1000);
    });
}

function initLightbox() {
    document.querySelectorAll('#canvas-dropzone .pb-lightbox-trigger').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const url = link.href || link.querySelector('img')?.src;
            if (!url) return;
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:999999;display:flex;align-items:center;justify-content:center;cursor:pointer;animation:fadeIn .2s';
            overlay.innerHTML = `<img src="${escHtml(url)}" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;box-shadow:0 8px 48px rgba(0,0,0,.5)"><button style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:24px;width:40px;height:40px;border-radius:50%;cursor:pointer;backdrop-filter:blur(4px)">${icon('x', 18)}</button>`;
            overlay.onclick = () => overlay.remove();
            document.body.appendChild(overlay);
        });
    });
}

export function renderStructure(elements, parentUl) {
    const ul = parentUl || document.getElementById('structure-tree');
    if (!parentUl) ul.innerHTML = '';
    (elements || []).forEach(el => {
        const li = document.createElement('li');
        li.className = 'pb-structure-item';
        li.dataset.elId = el.id;
        li.innerHTML = `<span class="si-icon">${structureIcon(el.type)}</span><span>${escHtml(elementName(el))}</span><span class="si-type">${el.type}</span>`;
        li.onclick = (e) => { e.stopPropagation(); /* will be bound by editor */ };
        ul.appendChild(li);
        if (el.children && el.children.length > 0) {
            const childUl = document.createElement('ul');
            childUl.className = 'pb-structure-children';
            li.appendChild(childUl);
            renderStructure(el.children, childUl);
        }
    });
}
