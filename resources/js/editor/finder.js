import state from './state.js';
import { escHtml, apiFetch, toastError, showToast } from './utils.js';
import { icon } from './icons.js';

export function openFindReplace() {
    const existing = document.getElementById('pb-find-replace-modal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'pb-find-replace-modal';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px)';

    modal.innerHTML = `
        <div style="background:var(--pb-surface);border-radius:12px;width:90%;max-width:520px;box-shadow:0 16px 48px rgba(0,0,0,.2);max-height:80vh;display:flex;flex-direction:column">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--pb-border);display:flex;justify-content:space-between;align-items:center">
                <h3 style="font-size:1rem;font-weight:600">Localizar e substituir</h3>
                <button onclick="this.closest('#pb-find-replace-modal').remove()" style="background:none;border:none;color:var(--pb-text2);cursor:pointer;font-size:1.3rem">${icon('x', 18)}</button>
            </div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;overflow-y:auto">
                <div>
                    <label style="font-size:.78rem;font-weight:500;color:var(--pb-text2);display:block;margin-bottom:.25rem">Localizar</label>
                    <input type="text" id="fr-find" placeholder="Texto a procurar..." style="width:100%;padding:.5rem .65rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:.85rem;box-sizing:border-box">
                </div>
                <div>
                    <label style="font-size:.78rem;font-weight:500;color:var(--pb-text2);display:block;margin-bottom:.25rem">Substituir por</label>
                    <input type="text" id="fr-replace" placeholder="Novo texto..." style="width:100%;padding:.5rem .65rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:.85rem;box-sizing:border-box">
                </div>
                <div style="display:flex;gap:.5rem">
                    <button id="fr-search-btn" style="flex:1;padding:.55rem;background:var(--pb-accent);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:.82rem;font-weight:500">Buscar</button>
                    <button id="fr-replace-btn" style="flex:1;padding:.55rem;background:#22c55e;border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:.82rem;font-weight:500">Substituir tudo</button>
                </div>
                <div id="fr-results" style="font-size:.78rem;color:var(--pb-text2);max-height:200px;overflow-y:auto;display:flex;flex-direction:column;gap:.25rem"></div>
            </div>
        </div>`;

    document.body.appendChild(modal);

    document.getElementById('fr-search-btn').onclick = () => {
        const q = document.getElementById('fr-find').value;
        if (!q) return;
        const results = document.getElementById('fr-results');
        results.innerHTML = '<span style="color:var(--pb-text2)">Searching...</span>';
        apiFetch('/page-builder/find-replace/search', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
            body: JSON.stringify({ query: q, page_id: state.pageId }),
        }).then(data => {
            if (data.elements && data.elements.length > 0) {
                results.innerHTML = `<span style="color:var(--pb-text2);margin-bottom:.35rem">Found ${data.total} matches:</span>` +
                    data.elements.map(e =>
                        `<div style="padding:.35rem .5rem;background:var(--pb-surface2);border-radius:4px;font-size:.75rem;display:flex;justify-content:space-between">
                            <span><strong>${e.page_title}</strong> › ${e.type}: ${e.name}</span>
                            <span style="color:var(--pb-text2);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${e.match || ''}</span>
                        </div>`
                    ).join('');
            } else {
                results.innerHTML = '<span style="color:var(--pb-text2)">Nenhum resultado encontrado</span>';
            }
        }).catch(err => {
            results.innerHTML = `<span style="color:var(--pb-danger)">Error: ${err.message}</span>`;
        });
    };

    document.getElementById('fr-replace-btn').onclick = () => {
        const find = document.getElementById('fr-find').value;
        const replace = document.getElementById('fr-replace').value;
        if (!find) return;
        if (!confirm(`Replace all occurrences of "${find}" with "${replace}"? This cannot be undone.`)) return;
        const results = document.getElementById('fr-results');
        results.innerHTML = '<span style="color:var(--pb-text2)">Replacing...</span>';
        apiFetch('/page-builder/find-replace/replace', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
            body: JSON.stringify({ find, replace, page_id: state.pageId }),
        }).then(data => {
            results.innerHTML = `<span style="color:#22c55e">${data.message}</span>`;
        }).catch(err => {
            results.innerHTML = `<span style="color:var(--pb-danger)">Error: ${err.message}</span>`;
        });
    };

    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
}

