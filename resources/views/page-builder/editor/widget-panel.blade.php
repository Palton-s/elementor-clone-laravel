<div class="pb-panel" role="navigation" aria-label="Painel de inserção">
    <div class="pb-panel-tabs" role="tablist" aria-label="Abas do painel">
        <button class="pb-panel-tab active" data-tab="widgets" onclick="editor.switchTab('widgets')" role="tab" aria-selected="true">Inserir</button>
        <button class="pb-panel-tab" data-tab="navigator" onclick="editor.switchTab('navigator')" role="tab" aria-selected="false">Camadas</button>
        <button class="pb-panel-tab" data-tab="structure" onclick="editor.switchTab('structure')" role="tab" aria-selected="false">Estrutura</button>
        <button class="pb-panel-tab" data-tab="layouts" onclick="editor.switchTab('layouts')" role="tab" aria-selected="false">Modelos</button>
    </div>
    <div class="pb-panel-body" id="panel-widgets">
        <div class="pb-widget-search">
            <input type="text" id="widget-search-input" placeholder="Procurar elementos..." autocomplete="off" aria-label="Procurar elementos">
            <span class="pb-widget-search-icon"><i data-icon="search" data-icon-size="14"></i></span>
        </div>
        <div class="pb-widget-groups-wrap">
            <div class="pb-widget-group" data-group="layout">
                <div class="pb-widget-group-title"><span>Layout</span></div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="section" data-search="seção section layout container"><span class="pb-widget-icon"></span><span class="pb-widget-label">Seção</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="inner_section" data-search="inner section seção interna nested columns colunas"><span class="pb-widget-icon"></span><span class="pb-widget-label">Seção Interna</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="column" data-search="coluna column layout"><span class="pb-widget-icon"></span><span class="pb-widget-label">Coluna</span></div>
                </div>
            </div>
            <div class="pb-widget-group" data-group="basic">
                <div class="pb-widget-group-title"><span>Básicos</span></div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="heading" data-search="título heading h1 h2 h3"><span class="pb-widget-icon"></span><span class="pb-widget-label">Título</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="text" data-search="texto text parágrafo content"><span class="pb-widget-icon"></span><span class="pb-widget-label">Texto</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="image" data-search="imagem image foto photo"><span class="pb-widget-icon"></span><span class="pb-widget-label">Imagem</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="button" data-search="botão button cta link"><span class="pb-widget-icon"></span><span class="pb-widget-label">Botão</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="video" data-search="vídeo vídeo youtube vimeo"><span class="pb-widget-icon"></span><span class="pb-widget-label">Vídeo</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="divider" data-search="divisor divider linha line separador"><span class="pb-widget-icon"></span><span class="pb-widget-label">Divisor</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="spacer" data-search="espaçador spacer espaço gap"><span class="pb-widget-icon"></span><span class="pb-widget-label">Espaçador</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="icon" data-search="ícone icon star"><span class="pb-widget-icon"></span><span class="pb-widget-label">Ícone</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="gallery" data-search="galeria gallery fotos images"><span class="pb-widget-icon"></span><span class="pb-widget-label">Galeria</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="form" data-search="formulário form contato contact"><span class="pb-widget-icon"></span><span class="pb-widget-label">Formulário</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="tabs" data-search="abas tabs tab"><span class="pb-widget-icon"></span><span class="pb-widget-label">Abas</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="accordion" data-search="accordion sanfona collapse"><span class="pb-widget-icon"></span><span class="pb-widget-label">Sanfona</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="icon_box" data-search="icon box ícone caixa"><span class="pb-widget-icon"></span><span class="pb-widget-label">Caixa com Ícone</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="image_box" data-search="image box imagem caixa"><span class="pb-widget-icon"></span><span class="pb-widget-label">Caixa com Imagem</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="carousel" data-search="carrossel slider galeria deslizante"><span class="pb-widget-icon"></span><span class="pb-widget-label">Carrossel</span></div>
                </div>
            </div>
            <div class="pb-widget-group" data-group="pro">
                <div class="pb-widget-group-title"><span>Interativo</span></div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="counter" data-search="contador counter número animado"><span class="pb-widget-icon"></span><span class="pb-widget-label">Contador</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="progress_bar" data-search="barra progresso progress"><span class="pb-widget-icon"></span><span class="pb-widget-label">Barra de Progresso</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="social_icons" data-search="redes sociais social icons"><span class="pb-widget-icon"></span><span class="pb-widget-label">Redes Sociais</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="testimonial" data-search="depoimento testimonial avaliação"><span class="pb-widget-icon"></span><span class="pb-widget-label">Depoimento</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="price_table" data-search="preços tabela price table plano"><span class="pb-widget-icon"></span><span class="pb-widget-label">Tabela de Preços</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="countdown" data-search="contador regressivo countdown timer"><span class="pb-widget-icon"></span><span class="pb-widget-label">Contagem Regressiva</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="google_maps" data-search="mapa google maps localização"><span class="pb-widget-icon"></span><span class="pb-widget-label">Mapa</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="mega_menu" data-search="mega menu navegação"><span class="pb-widget-icon"></span><span class="pb-widget-label">Menu de Navegação</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="lottie" data-search="lottie animação animation"><span class="pb-widget-icon"></span><span class="pb-widget-label">Animação Lottie</span></div>
                </div>
            </div>
            <div class="pb-widget-group" data-group="edu">
                <div class="pb-widget-group-title"><span>Educacional</span></div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="callout" data-search="destaque callout alerta dica"><span class="pb-widget-icon"></span><span class="pb-widget-label">Destaque</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="table" data-search="tabela table dados"><span class="pb-widget-icon"></span><span class="pb-widget-label">Tabela</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="math" data-search="matemática math fórmula latex"><span class="pb-widget-icon"></span><span class="pb-widget-label">Fórmula Matemática</span></div>
                </div>
            </div>
            <div id="widget-search-empty" style="display:none;text-align:center;padding:2rem 1rem;color:var(--pb-text2);font-size:.78rem">
                <div style="margin-bottom:.4rem;opacity:.4"><i data-icon="search" data-icon-size="28"></i></div>
                Nenhum elemento encontrado
            </div>
        </div>
    </div>
    <div class="pb-panel-body" id="panel-navigator" style="display:none">
        <div class="pb-navigator-body" id="navigator-body"></div>
    </div>
    <div class="pb-panel-body" id="panel-structure" style="display:none">
        <ul class="pb-structure-tree" id="structure-tree"></ul>
    </div>
    <div class="pb-panel-body" id="panel-layouts" style="display:none">
        <div class="pb-layout-templates" id="layout-templates"></div>
    </div>
</div>