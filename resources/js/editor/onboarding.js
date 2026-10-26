export function initOnboarding() {
    if (localStorage.getItem('pb_onboarded_v2')) return;
    const steps = [
        { title: 'Bem-vindo ao Page Builder!', desc: 'Este e o editor visual. Arraste widgets do painel esquerdo para criar sua página.', target: '.pb-panel:not(.pb-panel-right)', arrow: 'right' },
        { title: 'Canvas', desc: 'Aqui você ve uma pre-visualização ao vivo da sua página. Clique em qualquer elemento para edita-lo.', target: '#canvas', arrow: 'bottom' },
        { title: 'Painel de Configurações', desc: 'Selecione um elemento e edite suas configurações aqui (Conteúdo, Estilo, Avançado).', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Modo Responsivo', desc: 'Teste como sua página aparece em Desktop, Tablet e Mobile.', target: '[data-mode="tablet"]', arrow: 'bottom' },
        { title: 'Navigator', desc: 'Use o Navigator para ver a arvore de elementos e reordenar com drag-and-drop.', target: '[data-tab="navigator"]', arrow: 'right' },
        { title: 'Duplicar com Ctrl+D', desc: 'Selecione um elemento e pressione <strong>Ctrl+D</strong> para duplicar rapidamente. Funciona no canvas e no Navigator.', target: '#canvas', arrow: 'bottom' },
        { title: 'Copiar/Colar Estilos', desc: 'Selecione um elemento, pressione <strong>Ctrl+Shift+C</strong> para copiar seus estilos. Selecione outro e pressione <strong>Ctrl+Shift+V</strong> para colar. Tambem disponível nos menus de contexto (botão direito).', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Divisores de forma', desc: 'Nas configurações de uma Section (aba Estilo), adicione divisores de forma (waves, mountains, tilt, etc.) no topo e/ou base. Deixe suas secoes mais profissionais!', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Sobreposição do fundo', desc: 'Na aba Estilo de uma Section, configure um overlay semi-transparente sobre o background com cor, opacidade e blend mode. Perfeito para contraste com texto sobre imagens.', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Atalhos de Teclado', desc: '<strong>Ctrl+Z</strong> Desfazer | <strong>Ctrl+Shift+Z</strong> Refazer | <strong>Ctrl+S</strong> Salvar | <strong>Ctrl+D</strong> Duplicar | <strong>Ctrl+Shift+C/V</strong> Copiar/Colar Estilos | <strong>Delete</strong> Excluir | <strong>F11</strong> Tela Cheia', target: '.pb-toolbar', arrow: 'bottom' },
    ];
    let currentStep = 0;
    const overlay = document.createElement('div');
    overlay.className = 'pb-tour-overlay';
    const tooltip = document.createElement('div');
    tooltip.className = 'pb-tour-tooltip';
    document.body.appendChild(overlay);
    document.body.appendChild(tooltip);
    function showStep(idx) {
        if (idx >= steps.length) { finish(); return; }
        const s = steps[idx];
        tooltip.className = 'pb-tour-tooltip arrow-' + s.arrow;
        tooltip.innerHTML = `
            <div class="pb-tour-step">Passo ${idx + 1} de ${steps.length}</div>
            <div class="pb-tour-title">${s.title}</div>
            <div class="pb-tour-desc">${s.desc}</div>
            <div class="pb-tour-actions">
                <button class="pb-tour-btn pb-tour-btn-primary" id="tour-next">${idx === steps.length - 1 ? 'Comencar!' : 'Próximo'}</button>
                <button class="pb-tour-skip" id="tour-skip">Pular tour</button>
            </div>
        `;
        const target = document.querySelector(s.target);
        if (target) {
            const r = target.getBoundingClientRect();
            const tw = 300;
            let left = r.left + r.width / 2 - tw / 2;
            let top;
            if (s.arrow === 'bottom') top = r.bottom + 10;
            else if (s.arrow === 'right') { top = r.top + r.height / 2 - 60; left = r.right + 10; }
            else if (s.arrow === 'left') { top = r.top + r.height / 2 - 60; left = r.left - tw - 10; }
            else { top = r.top - 10; }
            left = Math.max(8, Math.min(window.innerWidth - tw - 8, left));
            top = Math.max(8, Math.min(window.innerHeight - 200, top));
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }
        document.getElementById('tour-next').onclick = () => { currentStep++; showStep(currentStep); };
        document.getElementById('tour-skip').onclick = finish;
    }
    function finish() {
        overlay.remove();
        tooltip.remove();
        localStorage.setItem('pb_onboarded_v2', '1');
    }
    setTimeout(() => showStep(currentStep), 500);
}
