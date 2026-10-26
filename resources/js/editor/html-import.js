import { showToast, toastError, apiFetch } from './utils.js';
import { icon } from './icons.js';

let modal = null;

function ensureModal() {
    if (modal) return modal;
    modal = document.createElement('div');
    modal.id = 'pb-html-import-modal';
    modal.style.cssText = 'display:none;position:fixed;inset:0;z-index:9999';
    modal.innerHTML = `
        <div class="modal-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,.5)" data-role="close"></div>
        <div class="modal-content" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;width:92%;max-width:680px;max-height:88vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.3)" onclick="event.stopPropagation()">
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
                <h3 style="margin:0;font-size:1.1rem;color:#1e293b">${icon('upload', 18)} Importar HTML de Site Externo</h3>
                <button data-role="close" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#64748b;line-height:1">${icon('x', 18)}</button>
            </div>
            <div style="padding:1.25rem 1.5rem">
                <p style="color:#64748b;margin:0 0 1rem;font-size:.875rem">
                    Cole o HTML de qualquer página web ou informe a URL. O conteúdo será convertido automaticamente em widgets editáveis.
                </p>
                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:.35rem;font-size:.85rem;color:#374151">URL do Site <span style="color:#94a3b8;font-weight:400">(opcional)</span></label>
                    <div style="display:flex;gap:.5rem">
                        <input type="url" id="pb-html-url" placeholder="https://exemplo.com/pagina" style="flex:1;padding:.5rem .75rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.85rem">
                        <button type="button" id="pb-html-fetch-btn" style="white-space:nowrap;padding:.5rem .85rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:.8rem;cursor:pointer;color:#374151;font-weight:500">${icon('search', 14)} Buscar</button>
                    </div>
                    <div id="pb-html-fetch-status" style="font-size:.78rem;margin-top:.3rem;display:none"></div>
                </div>
                <div style="text-align:center;margin-bottom:.75rem;font-size:.78rem;color:#94a3b8">— ou cole o HTML diretamente —</div>
                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:.35rem;font-size:.85rem;color:#374151">HTML do Conteúdo</label>
                    <textarea id="pb-html-content" rows="12" placeholder="&lt;h1&gt;Titulo da Pagina&lt;/h1&gt;&#10;&lt;p&gt;Conteudo aqui...&lt;/p&gt;&#10;&lt;img src=&quot;...&quot;&gt;" style="width:100%;padding:.6rem;border:1px solid #e2e8f0;border-radius:8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.8rem;resize:vertical;line-height:1.5;background:#f8fafc;color:#1e293b;box-sizing:border-box"></textarea>
                </div>
                <div style="margin-bottom:.5rem">
                    <label style="display:block;font-weight:600;margin-bottom:.35rem;font-size:.85rem;color:#374151">Título da Página <span style="color:#94a3b8;font-weight:400">(opcional — detectado automaticamente)</span></label>
                    <input type="text" id="pb-html-title" placeholder="Ex: Página Importada" style="width:100%;padding:.5rem .75rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.85rem;box-sizing:border-box">
                </div>
                <div id="pb-html-error" style="color:#ef4444;margin-top:.5rem;display:none;font-size:.85rem;padding:.5rem;background:#fef2f2;border-radius:6px"></div>
            </div>
            <div style="padding:1rem 1.5rem;border-top:1px solid #e2e8f0;display:flex;gap:.5rem;justify-content:flex-end">
                <button data-role="close" style="padding:.5rem 1rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:.85rem;cursor:pointer;color:#374151">Cancelar</button>
                <button id="pb-html-submit-btn" style="padding:.5rem 1rem;background:#3b82f6;border:none;border-radius:8px;font-size:.85rem;cursor:pointer;color:#fff;font-weight:500">${icon('upload', 14)} Importar e Editar</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    modal.querySelectorAll('[data-role="close"]').forEach(el => {
        el.addEventListener('click', closeModal);
    });
    return modal;
}

function closeModal() {
    if (modal) modal.style.display = 'none';
}

export function openHtmlImportModal(csrf) {
    const m = ensureModal();
    m.style.display = '';
    document.getElementById('pb-html-url').value = '';
    document.getElementById('pb-html-content').value = '';
    document.getElementById('pb-html-title').value = '';
    document.getElementById('pb-html-error').style.display = 'none';
    document.getElementById('pb-html-fetch-status').style.display = 'none';
    document.getElementById('pb-html-submit-btn').disabled = false;
    document.getElementById('pb-html-submit-btn').innerHTML = icon('upload', 14) + ' Importar e Editar';

    const fetchBtn = document.getElementById('pb-html-fetch-btn');
    const submitBtn = document.getElementById('pb-html-submit-btn');

    fetchBtn.onclick = () => fetchUrl(csrf);
    submitBtn.onclick = () => submitImport(csrf);
}

function fetchUrl(csrf) {
    const url = document.getElementById('pb-html-url').value.trim();
    const errorEl = document.getElementById('pb-html-error');
    const status = document.getElementById('pb-html-fetch-status');
    const fetchBtn = document.getElementById('pb-html-fetch-btn');

    if (!url) {
        errorEl.textContent = 'Informe uma URL válida.';
        errorEl.style.display = '';
        return;
    }

    fetchBtn.disabled = true;
    fetchBtn.textContent = 'Buscando...';
    status.style.display = '';
    status.style.color = '#64748b';
    status.textContent = 'Baixando conteúdo da URL...';
    errorEl.style.display = 'none';

    fetch('/page-builder/html-import/fetch?url=' + encodeURIComponent(url), {
        headers: { 'X-CSRF-TOKEN': csrf }
    })
    .then(r => r.json())
    .then(data => {
        if (data.html) {
            document.getElementById('pb-html-content').value = data.html;
            status.style.color = '#166534';
            const size = data.size > 1024 ? Math.round(data.size / 1024) + 'KB' : data.size + 'B';
            status.textContent = 'HTML baixado com sucesso (' + size + '). Revise e clique em Importar.';
        } else {
            status.style.color = '#ef4444';
            status.textContent = data.message || 'Erro ao buscar URL.';
        }
    })
    .catch(() => {
        status.style.color = '#ef4444';
        status.textContent = 'Falha ao conectar com a URL.';
    })
    .finally(() => {
        fetchBtn.disabled = false;
        fetchBtn.innerHTML = icon('search', 14) + ' Buscar';
    });
}

function submitImport(csrf) {
    const url = document.getElementById('pb-html-url').value.trim();
    const html = document.getElementById('pb-html-content').value.trim();
    const title = document.getElementById('pb-html-title').value.trim();
    const errorEl = document.getElementById('pb-html-error');
    const submitBtn = document.getElementById('pb-html-submit-btn');

    errorEl.style.display = 'none';

    if (!url && !html) {
        errorEl.textContent = 'Forneça uma URL ou cole o HTML.';
        errorEl.style.display = '';
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Importando...';

    const body = {};
    if (url) body.url = url;
    if (html) body.html = html;
    if (title) body.title = title;

    fetch('/page-builder/html-import', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(data => {
        if (data.redirect_url) {
            showToast(data.message || 'Importação concluída!');
            setTimeout(() => { window.location.href = data.redirect_url; }, 800);
        } else {
            errorEl.textContent = data.message || 'Erro na importação.';
            errorEl.style.display = '';
            submitBtn.disabled = false;
            submitBtn.innerHTML = icon('upload', 14) + ' Importar e Editar';
        }
    })
    .catch(() => {
        errorEl.textContent = 'Falha na comunicação com o servidor.';
        errorEl.style.display = '';
        submitBtn.disabled = false;
        submitBtn.innerHTML = icon('upload', 14) + ' Importar e Editar';
    });
}