export function toggleFinder() {
    const existing = document.getElementById('pb-finder-overlay');
    if (existing) { existing.remove(); return; }
    const overlay = document.createElement('div');
    overlay.id = 'pb-finder-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999999;display:flex;justify-content:center;padding-top:15vh;backdrop-filter:blur(4px);animation:fadeIn .15s';
    const modal = document.createElement('div');
    modal.style.cssText = 'width:480px;max-height:400px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:12px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.4);display:flex;flex-direction:column;animation:tourPop .2s';
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Buscar widgets, configurações, ações...';
    searchInput.style.cssText = 'width:100%;padding:14px 16px;background:transparent;border:none;border-bottom:1px solid var(--pb-border);color:var(--pb-text);font-size:.9rem;outline:none';
    const results = document.createElement('div');
    results.style.cssText = 'flex:1;overflow-y:auto;padding:4px';
    const actions = [
        { label: 'Salvar', icon: icon('save'), action: () => { import('./actions.js').then(m => m.save()); } },
        { label: 'Publicar', icon: icon('rocket'), action: () => { import('./actions.js').then(m => m.publish()); } },
        { label: 'Desfazer', icon: icon('undo'), action: () => { import('./history.js').then(m => m.undo(state)); } },
        { label: 'Refazer', icon: icon('redo'), action: () => { import('./history.js').then(m => m.redo(state)); } },
        { label: 'Camadas', icon: icon('layers'), action: () => { import('./navigator.js').then(m => m.toggleNavigator(state)); } },
        { label: 'Exportar site (.zip)', icon: icon('download'), action: () => window.pageBuilderEditor.exportPage() },
        { label: 'Exportar JSON (backup)', icon: icon('code'), action: () => window.open('/page-builder/pages/' + state.pageId + '/export', '_blank') },
        { label: 'Copiar HTML', icon: icon('code'), action: () => { import('./actions.js').then(m => m.copyHtml()); } },
        { label: 'Importar HTML', icon: icon('upload'), action: () => { import('./html-import.js').then(m => m.openHtmlImportModal(state.csrf)); } },
        { label: 'Configurações da página', icon: icon('settings'), action: () => { import('./page-settings.js').then(m => m.showPageSettings()); } },
        { label: 'Configurações do site', icon: icon('globe'), action: () => { import('./global-settings.js').then(m => m.showSiteSettings()); } },
        { label: 'Histórico de revisões', icon: icon('history'), action: () => { import('./revisions.js').then(m => m.showRevisionHistory()); } },
        { label: 'Modo Desktop', icon: icon('monitor'), action: () => { import('./ui.js').then(m => m.setResponsive('desktop')); } },
        { label: 'Modo Tablet', icon: icon('tablet'), action: () => { import('./ui.js').then(m => m.setResponsive('tablet')); } },
        { label: 'Modo Mobile', icon: icon('smartphone'), action: () => { import('./ui.js').then(m => m.setResponsive('mobile')); } },
        { label: 'Zoom 100%', icon: icon('zoom-in'), action: () => { import('./ui.js').then(m => m.setZoom(100)); } },
        { label: 'Tela Cheia', icon: icon('maximize'), action: () => { import('./ui.js').then(m => m.toggleFullscreen()); } },
    ];
    const widgetTypes = [
        { type: 'heading', label: 'Título' }, { type: 'text', label: 'Texto' },
        { type: 'image', label: 'Imagem' }, { type: 'button', label: 'Botão' },
        { type: 'video', label: 'Video' }, { type: 'section', label: 'Seção' },
        { type: 'column', label: 'Coluna' }, { type: 'divider', label: 'Divisor' },
        { type: 'spacer', label: 'Espaçador' }, { type: 'icon', label: 'Ícone' },
        { type: 'gallery', label: 'Galeria' }, { type: 'form', label: 'Formulário' },
        { type: 'tabs', label: 'Abas' }, { type: 'accordion', label: 'Sanfona' },
        { type: 'callout', label: 'Destaque' }, { type: 'table', label: 'Tabela' },
        { type: 'math', label: 'Fórmula Matemática' }, { type: 'counter', label: 'Contador' },
        { type: 'progress_bar', label: 'Barra de Progresso' }, { type: 'social_icons', label: 'Redes Sociais' },
        { type: 'icon_box', label: 'Caixa com Ícone' }, { type: 'image_box', label: 'Caixa com Imagem' },
        { type: 'testimonial', label: 'Depoimento' }, { type: 'price_table', label: 'Tabela de Preços' },
        { type: 'countdown', label: 'Contagem Regressiva' }, { type: 'google_maps', label: 'Google Maps' },
        { type: 'carousel', label: 'Carrossel' },
    ];
    const renderResults = (q) => {
        results.innerHTML = '';
        const query = q.toLowerCase().trim();
        const matchedActions = query ? actions.filter(a => a.label.toLowerCase().includes(query)) : actions;
        if (matchedActions.length) {
            const sec = document.createElement('div');
            sec.style.cssText = 'padding:6px 10px;font-size:.65rem;color:var(--pb-text2);text-transform:uppercase;letter-spacing:.5px;font-weight:600';
            sec.textContent = 'Ações';
            results.appendChild(sec);
            matchedActions.forEach(a => {
                const item = document.createElement('div');
                item.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 10px;cursor:pointer;border-radius:6px;font-size:.82rem;transition:background .1s';
                item.innerHTML = `<span style="width:20px;display:flex;justify-content:center;opacity:.6">${a.icon}</span><span>${a.label}</span>`;
                item.onmouseenter = () => item.style.background = 'var(--pb-surface2)';
                item.onmouseleave = () => item.style.background = 'transparent';
                item.onclick = () => { overlay.remove(); a.action(); };
                results.appendChild(item);
            });
        }
        const matchedWidgets = query ? widgetTypes.filter(w => w.label.toLowerCase().includes(query) || w.type.includes(query)) : [];
        if (matchedWidgets.length) {
            const sec = document.createElement('div');
            sec.style.cssText = 'padding:6px 10px;font-size:.65rem;color:var(--pb-text2);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-top:4px';
            sec.textContent = 'Widgets';
            results.appendChild(sec);
            matchedWidgets.forEach(w => {
                const item = document.createElement('div');
                item.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 10px;cursor:pointer;border-radius:6px;font-size:.82rem;transition:background .1s';
                item.innerHTML = `<span style="width:20px;display:flex;justify-content:center;opacity:.6">${icon('plus', 14)}</span><span>${w.label}</span><span style="font-size:.65rem;color:var(--pb-text2);margin-left:auto">${w.type}</span>`;
                item.onmouseenter = () => item.style.background = 'var(--pb-surface2)';
                item.onmouseleave = () => item.style.background = 'transparent';
                item.onclick = () => { overlay.remove(); document.querySelector(`.pb-widget-item[data-type="${w.type}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' }); };
                results.appendChild(item);
            });
        }
        if (!matchedActions.length && !matchedWidgets.length) {
            results.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2);font-size:.82rem">Nenhum resultado encontrado</div>';
        }
    };
    searchInput.addEventListener('input', () => renderResults(searchInput.value));
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') overlay.remove();
        if (e.key === 'Enter') {
            const first = results.querySelector('div[style*="cursor:pointer"]');
            if (first) first.click();
        }
    });
    overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
    modal.appendChild(searchInput);
    modal.appendChild(results);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    setTimeout(() => { searchInput.focus(); renderResults(''); }, 50);
}
