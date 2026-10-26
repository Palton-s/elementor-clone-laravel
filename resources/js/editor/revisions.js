import state from './state.js';
import { apiFetch, toastError, toastSuccess } from './utils.js';

export function showRevisionHistory() {
    const panel = document.getElementById('settings-empty');
    const form = document.getElementById('settings-form');
    const pageForm = document.getElementById('page-settings-form');
    if (panel) panel.style.display = 'none';
    if (form) form.style.display = 'none';
    if (pageForm) pageForm.style.display = 'none';
    state._prevSelected = state.selectedId;
    if (state.selectedId) { deselectAll(); state.selectedId = null; }

    document.getElementById('settings-title').textContent = 'Histórico de revisões';
    document.getElementById('settings-type').textContent = 'revisions';
    document.getElementById('editor-tabs').style.display = 'none';
    document.getElementById('responsive-tabs').style.display = 'none';
    form.style.display = 'block';

    const body = document.getElementById('settings-body');
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2)">Carregando...</div>';

    apiFetch(`/page-builder/pages/${state.pageId}/revisions`)
        .then(data => {
            const revisions = data.revisions?.data || data.revisions || [];
            body.innerHTML = '';
            if (revisions.length === 0) {
                body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2)">Nenhuma revisão encontrada</div>';
                return;
            }
            revisions.forEach(rev => {
                const card = document.createElement('div');
                card.style.cssText = 'background:var(--pb-surface2);border-radius:8px;padding:10px;margin-bottom:8px;border:1px solid var(--pb-border);';
                const date = new Date(rev.created_at).toLocaleString('pt-BR');
                const userName = rev.user?.name || 'Sistema';
                const version = rev.version || 'v1';
                card.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-weight:600;font-size:.8rem;color:var(--pb-text);">${version}</span>
                        <span style="font-size:.65rem;color:var(--pb-text2);">${date}</span>
                    </div>
                    <div style="font-size:.7rem;color:var(--pb-text2);margin-bottom:6px;">por ${userName}</div>
                    <div style="display:flex;gap:6px;">
                        <button class="pb-rev-restore" data-rev-id="${rev.id}" style="flex:1;padding:4px 8px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:var(--pb-success);border-radius:4px;cursor:pointer;font-size:.7rem;">Restaurar</button>
                        <button class="pb-rev-diff" data-rev-id="${rev.id}" style="flex:1;padding:4px 8px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);color:var(--pb-accent);border-radius:4px;cursor:pointer;font-size:.7rem;">Ver Diff</button>
                    </div>
                `;
                card.querySelector('.pb-rev-restore').onclick = () => {
                    if (!confirm(`Restaurar a revisao ${version}?`)) return;
                    apiFetch(`/page-builder/pages/${state.pageId}/revisions/${rev.id}/restore`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': state.csrf },
                    })
                    .then(() => { toastSuccess('Revisão restaurada! Recarregando...'); setTimeout(() => location.reload(), 800); })
                    .catch(() => toastError('Falha ao restaurar revisão'));
                };
                card.querySelector('.pb-rev-diff').onclick = () => {
                    showRevisionDiff(rev.id);
                };
                body.appendChild(card);
            });
        })
        .catch(() => { body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-danger)">Erro ao carregar revisões</div>'; });
}

function deselectAll() {
    document.querySelectorAll('.pb-el.selected, .pb-el.multi-selected').forEach(el => {
        el.classList.remove('selected');
        el.classList.remove('multi-selected');
    });
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    document.getElementById('settings-empty').style.display = '';
    document.getElementById('settings-form').classList.remove('active');
    document.getElementById('page-settings-form').classList.remove('active');
}

export function showRevisionDiff(revId) {
    const body = document.getElementById('settings-body');
    if (!body) return;
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2)">Carregando diff...</div>';
    apiFetch(`/page-builder/pages/${state.pageId}/revisions/${revId}/diff`)
        .then(data => {
            body.innerHTML = '';
            const current = data.current || {};
            const previous = data.previous || {};
            const version = data.version || '?';
            const prevVersion = data.previous_version || 'Inicio';

            const header = document.createElement('div');
            header.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:12px;';
            header.innerHTML = `<button id="diff-back" style="background:none;border:none;color:var(--pb-accent);cursor:pointer;font-size:.8rem;">&#8592; Voltar</button><span style="font-weight:600;font-size:.85rem;">Diff: ${prevVersion} → ${version}</span>`;
            body.appendChild(header);
            document.getElementById('diff-back').onclick = () => showRevisionHistory();

            const diffContainer = document.createElement('div');
            diffContainer.style.cssText = 'background:var(--pb-surface2);border-radius:8px;padding:12px;font-family:monospace;font-size:.7rem;max-height:400px;overflow-y:auto;';

            const prevContent = JSON.stringify(previous.content || [], null, 2);
            const currContent = JSON.stringify(current.content || [], null, 2);
            const prevLines = prevContent.split('\n');
            const currLines = currContent.split('\n');
            const maxLines = Math.max(prevLines.length, currLines.length);

            for (let i = 0; i < maxLines; i++) {
                const pl = prevLines[i] || '';
                const cl = currLines[i] || '';
                const line = document.createElement('div');
                if (pl !== cl) {
                    if (pl && !cl) {
                        line.style.cssText = 'background:rgba(239,68,68,.15);color:var(--pb-danger);padding:1px 6px;border-radius:2px;';
                        line.textContent = `- ${pl}`;
                    } else if (!pl && cl) {
                        line.style.cssText = 'background:rgba(34,197,94,.15);color:var(--pb-success);padding:1px 6px;border-radius:2px;';
                        line.textContent = `+ ${cl}`;
                    } else {
                        line.style.cssText = 'background:rgba(245,158,11,.15);color:var(--pb-warning);padding:1px 6px;border-radius:2px;';
                        line.textContent = `~ ${cl}`;
                    }
                } else {
                    line.style.cssText = 'color:var(--pb-text2);padding:1px 6px;';
                    line.textContent = `  ${pl}`;
                }
                diffContainer.appendChild(line);
            }
            body.appendChild(diffContainer);
        })
        .catch(() => { body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-danger)">Erro ao carregar diff</div>'; });
}
