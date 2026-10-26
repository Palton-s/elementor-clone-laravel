<div class="pb-toolbar" role="toolbar" aria-label="Barra de ferramentas do editor">
    @php $isThemeEditor = isset($themeContext) && !empty($themeContext); @endphp
    <div class="pb-toolbar-group">
        <span class="pb-toolbar-title">
            @if($isThemeEditor)
                <a href="{{ route('page-builder.themes.index') }}" class="btn-back" title="Voltar para temas"><i data-icon="arrow-left" data-icon-size="15"></i> Temas</a>
                <span style="color:var(--pb-accent);font-size:.7rem;font-weight:600;padding:.14rem .42rem;background:var(--pb-primary-light);border:1px solid rgba(59,130,246,.4);border-radius:999px">{{ $themeContext['template_type'] }}</span>
                <span style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $themeContext['template_title'] }}</span>
            @else
                <a href="{{ route('page-builder.pages.index') }}" class="btn-back" title="Voltar para páginas"><i data-icon="arrow-left" data-icon-size="15"></i> Páginas</a>
                <span style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $page->title }}</span>
            @endif
        </span>
        @if(!$isThemeEditor)
            <span class="pb-toolbar-badge" style="background:{{ $page->status === 'published' ? 'rgba(34,197,94,.16)' : 'rgba(245,158,11,.16)' }};color:{{ $page->status === 'published' ? '#22c55e' : '#f59e0b' }};border:1px solid {{ $page->status === 'published' ? 'rgba(34,197,94,.4)' : 'rgba(245,158,11,.4)' }}">{{ $page->status }}</span>
        @endif
    </div>
    <div class="pb-toolbar-spacer"></div>
    <div class="pb-toolbar-group">
        <button id="pb-undo" onclick="editor.undo()" title="Desfazer (Ctrl+Z)"><i data-icon="undo"></i></button>
        <button id="pb-redo" onclick="editor.redo()" title="Refazer (Ctrl+Shift+Z)"><i data-icon="redo"></i></button>
    </div>
    <span class="tb-divider"></span>
    <div class="pb-toolbar-group">
        <button class="active" data-mode="desktop" onclick="editor.setResponsive('desktop')" title="Visualização Desktop"><i data-icon="monitor"></i></button>
        <button data-mode="tablet" onclick="editor.setResponsive('tablet')" title="Visualização Tablet"><i data-icon="tablet"></i></button>
        <button data-mode="mobile" onclick="editor.setResponsive('mobile')" title="Visualização Mobile"><i data-icon="smartphone"></i></button>
    </div>
    <span class="tb-divider"></span>
    <div class="pb-toolbar-group">
        <button id="pb-zoom-out" onclick="editor.zoomOut()" title="Diminuir Zoom (-)" style="padding:.38rem .5rem"><i data-icon="minus" data-icon-size="14"></i></button>
        <span id="pb-zoom-label" style="font-size:.72rem;color:var(--pb-text2);min-width:40px;text-align:center;cursor:pointer" onclick="editor.zoomReset()" title="Redefinir Zoom (Ctrl+0)">100%</span>
        <button id="pb-zoom-in" onclick="editor.zoomIn()" title="Aumentar Zoom (+)" style="padding:.38rem .5rem"><i data-icon="plus" data-icon-size="14"></i></button>
        <button id="pb-fullscreen" onclick="editor.toggleFullscreen()" title="Tela Cheia (F11)"><i data-icon="maximize"></i></button>
    </div>
    <span class="tb-divider"></span>
    <div class="pb-toolbar-group">
        <button onclick="editor.showPageSettings()" title="Configurações da Página" id="btn-page-settings"><i data-icon="settings"></i> Página</button>
        <button onclick="editor.showSiteSettings()" title="Configurações do Site" id="btn-site-settings"><i data-icon="globe"></i> Site</button>
        <button onclick="editor.showRevisionHistory()" title="Histórico de Revisões"><i data-icon="history"></i> Revisões</button>
        <button onclick="editor.openFindReplace()" title="Localizar e Substituir"><i data-icon="search"></i> Localizar</button>
    </div>
    <span class="tb-divider"></span>
    <div class="pb-toolbar-group">
        @if($isThemeEditor)
            <a href="{{ route('page-builder.themes.render', $themeContext['template_id']) }}?t={{ time() }}" target="_blank" class="tb-link" title="Visualizar"><i data-icon="eye"></i> Visualizar</a>
        @else
            <a href="{{ route('page-builder.render', $page) }}?t={{ time() }}" target="_blank" class="tb-link" title="Visualizar"><i data-icon="eye"></i> Visualizar</a>
        @endif
        <button onclick="editor.exportPage()" title="Exportar página (.zip com HTML, imagens, vídeos e fontes)"><i data-icon="download"></i> .zip</button>
        <button onclick="editor.saveAsTemplate()" title="Salvar como modelo"><i data-icon="bookmark-plus"></i></button>
        <button onclick="editor.importHtml()" title="Importar HTML"><i data-icon="upload"></i></button>
    </div>
    <span class="tb-divider"></span>
    <div class="pb-toolbar-group">
        <button onclick="editor.save()" class="btn-save"><i data-icon="save"></i> Salvar</button>
        <button onclick="editor.publish()" class="btn-publish"><i data-icon="rocket"></i> Publicar</button>
    </div>
</div>