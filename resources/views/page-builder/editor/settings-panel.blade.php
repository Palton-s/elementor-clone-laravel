<div class="pb-panel pb-panel-right" role="complementary" aria-label="Painel de configurações">
    <div class="pb-settings" id="settings-panel">
        <div class="pb-settings-empty" id="settings-empty">
            <div class="pse-icon"><i data-icon="sliders" data-icon-size="40"></i></div>
            <p>Selecione um elemento na tela<br>para editar suas configurações</p>
        </div>
        <div id="settings-form" class="pb-settings-form">
            <div class="pb-settings-header">
                <div>
                    <h3 id="settings-title">Elemento</h3>
                    <span class="pb-sh-type" id="settings-type">tipo</span>
                </div>
                <button onclick="editor.deleteSelected()" style="background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1.1rem" title="Excluir elemento" aria-label="Excluir elemento selecionado"><i data-icon="trash"></i></button>
            </div>
            <div class="pb-editor-tabs" id="editor-tabs" role="tablist" aria-label="Abas do editor">
                <button class="pb-editor-tab active" data-etab="content" onclick="editor.switchEditorTab('content')" role="tab" aria-selected="true" aria-controls="settings-body">Conteúdo</button>
                <button class="pb-editor-tab" data-etab="style" onclick="editor.switchEditorTab('style')" role="tab" aria-selected="false" aria-controls="settings-body">Estilo</button>
                <button class="pb-editor-tab" data-etab="advanced" onclick="editor.switchEditorTab('advanced')" role="tab" aria-selected="false" aria-controls="settings-body">Avançado</button>
            </div>
            <div class="pb-responsive-tabs" id="responsive-tabs" role="group" aria-label="Breakpoint responsivo">
                <button class="pb-resp-tab active" data-device="desktop" onclick="editor.setResponsiveTab('desktop')" title="Desktop" aria-label="Visualizar desktop"><i data-icon="monitor" data-icon-size="14"></i></button>
                <button class="pb-resp-tab" data-device="tablet" onclick="editor.setResponsiveTab('tablet')" title="Tablet" aria-label="Visualizar tablet"><i data-icon="tablet" data-icon-size="14"></i></button>
                <button class="pb-resp-tab" data-device="mobile" onclick="editor.setResponsiveTab('mobile')" title="Mobile" aria-label="Visualizar mobile"><i data-icon="smartphone" data-icon-size="14"></i></button>
            </div>
            <div class="pb-settings-body" id="settings-body" role="tabpanel"></div>
        </div>
        <div id="page-settings-form" class="pb-settings-form">
            <div class="pb-settings-header">
                <div>
                    <h3>Layout da Página</h3>
                    <span class="pb-sh-type">Configurações da página</span>
                </div>
                <button onclick="editor.hidePageSettings()" style="background:none;border:none;color:var(--pb-text2);cursor:pointer;font-size:1.1rem" title="Fechar" aria-label="Fechar configurações da página"><i data-icon="x"></i></button>
            </div>
            <div class="pb-settings-body" id="page-settings-body"></div>
        </div>
    </div>
</div>