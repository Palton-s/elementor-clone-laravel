@extends('page-builder.layouts.app')

@section('title', 'Tutorial')

@section('content')
    <div class="container tutorial">
        <div class="page-header">
            <h1>&#128214; Tutorial — Page Builder</h1>
            <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">&larr; Voltar para Páginas</a>
        </div>

        <div class="toc">
            <strong>Conteúdo</strong>
            <a href="#prerequisites">Pré-requisitos &amp; Instalação</a>
            <a href="#overview">Visão Geral</a>
            <a href="#license">Custo &amp; Licença</a>
            <a href="#auth">Criar Conta &amp; Acessar</a>
            <a href="#projects-hub">Hub de Projetos (Estilo Unity)</a>
            <a href="#project-dashboard">Dashboard do Projeto</a>
            <a href="#create-page">Criar uma Página</a>
            <a href="#build-from-zero">Construir do Zero (Passo a Passo)</a>
            <a href="#editor">Conhecendo o Novo Editor</a>
            <a href="#drag-widgets">Inserir Elementos</a>
            <a href="#educational-widgets">Elementos Educacionais</a>
            <a href="#select-settings">Editar Propriedades</a>
            <a href="#page-settings">Configurações da Página &amp; Site</a>
            <a href="#responsive">Visualização Responsiva</a>
            <a href="#templates">Modelos &amp; Salvar como Modelo</a>
            <a href="#undo-redo">Desfazer, Refazer &amp; Revisões</a>
            <a href="#save-publish">Salvar &amp; Publicar</a>
            <a href="#preview">Visualizar Página</a>
            <a href="#duplicate-delete">Duplicar &amp; Excluir</a>
            <a href="#collaboration">Colaboração em Tempo Real</a>
            <a href="#html-import">Importação de HTML</a>
            <a href="#showcase">Template Showcase Completo</a>
            <a href="#theme-builder">Theme Builder</a>
            <a href="#popups">Popups</a>
            <a href="#projects">Projetos (Organização)</a>
            <a href="#architecture">Arquitetura do Projeto</a>
            <a href="#database">Banco de Dados</a>
            <a href="#routes">Rotas</a>
            <a href="#api">API REST (Sanctum)</a>
            <a href="#jobs">Jobs &amp; Filas</a>
            <a href="#quality">Qualidade &amp; Testes</a>
            <a href="#improvements">Melhorias Propostas</a>
            <a href="#moodle">Uso com Moodle 4.5+</a>
        </div>

        {{-- PREREQUISITES --}}
        <section id="prerequisites" class="step">
            <h2>0. Pré-requisitos &amp; Instalação</h2>
            <div class="step-body">
                <p>Antes de usar o Page Builder, você precisa ter o projeto rodando na sua máquina. Siga os passos abaixo:</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">O que você precisa ter instalado</h3>
                <table class="widget-table">
                    <tr><th>Ferramenta</th><th>Versão Mínima</th><th>Como verificar</th></tr>
                    <tr><td><strong>PHP</strong></td><td>8.2+</td><td><code>php -v</code></td></tr>
                    <tr><td><strong>Composer</strong></td><td>2.0+</td><td><code>composer -V</code></td></tr>
                    <tr><td><strong>Node.js</strong></td><td>18+</td><td><code>node -v</code></td></tr>
                    <tr><td><strong>NPM</strong></td><td>9+</td><td><code>npm -v</code></td></tr>
                    <tr><td><strong>Banco de dados</strong></td><td>SQLite ou MySQL/MariaDB</td><td>SQLite é o mais fácil para começar</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 1: Clonar o repositório</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>git clone https://github.com/seu-usuário/elementor-clone-laravel.git
cd elementor-clone-laravel</code></pre>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 2: Instalar dependências</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>composer install
npm install</code></pre>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 3: Configurar o ambiente</h3>
                <ol>
                    <li>Copie o arquivo de configuração:
                        <pre style="background:#1e1e2d;color:#a6e3a1;padding:.5rem 1rem;border-radius:6px;font-size:.85rem;margin:.5rem 0"><code>copy .env.example .env</code></pre>
                    </li>
                    <li>Gere a chave da aplicação:
                        <pre style="background:#1e1e2d;color:#a6e3a1;padding:.5rem 1rem;border-radius:6px;font-size:.85rem;margin:.5rem 0"><code>php artisan key:generate</code></pre>
                    </li>
                    <li>Configure o banco de dados no arquivo <code>.env</code>. Para SQLite (mais simples):
                        <pre style="background:#1e1e2d;color:#a6e3a1;padding:.5rem 1rem;border-radius:6px;font-size:.85rem;margin:.5rem 0"><code>DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite</code></pre>
                        Depois crie o arquivo: <code>type nul &gt; database\database.sqlite</code> (Windows) ou <code>touch database/database.sqlite</code> (Mac/Linux)
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 4: Rodar migrações e seeders</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>php artisan migrate
php artisan db:seed</code></pre>
                <p>O seeder cria automaticamente:</p>
                <ul>
                    <li>Um usuário de teste: <code>test@example.com</code> / <code>password</code> (o banco incluído no repositório já contém <code>admin@sistema.local</code> / <code>password</code>)</li>
                    <li>As definições de widgets disponíveis</li>
                    <li>Os 7 templates padrão (Blank, Landing, About, Contact, Portfolio, <strong>Showcase Completo</strong>, Blog Post)</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 5: Iniciar o servidor</h3>
                <p>O Page Builder usa dois servidores que trabalham juntos. É importante entender o papel de cada um:</p>
                <table class="widget-table">
                    <tr><th>Servidor</th><th>Comando</th><th>Porta</th><th>Função</th></tr>
                    <tr><td><strong>Laravel</strong></td><td><code>php artisan serve</code></td><td>8000</td><td>Aplicação (rotas, banco, autenticação). <strong>É a URL que você abre no navegador.</strong></td></tr>
                    <tr><td><strong>Vite (dev)</strong></td><td><code>npm run dev</code></td><td>5173</td><td>Serve os arquivos JS/CSS do editor e recarrega automaticamente ao editar. <strong>Não é para abrir no navegador</strong> — apenas fornece os assets para a aplicação.</td></tr>
                    <tr><td><strong>Vite (build)</strong></td><td><code>npm run build</code></td><td>—</td><td>Empacota e minifica os assets em <code>public/build/</code> (usado quando o <code>npm run dev</code> NÃO está rodando).</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Modo de Desenvolvimento (com hot reload) — recomendado</h3>
                <p>Abra <strong>dois terminais</strong> na pasta do projeto e rode:</p>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code># Terminal 1: servidor da aplicação
php artisan serve</code></pre>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code># Terminal 2: servidor de assets do Vite (fica rodando em segundo plano)
npm run dev</code></pre>
                <ol>
                    <li>Acesse no navegador: <strong><a href="http://localhost:8000" target="_blank">http://localhost:8000</a></strong>.</li>
                    <li>Se ao abrir o editor os widgets <strong>não arrastarem</strong>, dê um <strong>Ctrl+F5</strong> (recarregar ignorando cache).</li>
                    <li>Enquanto o <code>npm run dev</code> estiver rodando, qualquer alteração em <code>resources/js/**</code> ou <code>resources/css/**</code> é refletida no navegador automaticamente, sem recarregar a página.</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Importante:</strong> O Vite cria um arquivo <code>public/hot</code> para avisar o Laravel de que o servidor de dev está ativo. Ao parar o <code>npm run dev</code>, esse arquivo é removido. Se você trocar o <code>npm run dev</code> de terminal e o arquivo <code>public/hot</code> não for apagado, o Laravel continuará procurando os assets na porta 5173 e o editor pode "travar". Nesse caso, exclua manualmente o <code>public/hot</code> e reinicie o <code>npm run dev</code>.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Modo de Produção (sem npm run dev)</h3>
                <p>Se você quiser rodar <strong>apenas</strong> o servidor Laravel (ex.: em um servidor compartilhado), é preciso compilar os assets primeiro:</p>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code># 1. Empacote os arquivos JS/CSS em public/build/
npm run build

# 2. Rode somente o servidor da aplicação
php artisan serve</code></pre>
                <ol>
                    <li>Acesse no navegador: <strong><a href="http://localhost:8000" target="_blank">http://localhost:8000</a></strong>. A aplicação usará os arquivos compilados de <code>public/build/</code>.</li>
                    <li><strong>Atenção:</strong> neste modo, alterações feitas nos arquivos JS/CSS <strong>não</strong> aparecem até você rodar <code>npm run build</code> novamente. Para desenvolvimento com atualização automática, use o modo de desenvolvimento acima.</li>
                </ol>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> Se encontrar algum erro durante a instalação, verifique se todas as extensões PHP necessárias estão habilitadas (pdo_sqlite, mbstring, xml, curl, bcmath). O comando <code>php artisan about</code> mostra informações sobre o ambiente.
                </div>
            </div>
        </section>

        {{-- OVERVIEW --}}
        <section id="overview" class="step">
            <h2>1. Visão Geral</h2>

            <div class="step-body">
                <p>O <strong>Laravel Page Builder</strong> é um construtor de páginas visual drag-and-drop inspirado no Elementor, usando <strong>SortableJS</strong> para um D&D fluido e preciso. Permite criar páginas web completas arrastando elementos para uma tela, editando conteúdo e estilo em tempo real, sem escrever código.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Stack Tecnológica</h3>
                <table class="widget-table">
                    <tr><th>Camada</th><th>Tecnologia</th><th>Versão</th></tr>
                    <tr><td>Backend</td><td>Laravel</td><td>12.x</td></tr>
                    <tr><td>Frontend</td><td>JavaScript vanilla (sem frameworks)</td><td>ES2022+</td></tr>
                    <tr><td>Banco de Dados</td><td>SQLite (padrão) / MySQL / MariaDB</td><td>&#8212;</td></tr>
                    <tr><td>PHP</td><td>LTS</td><td>8.2+</td></tr>
                    <tr><td>Build</td><td>Vite + Tailwind CSS + SortableJS</td><td>7.x / 4.x / 1.x</td></tr>
                    <tr><td>Testes</td><td>PHPUnit</td><td>11.x</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Números do Projeto</h3>
                <table class="widget-table">
                    <tr><th>Métrica</th><th>Valor</th></tr>
                    <tr><td>Elementos disponíveis</td><td>30 (Heading, Text, Image, Button, Section, Column, Inner Section, Callout, Table, Math, Vídeo, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion, Counter, Progress Bar, Social Icons, Icon Box, Image Box, Testimonial, Price Table, Countdown, Google Maps, Carousel, Mega Menu, Lottie)</td></tr>
                    <tr><td>Templates prontos</td><td>7 (Blank, Landing, About, Contact, Portfolio, Showcase Completo, Blog Post)</td></tr>
                    <tr><td>Rotas definidas</td><td>100+ (CRUD páginas, elementos, revisões, templates, temas, popups, widgets globais, fontes, colaboração, HTML import, dynamic tags, find & replace, API REST)</td></tr>
                    <tr><td>Testes automatizados</td><td>155 (61 unitários + 94 de feature)</td></tr>
                    <tr><td>Tabelas no banco</td><td>9 principais (projects, pages, elements, revisions, form_submissions, custom_fonts, global_widgets, popups, theme_templates) + personal_access_tokens</td></tr>
                    <tr><td>Views Blade</td><td>22 (editor + 7 partials, pages, auth, tutorial, popups, theme-templates, layouts)</td></tr>
                    <tr><td>Linhas de JS do editor</td><td>~5000 (8 módulos ES em resources/js/editor/)</td></tr>
                    <tr><td>API REST</td><td>Sanctum (token-based) com CRUD páginas e elementos</td></tr>
                    <tr><td>Jobs (filas)</td><td>4 (ImportHtmlJob, ClearPageCacheJob, AutoSaveRevisionJob, ProcessFormSubmissionJob)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Funcionalidades</h3>
                <ul>
                    <li>Criar páginas com título e status (rascunho / publicado)</li>
                    <li>Abrir um <strong>editor visual</strong> em tela cheia com tema escuro</li>
                    <li>Arrastar <strong>30 elementos</strong> (Heading, Text, Image, Button, Section, Column, Inner Section, Callout, Table, Math, Vídeo, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion, Counter, Progress Bar, Social Icons, Icon Box, Image Box, Testimonial, Price Table, Countdown, Google Maps, Carousel, Mega Menu, Lottie)</li>
                    <li>Selecionar qualquer elemento e editar suas <strong>configurações</strong> no painel direito</li>
                    <li><strong>Editor de texto rich-text (WYSIWYG)</strong> com toolbar: negrito, itálico, links, imagens, vídeos YouTube, listas, código fonte</li>
                    <li><strong>Inserir imagens</strong> no texto via upload ou colar (Ctrl+V) — imagem inline no conteúdo</li>
                    <li><strong>Inserir vídeos YouTube</strong> — cole a URL e o embed responsivo 16:9 é inserido automaticamente</li>
                    <li>Inline editing — duplo-clique para editar texto diretamente no canvas (preserva HTML)</li>
                    <li>Alternar entre visualização <strong>desktop / tablet / mobile</strong> com <strong>frames de dispositivo</strong> (bezel de celular/tablet)</li>
                    <li><strong>Busca de elementos</strong> — barra de pesquisa na aba Inserir para encontrar elementos rapidamente</li>
                    <li><strong>Painéis redimensionáveis</strong> — arraste a borda dos painéis esquerdo/direito para ajustar largura</li>
                    <li><strong>Multi-select</strong> — Ctrl+Click para selecionar vários elementos, duplicar ou excluir em lote</li>
                    <li><strong>Tour de onboarding</strong> — tour guiado de 10 passos na primeira vez que o editor é aberto</li>
                    <li>Aplicar <strong>7 templates</strong> prontos</li>
                    <li>Desfazer / refazer alterações (Ctrl+Z / Ctrl+Shift+Z)</li>
                    <li>Salvamento automático a cada 60 segundos, ou salvar / publicar manualmente</li>
                    <li>Sistema de <strong>revisões</strong> com diff e restauração</li>
                    <li>Duplicar, exportar (JSON), importar, copiar HTML, excluir páginas</li>
                    <li><strong>Importação de HTML</strong> — importar páginas externas via URL ou colar HTML diretamente</li>
                    <li><strong>Navegador (Navigator)</strong> — painel flutuante com árvore de elementos, drag-and-drop SortableJS para reordenar, renomear, menu de contexto</li>
                    <li><strong>Right-click context menu</strong> no canvas e no Navigator (Editar, Duplicar, Copiar, Colar, Mover, Excluir)</li>
                    <li><strong>Drag handle</strong> com SortableJS — indicadores visuais de posição (drop-before/drop-after), aninhamento em containers</li>
                    <li><strong>Zoom do canvas</strong> com Ctrl+Scroll, botões +/-, reset com Ctrl+0 (25%-200%)</li>
                    <li><strong>Tela cheia (Fullscreen)</strong> — esconder painéis laterais com botão ou F11</li>
                    <li><strong>Preview em tempo real</strong> — debounce 300ms em todos os controles (text, color, number, etc.)</li>
                    <li><strong>Estilo por widget</strong> — 3 abas (Content, Style, Advanced): tipografia, fundo, borda, sombra, hover, dimensões, animação, CSS customizado, visibilidade responsiva</li>
                    <li><strong>Responsive Tabs</strong> — abas Desktop/Tablet/Mobile no painel de configurações com valores por breakpoint</li>
                    <li><strong>Gradient Background</strong> — gradientes lineares e radiais com editor visual de cores e ângulo</li>
                    <li><strong>Parallax &amp; Vídeo Background</strong> — efeito parallax no scroll e vídeos de fundo autoplay/mute/loop</li>
                    <li><strong>Shape Dividers</strong> — 10 tipos de divisores SVG (tilt, waves, mountains, clouds, triangles, drip, etc.)</li>
                    <li><strong>Background Overlay</strong> — cor overlay com blend modes e opacidade</li>
                    <li><strong>Scroll-Triggered Animations</strong> — fade, zoom, slide ativados no scroll (IntersectionObserver)</li>
                    <li><strong>Image Filters</strong> — 9 filtros CSS (grayscale, sepia, blur, brightness, etc.) + rotação + flip</li>
                    <li><strong>Sticky Position</strong> — seções sticky com offset e direção configurável</li>
                    <li><strong>Column Resize</strong> — slider percentual 1-100% para largura de colunas</li>
                    <li><strong>Text Shadow &amp; Stroke</strong> — sombra e contorno de texto por elemento</li>
                    <li><strong>Global Colors &amp; Fonts</strong> — paleta de cores e fontes globais do site</li>
                    <li><strong>Command Finder (Ctrl+K)</strong> — busca de ações, widgets e configurações</li>
                    <li><strong>Revision History</strong> — histórico de revisões com diff visual e restauração</li>
                    <li><strong>Save as Template</strong> — exportar página como template JSON</li>
                    <li><strong>Column Structure Picker</strong> — 6 opções de layout ao criar seção</li>
                    <li><strong>ARIA Accessibility</strong> — roles semânticos, navegação por Tab, focus-visible</li>
                    <li><strong>Colaboração em tempo real</strong> — presença de usuários, bloqueio de elementos, cursores</li>
                    <li><strong>Theme Builder</strong> — templates de tema (header, footer, single, archive, 404, search_results) com condições de exibição</li>
                    <li><strong>Popups</strong> — criação e edição de popups com gatilhos e condições de exibição</li>
                    <li><strong>Global Widgets</strong> — widgets reutilizáveis em várias páginas</li>
                    <li><strong>Custom Fonts</strong> — upload e gestão de fontes personalizadas (ttf, woff, woff2)</li>
                    <li><strong>Dynamic Tags</strong> — conteúdo dinâmico a partir de dados do sistema</li>
                    <li><strong>Find &amp; Replace</strong> — busca e substituição de conteúdo em toda a página</li>
                    <li><strong>Projetos</strong> — organizar páginas, temas e popups em projetos privados por usuário, com projeto padrão "Meus Projetos" e seletor de projeto nas listagens</li>
                    <li><strong>API REST</strong> — autenticação via Sanctum tokens, CRUD completo de páginas e elementos</li>
                    <li>Integração com <strong>Moodle 4.5+</strong> via HTML renderizado</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Arquitetura Resumida</h3>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.8rem;line-height:1.6;overflow-x:auto;font-family:monospace">
<pre style="margin:0">┌──────────────────────────────────────────────────┐
│                  FRONTEND                         │
│   editor.blade.php (7 partials)                   │
│   resources/js/editor/ (8 módulos ES)              │
│   state, utils, canvas, history, navigator, dragdrop, html-import, css
├──────────────────────────────────────────────────┤
│                CONTROLLERS                        │
│   PageController │ ElementController              │
│   RevisionController │ FormController             │
│   CollaborationController │ HtmlImportController  │
├──────────────────────────────────────────────────┤
│                 SERVICES                          │
│   PageBuilderService │ Renderer │ WidgetManager   │
│   TemplateManager │ ElementManager                │
│   CollaborationService │ HtmlImportService        │
├──────────────────────────────────────────────────┤
│                  WIDGETS (30)                     │
│   BaseWidget → Heading │ Text │ Image │ Button    │
│              Section │ Column │ InnerSection       │
│              Callout │ Table │ Math │ Vídeo        │
│              Divider │ Spacer │ Icon │ Gallery     │
│              Form │ Tabs │ Accordion               │
│              Counter │ ProgressBar │ SocialIcons   │
│              IconBox │ ImageBox │ Testimonial      │
│              PriceTable │ Countdown │ GoogleMaps   │
│              Carousel │ MegaMenu │ Lottie         │
├──────────────────────────────────────────────────┤
│              DATABASE (SQLite)                    │
│   pages → elements (árvore) → revisions           │
│   form_submissions │ personal_access_tokens       │
└──────────────────────────────────────────────────┘</pre>
                </div>
            </div>
        </section>

        {{-- LICENSE --}}
        <section id="license" class="step">
            <h2>1b. Custo &amp; Licença</h2>
            <div class="step-body">
                <p>O <strong>Laravel Page Builder</strong> é um projeto <strong>gratuito e open-source</strong>, licenciado sob a <strong>MIT License</strong>. Isso significa:</p>

                <table class="widget-table">
                    <tr><th>Pergunta</th><th>Resposta</th></tr>
                    <tr><td><strong>É gratuito?</strong></td><td>Sim. Não há custos de licença, assinatura ou uso comercial.</td></tr>
                    <tr><td><strong>Pode usar em produção?</strong></td><td>Sim. Não há restrições de uso — pessoal, educacional ou comercial.</td></tr>
                    <tr><td><strong>Pode modificar o código?</strong></td><td>Sim. Você pode alterar, estender e personalizar livremente.</td></tr>
                    <tr><td><strong>Pode redistribuir?</strong></td><td>Sim. A MIT License permite distribuir cópias, incluindo para uso comercial.</td></tr>
                    <tr><td><strong>Precisa dar crédito?</strong></td><td>Recomendado (não obrigatório). Manter a licença MIT nos arquivos originais é boa prática.</td></tr>
                    <tr><td><strong>Tem suporte oficial?</strong></td><td>Não. É um projeto independente. Use as issues do GitHub para reportar bugs ou sugestões.</td></tr>
                    <tr><td><strong>Tem versão premium?</strong></td><td>Não. Todas as funcionalidades estão disponíveis na versão open-source.</td></tr>
                </table>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O código-fonte está disponível no repositório do GitHub. Você pode clonar, modificar e hospedar em seu próprio servidor sem nenhuma restrição. Não há dependências de serviços pagos ou APIs externas obrigatórias.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">O que está incluído (gratuito)</h3>
                <ul>
                    <li>30 widgets (Heading, Text, Image, Button, Section, Column, Inner Section, Callout, Table, Math, Vídeo, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion, Counter, Progress Bar, Social Icons, Icon Box, Image Box, Testimonial, Price Table, Countdown, Google Maps, Carousel, Mega Menu, Lottie)</li>
                    <li>Editor visual completo com drag-and-drop (SortableJS), undo/redo, zoom, fullscreen</li>
                    <li>7 templates prontos (Blank, Landing, About, Contact, Portfolio, Showcase, Blog Post)</li>
                    <li>Sistema de revisões com diff e restauração</li>
                    <li>Exportação/Importação de páginas (JSON)</li>
                    <li>Copiar HTML para uso em outros sistemas (Moodle, WordPress, etc.)</li>
                    <li>Suporte a LaTeX via KaTeX</li>
                    <li>Projetos — organização privada de páginas, temas e popups</li>
                    <li>155 testes automatizados</li>
                    <li>Integração com Moodle 4.5+</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Stack tecnológica (tudo open-source)</h3>
                <table class="widget-table">
                    <tr><th>Camada</th><th>Tecnologia</th><th>Licença</th></tr>
                    <tr><td>Backend</td><td>Laravel 12</td><td>MIT</td></tr>
                    <tr><td>Frontend</td><td>JavaScript vanilla</td><td>—</td></tr>
                    <tr><td>Banco de Dados</td><td>SQLite</td><td>Public Domain</td></tr>
                    <tr><td>Build</td><td>Vite</td><td>MIT</td></tr>
                    <tr><td>CSS</td><td>Tailwind CSS</td><td>MIT</td></tr>
                    <tr><td>Ícones</td><td>Font Awesome</td><td>MIT (icons) + SIL OFL (fonts)</td></tr>
                    <tr><td>Fórmulas</td><td>KaTeX</td><td>MIT</td></tr>
                    <tr><td>Fontes</td><td>Google Fonts (Inter)</td><td>SIL OFL</td></tr>
                </table>
                <p style="margin-top:.75rem;font-size:.88rem;color:#666">Toda a stack é composta por tecnologias open-source gratuitas. Não há custos ocultos.</p>
            </div>
        </section>

        {{-- AUTH --}}
        <section id="auth" class="step">
            <h2>2. Criar Conta &amp; Acessar</h2>
            <div class="step-body">
                <p>Antes de usar o Page Builder, você precisa de uma conta de usuário.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Criar uma conta</h3>
                <ol>
                    <li>Acesse a página <strong><a href="{{ route('register') }}">Registro</a></strong> clicando em "Criar conta" na tela de login.</li>
                    <li>Preencha:
                        <ul>
                            <li><strong>Nome</strong> — seu nome de exibição</li>
                            <li><strong>Email</strong> — deve ser único no sistema</li>
                            <li><strong>Senha</strong> — mínimo de 6 caracteres</li>
                            <li><strong>Confirmar Senha</strong> — repita a senha</li>
                        </ul>
                    </li>
                    <li>Clique em <strong>"Criar Conta"</strong>. Você será autenticado automaticamente e redirecionado para o <strong>Hub de Projetos</strong>.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Entrar</h3>
                <ol>
                    <li>Acesse a página de <strong><a href="{{ route('login') }}">Login</a></strong>.</li>
                    <li>Informe seu <strong>Email</strong> e <strong>Senha</strong>.</li>
                    <li>Clique em <strong>"Entrar"</strong>.</li>
                    <li>Em caso de erro, uma mensagem será exibida acima do formulário.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Sair</h3>
                <p>Clique em <strong>"Sair"</strong> no menu do Page Builder para encerrar a sessão.</p>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O banco de dados incluído no repositório já contém o usuário <code>admin@sistema.local</code> com senha <code>password</code>. Em uma instalação nova, o seeder cria <code>test@example.com</code> com senha <code>password</code>. Use a página de registro para adicionar novos usuários.
                </div>
            </div>
        </section>

        {{-- PROJECTS HUB --}}
        <section id="projects-hub" class="step">
            <h2>2b. Hub de Projetos (Estilo Unity)</h2>
            <div class="step-body">
                <p>Após fazer login, você agora é direcionado diretamente para o <strong>Hub de Projetos</strong>. A ideia é organizar tudo por projeto, assim como o Unity Hub faz com seus projetos de jogo.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">O que é o Hub de Projetos</h3>
                <ul>
                    <li><strong>Ponto de partida</strong> — Todas as criações começam a partir daqui</li>
                    <li><strong>Visão unificada</strong> — Veja todos os seus projetos em cards com contagem de páginas, temas e popups</li>
                    <li><strong>Navegação simples</strong> — Clique em um projeto para abrir seu Dashboard</li>
                    <li><strong>Organização por contexto</strong> — Páginas, temas e popups ficam agrupados por projeto</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Criando um novo projeto</h3>
                <ol>
                    <li>No Hub de Projetos, clique em <strong>"+ Novo Projeto"</strong>.</li>
                    <li>Preencha o formulário:
                        <ul>
                            <li><strong>Nome do projeto *</strong> — Nome obrigatório (ex.: "Site Institucional")</li>
                            <li><strong>Local do projeto</strong> — Campo informativo (ex.: <code>D:\Projetos\Site-Institucional</code>). Não é obrigatório.</li>
                            <li><strong>Descrição</strong> — Opcional, para identificar o objetivo do projeto</li>
                        </ul>
                    </li>
                    <li>Clique em <strong>"Criar"</strong>. O projeto será criado e você poderá acessá-lo pelo card.</li>
                </ol>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O campo "Local do projeto" é apenas informativo neste momento, seguindo a experiência do Unity Hub. Ele ajuda a organizar mentalmente onde aquele projeto "pertence".
                </div>
            </div>
        </section>

        {{-- PROJECT DASHBOARD --}}
        <section id="project-dashboard" class="step">
            <h2>2c. Dashboard do Projeto</h2>
            <div class="step-body">
                <p>Ao clicar em um projeto no Hub, você abre o <strong>Dashboard</strong> com tudo centralizado.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Elementos do Dashboard</h3>
                <ul>
                    <li><strong>Cabeçalho</strong> — Nome do projeto, descrição e botão "Voltar ao Hub"</li>
                    <li><strong>Estatísticas</strong> — Contadores rápidos de Páginas, Temas e Popups</li>
                    <li><strong>Cards Rápidos</strong> — Atalhos diretos para gerenciar Páginas, Temas e Popups</li>
                    <li><strong>Botão "+ Novo"</strong> — Menu dropdown com "Nova Página", "Novo Tema" e "Novo Popup"</li>
                    <li><strong>Recentes</strong> — Últimas páginas criadas/atualizadas neste projeto para acesso rápido</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Navegação</h3>
                <ol>
                    <li><strong>Hub → Projeto</strong> — Clique no card do projeto para abrir o dashboard</li>
                    <li><strong>Dashboard → Seção</strong> — Clique em "Páginas", "Temas" ou "Popups" para gerenciar</li>
                    <li><strong>Voltar</strong> — Use "Voltar ao Hub" para trocar de projeto rapidamente</li>
                    <li><strong>Criação rápida</strong> — Use o dropdown "+ Novo" para criar direto do dashboard</li>
                </ol>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O dashboard mantém você sempre no contexto do projeto. Ao criar uma página pelo dropdown "+ Novo", ela já será vinculada automaticamente ao projeto atual.
                </div>
            </div>
        </section>

        {{-- CREATE PAGE --}}
        <section id="create-page" class="step">
            <h2>3. Criar uma Página</h2>
            <div class="step-body">
                <p>Existem duas formas práticas para criar uma página:</p>
                <ol>
                    <li><strong>Pelo Dashboard do Projeto</strong> — Abra o projeto e clique em <strong>"+ Novo → Nova Página"</strong></li>
                    <li><strong>Pela lista de páginas</strong> — Dentro do projeto, acesse "Páginas" e clique em <strong>"Nova Página"</strong> na barra de navegação superior</li>
                </ol>
                <div class="illustration">
                    <div class="ill-preview" style="max-width:400px">
                        <div style="background:#f8f9fa;padding:1rem;border-radius:6px;border:1px solid #ddd;text-align:center">
                            <div style="font-size:.8rem;color:#888;margin-bottom:.5rem">cabeçalho da lista de páginas</div>
                            <div style="display:flex;gap:.5rem;justify-content:center">
                                <span style="display:inline-block;padding:.3rem .8rem;background:#007bff;color:#fff;border-radius:4px;font-size:.85rem">+ Nova Página</span>
                            </div>
                        </div>
                    </div>
                </div>
                <ol>
                    <li>Insira um <strong>Título</strong> (ex.: "Minha Landing Page")</li>
                    <li>Escolha um <strong>Status</strong> — <em>Rascunho</em> mantém oculta, <em>Publicado</em> a torna visível</li>
                    <li>Opcionalmente, escolha um <strong>Template</strong> (veja <a href="#templates">passo 10</a>)</li>
                    <li>Clique em <strong>"Criar &amp; Abrir Editor"</strong> para ir direto ao editor visual</li>
                </ol>
            </div>
        </section>

        {{-- BUILD FROM ZERO --}}
        <section id="build-from-zero" class="step">
            <h2>3b. Construindo uma Página do Zero (Template em Branco)</h2>
            <div class="step-body">
                <p>Este guia passo a passo mostra como construir uma página completa a partir do zero, usando o template <strong>"Página em Branco"</strong>. Vamos criar uma página simples com um cabeçalho hero, uma seção de conteúdo com duas colunas e um rodapé.</p>

                <div class="tip">
                    <strong>&#128161; Antes de começar:</strong> Entenda a hierarquia fundamental do page builder:
                    <br><br>
                    <strong>Página</strong> → <strong>Seção</strong> (container de linha) → <strong>Coluna</strong> (container de coluna) → <strong>Widget</strong> (conteúdo: título, texto, imagem, botão, etc.)
                    <br><br>
                    Você <em>sempre</em> começa arrastando uma <strong>Seção</strong> para a página. Dentro da Seção, colocamos <strong>Colunas</strong>. Dentro das Colunas, colocamos <strong>Widgets</strong>.
                </div>

                <div class="tip" style="background:#fff3cd;border-color:#ffc107">
                    <strong>&#9888; Atenção ao copiar texto:</strong> Para os exemplos de texto abaixo, <strong>digite manualmente</strong> no editor em vez de copiar e colar do tutorial. Copiar do navegador inclui formatação HTML indesejada (spans com estilos inline) nos campos de entrada.
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 1 — Criar a Página em Branco</h3>
                <ol>
                    <li>Na barra de navegação superior, clique em <strong>"+ Nova Página"</strong>.</li>
                    <li>No formulário de criação, preencha:
                        <ul>
                            <li><strong>Título:</strong> <code>Minha Primeira Página</code></li>
                            <li><strong>Status:</strong> <code>Rascunho</code> (recomendado enquanto estiver editando)</li>
                            <li><strong>Template:</strong> selecione <code>Página em Branco (começa do Zero)</code></li>
                        </ul>
                    </li>
                    <li>Clique em <strong>"Criar &amp; Abrir Editor"</strong>.</li>
                    <li>O editor abrirá com um canvas vazio — nenhuma seção ou widget ainda.</li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 2 — Adicionar a Primeira Seção (Cabeçalho Hero)</h3>
                <p>A seção é o container principal. Tudo começa com ela.</p>
                <ol>
                    <li>No painel esquerdo (Widgets), localize o widget <strong>"Seção" &#128194;</strong>.</li>
                    <li><strong>Arraste</strong> o widget "Seção" e <strong>solte</strong> na área cinza do canvas (onde diz "Arraste widgets aqui" ou similar).</li>
                    <li>Uma seção aparecerá no canvas como uma caixa vazia (a seção não cria colunas automaticamente).</li>
                    <li><strong>Clique na seção</strong> (na borda azul) para selecioná-la. O painel direito mostrará as configurações.</li>
                    <li>No painel direito, configure a seção:
                        <ul>
                            <li><strong>Layout:</strong> <code>full_width</code> (ocupa toda a largura da tela)</li>
                            <li><strong>Cor de fundo (aba Style):</strong> <code>#1e293b</code> (azul escuro)</li>
                            <li><strong>Padding superior (aba Advanced):</strong> <code>80px</code></li>
                            <li><strong>Padding inferior (aba Advanced):</strong> <code>80px</code></li>
                            <li><strong>Alinhar itens:</strong> <code>center</code></li>
                            <li><strong>Justificar conteúdo:</strong> <code>center</code></li>
                        </ul>
                    </li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para alterar um valor de padding, clique na aba <strong>"Advanced"</strong> no painel direito. Lá você encontra os campos de Padding e Margin para cada lado (topo, direita, baixo, esquerda).
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 3 — Adicionar e Configurar a Coluna</h3>
                <p>A seção criada está vazia. Agora precisamos adicionar uma coluna dentro dela.</p>
                <ol>
                    <li>No painel esquerdo (Widgets), localize o widget <strong>"Coluna" &#128194;</strong>.</li>
                    <li><strong>Arraste</strong> o widget "Coluna" e <strong>solte</strong> dentro da seção que criamos no Passo 2.</li>
                    <li>Uma coluna aparecerá dentro da seção.</li>
                    <li><strong>Clique na coluna</strong> para selecioná-la. No painel direito, configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code> (largura total)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                        </ul>
                    </li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Importante:</strong> A seção <strong>não cria colunas automaticamente</strong>. Você sempre precisa arrastar o widget "Coluna" para dentro da seção manualmente. Widgets de conteúdo (Título, Texto, Botão, etc.) só podem ser colocados dentro de colunas.
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 4 — Adicionar um Título (Heading)</h3>
                <p>Agora vamos colocar conteúdo dentro da coluna.</p>
                <ol>
                    <li>No painel esquerdo, localize o widget <strong>"Título" &#128221;</strong>.</li>
                    <li><strong>Arraste</strong> o widget "Título" e <strong>solte</strong> dentro da coluna (área azulclara dentro da seção).</li>
                    <li>Um título com texto padrão aparecerá. <strong>Clique nele</strong> para selecionar.</li>
                    <li>No painel direito, configure:
                        <ul>
                            <li><strong>Title (texto):</strong> digite: <em>Bem-vindo à Minha Página</em></li>
                            <li><strong>HTML Tag:</strong> <code>H1</code></li>
                            <li><strong>Size:</strong> <code>xxl</code></li>
                            <li><strong>Alignment:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li>Vá na aba <strong>"Style"</strong> do painel direito e configure:
                        <ul>
                            <li><strong>Color:</strong> <code>#ffffff</code> (branco, para contrastar com o fundo escuro)</li>
                            <li><strong>Font Weight:</strong> <code>800</code> (negrito forte)</li>
                        </ul>
                    </li>
                    <li>Vá na aba <strong>"Advanced"</strong> e configure:
                        <ul>
                            <li><strong>Bottom Margin:</strong> <code>16px</code> (espaço abaixo do título)</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 5 — Adicionar um Texto Descritivo</h3>
                <ol>
                    <li>No painel esquerdo, localize o widget <strong>"Texto" &#128196;</strong>.</li>
                    <li><strong>Arraste</strong> o widget "Texto" e <strong>solte</strong> dentro da mesma coluna, <em>abaixo</em> do título.</li>
                    <li><strong>Clique no texto</strong> para selecionar.</li>
                    <li>No painel direito, você verá um editor rich-text (WYSIWYG). Digite:
                        <ul>
                            <li><em>Esta é minha primeira página criada com o Page Builder. Arraste e solte widgets para construir o layout desejado.</em></li>
                        </ul>
                    </li>
                    <li>Configure os campos abaixo do editor:
                        <ul>
                            <li><strong>Alignment:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li>Vá na aba <strong>"Style"</strong> e configure:
                        <ul>
                            <li><strong>Font Size:</strong> <code>18</code></li>
                            <li><strong>Line Height:</strong> <code>1.8</code></li>
                        </ul>
                    </li>
                    <li>Vá na aba <strong>"Advanced"</strong> e configure:
                        <ul>
                            <li><strong>Bottom Margin:</strong> <code>32px</code></li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 6 — Adicionar um Botão</h3>
                <ol>
                    <li>No painel esquerdo, localize o widget <strong>"Botão" &#128073;</strong>.</li>
                    <li><strong>Arraste</strong> o widget "Botão" e <strong>solte</strong> dentro da mesma coluna, <em>abaixo</em> do texto.</li>
                    <li><strong>Clique no botão</strong> para selecionar.</li>
                    <li>No painel direito, configure:
                        <ul>
                            <li><strong>Text:</strong> <code>Saiba Mais</code></li>
                            <li><strong>Link:</strong> <code>#</code> (ou uma URL real)</li>
                            <li><strong>Size:</strong> <code>large</code></li>
                            <li><strong>Alignment:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li>Vá na aba <strong>"Style"</strong> e configure:
                        <ul>
                            <li><strong>Background Color:</strong> <code>#3b82f6</code> (azul)</li>
                            <li><strong>Text Color:</strong> <code>#ffffff</code> (branco)</li>
                            <li><strong>Border Radius:</strong> <code>8px</code></li>
                        </ul>
                    </li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Resultado até aqui:</strong> Você tem uma seção escura com um título branco grande, um texto descritivo e um botão azul centralizados. É o "hero" da página.
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 7 — Adicionar uma Segunda Seção (Conteúdo com Duas Colunas)</h3>
                <p>Agora vamos criar uma seção com duas colunas lado a lado.</p>
                <ol>
                    <li><strong>Arraste outro widget "Seção"</strong> do painel esquerdo e <strong>solte</strong> na página, <em>abaixo</em> da seção hero.</li>
                    <li>Selecione a nova seção e configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>boxed</code> (largura contida, centralizada)</li>
                            <li><strong>Cor de fundo (aba Style):</strong> <code>#ffffff</code> (branco)</li>
                            <li><strong>Padding superior (aba Advanced):</strong> <code>80px</code></li>
                            <li><strong>Padding inferior (aba Advanced):</strong> <code>80px</code></li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 8 — Criar a Primeira Coluna (Lado Esquerdo)</h3>
                <ol>
                    <li>No painel esquerdo, arraste o widget <strong>"Coluna"</strong> e <strong>solte</strong> dentro da seção que criou no Passo 7.</li>
                    <li><strong>Selecione essa coluna</strong>. No painel direito, configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-6</code> (50% da largura)</li>
                        </ul>
                    </li>
                    <li>Dentro dessa coluna, arraste um widget <strong>"Título"</strong> e configure:
                        <ul>
                            <li><strong>Title:</strong> digite: <em>Sobre Nós</em></li>
                            <li><strong>HTML Tag:</strong> <code>H2</code></li>
                            <li><strong>Size:</strong> <code>xl</code></li>
                            <li><strong>Alignment:</strong> <code>left</code></li>
                        </ul>
                    </li>
                    <li>Vá na aba <strong>"Style"</strong> do título e configure:
                        <ul>
                            <li><strong>Color:</strong> <code>#1e293b</code></li>
                            <li><strong>Font Weight:</strong> <code>700</code></li>
                        </ul>
                    </li>
                    <li>Abaixo do título, arraste um widget <strong>"Texto"</strong> e configure:
                        <ul>
                            <li><strong>Conteúdo:</strong> digite: <em>Somos uma equipe apaixonada por criar soluções digitais inovadoras. Nosso objetivo é transformar ideias em experiências excepcionais para nossos clientes.</em></li>
                            <li><strong>Font Size (aba Style):</strong> <code>16</code></li>
                            <li><strong>Line Height (aba Style):</strong> <code>1.8</code></li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 9 — Criar a Segunda Coluna (Lado Direito)</h3>
                <ol>
                    <li><strong>Arraste outro widget "Coluna"</strong> do painel esquerdo e <strong>solte</strong> dentro da mesma seção (abaixo ou ao lado da primeira coluna).</li>
                    <li>Selecione essa nova coluna e configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-6</code> (50% da largura)</li>
                        </ul>
                    </li>
                    <li>O page builder automaticamente colocará as duas colunas <strong>lado a lado</strong> (50% cada).</li>
                    <li>Dentro dessa segunda coluna, arraste um widget <strong>"Imagem"</strong> e configure:
                        <ul>
                            <li><strong>Image URL:</strong> cole a URL de uma imagem (ou use o botão de upload)</li>
                            <li><strong>Alt Text:</strong> <code>Imagem ilustrativa</code></li>
                            <li><strong>Width:</strong> <code>100%</code></li>
                        </ul>
                    </li>
                    <li>Opcionalmente, adicione um widget <strong>"Texto"</strong> abaixo da imagem com uma descrição.</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Se as colunas não estiverem lado a lado, verifique se ambas pertencem à <em>mesma seção</em>. Colunas dentro da mesma seção são automaticamente posicionadas horizontalmente.
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 10 — Adicionar uma Terceira Seção (Rodapé / CTA)</h3>
                <ol>
                    <li><strong>Arraste mais uma "Seção"</strong> e solte abaixo da seção anterior.</li>
                    <li>Configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>full_width</code></li>
                            <li><strong>Cor de fundo (aba Style):</strong> <code>#3b82f6</code> (azul)</li>
                            <li><strong>Padding superior (aba Advanced):</strong> <code>60px</code></li>
                            <li><strong>Padding inferior (aba Advanced):</strong> <code>60px</code></li>
                            <li><strong>Alinhar itens:</strong> <code>center</code></li>
                            <li><strong>Justificar conteúdo:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li>Arraste o widget <strong>"Coluna"</strong> e <strong>solte</strong> dentro dessa seção. Selecione a coluna e configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code></li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li>Dentro da coluna, arraste um <strong>"Título"</strong>:
                        <ul>
                            <li><strong>Title:</strong> digite: <em>Pronto para Começar?</em></li>
                            <li><strong>HTML Tag:</strong> <code>H2</code></li>
                            <li><strong>Size:</strong> <code>xl</code></li>
                            <li><strong>Alignment:</strong> <code>center</code></li>
                            <li><strong>Color (aba Style):</strong> <code>#ffffff</code></li>
                        </ul>
                    </li>
                    <li>Abaixo do título, arraste um <strong>"Botão"</strong>:
                        <ul>
                            <li><strong>Text:</strong> digite: <em>Fale Conosco</em></li>
                            <li><strong>Link:</strong> <code>#</code></li>
                            <li><strong>Size:</strong> <code>large</code></li>
                            <li><strong>Alignment:</strong> <code>center</code></li>
                            <li><strong>Background Color (aba Style):</strong> <code>#ffffff</code></li>
                            <li><strong>Text Color (aba Style):</strong> <code>#3b82f6</code></li>
                            <li><strong>Border Radius (aba Style):</strong> <code>50px</code> (formato pílula)</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 11 — Salvar e Publicar</h3>
                <ol>
                    <li>Revise a página no canvas — você deve ver 3 seções empilhadas: hero escura, conteúdo branco com 2 colunas, e rodapé azul.</li>
                    <li>Clique no botão <strong>&#128190; Salvar</strong> na barra superior para salvar como rascunho.</li>
                    <li>Quando estiver satisfeito, clique em <strong>&#128640; Publicar</strong> para tornar a página visível publicamente.</li>
                    <li>Para ver o resultado final, clique em <strong>&#128065; Visualizar</strong> (ícone de olho) ou acesse a URL pública da página.</li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Estrutura Final da Página</h3>
                <p>Após concluir todos os passos, sua árvore de elementos ficará assim:</p>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>Página: "Minha Primeira Página"
│
├── Seção 1 (full_width, fundo #1e293b)
│   └── Coluna (col-12, center)
│       ├── Título H1: "Bem-vindo à Minha Página" (branco, xxl)
│       ├── Texto: "Esta é minha primeira página..." (18px)
│       └── Botão: "Saiba Mais" (azul, large)
│
├── Seção 2 (boxed, fundo #ffffff)
│   ├── Coluna (col-6)
│   │   ├── Título H2: "Sobre Nós" (#1e293b, xl)
│   │   └── Texto: "Somos uma equipe..." (16px)
│   └── Coluna (col-6)
│       └── Imagem (100% width)
│
└── Seção 3 (full_width, fundo #3b82f6)
    └── Coluna (col-12, center)
        ├── Título H2: "Pronto para Começar?" (branco, xl)
        └── Botão: "Fale Conosco" (branco, pílula)</code></pre>

                <div class="tip">
                    <strong>&#128161; Dicas para personalizar:</strong>
                    <ul style="margin-top:.5rem">
                        <li><strong>Para reordenar elementos:</strong> arraste um widget para cima ou para baixo dentro da coluna.</li>
                        <li><strong>Para duplicar:</strong> clique com o botão direito em um elemento e selecione "Duplicar".</li>
                        <li><strong>Para remover:</strong> passe o mouse sobre o elemento e clique no ícone ✕.</li>
                        <li><strong>Para desfazer:</strong> pressione <kbd>Ctrl</kbd>+<kbd>Z</kbd> (até 50 etapas).</li>
                        <li><strong>Para editar texto rapidamente:</strong> duplo-clique no título ou texto no canvas para editar inline.</li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- THE EDITOR --}}
        <section id="editor" class="step">
            <h2>4. Conhecendo o Novo Editor</h2>
            <div class="step-body">
                <p>O editor foi redesenhado com uma interface mais limpa e fluida, inspirada no <strong>Articulate Storyline/Rise</strong>. Agora é mais fácil focar no conteúdo, com menos distrações. Ele carrega ao acessar <code>/page-builder/pages/{id}/editor</code>.</p>
                <div class="panel-layout-ill">
                    <div class="panel-ill left"><strong>Inserir / Camadas</strong><br><span style="font-size:.75rem">elementos e hierarquia</span></div>
                    <div class="panel-ill center"><strong>Canvas</strong><br><span style="font-size:.75rem">prévia limpa ao vivo</span></div>
                    <div class="panel-ill right"><strong>Propriedades</strong><br><span style="font-size:.75rem">estilo e configurações</span></div>
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Painel Esquerdo — Inserir, Camadas, Estrutura e Modelos</h3>
                <ul>
                    <li><strong>Inserir</strong> — Lista os elementos disponíveis em grupos organizados (Layout, Básicos, Interativo, Educacional). Arraste e solte direto no canvas.</li>
                    <li><strong>Camadas</strong> — Árvore unificada com hierarquia Seção → Coluna → Widget. Ótimo para navegar e reorganizar rapidamente.</li>
                    <li><strong>Estrutura</strong> — Visão alternativa da hierarquia para inspeção rápida.</li>
                    <li><strong>Modelos</strong> — Acesso rápido a modelos e blocos prontos.</li>
                    <li><strong>Busca</strong> — Digite para filtrar elementos instantaneamente.</li>
                    <li><strong>Painéis redimensionáveis</strong> — Ajuste a largura dos painéis conforme seu fluxo de trabalho.</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Centro — Canvas</h3>
                <ul>
                    <li>Exibe a prévia ao vivo da página como <strong>DOM real</strong> da árvore de elementos (edição direta, não um iframe)</li>
                    <li>Clique em qualquer elemento para selecioná-lo — ele fica com borda azul</li>
                    <li><strong>Multi-select:</strong> Ctrl+Click (ou Cmd+Click) para selecionar vários elementos — borda laranja indicando seleção múltipla</li>
                    <li>Barra flutuante aparece com opções: Duplicar, Excluir ou Limpar seleção</li>
                    <li>O elemento selecionado aparece destacado no painel direito</li>
                    <li>Duplo-clique em textos de Título e Texto para edição inline</li>
                    <li>Botão de remover (✕) aparece ao passar o mouse sobre elementos</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Painel Direito — Configurações</h3>
                <ul>
                    <li>Mostra as configurações do elemento selecionado, ou da página se nada estiver selecionado</li>
                    <li><strong>Abas de breakpoint</strong> — Desktop / Tablet / Mobile para definir valores responsivos por dispositivo</li>
                    <li>Cada configuração tem um campo de entrada (texto, cor, seleção, gradient, etc.)</li>
                    <li>O elemento de texto exibe um <strong>editor WYSIWYG</strong> com barra de ferramentas para formatação, imagens e vídeos</li>
                    <li>As mudanças são aplicadas instantaneamente ao canvas</li>
                    <li>Quando nada está selecionado, mostra um aviso para selecionar um elemento; ao selecionar, exibe as configurações (Content / Style / Advanced)</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Barra Superior</h3>
                <table class="widget-table">
                    <tr><th>Grupo</th><th>Botões</th><th>Função</th></tr>
                    <tr><td>Navegação</td><td>← Páginas / Temas + título + status</td><td>Voltar à listagem e visualizar status (Rascunho / Publicado)</td></tr>
                    <tr><td>Histórico</td><td>Desfazer / Refazer</td><td>Ctrl+Z / Ctrl+Shift+Z (máximo 50 estados)</td></tr>
                    <tr><td>Responsivo</td><td>Desktop / Tablet / Mobile</td><td>Alterna visualização com frame limpo por dispositivo</td></tr>
                    <tr><td>Zoom &amp; Tela Cheia</td><td>+ / - / 100% / Tela Cheia</td><td>Controle de zoom (25%-200%) e modo sem distrações</td></tr>
                    <tr><td>Ferramentas</td><td>Página / Site / Revisões / Localizar</td><td>Configurações globais, histórico e busca/substituição</td></tr>
                    <tr><td>Utilitários</td><td>Visualizar / Exportar / Salvar como Modelo / Importar HTML</td><td>Ações complementares organizadas em grupo</td></tr>
                    <tr><td>Ações Primárias</td><td>Salvar / Publicar</td><td>Salvar rascunho ou publicar a página</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Carregamento do Editor</h3>
                <ol>
                    <li>A página é carregada via GET para <code>/page-builder/pages/{id}/editor</code></li>
                    <li>O JS busca <code>GET /api/page-builder/pages/{id}/elements</code> para obter a árvore de elementos</li>
                    <li>Os elementos são renderizados recursivamente (Seção → Coluna → widgets)</li>
                    <li>O painel de configurações é carregado via <code>GET /api/page-builder/widgets/{type}/controls</code></li>
                    <li>Salvamento automático inicia a cada 60 segundos</li>
                </ol>
            </div>
        </section>

        {{-- ADDING WIDGETS --}}
        <section id="drag-widgets" class="step">
            <h2>5. Inserir Elementos (Arrastar &amp; Soltar)</h2>
            <div class="step-body">
                <p>O editor usa <strong>SortableJS</strong> para um arrastar e soltar fluido e preciso. Abra a aba <strong>Inserir</strong> no painel esquerdo, arraste o elemento desejado e solte no local exato do canvas. Indicadores sutis mostram onde ele será inserido.</p>
                <p>Cada elemento adiciona um tipo diferente de conteúdo. Veja o que cada um faz e suas configurações disponíveis:</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Visão Geral dos Elementos</h3>
                <table class="widget-table">
                    <tr><th>Elemento</th><th>O que cria</th><th>Total de controles</th></tr>
                    <tr><td><strong>Título (Heading)</strong></td><td>Um título grande (&lt;h1&gt;–&lt;h6&gt;) com tag, texto, alinhamento e cor configuráveis</td><td>8</td></tr>
                    <tr><td><strong>Texto</strong></td><td>Um parágrafo ou bloco de texto com conteúdo rich-text (WYSIWYG), imagens, vídeos e listas</td><td>7</td></tr>
                    <tr><td><strong>Imagem</strong></td><td>Uma imagem com URL, texto alternativo e largura configuráveis</td><td>7</td></tr>
                    <tr><td><strong>Botão</strong></td><td>Um botão clicável com texto, URL, alinhamento e cor configuráveis</td><td>8</td></tr>
                    <tr><td><strong>Seção</strong></td><td>Um contêiner estrutural. Você coloca Colunas ou outros elementos dentro dela</td><td>16</td></tr>
                    <tr><td><strong>Coluna</strong></td><td>Uma coluna vertical dentro de uma Seção. Controla largura, alinhamento, padding, fundo</td><td>10</td></tr>
                    <tr><td><strong>Seção Interna</strong></td><td>Permite criar colunas aninhadas dentro de uma coluna existente</td><td>10</td></tr>
                    <tr><td><strong>Destaque (Callout)</strong></td><td>Caixa de destaque com 9 tipos (info, success, warning, danger, tip, definition, theorem, exercise, note)</td><td>7</td></tr>
                    <tr><td><strong>Tabela</strong></td><td>Tabela HTML estilizada com cabeçalho, linhas alternadas e bordas configuráveis</td><td>9</td></tr>
                    <tr><td><strong>Fórmula Matemática</strong></td><td>Fórmula matemática LaTeX renderizada via KaTeX</td><td>8</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Título (Heading)</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Title</td><td>text (obrigatório)</td><td>Texto do título (máx. 500 caracteres)</td></tr>
                    <tr><td>HTML Tag</td><td>select</td><td>h1, h2, h3, h4, h5, h6, p, div</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right, justify</td></tr>
                    <tr><td>Size</td><td>select</td><td>small, default, medium, large, xl, xxl</td></tr>
                    <tr><td>Color</td><td>color</td><td>Qualquer cor (hex)</td></tr>
                    <tr><td>Font Family</td><td>text</td><td>Nome da fonte (ex: Arial, Georgia)</td></tr>
                    <tr><td>Font Weight</td><td>select</td><td>300, 400, 500, 600, 700, 800, 900</td></tr>
                    <tr><td>Link</td><td>url</td><td>URL para onde o título链接 (opcional)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Texto</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Content</td><td>wysiwyg (obrigatório)</td><td>Editor rich-text com toolbar (negrito, itálico, links, imagens, vídeos YouTube, listas, código fonte)</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right, justify</td></tr>
                    <tr><td>Color</td><td>color</td><td>Qualquer cor (hex)</td></tr>
                    <tr><td>Font Size</td><td>number</td><td>8–200px</td></tr>
                    <tr><td>Font Family</td><td>text</td><td>Nome da fonte</td></tr>
                    <tr><td>Line Height</td><td>number</td><td>0.5–5.0</td></tr>
                    <tr><td>Drop Cap</td><td>boolean</td><td>Sim/Não (primeira letra em destaque)</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Toolbar do Editor de Texto</h4>
                <p>Ao selecionar um widget de texto, o painel direito exibe um editor rich-text (WYSIWYG) com as seguintes ferramentas:</p>
                <table class="widget-table">
                    <tr><th>Botão</th><th>Função</th><th>Como usar</th></tr>
                    <tr><td><strong>B</strong></td><td>Negrito</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>I</strong></td><td>Itálico</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>U</strong></td><td>Sublinhado</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>S</strong></td><td>Tachado</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>&#9650;</strong></td><td>Título H2</td><td>Converte o parágrafo em título</td></tr>
                    <tr><td><strong>&#182;</strong></td><td>Parágrafo</td><td>Converte de volta para parágrafo</td></tr>
                    <tr><td><strong>&#128279;</strong></td><td>Inserir link</td><td>Pede a URL e cria um hyperlink</td></tr>
                    <tr><td><strong>&#128247;</strong></td><td>Inserir imagem</td><td>Abre seletor de arquivo, faz upload e insere inline</td></tr>
                    <tr><td><strong>&#9654;</strong></td><td>Vídeo YouTube</td><td>Pede a URL do YouTube e insere embed responsivo 16:9</td></tr>
                    <tr><td><strong>&#8226;</strong></td><td>Lista</td><td>Cria lista com marcadores</td></tr>
                    <tr><td><strong>1.</strong></td><td>Lista Numerada</td><td>Cria lista numerada</td></tr>
                    <tr><td><strong>&lt;/&gt;</strong></td><td>Código fonte</td><td>Alterna entre visual e edição HTML raw</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Você também pode <strong>colar imagens</strong> diretamente no editor (Ctrl+V) — elas são enviadas automaticamente e inseridas no texto. Para vídeos, clique no botão <strong>&#9654;</strong> e cole a URL do YouTube.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Imagem</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Image</td><td>image</td><td>URL da imagem</td></tr>
                    <tr><td>Caption</td><td>text</td><td>Legenda (máx. 300 caracteres)</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Width</td><td>text</td><td>Largura (ex: 100%, 300px)</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (ex: 8px, 50%)</td></tr>
                    <tr><td>Link</td><td>url</td><td>URL de destino ao clicar</td></tr>
                    <tr><td>Enable Lightbox</td><td>boolean</td><td>Sim/Não (abrir em lightbox)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Botão</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Button Text</td><td>text (obrigatório)</td><td>Texto do botão (máx. 100 caracteres)</td></tr>
                    <tr><td>Link</td><td>url</td><td>URL de destino</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right, stretch</td></tr>
                    <tr><td>Size</td><td>select</td><td>small, medium, large, xl</td></tr>
                    <tr><td>Background Color</td><td>color</td><td>Cor de fundo do botão</td></tr>
                    <tr><td>Text Color</td><td>color</td><td>Cor do texto</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (ex: 6px)</td></tr>
                    <tr><td>Font Size</td><td>number</td><td>10–100px</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Seção</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Layout</td><td>select</td><td>boxed, full_width, full_height</td></tr>
                    <tr><td>Content Width</td><td>text</td><td>Largura do conteúdo (ex: 1140px)</td></tr>
                    <tr><td>Min Height</td><td>text</td><td>Altura mínima (ex: 400px)</td></tr>
                    <tr><td>Align Items</td><td>select</td><td>stretch, flex-start, center, flex-end</td></tr>
                    <tr><td>Justify Content</td><td>select</td><td>flex-start, center, flex-end, space-between, space-around, space-evenly</td></tr>
                    <tr><td>Background Type</td><td>select</td><td>none, classic, gradient, vídeo</td></tr>
                    <tr><td>Background Color</td><td>color</td><td>Cor de fundo</td></tr>
                    <tr><td>Padding Top</td><td>text</td><td>Espaçamento superior (ex: 80px)</td></tr>
                    <tr><td>Padding Bottom</td><td>text</td><td>Espaçamento inferior</td></tr>
                    <tr><td>Padding Left</td><td>text</td><td>Espaçamento esquerdo</td></tr>
                    <tr><td>Padding Right</td><td>text</td><td>Espaçamento direito</td></tr>
                    <tr><td>Margin Top</td><td>text</td><td>Margem superior</td></tr>
                    <tr><td>Margin Bottom</td><td>text</td><td>Margem inferior</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda</td></tr>
                    <tr><td>Box Shadow</td><td>text</td><td>Sombra (ex: 0 4px 6px rgba(0,0,0,0.1))</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Coluna</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Column Width</td><td>slider (1-100%)</td><td>Largura percentual da coluna (padrão: 50%)</td></tr>
                    <tr><td>Vertical Alignment</td><td>select</td><td>stretch, flex-start, center, flex-end</td></tr>
                    <tr><td>Text Align</td><td>select</td><td>left, center, right, justify</td></tr>
                    <tr><td>Background Color</td><td>color</td><td>Cor de fundo</td></tr>
                    <tr><td>Padding Top</td><td>text</td><td>Espaçamento superior</td></tr>
                    <tr><td>Padding Bottom</td><td>text</td><td>Espaçamento inferior</td></tr>
                    <tr><td>Padding Left</td><td>text</td><td>Espaçamento esquerdo</td></tr>
                    <tr><td>Padding Right</td><td>text</td><td>Espaçamento direito</td></tr>
                    <tr><td>Margin</td><td>text</td><td>Margem externa</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda</td></tr>
                </table>

                <div class="tip">
                    <strong>&#128161; Dica — Unidades CSS:</strong> Nos campos de texto (Min Height, Padding, Margin, etc.), você pode usar diferentes unidades CSS:
                    <table class="widget-table" style="margin:.75rem 0">
                        <tr><th>Unidade</th><th>Exemplo</th><th>Significado</th></tr>
                        <tr><td><strong>vh</strong></td><td><code>90vh</code></td><td>Viewport Height — 90% da altura da tela do navegador</td></tr>
                        <tr><td><strong>vw</strong></td><td><code>100vw</code></td><td>Viewport Width — 100% da largura da tela</td></tr>
                        <tr><td><strong>px</strong></td><td><code>400px</code></td><td>Pixels — medida fixa</td></tr>
                        <tr><td><strong>%</strong></td><td><code>50%</code></td><td>Percentual — relativo ao elemento pai</td></tr>
                        <tr><td><strong>rem</strong></td><td><code>2rem</code></td><td>Relativo ao tamanho da fonte raiz (geralmente 16px)</td></tr>
                        <tr><td><strong>em</strong></td><td><code>1.5em</code></td><td>Relativo ao tamanho da fonte do elemento pai</td></tr>
                    </table>
                    Para uma <strong>seção hero</strong> que ocupa quase toda a tela, use <code>90vh</code> ou <code>100vh</code> no campo <em>Min Height</em>.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Como Adicionar</h3>
                <ol>
                    <li>Na aba <strong>Inserir</strong> do painel esquerdo, encontre o elemento desejado</li>
                    <li><strong>Arraste</strong> (clique e segure) e <strong>solte</strong> na tela</li>
                    <li>O elemento aparece instantaneamente no canvas</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Comece arrastando uma <strong>Seção</strong> para a tela, depois arraste uma <strong>Coluna</strong> para dentro da seção, e então arraste elementos de conteúdo (Título, Texto, etc.) para dentro da coluna. A estrutura é: Seção → Coluna → Elemento.
                </div>
            </div>
        </section>

        {{-- EDUCATIONAL WIDGETS --}}
        <section id="educational-widgets" class="step">
            <h2>5b. Elementos Educacionais</h2>
            <div class="step-body">
                <p>Além dos elementos básicos, o Page Builder inclui elementos especiais para conteúdo educacional, disponíveis no grupo <strong>"Educacional"</strong> na aba <strong>Inserir</strong> do painel esquerdo:</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Destaque (Callout)</h3>
                <p>Caixas coloridas para destacar informações importantes no conteúdo. Perfeitas para definições, teoremas, exercícios, avisos e dicas.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Type</td><td>select</td><td><strong>info</strong> (azul), <strong>success</strong> (verde), <strong>warning</strong> (amarelo), <strong>danger</strong> (vermelho), <strong>tip</strong> (ciano), <strong>definition</strong> (roxo), <strong>theorem</strong> (laranja), <strong>exercise</strong> (verde escuro), <strong>note</strong> (cinza)</td></tr>
                    <tr><td>Title</td><td>text</td><td>Título do callout (opcional)</td></tr>
                    <tr><td>Content</td><td>wysiwyg (obrigatório)</td><td>Conteúdo rich-text — use o editor para formatação, imagens, links</td></tr>
                    <tr><td>Show Icon</td><td>boolean</td><td>Mostrar/esconder o ícone do tipo selecionado</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (padrão: 8px)</td></tr>
                    <tr><td>Padding</td><td>text</td><td>Espaçamento interno (padrão: 16px 20px)</td></tr>
                    <tr><td>Margin Bottom</td><td>text</td><td>Margem inferior (padrão: 20px)</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Cada tipo de callout tem cores e ícones automáticos. Use <strong>definition</strong> para definições matemáticas, <strong>theorem</strong> para teoremas e <strong>exercise</strong> para exercícios — perfeito para cursos de matemática!
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Table (Tabela)</h3>
                <p>Tabelas HTML estilizadas com cabeçalho destacado, linhas alternadas e bordas. Insira o HTML da tabela diretamente no editor.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Table Content</td><td>wysiwyg (obrigatório)</td><td>HTML da tabela (<code>&lt;table&gt;</code>, <code>&lt;thead&gt;</code>, <code>&lt;tbody&gt;</code>, <code>&lt;th&gt;</code>, <code>&lt;td&gt;</code>)</td></tr>
                    <tr><td>Header Background</td><td>color</td><td>Cor de fundo do cabeçalho (padrão: #f1f5f9)</td></tr>
                    <tr><td>Header Text Color</td><td>color</td><td>Cor do texto do cabeçalho (padrão: #1e293b)</td></tr>
                    <tr><td>Border Color</td><td>color</td><td>Cor das bordas (padrão: #e2e8f0)</td></tr>
                    <tr><td>Alternating Rows</td><td>boolean</td><td>Linhas alternadas com cores diferentes (ativado por padrão)</td></tr>
                    <tr><td>Text Alignment</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Font Size</td><td>text</td><td>Tamanho da fonte (padrão: 14px)</td></tr>
                    <tr><td>Cell Padding</td><td>text</td><td>Espaçamento das células (padrão: 10px 14px)</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (padrão: 8px)</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para criar a tabela, insira o HTML básico no editor WYSIWYG. Exemplo: <code>&lt;table&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Nome&lt;/th&gt;&lt;th&gt;Nota&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;João&lt;/td&gt;&lt;td&gt;8.5&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;</code>
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Fórmula Matemática (Math)</h3>
                <p>Fórmulas matemáticas em LaTeX, renderizadas via <a href="https://katex.org" target="_blank">KaTeX</a>. Suporta display mode (centralizado e maior) e inline mode.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>LaTeX Formula</td><td>textarea (obrigatório)</td><td>Fórmula em sintaxe LaTeX (ex: <code>x = \frac{-b \pm \sqrt{b^2-4ac}}{2a}</code>)</td></tr>
                    <tr><td>Display Mode</td><td>boolean</td><td>Modo display — fórmula centralizada e maior (ativado por padrão)</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Font Size</td><td>text</td><td>Tamanho da fonte (padrão: 1.3em)</td></tr>
                    <tr><td>Color</td><td>color</td><td>Cor da fórmula (padrão: #1e293b)</td></tr>
                    <tr><td>Label</td><td>text</td><td>Rótulo ao lado da fórmula (ex: "(1)" para numerar)</td></tr>
                    <tr><td>Margin Top</td><td>text</td><td>Margem superior (padrão: 16px)</td></tr>
                    <tr><td>Margin Bottom</td><td>text</td><td>Margem inferior (padrão: 16px)</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para fórmulas inline no texto do widget <strong>Texto</strong>, clique no botão &#945; na toolbar do WYSIWYG e digite a fórmula LaTeX. A fórmula será renderizada inline no parágrafo. O widget <strong>Math</strong> é ideal para fórmulas em destaque ou centralizadas.
                </div>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Sintaxe LaTeX Básica</h4>
                <table class="widget-table">
                    <tr><th>Sintaxe</th><th>Resultado</th></tr>
                    <tr><td><code>x^2</code></td><td>x ao quadrado</td></tr>
                    <tr><td><code>\frac{a}{b}</code></td><td>fração a/b</td></tr>
                    <tr><td><code>\sqrt{x}</code></td><td>raiz quadrada de x</td></tr>
                    <tr><td><code>\sum_{i=1}^{n}</code></td><td>somatório de i=1 a n</td></tr>
                    <tr><td><code>\int_{a}^{b}</code></td><td>integral de a a b</td></tr>
                    <tr><td><code>\lim_{x \to 0}</code></td><td>limite quando x tende a 0</td></tr>
                    <tr><td><code>\alpha, \beta, \gamma</code></td><td>letras gregas &#945;, &#946;, &#947;</td></tr>
                    <tr><td><code>\leq, \geq, \neq</code></td><td>menor ou igual, maior ou igual, diferente</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Vídeo (Vídeo)</h3>
                <p>Incorporar vídeos do YouTube e Vimeo com configurações de aspect ratio, autoplay, loop e mute.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Vídeo URL</td><td>url</td><td>URL do YouTube ou Vimeo</td></tr>
                    <tr><td>Aspect Ratio</td><td>select</td><td>16:9, 4:3, 1:1, 21:9</td></tr>
                    <tr><td>Autoplay</td><td>boolean</td><td>Reproduzir automaticamente</td></tr>
                    <tr><td>Loop</td><td>boolean</td><td>Repetir vídeo</td></tr>
                    <tr><td>Mute</td><td>boolean</td><td>Silenciar áudio</td></tr>
                    <tr><td>Controls</td><td>boolean</td><td>Mostrar controles do player</td></tr>
                    <tr><td>Start/End Time</td><td>number</td><td>Tempo de início/fim em segundos</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Divider (Divisor)</h3>
                <p>Linha horizontal com configurações de estilo, espessura, cor e espaçamento.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Style</td><td>select</td><td>solid, dashed, dotted, double</td></tr>
                    <tr><td>Width</td><td>number</td><td>Largura em % (0-100)</td></tr>
                    <tr><td>Thickness</td><td>number</td><td>Espessura em px (1-20)</td></tr>
                    <tr><td>Color</td><td>color</td><td>Cor da linha</td></tr>
                    <tr><td>Space Before/After</td><td>number</td><td>Espaçamento antes/depois em px</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Spacer (Espaçador)</h3>
                <p>Espaço em branco configurável para ajustar distâncias entre elementos.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Space</td><td>number</td><td>Altura em px (0-500, padrão: 50)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Icon (Ícone)</h3>
                <p>Ícone Font Awesome com configurações de tamanho, cor e link.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Icon</td><td>icon picker</td><td>70+ ícones Font Awesome (fas/fab)</td></tr>
                    <tr><td>Icon Size</td><td>number</td><td>Tamanho em px (12-200)</td></tr>
                    <tr><td>Color</td><td>color</td><td>Cor do ícone</td></tr>
                    <tr><td>Align</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Link</td><td>url</td><td>Link opcional</td></tr>
                    <tr><td>Link New Tab</td><td>boolean</td><td>Abrir em nova aba</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Gallery (Galeria)</h3>
                <p>Galeria de imagens em layout grid ou masonry com suporte a upload múltiplo.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Images</td><td>gallery</td><td>Upload múltiplo com drag-and-drop</td></tr>
                    <tr><td>Columns</td><td>select</td><td>1-6 colunas</td></tr>
                    <tr><td>Gap</td><td>number</td><td>Espaçamento entre imagens (px)</td></tr>
                    <tr><td>Layout</td><td>select</td><td>grid, masonry</td></tr>
                    <tr><td>Show Captions</td><td>boolean</td><td>Mostrar legendas</td></tr>
                    <tr><td>Border Radius</td><td>number</td><td>Raio da borda (px)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Form (Formulário)</h3>
                <p>Formulário de contato com campos dinâmicos (text, email, textarea, select, checkbox, radio).</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Fields</td><td>repeater</td><td>Label, type, required — adicionar/remover campos</td></tr>
                    <tr><td>Button Text</td><td>text</td><td>Texto do botão de envio</td></tr>
                    <tr><td>Button Color</td><td>color</td><td>Cor de fundo do botão</td></tr>
                    <tr><td>Button Width</td><td>select</td><td>auto, full (largura total)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabs (Abas)</h3>
                <p>Abas de conteúdo com navigacao horizontal.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Tabs</td><td>repeater</td><td>Title, content — adicionar/remover abas</td></tr>
                    <tr><td>Active Tab</td><td>number</td><td>Aba ativa por padrão</td></tr>
                    <tr><td>Tab Color</td><td>color</td><td>Cor da aba ativa</td></tr>
                    <tr><td>Border Color</td><td>color</td><td>Cor da borda inferior</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Accordion (Acordeão)</h3>
                <p>Seções expansíveis/colapsáveis com conteúdo oculto.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Items</td><td>repeater</td><td>Title, content, open — adicionar/remover itens</td></tr>
                    <tr><td>Tab Color</td><td>color</td><td>Cor do cabeçalho aberto</td></tr>
                    <tr><td>Border Color</td><td>color</td><td>Cor da borda</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Navegador &amp; Atalhos</h3>
                <p>O editor inclui funcionalidades avançadas de produtividade:</p>
                <table class="widget-table">
                    <tr><th>Feature</th><th>Descrição</th></tr>
                    <tr><td><strong>Navigator</strong></td><td>Painel flutuante com árvore de elementos — drag-and-drop SortableJS para reordenar e aninhar, renomear (duplo clique), menu de contexto, busca por nome</td></tr>
                    <tr><td><strong>Multi-select</strong></td><td>Ctrl+Click para selecionar vários elementos — barra flutuante com Duplicar, Excluir, Limpar seleção</td></tr>
                    <tr><td><strong>Widget Search</strong></td><td>Barra de busca no painel esquerdo — filtra widgets por nome em tempo real</td></tr>
                    <tr><td><strong>Command Finder</strong></td><td><kbd>Ctrl</kbd>+<kbd>K</kbd> — modal de busca global para ações, widgets e configurações</td></tr>
                    <tr><td><strong>Right-click</strong></td><td>Menu de contexto no canvas: Editar, Duplicar, Mover cima/baixo, Copiar, Colar, Excluir</td></tr>
                    <tr><td><strong>Drag Handle</strong></td><td>Ícone ⣿ para arrastar reordenar com linhas indicadoras de posição</td></tr>
                    <tr><td><strong>Zoom</strong></td><td>Ctrl+Scroll ou botões +/- (25%-200%), Ctrl+0 para reset</td></tr>
                    <tr><td><strong>Fullscreen</strong></td><td>Botão ou F11 para esconder painéis laterais</td></tr>
                    <tr><td><strong>Painéis Resizáveis</strong></td><td>Arraste a borda dos painéis esquerdo/direito para ajustar largura (200px–500px)</td></tr>
                    <tr><td><strong>Onboarding</strong></td><td>Tour guiado de 10 passos na primeira vez que o editor é aberto</td></tr>
                    <tr><td><strong>Atalhos</strong></td><td>Ctrl+Z desfazer, Ctrl+Shift+Z refazer, Ctrl+S salvar, Ctrl+D duplicar, Ctrl+K finder, Delete excluir, Tab navegar, Escape limpar seleção</td></tr>
                    <tr><td><strong>Inline Editing</strong></td><td>Duplo-clique no canvas para editar texto diretamente (Heading, Text, Button, Callout)</td></tr>
                    <tr><td><strong>Copy/Paste Styles</strong></td><td>Ctrl+Shift+C para copiar estilos, Ctrl+Shift+V para colar em outro widget</td></tr>
                    <tr><td><strong>ARIA Accessibility</strong></td><td>Roles semânticos, navegação por Tab/Shift+Tab, focus-visible outlines, screen-reader support</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.5rem;margin-bottom:.5rem">Widgets Avançados</h3>
                <p>Além dos widgets básicos e educacionais, o Page Builder inclui widgets avançados para elementos visuais comuns em páginas web modernas:</p>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Counter (Contador Animado)</h4>
                <p>Número animado que conta do valor inicial ao final com efeito visual.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Starting Number</td><td>number</td><td>Número inicial (padrão: 0)</td></tr>
                    <tr><td>Ending Number</td><td>number</td><td>Número final (padrão: 100)</td></tr>
                    <tr><td>Separator</td><td>text</td><td>Separador de milhar (ex: , ou .)</td></tr>
                    <tr><td>Prefix</td><td>text</td><td>Texto antes do número (ex: $)</td></tr>
                    <tr><td>Suffix</td><td>text</td><td>Texto depois do número (ex: +)</td></tr>
                    <tr><td>Duration</td><td>number</td><td>Duração da animação em segundos (padrão: 2)</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Progress Bar (Barra de Progresso)</h4>
                <p>Barra de progresso com título, porcentagem e animação de preenchimento.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Title</td><td>text</td><td>Título da barra</td></tr>
                    <tr><td>Percentage</td><td>number</td><td>Porcentagem (0-100)</td></tr>
                    <tr><td>Background Color</td><td>color</td><td>Cor de fundo da barra</td></tr>
                    <tr><td>Fill Color</td><td>color</td><td>Cor do preenchimento</td></tr>
                    <tr><td>Animate</td><td>boolean</td><td>Animação de preenchimento ao entrar na viewport</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Social Icons</h4>
                <p>Grade de ícones de redes sociais com links configuráveis.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Icons</td><td>repeater</td><td>Plataforma (Facebook, Twitter, Instagram, LinkedIn, YouTube, GitHub, WhatsApp, Email), cor, URL</td></tr>
                    <tr><td>Columns</td><td>select</td><td>1-6 colunas</td></tr>
                    <tr><td>Icon Size</td><td>number</td><td>Tamanho dos ícones em px</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Icon Box</h4>
                <p>Caixa com ícone, título e descrição — perfeita para features e benefícios.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Icon</td><td>icon picker</td><td>Ícone Font Awesome</td></tr>
                    <tr><td>Title</td><td>text</td><td>Título da feature</td></tr>
                    <tr><td>Description</td><td>textarea</td><td>Descrição curta</td></tr>
                    <tr><td>Icon Position</td><td>select</td><td>left, top</td></tr>
                    <tr><td>Icon Color</td><td>color</td><td>Cor do ícone</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Image Box</h4>
                <p>Caixa com imagem, título e descrição — ideal para cards e conteúdo visual.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Image</td><td>image</td><td>URL da imagem</td></tr>
                    <tr><td>Title</td><td>text</td><td>Título</td></tr>
                    <tr><td>Description</td><td>textarea</td><td>Descrição</td></tr>
                    <tr><td>Title Tag</td><td>select</td><td>h2, h3, h4, h5, h6, p</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Testimonial</h4>
                <p>Card de depoimento com nome, cargo, empresa, conteúdo e avaliação por estrelas.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Name</td><td>text</td><td>Nome do depoente</td></tr>
                    <tr><td>Position</td><td>text</td><td>Cargo</td></tr>
                    <tr><td>Company</td><td>text</td><td>Empresa</td></tr>
                    <tr><td>Content</td><td>textarea</td><td>Texto do depoimento</td></tr>
                    <tr><td>Avatar</td><td>image</td><td>Foto do depoente</td></tr>
                    <tr><td>Rating</td><td>number</td><td>Avaliação 1-5 estrelas</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Price Table</h4>
                <p>Tabela de preços com features, destaque e período.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Title</td><td>text</td><td>Nome do plano</td></tr>
                    <tr><td>Price</td><td>text</td><td>Preço (ex: 99)</td></tr>
                    <tr><td>Currency</td><td>text</td><td>Moeda (ex: R$)</td></tr>
                    <tr><td>Period</td><td>text</td><td>Período (ex: /mês)</td></tr>
                    <tr><td>Features</td><td>repeater</td><td>Lista de features incluídas</td></tr>
                    <tr><td>Featured</td><td>boolean</td><td>Plano destacado</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Countdown</h4>
                <p>Timer regressivo com contadores de dias, horas, minutos e segundos.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Target Date</td><td>text</td><td>Data alvo (formato ISO)</td></tr>
                    <tr><td>Show Labels</td><td>boolean</td><td>Mostrar labels (Days, Hours, etc.)</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Google Maps (OpenStreetMap)</h4>
                <p>Mapa interativo usando OpenStreetMap (sem necessidade de API key).</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Latitude</td><td>text</td><td>Latitude (ex: -15.7975)</td></tr>
                    <tr><td>Longitude</td><td>text</td><td>Longitude (ex: -47.8919)</td></tr>
                    <tr><td>Zoom</td><td>number</td><td>Nível de zoom (1-19)</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Carousel (Carrossel de Imagens)</h4>
                <p>Carrossel CSS com scroll-snap para galerias de imagens.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Images</td><td>gallery</td><td>Upload múltiplo</td></tr>
                    <tr><td>Columns</td><td>number</td><td>Colunas visíveis (1-5)</td></tr>
                    <tr><td>Gap</td><td>number</td><td>Espaçamento entre imagens (px)</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Inner Section (Seção Interna)</h4>
                <p>Container para sub-colunas dentro de uma coluna — permite layouts aninhados sem depender de uma nova seção principal.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Columns</td><td>number</td><td>Número de colunas (1-10)</td></tr>
                    <tr><td>Column Gap</td><td>select</td><td>small, medium, large, no gap</td></tr>
                    <tr><td>Direction</td><td>select</td><td>row, column</td></tr>
                </table>
            </div>
        </section>

        {{-- EDITING WIDGET SETTINGS --}}
        <section id="select-settings" class="step">
            <h2>6. Selecionar &amp; Editar Propriedades</h2>
            <div class="step-body">
                <ol>
                    <li><strong>Clique</strong> em qualquer elemento no canvas — ele ganha um destaque azul indicando que está selecionado</li>
                    <li>O <strong>painel direito</strong> é atualizado para mostrar todas as propriedades editáveis daquele elemento</li>
                    <li>Cada elemento expõe seus próprios controles (ex.: Título tem <em>Tag HTML</em>, <em>Texto</em>, <em>Alinhamento</em>, <em>Cor</em>)</li>
                    <li>Altere qualquer valor — o canvas <strong>atualiza em tempo real</strong></li>
                </ol>
                <div class="illustration">
                    <div class="ill-flow">
                        <span>Clique no elemento</span>
                        <span>&rarr;</span>
                        <span>Painel de propriedades exibe controles</span>
                        <span>&rarr;</span>
                        <span>Altere o valor</span>
                        <span>&rarr;</span>
                        <span>Canvas atualiza ao vivo</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- PAGE SETTINGS --}}
        <section id="page-settings" class="step">
            <h2>7. Configurações da Página &amp; Configurações do Site</h2>
            <div class="step-body">
                <p>Você também pode alterar configurações que se aplicam a página inteira:</p>
                <ol>
                    <li>Na barra de ferramentas do editor, clique em <strong>"Config. da Página"</strong></li>
                    <li>O painel direito alterna para controles de nível de página</li>
                    <li>Configure:
                        <ul>
                            <li><strong>Largura do Container</strong> — largura máxima do conteúdo (ex.: 1140px, largura total)</li>
                            <li><strong>Fundo da Página</strong> — cor ou imagem de fundo para toda a página</li>
                            <li><strong>Padding do Conteúdo</strong> — espaçamento interno ao redor do conteúdo</li>
                            <li><strong>CSS Personalizado</strong> — CSS puro para customização avançada</li>
                        </ul>
                    </li>
                    <li>As alterações se aplicam instantaneamente à prévia no canvas</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Configurações do Site (Global Colors &amp; Fonts)</h3>
                <p>O Page Builder permite definir <strong>cores e fontes globais</strong> que podem ser reutilizadas em toda a página:</p>
                <ol>
                    <li>No editor, clique em <strong>"⚙️ Site"</strong> na barra de ferramentas (ou use <kbd>Ctrl</kbd>+<kbd>K</kbd> e digite "site settings")</li>
                    <li>O painel mostra duas seções:
                        <ul>
                            <li><strong>Cores Globais</strong> — adicione, edite e remova cores que podem ser aplicadas em qualquer widget</li>
                            <li><strong>Fontes Globais</strong> — selecione entre 14 fontes do sistema (Inter, Roboto, Open Sans, Lato, Montserrat, Poppins, Raleway, Nunito, Merriweather, Playfair Display, Oswald, Ubuntu, Crimson Text)</li>
                        </ul>
                    </li>
                    <li>As cores e fontes globais ficam salvas nas configurações da página</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Clique em "Config. da Página" novamente para desselecionar e voltar à edição de widgets. As configurações do site ficam acessíveis pelo botão "Site" ou pelo Finder (Ctrl+K).
                </div>
            </div>
        </section>

        {{-- RESPONSIVE --}}
        <section id="responsive" class="step">
            <h2>8. Visualização Responsiva</h2>
            <div class="step-body">
                <p>Veja como sua página fica em diferentes tamanhos de tela, com <strong>frames de dispositivo</strong> realistas e <strong>valores responsivos por breakpoint</strong>:</p>
                <ol>
                    <li>Na barra de ferramentas do editor, clique em um dos ícones responsivos:
                        <ul>
                            <li>&#128187; <strong>Desktop</strong> — largura total, sem frame</li>
                            <li>&#128241; <strong>Tablet</strong> — 768px com frame de tablet (bezel escuro, cantos arredondados)</li>
                            <li>&#128241; <strong>Mobile</strong> — 375px com frame de celular (notch, cantos mais arredondados)</li>
                        </ul>
                    </li>
                    <li>O canvas é envolto por um <strong>device frame</strong> com notch e label indicando o tamanho</li>
                    <li>A largura do canvas se ajusta instantaneamente com transição suave</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Valores Responsivos por Breakpoint</h3>
                <p>No painel direito de configurações, cada control de estilo possui <strong>abas de breakpoint</strong> (Desktop, Tablet, Mobile) que permitem definir valores diferentes para cada dispositivo:</p>
                <ol>
                    <li>Selecione um widget no canvas</li>
                    <li>No painel direito, observe as <strong>abas de breakpoint</strong> (Desktop / Tablet / Mobile) acima dos controles</li>
                    <li>Clique em <strong>Tablet</strong> ou <strong>Mobile</strong> para definir valores específicos</li>
                    <li>O sistema salva automaticamente com sufixos <code>_tablet</code> e <code>_mobile</code></li>
                    <li>O frontend aplica <strong>media queries CSS</strong> para usar o valor correto em cada breakpoint</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Se você não definir um valor para Tablet ou Mobile, o sistema usa automaticamente o valor do Desktop como fallback. Isso permite definir apenas as exceções responsivas.
                </div>
            </div>
        </section>

        {{-- TEMPLATES --}}
        <section id="templates" class="step">
            <h2>9. Usar Templates</h2>
            <div class="step-body">
                <p>Os templates fornecem um ponto de partida pré-populando a página com seções, colunas e widgets.</p>
                <p><strong>Ao criar uma nova página:</strong></p>
                <ol>
                    <li>No formulário de <strong>Criar Página</strong>, procure pela seção "Template"</li>
                    <li>Escolha entre:
                        <ul>
                            <li><strong>Página em Branco</strong> — começa vazia (padrão)</li>
                            <li><strong>Landing Page</strong> — seção hero + grade de recursos com títulos, textos e botões</li>
                            <li><strong>Sobre (About)</strong> — introdução da empresa com seções de missão/equipe</li>
                            <li><strong>Contato (Contact)</strong> — layout de formulário de contato com informações e área de formulário</li>
                            <li><strong>Showcase Completo</strong> — página marketing futurista (tema dark) com hero em gradiente + vídeo, galeria e carousel de imagens, imagens com lightbox e links, contadores, progress bars, countdown, tabs, accordion, preços, depoimentos, animação Lottie, formulário, mapa e redes sociais</li>
                        </ul>
                    </li>
                    <li>Clique em <strong>"Criar &amp; Abrir Editor"</strong> para ver o template carregado no editor</li>
                    <li>Você pode então modificar qualquer elemento ou adicionar mais widgets</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Salvar como Template</h3>
                <p>Você pode salvar uma página como template reutilizável:</p>
                <ol>
                    <li>No editor, clique em <strong>"Template"</strong> na barra de ferramentas</li>
                    <li>Digite um <strong>nome</strong> para o template</li>
                    <li>O template é exportado como arquivo JSON nomeado</li>
                    <li>Para reutilizar, importe o template na criação de uma nova página</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Você também pode aplicar um template a uma página existente a partir da lista de páginas usando o botão "Aplicar Template" no menu de ações.
                </div>
            </div>
        </section>

        {{-- UNDO / REDO --}}
        <section id="undo-redo" class="step">
            <h2>10. Desfazer, Refazer &amp; Histórico de Revisões</h2>
            <div class="step-body">
                <p>Toda alteração feita no editor é rastreada, permitindo voltar ou avançar:</p>
                <ul>
                    <li><strong>Desfazer</strong> — pressione <kbd>Ctrl</kbd> + <kbd>Z</kbd> ou clique no botão &#8630; na barra de ferramentas</li>
                    <li><strong>Refazer</strong> — pressione <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> ou clique no botão &#8631; na barra de ferramentas</li>
                </ul>
                <p>Funciona em todas as ações: adicionar, mover, excluir elementos, alterar configurações, etc.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Histórico de Revisões</h3>
                <p>O Page Builder mantém um <strong>histórico completo de revisões</strong> da página, permitindo ver todas as versões anteriores e restaurá-las:</p>
                <ol>
                    <li>No editor, clique em <strong>"Revisões"</strong> na barra de ferramentas</li>
                    <li>Uma lista de revisões aparece com data, hora, tipo (manual/auto-save) e autor</li>
                    <li>Cada revisão tem dois botões:
                        <ul>
                            <li><strong>Restaurar</strong> — reverte a página para aquela versão</li>
                            <li><strong>Ver Diff</strong> — mostra uma comparação linha por linha com cores (verde = adicionado, vermelho = removido)</li>
                        </ul>
                    </li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> O sistema cria revisões automaticamente a cada 60 segundos (auto-save) e sempre que você salva manualmente. As revisões ficam armazenadas no banco de dados e podem ser comparadas com diff visual colorido.
                </div>
            </div>
        </section>

        {{-- SAVE & PUBLISH --}}
        <section id="save-publish" class="step">
            <h2>11. Salvar &amp; Publicar</h2>
            <div class="step-body">
                <p>Dois botões na barra de ferramentas do editor permitem persistir seu trabalho:</p>
                <table class="widget-table">
                    <tr><th>Botão</th><th>O que faz</th></tr>
                    <tr><td><strong>Salvar (Save)</strong></td><td>Envia os dados atuais da página para o servidor via requisição <code>PUT /page-builder/pages/{id}</code> com status <code>draft</code>. O editor exibe um toast "Page saved!" e o badge de status permanece como "draft".</td></tr>
                    <tr><td><strong>Publicar (Publish)</strong></td><td>Exibe uma confirmação: <em>"Publish this page?"</em>. Se confirmado, envia um <code>POST /page-builder/pages/{id}/publish</code> que altera o campo <code>status</code> da página no banco de dados para <code>published</code>. Após a resposta, exibe um toast "Page published!" e <strong>recarrega</strong> a página do editor para refletir o novo status.</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Fluxo detalhado do Publish</h3>
                <ol>
                    <li>O usuário clica no botão <strong>Publish</strong> (ícone &#128752;).</li>
                    <li>Uma caixa de diálogo <code>confirm()</code> pergunta: <em>"Publish this page?"</em>.</li>
                    <li>Se o usuário cancelar, nada acontece.</li>
                    <li>Se confirmar, o JavaScript envia uma requisição <code>POST</code> para a rota <code>/page-builder/pages/{id}/publish</code> com o token CSRF.</li>
                    <li>No servidor, o controller <code>PageController@publish()</code> (arquivo <code>app/Http/Controllers/PageBuilder/PageController.php:405</code>) executa:
                        <ul>
                            <li><code>$page->status = 'published'</code> — altera o status no model Eloquent.</li>
                            <li><code>$page->save()</code> — persiste a alteração no banco de dados.</li>
                            <li>Retorna um JSON: <code>{"message": "Page published successfully", "page": {...}}</code>.</li>
                        </ul>
                    </li>
                    <li>O JavaScript recebe a resposta, exibe um toast "Page published!" e chama <code>location.reload()</code>.</li>
                    <li>Após o recarregamento, o badge na barra de ferramentas do editor mostra <strong>published</strong> em verde.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Diferença entre Salvar e Publicar</h3>
                <ul>
                    <li><strong>Salvar</strong> apenas persiste os dados. O status da página não é alterado — permanece como estava (draft ou published).</li>
                    <li><strong>Publicar</strong> altera o status para <code>published</code> e também recarrega o editor para refletir visualmente a mudança.</li>
                    <li>Não há diferença de visibilidade no front-end público: todas as rotas de renderização são protegidas por <code>auth</code>, então apenas usuários logados podem ver qualquer página, seja rascunho ou publicada.</li>
                    <li>O status <code>published</code> vs <code>draft</code> serve como indicador de progresso e pode ser usado por integrações externas para saber quais páginas estão prontas para exibição.</li>
                </ul>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O editor também faz <strong>salvamento automático</strong> a cada 60 segundos (via <code>setInterval</code> no JavaScript), para que você não perca trabalho se esquecer de salvar manualmente. O auto-save usa o mesmo fluxo do botão "Save" — ou seja, não publica automaticamente.
                </div>
            </div>
        </section>

        {{-- PREVIEW --}}
        <section id="preview" class="step">
            <h2>12. Visualizar uma Página</h2>
            <div class="step-body">
                <p>Na lista de páginas, clique em <strong>"Visualizar"</strong> ao lado de qualquer página.</p>
                <p>Isso abre uma prévia limpa da página com todos os widgets renderizados e estilizados como apareceriam no site ao vivo.</p>
                <p>Se a página ainda for um <strong>rascunho</strong>, apenas você (usuários logados) pode vê-la.</p>
            </div>
        </section>

        {{-- DUPLICATE / DELETE --}}
        <section id="duplicate-delete" class="step">
            <h2>13. Ações nas Páginas (Listagem)</h2>
            <div class="step-body">
                <p>Na lista de páginas, cada página tem os seguintes botões de ação:</p>
                <table class="widget-table">
                    <tr><th>Ação</th><th>Como funciona</th></tr>
                    <tr><td><strong>Editar (Edit)</strong></td><td>Abre o editor visual em tela cheia para modificar o layout e conteúdo da página.</td></tr>
                    <tr><td><strong>Visualizar (View)</strong></td><td>Abre uma prévia limpa da página em uma nova aba, como os visitantes a veriam.</td></tr>
                    <tr><td><strong>Duplicar (Duplicate)</strong></td><td>Cria uma cópia exata da página (incluindo todos os elementos) com "(cópia)" anexado ao título. A página duplicada inicia como rascunho.</td></tr>
                    <tr><td><strong>Exportar (Export)</strong></td><td>Baixa a página como um arquivo <code>.json</code> — você pode compartilhar ou fazer backup. O nome do arquivo é baseado no título da página.</td></tr>
                    <tr><td><strong>Copiar HTML (Copy HTML)</strong></td><td>Copia o HTML renderizado da página para a área de transferência. Ideal para colar o conteúdo em outras ferramentas ou sistemas (como o Moodle).</td></tr>
                    <tr><td><strong>Importar (Import)</strong></td><td>Abre um modal para selecionar um arquivo <code>.json</code> previamente exportado. A página importada é recriada no sistema com novo ID.</td></tr>
                    <tr><td><strong>Excluir (Delete)</strong></td><td>Remove permanentemente a página e todos os seus elementos. Uma confirmação é solicitada antes da exclusão.</td></tr>
                </table>
                <div class="tip" style="margin-top:1rem">
                    <strong>&#128161; Dica:</strong> Use <strong>Exportar</strong> para fazer backup das suas páginas ou transferi-las entre instalações. Use <strong>Copiar HTML</strong> para colar o conteúdo renderizado em sistemas externos como Moodle, WordPress ou editores HTML.
                </div>
            </div>
        </section>

        {{-- COLLABORATION --}}
        <section id="collaboration" class="step">
            <h2>14. Colaboração em Tempo Real</h2>
            <div class="step-body">
                <p>O Page Builder suporta <strong>colaboração simultânea</strong> — múltiplos usuários podem editar a mesma página ao mesmo tempo. O sistema usa cache (Redis/Cache) para rastrear presença e bloqueio de elementos.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Funcionalidades de Colaboração</h3>
                <table class="widget-table">
                    <tr><th>Feature</th><th>Descrição</th></tr>
                    <tr><td><strong>Presença de Usuários</strong></td><td>Mostra quem está editando a página em tempo real. Cada usuário tem uma cor atribuída automaticamente.</td></tr>
                    <tr><td><strong>Bloqueio de Elementos</strong></td><td>Quando um usuário seleciona um elemento, ele fica bloqueado para outros usuários. Retorna HTTP 409 se outro usuário tentar editar.</td></tr>
                    <tr><td><strong>Heartbeat</strong></td><td>A cada 15 segundos, o editor envia um heartbeat para manter a presença ativa. Usuários inativos por mais de 30 segundos são removidos da lista.</td></tr>
                    <tr><td><strong>Cursores</strong></td><td>A posição do cursor é compartilhada entre os usuários (via heartbeat).</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Colaboração</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Descrição</th></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/collab/join</code></td><td>Entrar na sessão de colaboração</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/collab/leave</code></td><td>Sair da sessão</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/collab/heartbeat</code></td><td>Enviar heartbeat com posição do cursor</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/collab/users</code></td><td>Listar usuários ativos na página</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements/{elementId}/lock</code></td><td>Bloquear elemento para edição</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements/{elementId}/unlock</code></td><td>Desbloquear elemento</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Como Usar</h3>
                <ol>
                    <li>Abram a <strong>mesma página</strong> no editor (ambos logados)</li>
                    <li>O painel de colaboração mostra os <strong>usuários ativos</strong> com suas cores</li>
                    <li>Ao <strong>selecionar um elemento</strong>, ele fica bloqueado para outros</li>
                    <li>Se outro usuário tentar editar o mesmo elemento, receberá uma mensagem: <em>"Element is being edited by another user"</em></li>
                    <li>Ao <strong>deselecionar</strong> o elemento, ele é desbloqueado automaticamente</li>
                </ol>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> A colaboração usa o cache do Laravel (configurado como <code>cache_store</code> no <code>.env</code>). Para produção, configure Redis para melhor performance. O TTL de presença é de 30 segundos — usuários inativos são removidos automaticamente.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Cores de Usuário</h3>
                <p>Cada usuário recebe uma cor automaticamente baseada no seu <code>user_id</code>. As cores disponíveis são: vermelho, laranja, amarelo, verde, ciano, azul, roxo, rosa, e mais. A cor é exibida ao lado do nome do usuário no painel de colaboração.</p>
            </div>
        </section>

        {{-- HTML IMPORT --}}
        <section id="html-import" class="step">
            <h2>15. Importação de HTML</h2>
            <div class="step-body">
                <p>O Page Builder permite <strong>importar páginas externas</strong> a partir de HTML puro ou URLs. O sistema converte automaticamente o HTML em elementos do page builder (seções, colunas e widgets).</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Como Importar</h3>
                <ol>
                    <li>Na lista de páginas, clique em <strong>"Import"</strong></li>
                    <li>Escolha uma das opções:
                        <ul>
                            <li><strong>Cole HTML:</strong> paste o código HTML diretamente no textarea</li>
                            <li><strong>URL:</strong> cole a URL de uma página web para buscar automaticamente</li>
                        </ul>
                    </li>
                    <li>Opcionalmente, defina um <strong>título</strong> para a página importada</li>
                    <li>Clique em <strong>"Importar"</strong> — a importação é processada em segundo plano via Job</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">O que é Convertido</h3>
                <table class="widget-table">
                    <tr><th>HTML Original</th><th>Widget do Page Builder</th></tr>
                    <tr><td><code>&lt;h1&gt;</code>–<code>&lt;h6&gt;</code></td><td>Widget <strong>Heading</strong> com tag e cor preservadas</td></tr>
                    <tr><td><code>&lt;p&gt;</code>, <code>&lt;div&gt;</code>, <code>&lt;span&gt;</code></td><td>Widget <strong>Text</strong> com formatação inline</td></tr>
                    <tr><td><code>&lt;img&gt;</code>, <code>&lt;figure&gt;</code></td><td>Widget <strong>Image</strong> com src, alt e caption</td></tr>
                    <tr><td><code>&lt;a&gt;</code> com classes de botão</td><td>Widget <strong>Button</strong> com texto, link e cores</td></tr>
                    <tr><td><code>&lt;table&gt;</code></td><td>Widget <strong>Text</strong> com HTML da tabela preservado</td></tr>
                    <tr><td><code>&lt;ul&gt;</code>, <code>&lt;ol&gt;</code></td><td>Widget <strong>Text</strong> com lista preservada</td></tr>
                    <tr><td><code>&lt;blockquote&gt;</code></td><td>Widget <strong>Callout</strong> (tipo note)</td></tr>
                    <tr><td><code>&lt;pre&gt;</code></td><td>Widget <strong>Text</strong> com bloco de código</td></tr>
                    <tr><td><code>&lt;hr&gt;</code></td><td>Widget <strong>Divider</strong></td></tr>
                    <tr><td>Links YouTube</td><td>Widget <strong>Video</strong> com embed automático</td></tr>
                    <tr><td><code>&lt;div&gt;</code> com background</td><td><strong>Seção</strong> com cor de fundo e padding</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Importação</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Descrição</th></tr>
                    <tr><td>POST</td><td><code>/page-builder/html-import</code></td><td>Importar HTML (via textarea ou URL) — processa em background via Job</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/html-import/fetch</code></td><td>Buscar HTML de uma URL (retorna o HTML bruto)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Limitações</h3>
                <ul>
                    <li><strong>Tamanho máximo:</strong> 500KB de HTML</li>
                    <li><strong>URLs permitidas:</strong> apenas HTTP/HTTPS</li>
                    <li><strong>Timeout:</strong> 15 segundos para buscar URLs</li>
                    <li><strong>Scripts:</strong> tags <code>&lt;script&gt;</code>, <code>&lt;style&gt;</code> e <code>&lt;noscript&gt;</code> são removidos</li>
                    <li><strong>Processamento:</strong> a importação roda via Queue Job (<code>ImportHtmlJob</code>) — necessário configurar <code>QUEUE_CONNECTION</code> no <code>.env</code></li>
                </ul>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para importar páginas do Moodle ou WordPress, copie o HTML da página e cole no textarea. O sistema converte automaticamente headings, parágrafos, imagens, tabelas e listas em widgets do page builder. Após importar, abra a página no editor para ajustar o layout.
                </div>
            </div>
        </section>

        {{-- SHOWCASE COMPLETO TEMPLATE --}}
        <section id="showcase" class="step">
            <h2>16. Template Showcase Completo</h2>
            <div class="step-body">
                <p>O template <strong>Showcase Completo</strong> é uma landing page futurista completa construída inteiramente com o page builder. Ele foi redesenhado com uma paleta <strong>dark sci-fi</strong> (fundo <code>#0b1120</code>, acentos neon <code>#22d3ee</code>, <code>#a78bfa</code>, <code>#34d399</code> e <code>#f0abfc</code>) e demonstra o máximo de funcionalidade possível: <strong>seções com gradiente</strong>, <strong>vídeos</strong> (YouTube e Vimeo), <strong>imagens com links e lightbox</strong>, <strong>galeria e carousel</strong>, <strong>contadores, progress bars e countdown</strong>, <strong>preços, depoimentos, animação Lottie, formulário, mapa e redes sociais</strong> — tudo isso mantendo a estrutura simples de arrastar e soltar.</p>

                <p>Este passo a passo mostra as técnicas de construção usadas no template e descreve sua estrutura atual. Cada cor, texto e configuração abaixo corresponde à versão redesenhada do template. Para ver a configuração exata de cada elemento, crie uma página com o template "Showcase Completo" e clique em cada widget no editor para inspecionar o painel direito.</p>

                <div class="tip">
                    <strong>&#128161; Dica Rápida:</strong> Você não precisa construir tudo manualmente! Basta criar uma página e selecionar o template <strong>"Showcase Completo"</strong> na hora da criação. O passo a passo abaixo é para quem quer entender como cada seção foi construída e personalizar depois.
                </div>
                <div class="info" style="background:#eff6ff;border-color:#93c5fd;padding:.75rem 1rem;border-radius:8px;margin:.75rem 0">
                    <strong>&#128204; Sobre as abas de configuração:</strong> Cada widget possui três abas no painel direito: <strong>Content</strong> (conteúdo principal), <strong>Style</strong> (aparência visual: cores, tipografia, background, border) e <strong>Advanced</strong> (espaçamento: padding, margin, z-index, CSS). As instruções abaixo indicam a aba correta entre parênteses quando a configuração não está na aba padrão (Content). Exemplo: <code>Padding superior: 120px (aba Advanced)</code>.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Visão Geral das Seções</h3>
                <p>O template possui <strong>19 seções</strong> que, de cima para baixo, formam a landing page completa:</p>
                <table class="widget-table">
                    <tr><th>#</th><th>Seção</th><th>Fundo / Layout</th><th>Widgets em destaque</th></tr>
                    <tr><td>1</td><td><strong>Hero</strong></td><td>Gradiente escuro <code>#020617 → #312e81</code>, full_width, 90vh</td><td>Callout, Título H1, Texto, 2 Botões com link (<code>#precos</code>, <code>#video</code>), Divider, Social Icons, <strong>Vídeo YouTube</strong></td></tr>
                    <tr><td>2</td><td>Números (cabeçalho)</td><td><code>#0b1120</code> (escuro)</td><td>Título H2, Texto, Divider</td></tr>
                    <tr><td>3</td><td><strong>Contadores</strong></td><td><code>#0b1120</code>, full_width</td><td>4x Counter com prefixo/sufixo e cores neon</td></tr>
                    <tr><td>4</td><td>Recursos (cabeçalho)</td><td><code>#0b1120</code>, boxed</td><td>Título H2, Texto, Divider</td></tr>
                    <tr><td>5</td><td><strong>Recursos Futuristas</strong></td><td>3 cards <code>#111827</code> com radius e shadow</td><td>Icon + Título H3 + Texto + <strong>Progress Bar</strong> em cada card</td></tr>
                    <tr><td>6</td><td>Galeria (cabeçalho)</td><td><code>#0b1120</code>, boxed</td><td>Título H2, Texto, Divider</td></tr>
                    <tr><td>7</td><td><strong>Galeria de Imagens</strong></td><td><code>#0b1120</code>, boxed</td><td><strong>Gallery</strong> em grade 3 colunas com legendas + <strong>Carousel</strong> com autoplay, dots e setas</td></tr>
                    <tr><td>8</td><td>Imagens com Links (cabeçalho)</td><td><code>#0b1120</code>, boxed</td><td>Título H2, Texto</td></tr>
                    <tr><td>9</td><td><strong>Imagens com Links</strong></td><td><code>#0b1120</code>, boxed</td><td>Imagem com <strong>lightbox</strong>, Imagem com <strong>link externo</strong>, Título com link, Botão link + <strong>Vídeo Vimeo</strong></td></tr>
                    <tr><td>10</td><td>Widgets Interativos (cabeçalho)</td><td><code>#0b1120</code>, boxed</td><td>Título H2, Texto</td></tr>
                    <tr><td>11</td><td><strong>Widgets Interativos</strong></td><td><code>#0b1120</code>, 2 colunas</td><td><strong>Tabs</strong> + <strong>Countdown</strong> em uma coluna; <strong>Accordion</strong> na outra</td></tr>
                    <tr><td>12</td><td>Preços (cabeçalho)</td><td><code>#0b1120</code>, boxed</td><td>Título H2, Texto</td></tr>
                    <tr><td>13</td><td><strong>Tabelas de Preço</strong></td><td>3 cards, plano Pro destacado (<code>#a855f7</code>)</td><td>3x <strong>Price Table</strong> com features e botão de link (<code>#contato</code>)</td></tr>
                    <tr><td>14</td><td>Depoimentos (cabeçalho)</td><td><code>#0b1120</code>, boxed</td><td>Título H2, Texto</td></tr>
                    <tr><td>15</td><td><strong>Depoimentos</strong></td><td>3 cards <code>#1e293b</code></td><td>3x <strong>Testimonial</strong> com avatar, cargo e nota 5 estrelas</td></tr>
                    <tr><td>16</td><td><strong>CTA + Lottie</strong></td><td>Gradiente radial <code>#1e1b4b → #0b1120</code></td><td><strong>Animação Lottie</strong>, Título H2, Texto, Botão link (<code>#contato</code>)</td></tr>
                    <tr><td>17</td><td>Contato (cabeçalho)</td><td><code>#0b1120</code>, boxed</td><td>Título H2, Texto</td></tr>
                    <tr><td>18</td><td><strong>Contato</strong></td><td>card <code>#111827</code> + coluna de info</td><td><strong>Form</strong> (nome, email, assunto, mensagem), Callout + <strong>Google Maps</strong></td></tr>
                    <tr><td>19</td><td><strong>Rodapé</strong></td><td><code>#020617</code> (escuro), full_width</td><td><strong>Social Icons</strong>, Divider, Texto com links (Termos, Privacidade)</td></tr>
                </table>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.1 Funcionalidades em Destaque — Links, Vídeos e Imagens</h3>
                <p>A versão redesenhada do <strong>Showcase Completo</strong> foi pensada para demonstrar, de forma simples, os principais recursos de <strong>links</strong>, <strong>vídeos</strong> e <strong>imagens</strong>:</p>
                <ul>
                    <li><strong>Links:</strong> botões com âncoras da própria página (<code>#precos</code>, <code>#video</code>, <code>#contato</code>), imagens com link externo que abrem em nova aba (<em>target="_blank"</em>), títulos com link, botões das tabelas de preço e ícones de redes sociais — tudo configurado no campo <strong>Link</strong> de cada widget (aba Content).</li>
                    <li><strong>Vídeos:</strong> um vídeo <strong>YouTube</strong> no Hero e um vídeo <strong>Vimeo</strong> na seção "Imagens com Links". Basta colar a URL no widget <strong>Vídeo</strong> (campo <em>Vídeo URL</em>) — a prévia de incorporação (embed) é gerada automaticamente. Opções: autoplay, loop, controles, mudo, tempo de início/fim e proporção (16:9, 4:3, 1:1, 21:9).</li>
                    <li><strong>Imagens:</strong>
                        <ul>
                            <li><strong>Lightbox:</strong> ative <em>Enable Lightbox</em> no widget Imagem para abrir a foto ampliada ao clicar (Hero de imagens com links);</li>
                            <li><strong>Galeria:</strong> layout em grade com 3 colunas, legendas e canto arredondado;</li>
                            <li><strong>Carousel:</strong> com autoplay, loop, dots, setas e velocidade configuráveis;</li>
                            <li><strong>Filtros e hover:</strong> efeitos como zoom no hover e filtros (grayscale, sepia, brilho) na aba Style do widget Imagem.</li>
                        </ul>
                    </li>
                </ul>
                <div class="tip">
                    <strong>&#128161; Como testar:</strong> crie uma página com o template "Showcase Completo", clique em <strong>Visualizar</strong> (olho na barra de ferramentas) e teste os links, o lightbox das imagens, o carousel e o player de vídeo. Para trocar uma imagem, vídeo ou link, selecione o widget no editor e edite o painel direito.
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2 Construindo o Template — Roteiro Completo</h3>
                <p>O template segue um padrão de construção repetido: primeiro uma <strong>seção-cabeçalho</strong> (coluna <code>col-12</code> com Título H2, Texto descritivo e Divider neon), depois uma <strong>seção de conteúdo</strong> com colunas em grade. O fluxo geral em qualquer peça é: <strong>arraste uma Seção → arraste Colunas → arraste os Widgets</strong> (na ordem correta) → configure no painel direito.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.1 Hero em 2 Colunas (Seção 1)</h3>
                <p>O Hero moderno usa <strong>duas colunas 50/50</strong>: à esquerda o texto com botões e links; à direita um <strong>vídeo</strong>. Fundo em gradiente, não cor sólida.</p>
                <ol>
                    <li><strong>Seção:</strong> arraste uma Seção e configure (aba Content):
                        <ul>
                            <li><strong>Layout:</strong> <code>full_width</code></li>
                            <li><strong>Background Type:</strong> <code>gradient</code> → <strong>Linear</strong>, ângulo <code>160</code>, <strong>Cor 1:</strong> <code>#020617</code>, <strong>Cor 2:</strong> <code>#312e81</code></li>
                            <li><strong>Padding vertical:</strong> <code>100px</code> (aba Advanced)</li>
                            <li><strong>Altura mínima:</strong> <code>90vh</code>, <strong>Alinhar itens:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li><strong>Coluna esquerda</strong> (<code>col-6</code>, alinhamento vertical <code>center</code>, padding direita <code>40px</code>). Widgets, em ordem:
                        <ul>
                            <li><strong>Callout</strong> (type <code>tip</code>): título <code>"Nova versão 3.0"</code>, conteúdo <code>"Interface mais rápida, inteligente e futurista."</code>, <strong>border radius</strong> <code>50px</code>, margem inferior <code>24px</code>.</li>
                            <li><strong>Título</strong> H1: <code>"Construa o Futuro com o Page Builder"</code>, tamanho <code>xxl</code>, cor <code>#f8fafc</code>, peso <code>800</code>, <strong>text shadow</strong> <code>0 0 24px rgba(129,140,248,.55)</code> (efeito de brilho neon).</li>
                            <li><strong>Texto:</strong> descrição com palavras em <code>&lt;strong&gt;</code> destacadas (vídeos, imagens, animações, links).</li>
                            <li><strong>Botão 1:</strong> <code>"Começar Agora"</code>, <strong>Link:</strong> <code>#precos</code>, <strong>Background Type:</strong> <code>gradient</code> <code>#6366f1 → #a855f7</code>, radius <code>50px</code>, sombra roxa.</li>
                            <li><strong>Botão 2:</strong> <code>"▶ Ver Demonstração"</code>, <strong>Link:</strong> <code>#video</code>, <strong>classic transparente</strong> com borda <code>2px #6366f1</code>.</li>
                            <li><strong>Divider:</strong> largura <code>100</code>%, espessura <code>1</code>, cor <code>#1e293b</code>.</li>
                            <li><strong>Social Icons:</strong> github, linkedin, twitter, youtube (4 colunas, ícones <code>36px</code>).</li>
                        </ul>
                    </li>
                    <li><strong>Coluna direita</strong> (<code>col-6</code>, centralizada). Widget:
                        <ul>
                            <li><strong>Vídeo:</strong> <strong>Vídeo Type:</strong> <code>youtube</code>, <strong>Vídeo URL:</strong> <code>https://www.youtube.com/watch?v=ScMzIvxBSi4</code>, <strong>Aspect Ratio:</strong> <code>16:9</code>, largura <code>100%</code>. (O campo correto é <em>Vídeo URL</em>, não "URL" — sem ele o vídeo não aparece.)</li>
                        </ul>
                    </li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Âncoras:</strong> os links dos botões apontam para âncoras da própria página (<code>#precos</code>, <code>#video</code>, <code>#contato</code>). Como as seções não possuem ID próprio, as âncoras são uma demonstração didática de links internos.
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.2 Contadores Neon (Seções 2 e 3)</h3>
                <p>Números animados com cores neon criam impacto visual. A seção 2 é o cabeçalho e a seção 3 é a grade com os contadores.</p>
                <ol>
                    <li><strong>Seção 2 (cabeçalho):</strong> layout <code>boxed</code>, fundo <code>#0b1120</code>, padding <code>100px</code>. Coluna <code>col-12</code> centralizada com:
                        <ul>
                            <li><strong>Título:</strong> <code>"O Poder em Números"</code>, H2, tamanho <code>xl</code>, cor <code>#f8fafc</code>, peso <code>700</code>.</li>
                            <li><strong>Texto:</strong> descrição em <code>#94a3b8</code>, máximo <code>600px</code>.</li>
                            <li><strong>Divider:</strong> largura <code>60</code>%, espessura <code>3</code>, cor <code>#22d3ee</code> (ciano), margens <code>16px</code>.</li>
                        </ul>
                    </li>
                    <li><strong>Seção 3 (conteúdo):</strong> layout <code>boxed</code>, fundo <code>#0b1120</code>, padding inferior <code>100px</code>. <strong>Quatro colunas <code>col-3</code></strong> com texto centralizado.</li>
                    <li><strong>Em cada coluna use o widget Contador (Counter)</strong> — não um Título:
                        <ul>
                            <li><code>"500+"</code> com <strong>Title:</strong> <code>"Projetos Entregues"</code>, <strong>Suffix:</strong> <code>+</code>, duração <code>2000</code>, cor do número <code>#22d3ee</code>.</li>
                            <li><code>"98%"</code> com <strong>Suffix:</strong> <code>%</code>, cor <code>#a78bfa</code>, título <code>"Satisfação dos Clientes"</code>.</li>
                            <li><code>"50+"</code> com <strong>Suffix:</strong> <code>+</code>, cor <code>#34d399</code>, título <code>"Profissionais"</code>.</li>
                            <li><code>"12+"</code> com <strong>Suffix:</strong> <code>+</code>, cor <code>#f0abfc</code>, título <code>"Anos de Experiência"</code>.</li>
                        </ul>
                    </li>
                    <li>Configure em cada Counter: <strong>Font Size do número:</strong> <code>2.6rem</code>, peso <code>800</code>; título em <code>#94a3b8</code>.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.3 Cards de Recursos com Barras de Progresso (Seções 4 e 5)</h3>
                <ol>
                    <li><strong>Seção 4 (cabeçalho):</strong> boxed, fundo <code>#0b1120</code>, padding <code>100px</code>. Coluna <code>col-12</code> centralizada: Título <code>"Recursos Poderosos"</code> + Texto descritivo + Divider ciano.</li>
                    <li><strong>Seção 5 (conteúdo):</strong> boxed, fundo <code>#0b1120</code>, padding inferior <code>100px</code>. <strong>Três colunas <code>col-4</code></strong>, cada uma estilo <strong>card escuro</strong>: fundo <code>#111827</code>, radius <code>16px</code>, box shadow <code>0 8px 32px rgba(0,0,0,.35)</code>, padding <code>32px</code>. Dentro de cada card, em ordem:
                        <ul>
                            <li><strong>Ícone:</strong> Font Awesome neon, ex.: <code>fas fa-bolt</code> cor <code>#22d3ee</code>, tamanho <code>2.5rem</code>.</li>
                            <li><strong>Título:</strong> H3 <code>"Aprendizado Rápido"</code>, cor <code>#f8fafc</code>, peso <code>600</code>.</li>
                            <li><strong>Texto:</strong> descrição em <code>#94a3b8</code>.</li>
                            <li><strong>Barra de Progresso:</strong> <strong>Title:</strong> <code>"Interatividade"</code>, <strong>Percentage:</strong> <code>98</code>, <strong>Bar Color:</strong> <code>#22d3ee</code>, <strong>Track Color:</strong> <code>#1e293b</code>.</li>
                        </ul>
                    </li>
                    <li>Card 2: ícone <code>fas fa-mobile-alt</code> cor <code>#a78bfa</code>, título <code>"Fácil de Usar"</code>, progresso <code>100</code>% (bar color <code>#a78bfa</code>).</li>
                    <li>Card 3: ícone <code>fas fa-puzzle-piece</code> cor <code>#34d399</code>, título <code>"Ferramentas Profissionais"</code>, progresso <code>85</code>% (bar color <code>#34d399</code>).</li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.4 Galeria e Carousel (Seções 6 e 7)</h3>
                <p>Dois widgets de apresentação de imagens: uma <strong>galeria em grade</strong> e um <strong>carousel com autoplay</strong>. A seção 6 é o cabeçalho; as seções 7 carregam o conteúdo.</p>
                <ol>
                    <li><strong>Seção 6 (cabeçalho):</strong> boxed, fundo <code>#0b1120</code>. Coluna <code>col-12</code> centralizada: Título <code>"Nossas Criações"</code> + Texto + Divider ciano.</li>
                    <li><strong>Seção 7 — coluna <code>col-12</code>:</strong> arraste o widget <strong>Galeria</strong> e configure:
                        <ul>
                            <li><strong>Images:</strong> adicione 6 imagens (ex.: <code>https://picsum.photos/seed/g1/600/400</code> … <code>g6</code>).</li>
                            <li><strong>Layout:</strong> <code>grid</code>, <strong>Columns:</strong> <code>3</code>, <strong>Gap:</strong> <code>16px</code>, <strong>Show Caption:</strong> <code>yes</code>, <strong>Enable Lightbox:</strong> <code>yes</code>.</li>
                            <li><strong>Border Radius:</strong> <code>12px</code>, objeto <code>cover</code>, altura <code>260px</code>.</li>
                        </ul>
                    </li>
                    <li><strong>Outra Seção 7 — coluna <code>col-12</code>:</strong> arraste o widget <strong>Carousel</strong>:
                        <ul>
                            <li><strong>Images:</strong> 3 imagens (picsum <code>c1</code> … <code>c3</code>, 800×500).</li>
                            <li><strong>Columns:</strong> <code>2</code>, <strong>Autoplay:</strong> <code>true</code>, <strong>Loop:</strong> <code>true</code>, <strong>Show Dots:</strong> <code>true</code>, <strong>Show Arrows:</strong> <code>true</code>, <strong>Speed:</strong> <code>3000</code>.</li>
                            <li><strong>Border Radius:</strong> <code>16px</code>, altura <code>300px</code>.</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.5 Imagens com Links, Lightbox e Vimeo (Seções 8 e 9)</h3>
                <ol>
                    <li><strong>Seção 8 (cabeçalho):</strong> boxed, fundo <code>#0b1120</code>. Título <code>"Imagens, Links e Vídeo"</code> + Texto + Divider ciano.</li>
                    <li><strong>Seção 9 — coluna esquerda <code>col-6</code>, widgets em ordem:</strong>
                        <ul>
                            <li><strong>Imagem</strong> (destaque): <strong>Lightbox:</strong> <code>yes</code>, caption <code>"Clique para abrir no lightbox"</code>, radius <code>16px</code>, hover <code>zoom</code>.</li>
                            <li><strong>Título</strong> H3 com <strong>Link:</strong> <code>https://example.com</code>, target <code>_blank</code> (título clicável).</li>
                            <li><strong>Texto</strong> descritivo em <code>#94a3b8</code>.</li>
                            <li><strong>Botão:</strong> gradiente <code>#06b6d4 → #6366f1</code>, <code>"Visitar Site"</code>, link <code>https://example.com</code>, target <code>_blank</code> — mostra como abrir conteúdo externo em nova aba.</li>
                        </ul>
                    </li>
                    <li><strong>Coluna direita <code>col-6</code>, widgets em ordem:</strong>
                        <ul>
                            <li><strong>Imagem</strong>: <strong>Link:</strong> <code>https://example.com</code>, <strong>Link Target:</strong> <code>_blank</code>, caption <code>"Abre em uma nova aba"</code>, hover <code>zoom</code>, radius <code>16px</code>. (Toda a imagem vira um link ao clicar.)</li>
                            <li><strong>Vídeo Vimeo:</strong> <strong>Vídeo Type:</strong> <code>vimeo</code>, <strong>Vídeo URL:</strong> <code>https://vimeo.com/76979871</code>, largura <code>100%</code>.</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.6 Tabs, Countdown e Accordion (Seções 10 e 11)</h3>
                <p>Widgets interativos: <strong>Tabs</strong> e <strong>Countdown</strong> à esquerda, <strong>Accordion</strong> à direita. A seção 10 é o cabeçalho.</p>
                <ol>
                    <li><strong>Seção 10 (cabeçalho):</strong> boxed, fundo <code>#0b1120</code>. Título <code>"Interatividade"</code> + Texto + Divider ciano.</li>
                    <li><strong>Seção 11 — coluna esquerda <code>col-6</code>:</strong>
                        <ul>
                            <li><strong>Tabs:</strong> adicione 3 abas — <code>"Descrição"</code>, <code>"Funcionalidades"</code>, <code>"Preços"</code> — cada uma com <strong>Title</strong> e <strong>Content</strong> (HTML). <strong>Orientation:</strong> <code>horizontal</code>, <strong>Active Tab:</strong> primeira, <strong>Tab Color ativa:</strong> <code>#22d3ee</code>, <strong>Border:</strong> <code>#1e293b</code>, padding <code>20px</code>.</li>
                            <li><strong>Countdown:</strong> <strong>Target Date:</strong> <code>2027-12-31</code> (datetime-local), rótulos <code>Dias</code>/<code>Horas</code>/<code>Min</code>/<code>Seg</code>, cor dos dígitos <code>#f0abfc</code>, fundo do bloco <code>#1e293b</code>, radius <code>12px</code>.</li>
                        </ul>
                    </li>
                    <li><strong>Seção 11 — coluna direita <code>col-6</code>:</strong>
                        <ul>
                            <li><strong>Accordion:</strong> 3 itens com <strong>Title</strong> e <strong>Content</strong>: <code>"É realmente gratuito?"</code>, <code>"Posso usar em múltiplos projetos?"</code>, <code>"Existe suporte para iniciantes?"</code>. Primeiro item <strong>Open:</strong> <code>true</code>. <strong>Active Color:</strong> <code>#22d3ee</code>, <strong>Border:</strong> <code>#1e293b</code>.</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.7 Tabelas de Preço (Seções 12 e 13)</h3>
                <p>A seção 12 é o cabeçalho; a seção 13 traz três planos em cards escuros, com o plano <strong>Professional</strong> em destaque roxo.</p>
                <ol>
                    <li><strong>Seção 12 (cabeçalho):</strong> boxed, fundo <code>#0b1120</code>. Título <code>"Preços"</code> + Texto + Divider ciano.</li>
                    <li><strong>Seção 13:</strong> três colunas <code>col-4</code> como cards (fundo <code>#111827</code>, radius <code>16px</code>, padding <code>32px</code>, <strong>border top</strong> <code>3px solid #22d3ee</code> no plano central). Widget <strong>Price Table</strong> em cada:
                        <ul>
                            <li><strong>Plano 1 (Starter):</strong> <strong>Plan Name:</strong> <code>"Starter"</code>, <strong>Price:</strong> <code>29</code>, moeda <code>R$</code>, período <code>/mês</code>, botão <code>"Começar"</code> link <code>#contato</code>.</li>
                            <li><strong>Plano 2 (Professional) — destaque:</strong> <strong>Price:</strong> <code>79</code>, fundo/borda do card em gradiente roxo <code>#a855f7</code>, badge <code>"Mais Popular"</code>.</li>
                            <li><strong>Plano 3 (Enterprise):</strong> <strong>Price:</strong> <code>199</code>, texto "Sob consulta para grandes equipes".</li>
                            <li>Em todos: use o repeater <strong>Features</strong> para listar itens (ex.: <code>"1 site"</code>, <code>"5 GB de armazenamento"</code>…) — cada feature entra como item com <strong>Text</strong> (e opcionalmente <strong>Icon</strong>/<strong>Included</strong>).</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.8 Depoimentos com Avatar (Seções 14 e 15)</h3>
                <p>A seção 14 é o cabeçalho; a seção 15 tem três cards com o widget <strong>Testimonial</strong> (mais completo que Título+Texto).</p>
                <ol>
                    <li><strong>Seção 14 (cabeçalho):</strong> boxed, fundo <code>#0b1120</code>. Título <code>"O Que Nossos Clientes Dizem"</code> + Texto + Divider ciano.</li>
                    <li><strong>Seção 15:</strong> três colunas <code>col-4</code> (fundo <code>#1e293b</code>, radius <code>16px</code>, padding <code>32px</code>). Widget <strong>Testimonial</strong> em cada:
                        <ul>
                            <li><strong>Content:</strong> o depoimento em texto cinza <code>#cbd5e1</code>.</li>
                            <li><strong>Avatar:</strong> imagem (ex.: <code>https://picsum.photos/seed/t1/150/150</code>), <strong>Avatar Shape:</strong> <code>circle</code>. (Avatar vem do campo <em>Avatar</em>, não de um widget Imagem separado.)</li>
                            <li><strong>Name:</strong> <code>"João Mendes"</code> em <code>#f8fafc</code>; <strong>Position:</strong> <code>"CEO, TechStart"</code>; <strong>Company:</strong> <code>"TechStart"</code>.</li>
                            <li><strong>Rating:</strong> <code>5</code> estrelas (cor amarela).</li>
                            <li>Repita com <code>"Fernanda Lima"</code> (InnovateLab) e <code>"Pedro Alves"</code> (WebPlus).</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.9 CTA Futurista com Lottie (Seção 16)</h3>
                <ol>
                    <li><strong>Seção:</strong> layout <code>full_width</code>, <strong>fundo em gradiente radial</strong> <code>#1e1b4b → #0b1120</code>, padding <code>100px</code>. Coluna <code>col-12</code> centralizada.</li>
                    <li><strong>Widgets em ordem:</strong>
                        <ul>
                            <li><strong>Lottie:</strong> <strong>Lottie URL:</strong> <code>https://lottie.host/lf20_g1al0rje.json</code> (ou outro arquivo Lottie), <strong>Loop:</strong> <code>true</code>, <strong>Autoplay:</strong> <code>true</code>, <strong>Speed:</strong> <code>1</code>, largura/altura <code>220px</code>. (Animações Lottie substituem GIFs com vectores leves.)</li>
                            <li><strong>Título:</strong> <code>"Pronto para Começar?"</code>, H2, tamanho <code>xxl</code>, cor <code>#f8fafc</code>, peso <code>800</code>.</li>
                            <li><strong>Texto:</strong> descrição em <code>#94a3b8</code>.</li>
                            <li><strong>Botão:</strong> <code>"Criar Página Grátis"</code>, <strong>Link:</strong> <code>#contato</code>, <strong>gradiente</strong> <code>#6366f1 → #ec4899</code>, radius <code>50px</code>.</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.10 Contato e Mapa (Seções 17 e 18)</h3>
                <ol>
                    <li><strong>Seção 17 (cabeçalho):</strong> boxed, fundo <code>#0b1120</code>, padding superior <code>100px</code>. Título <code>"Fale Conosco"</code> + Texto + Divider ciano (padding inferior <code>0px</code> nesta seção).</li>
                    <li><strong>Seção 18 — coluna esquerda <code>col-6</code> (card escuro):</strong> fundo <code>#111827</code>, radius <code>16px</code>, padding <code>32px</code>:
                        <ul>
                            <li><strong>Título:</strong> <code>"Formulário de Contato"</code>, H3, cor <code>#f1f5f9</code>.</li>
                            <li><strong>Formulário (Form):</strong> <strong>Form Name:</strong> <code>"Contato Showcase"</code>, campos Nome/Email (text/email, obrigatórios), Assunto (select), Mensagem (textarea, largura 100). <strong>Button Text:</strong> <code>"Enviar Mensagem"</code>, <strong>Button Color:</strong> <code>#a855f7</code>, texto <code>#ffffff</code>, botão <code>full</code>, <strong>Field Spacing:</strong> <code>14</code>, <strong>Field Radius:</strong> <code>8</code>, <strong>Success Message:</strong> <code>"Recebemos sua mensagem! Obrigado pelo contato."</code></li>
                        </ul>
                    </li>
                    <li><strong>Coluna direita <code>col-6</code>:</strong>
                        <ul>
                            <li><strong>Callout</strong> (type <code>info</code>): título <code>"Informações de Contato"</code>, conteúdo em <code>#cbd5e1</code> com Endereço, Telefone, Email e Horário (destaques em <code>#f1f5f9</code>), radius <code>12px</code>.</li>
                            <li><strong>Google Maps:</strong> <strong>Address:</strong> <code>"Av. Paulista, 1000, São Paulo, SP"</code>, latitude <code>-23.5632</code>, longitude <code>-46.6542</code>, <strong>Zoom:</strong> <code>15</code>, <strong>Height:</strong> <code>280px</code>, tipo <code>roadmap</code>, radius <code>16px</code>.</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2.11 Rodapé (Seção 19)</h3>
                <ol>
                    <li><strong>Seção:</strong> layout <code>full_width</code>, fundo <code>#020617</code> (quase preto), padding <code>60px</code>. Coluna <code>col-12</code> centralizada, widgets em ordem:
                        <ul>
                            <li><strong>Social Icons:</strong> 6 redes (facebook <code>#1877F2</code>, twitter <code>#1DA1F2</code>, linkedin <code>#0A66C2</code>, instagram <code>#E4405F</code>, youtube <code>#FF0000</code>, github <code>#94a3b8</code>), <strong>Columns:</strong> <code>6</code>, tamanho <code>40px</code>, gap <code>12</code>, centralizado.</li>
                            <li><strong>Divider:</strong> largura <code>100</code>%, espessura <code>1</code>, cor <code>#1e293b</code>.</li>
                            <li><strong>Texto:</strong> copyright <code>"© 2026 Page Builder. Todos os direitos reservados."</code> em <code>#64748b</code>, com links <code>Termos</code> e <code>Privacidade</code> em <code>#a5b4fc</code>.</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <div class="tip">
                    <strong>&#128161; Dica Final:</strong> O template Showcase Completo é o melhor exemplo de construção avançada de páginas. Ele demonstra: <strong>seções com fundo em gradiente</strong>, <strong>layout full_width e boxed</strong>, <strong>colunas com larguras variadas</strong>, uma <strong>paleta futurista dark</strong> com acentos neon, e uma enorme variedade de widgets — <strong>Título, Texto, Botão, Vídeo, Imagem, Galeria, Carousel, Contador, Progress Bar, Countdown, Tabs, Accordion, Tabela de Preço, Depoimento, Lottie, Formulário, Mapa e Redes Sociais</strong>. Para ver exatamente como cada elemento está configurado, crie uma página com o template e abra no editor — clique em cada elemento para inspecionar suas configurações no painel direito.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.3 Referência Rápida — Configurações-Chave dos Widgets</h3>
                <p>Estas são as configurações que mais geram dúvida e as que o template usa de verdade. Para inspecionar os valores exatos de cada elemento, basta abrir o editor de uma página criada com o template e clicar em cada elemento.</p>

                <table class="widget-table">
                    <tr><th>Widget</th><th>Configurações-chave</th></tr>
                    <tr><td><strong>Vídeo</strong></td><td><strong>Vídeo Type:</strong> <code>youtube</code> ou <code>vimeo</code>; <strong>Vídeo URL</strong> (o campo é <em>Vídeo URL</em>, não "URL"); <strong>Aspect Ratio:</strong> <code>16:9</code>. Sem <em>Vídeo URL</em> o vídeo não é renderizado.</td></tr>
                    <tr><td><strong>Imagem</strong></td><td><strong>URL</strong>, <strong>Caption</strong>, <strong>Enable Lightbox:</strong> <code>yes</code>; <strong>Link</strong> + <strong>Link Target:</strong> <code>_blank</code> (imagem clicável); <strong>Hover Animation:</strong> <code>zoom</code>.</td></tr>
                    <tr><td><strong>Galeria</strong></td><td>repeater <strong>Images</strong>; <strong>Layout:</strong> <code>grid</code>; <strong>Columns:</strong> <code>3</code>; <strong>Gap</strong>, <strong>Show Caption</strong>, <strong>Enable Lightbox</strong>.</td></tr>
                    <tr><td><strong>Carousel</strong></td><td>repeater <strong>Images</strong>; <strong>Columns</strong>, <strong>Autoplay:</strong> <code>true</code>, <strong>Loop</strong>, <strong>Show Dots</strong>, <strong>Show Arrows</strong>, <strong>Speed:</strong> <code>3000</code> ms.</td></tr>
                    <tr><td><strong>Contador</strong></td><td><strong>Number</strong>, <strong>Suffix</strong> (<code>+</code>, <code>%</code>), <strong>Title</strong>, <strong>Duration</strong>; cor do número na aba Style.</td></tr>
                    <tr><td><strong>Progress Bar</strong></td><td><strong>Title</strong>, <strong>Percentage</strong> (0–100), <strong>Bar Color</strong>, <strong>Track Color</strong>.</td></tr>
                    <tr><td><strong>Tabs</strong></td><td>repeater com <strong>Title</strong> e <strong>Content</strong>; <strong>Orientation:</strong> <code>horizontal</code>, <strong>Active Tab</strong>. (Os campos repeater são <em>Title</em>/<em>Content</em> — não "label".)</td></tr>
                    <tr><td><strong>Accordion</strong></td><td>repeater com <strong>Title</strong>/<strong>Content</strong>; <strong>Open by Default</strong>, <strong>Active Color</strong>, <strong>Border</strong>.</td></tr>
                    <tr><td><strong>Countdown</strong></td><td><strong>Target Date</strong> (datetime-local, ex.: <code>2027-12-31</code>); rótulos <strong>Dias/Horas/Min/Seg</strong>; cores dos dígitos e do bloco.</td></tr>
                    <tr><td><strong>Price Table</strong></td><td><strong>Plan Name</strong>, <strong>Price</strong>, <strong>Currency</strong>, <strong>Period</strong>; repeater <strong>Features</strong> (itens da lista); <strong>Button Text/Link</strong>.</td></tr>
                    <tr><td><strong>Testimonial</strong></td><td><strong>Content</strong>, <strong>Avatar</strong> (imagem + shape <code>circle</code>), <strong>Name</strong>, <strong>Position</strong>, <strong>Company</strong>, <strong>Rating</strong> (1–5).</td></tr>
                    <tr><td><strong>Lottie</strong></td><td><strong>Lottie URL</strong> (arquivo <code>.json</code>), <strong>Loop</strong>, <strong>Autoplay</strong>, <strong>Speed</strong>, largura/altura.</td></tr>
                    <tr><td><strong>Icon Box</strong></td><td><strong>Icon Class</strong> (Font Awesome, ex.: <code>fas fa-bolt</code>), cor e tamanho na aba Style.</td></tr>
                    <tr><td><strong>Callout</strong></td><td><strong>Type</strong>: <code>tip</code>, <code>info</code>, <code>success</code> ou <code>warning</code>; <strong>Title</strong>, <strong>Content</strong>, <strong>Show Icon</strong>.</td></tr>
                    <tr><td><strong>Form</strong></td><td><strong>Form Name</strong>; repeater <strong>Fields</strong> com <strong>Label</strong>, <strong>Placeholder</strong>, <strong>Type</strong> (text/email/select/textarea), <strong>Required</strong>; <strong>Button Text</strong>, <strong>Button Color</strong>, <strong>Success Message</strong>, <strong>Field Spacing</strong>, <strong>Field Radius</strong>.</td></tr>
                    <tr><td><strong>Google Maps</strong></td><td><strong>Address</strong>, <strong>Latitude</strong>, <strong>Longitude</strong>, <strong>Zoom</strong>, <strong>Height</strong>, <strong>Map Type</strong>.</td></tr>
                    <tr><td><strong>Social Icons</strong></td><td>repeater <strong>Icons</strong> (platform, url, cor); <strong>Columns</strong>, <strong>Icon Size</strong>, <strong>Gap</strong>, <strong>Alignment</strong>.</td></tr>
                    <tr><td><strong>Divider</strong></td><td><strong>Width</strong> (%), <strong>Thickness</strong> (px), <strong>Color</strong>, <strong>Space Before/After</strong>.</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Como o fundo em gradiente é aplicado:</strong> Seções e botões usam <strong>Background Type: gradient</strong> (com <strong>Cor 1</strong>, <strong>Cor 2</strong> e ângulo) ou <strong>radial gradient</strong> no Content Tab — o gradiente é armazenado nas configurações do elemento e renderizado como CSS. Para espaçamentos use sempre a aba <strong>Advanced</strong> (Padding &amp; Margin); para bordas, sombras e tipografia, a aba <strong>Style</strong>.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Navigator — Gerenciando Elementos</h3>
                <ol>
                    <li><strong>Abrir Navigator:</strong> clique no botão flutuante ☰ (canto inferior direito) → painel com a árvore completa de elementos.</li>
                    <li><strong>Reordenar (SortableJS):</strong> arraste um item para cima/baixo na árvore → a ordem muda no canvas.</li>
                    <li><strong>Mover entre colunas:</strong> arraste um widget de uma coluna para outra.</li>
                    <li><strong>Renomear:</strong> duplo clique no nome → digite o novo nome → Enter.</li>
                    <li><strong>Context menu:</strong> clique direito → Duplicate, Delete, Copy, Paste, Move Up/Down.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Paleta de Cores Utilizada</h3>
                <table class="widget-table">
                    <tr><th>Cor</th><th>Código</th><th>Uso</th></tr>
                    <tr><td style="background:#020617;color:#fff;padding:4px 8px;border-radius:4px">Quase preto</td><td><code>#020617</code></td><td>Fundo do rodapé / cor 1 do gradiente do Hero</td></tr>
                    <tr><td style="background:#0b1120;color:#fff;padding:4px 8px;border-radius:4px">Preto azulado</td><td><code>#0b1120</code></td><td>Fundo principal das seções</td></tr>
                    <tr><td style="background:#111827;color:#fff;padding:4px 8px;border-radius:4px">Card escuro</td><td><code>#111827</code></td><td>Cards de recursos e do formulário</td></tr>
                    <tr><td style="background:#1e293b;color:#fff;padding:4px 8px;border-radius:4px">Cinza escuro</td><td><code>#1e293b</code></td><td>Bordas, tracks de progresso, cards de depoimentos</td></tr>
                    <tr><td style="background:#312e81;color:#fff;padding:4px 8px;border-radius:4px">Índigo escuro</td><td><code>#312e81</code></td><td>Cor 2 do gradiente do Hero</td></tr>
                    <tr><td style="background:#1e1b4b;color:#fff;padding:4px 8px;border-radius:4px">Púrpura profundo</td><td><code>#1e1b4b</code></td><td>Gradiente radial do CTA</td></tr>
                    <tr><td style="background:#22d3ee;color:#01303a;padding:4px 8px;border-radius:4px">Ciano neon</td><td><code>#22d3ee</code></td><td>Dividers, abas ativas, contador, progress bar</td></tr>
                    <tr><td style="background:#a78bfa;color:#fff;padding:4px 8px;border-radius:4px">Violeta neon</td><td><code>#a78bfa</code></td><td>Contador "98%", ícones de recursos</td></tr>
                    <tr><td style="background:#34d399;color:#fff;padding:4px 8px;border-radius:4px">Verde neon</td><td><code>#34d399</code></td><td>Contador "50+", ícones</td></tr>
                    <tr><td style="background:#f0abfc;color:#fff;padding:4px 8px;border-radius:4px">Rosa neon</td><td><code>#f0abfc</code></td><td>Contador "12+", countdown</td></tr>
                    <tr><td style="background:#a855f7;color:#fff;padding:4px 8px;border-radius:4px">Roxo</td><td><code>#a855f7</code></td><td>Plano em destaque, botão do formulário</td></tr>
                    <tr><td style="background:#6366f1;color:#fff;padding:4px 8px;border-radius:4px">Índigo</td><td><code>#6366f1</code></td><td>Gradientes de botões (<code>#6366f1 → #a855f7</code> e <code>#6366f1 → #ec4899</code>)</td></tr>
                    <tr><td style="background:#94a3b8;color:#fff;padding:4px 8px;border-radius:4px">Cinza médio</td><td><code>#94a3b8</code></td><td>Textos secundários e descrições</td></tr>
                    <tr><td style="background:#cbd5e1;color:#0f172a;padding:4px 8px;border-radius:4px">Cinza claro</td><td><code>#cbd5e1</code></td><td>Textos de depoimentos e contato</td></tr>
                    <tr><td style="background:#f8fafc;color:#0f172a;padding:4px 8px;border-radius:4px;border:1px solid #ddd">Branco azulado</td><td><code>#f8fafc</code></td><td>Títulos e textos principais</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.4 Templates Básicos para Temas (Theme Builder)</h3>
                <p>Além das páginas, o <strong>Theme Builder</strong> ganhou <strong>starter templates</strong>: o formulário de criação de Theme Template agora tem o campo <strong>"Starter Template"</strong>, que pre-preenche o editor com um conteúdo básico pronto para cada tipo de tema. Ele só é aplicado quando o tema usa uma <strong>página nova</strong> (se você associar uma página existente, o conteúdo dela não é alterado).</p>
                <table class="widget-table">
                    <tr><th>Starter</th><th>Nome</th><th>Conteúdo criado no editor</th></tr>
                    <tr><td><code>header</code></td><td>Header Básico</td><td>Seção <code>full_width</code> fundo <code>#0b1120</code> com 3 colunas: logo (ícone + marca), navegação em texto e botão CTA em gradiente <code>#6366f1 → #a855f7</code></td></tr>
                    <tr><td><code>footer</code></td><td>Rodapé Básico</td><td>Seção <code>full_width</code> fundo <code>#020617</code> com 3 colunas: marca/copyright, links de navegação e <strong>Social Icons</strong> (4 redes)</td></tr>
                    <tr><td><code>single</code></td><td>Artigo Simples</td><td>Imagem de destaque, título H1, metadados, divider e texto do artigo + callout <strong>info</strong> "Sobre o Autor"</td></tr>
                    <tr><td><code>archive</code></td><td>Listagem de Posts</td><td>Cabeçalho (H1 + divider ciano) e grade de <strong>3 cards</strong> col-4 com imagem, título, texto e botão "Ler Mais"</td></tr>
                    <tr><td><code>404</code></td><td>Página 404</td><td>Seção centralizada com "404" gigante em <code>#22d3ee</code> (text shadow neon), mensagem e botão gradiente "Voltar para a Home"</td></tr>
                    <tr><td><code>search_results</code></td><td>Resultados de Busca</td><td>Cabeçalho "Resultados de Busca" + divider e lista de <strong>3 callouts</strong> info como resultados (duplique no Navigator para mais)</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Ao escolher o <strong>Type</strong> do tema, o Starter Template correspondente é <strong>selecionado automaticamente</strong> no formulário. Você pode trocá-lo ou deixar em "Blank" para começar do zero. Depois de criar, basta abrir o editor e adaptar o conteúdo.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.5 Templates Básicos para Popups</h3>
                <p>O formulário de criação de Popup também ganhou o campo <strong>"Starter Template"</strong>. Além do conteúdo, o starter já define as <strong>configurações padrão do popup</strong> (largeura, posição, overlay e botão fechar), poupando configuração manual.</p>
                <table class="widget-table">
                    <tr><th>Starter</th><th>Nome</th><th>Conteúdo criado no editor</th><th>Popup Settings</th></tr>
                    <tr><td><code>popup-newsletter</code></td><td>Newsletter</td><td>Ícone de envelope, título "Fique por dentro!", texto e <strong>Formulário</strong> com campo de e-mail + botão "Assinar Grátis" (<code>#6366f1</code>)</td><td>Largura <code>480px</code>, posição <code>center</code>, overlay <code>sim</code>, botão fechar <code>sim</code></td></tr>
                    <tr><td><code>popup-announcement</code></td><td>Anúncio</td><td>Callout <strong>warning</strong> "Novidade!", título, texto descritivo e botão gradiente <code>#6366f1 → #ec4899</code> "Saiba Mais"</td><td>Largura <code>480px</code>, posição <code>center</code>, overlay <code>sim</code>, botão fechar <code>sim</code></td></tr>
                </table>
                <p>Depois de criar, configure os <strong>Gatilhos</strong> (quando o popup aparece) e as <strong>Condições</strong> (em quais páginas) — o popup só aparece em páginas publicadas. Para um popup que surge após 3 segundos, adicione o gatilho <code>After Timer</code> com valor <code>3</code>.</p>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para deixar o popup com fundo branco ou escuro, troque a <strong>cor da seção</strong> no editor (newsletter usa <code>#ffffff</code>; anúncio usa <code>#0b1120</code>). Os dois temas seguem a mesma paleta neon da seção 16.3.
                </div>
            </div>
        </section>

        {{-- THEME BUILDER --}}
        <section id="theme-builder" class="step">
            <h2>17. Theme Builder</h2>
            <div class="step-body">
                <p>O <strong>Theme Builder</strong> permite criar templates de tema (cabeçalho, rodapé, single, archive, página 404, resultados de busca) que são renderizados ao redor do conteúdo das páginas. Diferente de páginas normais, os templates de tema têm <strong>condições de exibição</strong> que determinam em quais páginas cada template aparece.</p>

                <div class="tip">
                    <strong>&#128161; Conceito:</strong> Theme Templates funcionam como <strong>layouts mestres</strong>. Você cria um header uma vez, define em quais páginas ele aparece, e o sistema o renderiza automaticamente ao redor do conteúdo de cada página.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tipos de Template</h3>
                <table class="widget-table">
                    <tr><th>Tipo</th><th>Onde aparece</th><th>Exemplo de uso</th></tr>
                    <tr><td><code>header</code></td><td>No topo da página</td><td>Logo, menu de navegação, barra de busca</td></tr>
                    <tr><td><code>footer</code></td><td>No final da página</td><td>Links de rodapé, copyright, redes sociais</td></tr>
                    <tr><td><code>single</code></td><td>Em páginas individuais</td><td>Layout padrão de conteúdo/post</td></tr>
                    <tr><td><code>archive</code></td><td>Em listagens e arquivos</td><td>Lista de posts, categorias</td></tr>
                    <tr><td><code>404</code></td><td>Em páginas não encontradas</td><td>Página de erro 404 personalizada</td></tr>
                    <tr><td><code>search_results</code></td><td>Em resultados de busca</td><td>Layout da página de pesquisa</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo a Passo — Criar um Header</h3>
                <ol>
                    <li><strong>Acesse a listagem:</strong> Vá em <strong>"Theme Templates"</strong> no menu lateral (<code>/page-builder/themes</code>).</li>
                    <li><strong>Clique em "Criar Template":</strong> Preencha:
                        <ul>
                            <li><strong>Título:</strong> <code>"Header Principal"</code></li>
                            <li><strong>Tipo:</strong> <code>header</code></li>
                            <li><strong>Status:</strong> <code>draft</code> (você publicará depois)</li>
                        </ul>
                    </li>
                    <li><strong>Salve</strong> o template. Você será redirecionado à listagem.</li>
                    <li><strong>Abra o editor visual</strong> clicando em <strong>"Editor"</strong> no template criado.</li>
                    <li><strong>Construa o header</strong> dentro do editor:
                        <ul>
                            <li>Arraste uma <strong>Seção</strong> com layout <code>full_width</code> e fundo <code>#1e293b</code> (cinza escuro).</li>
                            <li>Arraste uma <strong>Coluna</strong> com largura <code>col-6</code> (metade esquerda).</li>
                            <li>Dentro dela, arraste um widget <strong>Ícone</strong> ou <strong>Texto</strong> com o nome da sua marca.</li>
                            <li>Arraste outra <strong>Coluna</strong> com largura <code>col-6</code> (metade direita).</li>
                            <li>Dentro dela, arraste botões ou texto para links de navegação.</li>
                            <li>Ajuste padding vertical da seção para <code>16px</code> (aba <strong>Advanced</strong>).</li>
                        </ul>
                    </li>
                    <li><strong>Salve</strong> o template no editor (botão Salvar).</li>
                    <li><strong>Publique</strong> o template clicando em <strong>"Publicar"</strong> na listagem ou no editor.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo a Passo — Criar um Footer</h3>
                <ol>
                    <li>Repita os passos 1–3 acima com:
                        <ul>
                            <li><strong>Título:</strong> <code>"Footer Principal"</code></li>
                            <li><strong>Tipo:</strong> <code>footer</code></li>
                        </ul>
                    </li>
                    <li><strong>No editor:</strong>
                        <ul>
                            <li>Arraste uma <strong>Seção</strong> com layout <code>boxed</code> e fundo <code>#0f172a</code> (quase preto).</li>
                            <li>Arraste <strong>3 Colunas</strong> com largura <code>col-4</code> cada.</li>
                            <li>Na primeira coluna: widget <strong>Texto</strong> com informações de copyright.</li>
                            <li>Na segunda coluna: widget <strong>Título</strong> + links de navegação.</li>
                            <li>Na terceira coluna: widget <strong>Social Icons</strong> com redes sociais.</li>
                            <li>Padding superior/inferior: <code>60px</code> (aba <strong>Advanced</strong>).</li>
                        </ul>
                    </li>
                    <li><strong>Salve e publique</strong>.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Condições de Exibição</h3>
                <p>Após criar e publicar, defina <strong>onde</strong> o template aparece. Acesse o template na listagem e clique em <strong>"Condições"</strong>.</p>
                <table class="widget-table">
                    <tr><th>Condição</th><th>Comportamento</th></tr>
                    <tr><td><code>Entire Site</code></td><td>Aparece em todas as páginas (ideal para header/footer globais)</td></tr>
                    <tr><td><code>Homepage</code></td><td>Aparece apenas na página inicial</td></tr>
                    <tr><td><code>All Pages</code></td><td>Aparece em todas as páginas exceto a home</td></tr>
                    <tr><td><code>All Singular</code></td><td>Aparece em páginas individuais</td></tr>
                    <tr><td><code>Specific Page</code></td><td>Aparece em uma página específica (selecione no dropdown)</td></tr>
                </table>
                <p>Exemplo de configuração:</p>
                <ul>
                    <li><strong>Header Principal</strong> → condição <code>Entire Site</code> (aparece em todas as páginas)</li>
                    <li><strong>Footer Principal</strong> → condição <code>Entire Site</code> (aparece em todas as páginas)</li>
                </ul>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> Você pode criar múltiplos headers com condições diferentes. Ex: um header para a homepage (com menu completo) e outro para páginas internas (com menu simplificado). O primeiro template que satisfizer a condição é usado.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Visualizar o Resultado</h3>
                <p>Para ver os templates aplicados:</p>
                <ol>
                    <li>Vá até a <strong>lista de páginas</strong> (<code>/page-builder/pages</code>).</li>
                    <li>Na coluna <strong>"Ações"</strong> de qualquer página, clique em <strong>"Visualizar"</strong> (ícone de olho).</li>
                    <li>O header e footer serão renderizados automaticamente ao redor do conteúdo da página.</li>
                </ol>
                <p>Você também pode acessar diretamente a URL pública da página (<code>http://localhost:8000/p/{slug}</code>) para ver o tema completo aplicado.</p>

                <div class="warning">
                    <strong>&#9888;&#65039; Importante:</strong> O Theme Builder só funciona em páginas com <strong>status "published"</strong>. Páginas em draft usam o editor visual normal sem templates de tema.
                </div>
            </div>
        </section>

        {{-- POPUPS --}}
        <section id="popups" class="step">
            <h2>18. Popups</h2>
            <div class="step-body">
                <p>O sistema de <strong>Popups</strong> permite criar janelas modais que aparecem sobre o conteúdo da página. Cada popup tem <strong>gatilhos</strong> (quando aparece) e <strong>condições</strong> (em quais páginas aparece).</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo a Passo — Criar um Popup</h3>
                <ol>
                    <li><strong>Acesse Popups:</strong> Vá em <strong>"Popups"</strong> no menu lateral (<code>/page-builder/popups</code>).</li>
                    <li><strong>Clique em "Criar Popup":</strong> Preencha:
                        <ul>
                            <li><strong>Título:</strong> <code>"Newsletter"</code></li>
                            <li><strong>Status:</strong> <code>draft</code></li>
                        </ul>
                    </li>
                    <li><strong>Salve</strong> o popup. Você será redirecionado à listagem.</li>
                    <li><strong>Abra o editor visual</strong> clicando em <strong>"Editor"</strong> no popup criado.</li>
                    <li><strong>Construa o conteúdo</strong> do popup dentro do editor:
                        <ul>
                            <li>Arraste uma <strong>Seção</strong> com fundo <code>#ffffff</code> e padding <code>40px</code>.</li>
                            <li>Arraste uma <strong>Coluna</strong> com largura <code>col-12</code>.</li>
                            <li>Dentro da coluna, arraste:
                                <ul>
                                    <li><strong>Título:</strong> <code>"Receba Nossas Novidades"</code>, tag <code>H2</code>, centralizado.</li>
                                    <li><strong>Texto:</strong> <code>"Inscreva-se para receber dicas e ofertas exclusivas."</code>, centralizado.</li>
                                    <li><strong>Formulário</strong> (ou botão) para capturar email.</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><strong>Salve</strong> o popup no editor.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Configurar Gatilhos (Triggers)</h3>
                <p>Na listagem de popups, clique em <strong>"Gatilhos"</strong> no popup desejado. Os gatilhos definem <strong>quando</strong> o popup aparece:</p>
                <table class="widget-table">
                    <tr><th>Gatilho</th><th>Comportamento</th></tr>
                    <tr><td><code>On Load</code></td><td>Aparece assim que a página carrega</td></tr>
                    <tr><td><code>On Timer</code></td><td>Aparece após X segundos (configurável, ex: 5s)</td></tr>
                    <tr><td><code>On Scroll</code></td><td>Aparece quando o usuário rola X% da página</td></tr>
                    <tr><td><code>On Exit Intent</code></td><td>Aparece quando o mouse sai da janela (intenção de sair)</td></tr>
                    <tr><td><code>On Click</code></td><td>Aparece quando o usuário clica em um seletor CSS específico</td></tr>
                </table>
                <p>Exemplo — popup que aparece 5 segundos após carregar a página:</p>
                <ol>
                    <li>Clique em <strong>"Adicionar Gatilho"</strong>.</li>
                    <li>Selecione <strong>Tipo:</strong> <code>On Timer</code>.</li>
                    <li>Defina <strong>Valor:</strong> <code>5</code> (segundos).</li>
                    <li><strong>Salve</strong> os gatilhos.</li>
                </ol>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> Você pode combinar múltiplos gatilhos. Ex: <code>On Timer</code> (3s) + <code>On Exit Intent</code> — o popup aparece após 3s ou quando o usuário tenta sair, o que acontecer primeiro. O popup também respeita o limite de <strong>uma vez por sessão</strong> (não reaparece até recarregar a página).
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Configurar Condições</h3>
                <p>Na listagem, clique em <strong>"Condições"</strong> para definir em <strong>quais páginas</strong> o popup aparece:</p>
                <table class="widget-table">
                    <tr><th>Condição</th><th>Comportamento</th></tr>
                    <tr><td><code>All Pages</code></td><td>Aparece em todas as páginas</td></tr>
                    <tr><td><code>Homepage</code></td><td>Aparece apenas na página inicial</td></tr>
                    <tr><td><code>Specific Page</code></td><td>Aparece em uma página específica (selecione no dropdown)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Configurações do Popup</h3>
                <p>No formulário de edição do popup, você pode ajustar:</p>
                <table class="widget-table">
                    <tr><th>Configuração</th><th>Opções</th><th>Descrição</th></tr>
                    <tr><td><strong>Largura</strong></td><td><code>300px</code> a <code>1200px</code></td><td>Largura máxima do popup (ex: <code>500px</code>)</td></tr>
                    <tr><td><strong>Posição</strong></td><td><code>center</code>, <code>top_left</code>, <code>top_right</code>, <code>bottom_left</code>, <code>bottom_right</code></td><td>Onde o popup aparece na tela</td></tr>
                    <tr><td><strong>Overlay</strong></td><td>Sim / Não</td><td>Se o fundo escuro atrás do popup aparece</td></tr>
                    <tr><td><strong>Botão Fechar</strong></td><td>Sim / Não</td><td>Exibe o botão X para fechar o popup</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Publicar e Testar</h3>
                <ol>
                    <li>Na listagem de popups, clique em <strong>"Publicar"</strong> no popup.</li>
                    <li>Acesse a página onde o popup deve aparecer (precisa ser uma página publicada).</li>
                    <li>O popup será exibido conforme os gatilhos configurados.</li>
                    <li>Para testar sem publicar, abra a página no editor e clique em <strong>"Visualizar"</strong>.</li>
                </ol>

                <div class="warning">
                    <strong>&#9888;&#65039; Atenção:</strong> Popups só funcionam em páginas com <strong>status "published"</strong>. Popups em draft não são exibidos. O popup é renderizado junto com a página, então você precisa salvar a página para que as alterações no popup apareçam.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Exemplo Completo — Popup de Newsletter</h3>
                <p>Siga estes passos para criar um popup funcional do zero:</p>
                <ol>
                    <li>Crie um popup com título <code>"Newsletter"</code>.</li>
                    <li>No editor, adicione: Seção (fundo branco) → Coluna → Título (<code>"Fique por Dentro"</code>, H2) → Texto (<code>"Receba novidades toda semana"</code>) → Formulário (campos: Nome, Email).</li>
                    <li>Salve o popup.</li>
                    <li>Configure os gatilhos: <code>On Timer</code> (3 segundos).</li>
                    <li>Configure as condições: <code>All Pages</code>.</li>
                    <li>Publique o popup.</li>
                    <li>Acesse qualquer página publicada — o popup aparecerá após 3 segundos.</li>
                </ol>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> Popups são úteis para: captura de leads (newsletter), avisos importantes (manutenção), promoções (cupom de desconto), e cookies (aviso de cookies). Cada popup pode ter gatilhos e condições diferentes.
                </div>
            </div>
        </section>

        {{-- PROJECTS --}}
        <section id="projects" class="step">
            <h2>18b. Projetos (Organização)</h2>
            <div class="step-body">
                <p>Os <strong>Projetos</strong> permitem organizar suas <strong>páginas</strong>, <strong>templates de tema</strong> e <strong>popups</strong> em grupos separados. Cada projeto é <strong>privado por usuário</strong> — somente o dono vê e gerencia seus projetos. Widgets globais e fontes globais continuam sendo <strong>por usuário</strong> (compartilhados entre todos os seus projetos).</p>

                <div class="tip">
                    <strong>&#128161; Projeto padrão:</strong> Todo usuário possui um projeto padrão chamado <strong>"Meus Projetos"</strong> (marcado com <code>is_default</code>). Ele é criado automaticamente e <strong>não pode ser excluído</strong>. Se um projeto for removido, os itens que estavam nele são reatribuídos ao projeto padrão.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Acessar os Projetos</h3>
                <ol>
                    <li>No menu do Page Builder, clique em <strong>"Projetos"</strong>.</li>
                    <li>A tela lista seus projetos, mostrando a contagem de páginas, temas e popups de cada um.</li>
                    <li>O projeto padrão aparece destacado e não exibe a opção de exclusão.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Criar um Projeto</h3>
                <ol>
                    <li>Na tela de Projetos, clique em <strong>"Novo Projeto"</strong>.</li>
                    <li>Informe o <strong>Nome</strong> (o slug é gerado automaticamente e é único por usuário).</li>
                    <li>Opcionalmente, adicione uma <strong>Descrição</strong>.</li>
                    <li>Clique em <strong>Salvar</strong>.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Filtrar Itens por Projeto</h3>
                <p>As listagens de <strong>Páginas</strong>, <strong>Temas</strong> e <strong>Popups</strong> possuem um <strong>seletor de projeto</strong> no cabeçalho. Ao escolher um projeto, a listagem é filtrada via <code>?project={id}</code>. Ao criar um item com um projeto selecionado, ele já é vinculado a esse projeto.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Mover Itens entre Projetos</h3>
                <p>É possível mover páginas, temas ou popups para outro projeto. O endpoint <code>POST /page-builder/projects/{id}/move</code> recebe o <code>type</code> (<code>page</code>, <code>theme_template</code> ou <code>popup</code>) e um array de <code>ids</code>, atualizando o <code>project_id</code> dos itens que pertencem ao usuário dono do projeto.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Excluir um Projeto</h3>
                <ol>
                    <li>Na tela de Projetos, clique no menu de ações do projeto (que não seja o padrão).</li>
                    <li>Clique em <strong>"Excluir"</strong>.</li>
                    <li>Os itens do projeto são reatribuídos automaticamente ao projeto padrão — nada é perdido.</li>
                </ol>

                <div class="warning">
                    <strong>&#9888;&#65039; Atenção:</strong> O projeto padrão "Meus Projetos" não pode ser excluído (a API responde <code>422</code>). Mover itens entre projetos respeita o dono — você só move itens que pertencem a você.
                </div>
            </div>
        </section>

        {{-- PROJECT STRUCTURE --}}
        <section id="architecture" class="step">
            <h2>19. Arquitetura do Projeto</h2>
            <div class="step-body">
                <p>O projeto segue o padrão MVC do Laravel com uma camada adicional de <strong>Serviços</strong> e <strong>Widgets</strong> para isolar a lógica do page builder.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Diagrama de Arquitetura</h3>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.78rem;line-height:1.5;overflow-x:auto;font-family:monospace">
<pre style="margin:0">┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (8 módulos ES)                   │
│  resources/js/editor/ ← editor.blade.php (7 partials)            │
│  state, utils, canvas, history, navigator, dragdrop (SortableJS), html-import, css │
├─────────────────────────────────────────────────────────────────┤
│                    CONTROLLERS (14 classes)                       │
│  PageController        — CRUD páginas, templates, globals       │
│  ElementController     — CRUD elementos, controles, upload      │
│  RevisionController    — revisões, diff, restore, auto-save     │
│  FormController        — processamento de formulários           │
│  CollaborationController — presença, bloqueio, cursores         │
│  HtmlImportController  — importação de HTML via URL ou textarea │
│  ThemeTemplateController — CRUD templates de tema               │
│  CustomFontController  — upload e gestão de fontes              │
│  GlobalWidgetController — CRUD widgets globais                  │
│  PopupController       — CRUD popups, triggers, condições       │
│  DynamicTagController  — lista de dynamic tags disponíveis      │
│  FindReplaceController — busca e substituição em páginas        │
│  PageApiController     — API REST (Sanctum) para páginas        │
│  ElementApiController  — API REST (Sanctum) para elementos      │
├─────────────────────────────────────────────────────────────────┤
│                     SERVICES (10 classes)                         │
│  PageBuilderService   — orquestra tudo (create, update, render) │
│  ElementManager       — CRUD elementos, árvore, reordenação     │
│  WidgetManager        — registra e gerencia widgets dinamicamente│
│  Renderer             — renderiza árvore de elementos → HTML    │
│  TemplateManager      — 7 templates predefinidos                │
│  CollaborationService — presença, bloqueio, cursores (Cache)    │
│  HtmlImportService    — converte HTML externo em widgets        │
│  DynamicTagService    — substitui @{{ tags }} por valores        │
│  HtmlSanitizer        — sanitiza CSS, JS e URLs contra XSS     │
│  PopupService         — gerencia popups publicados com cache    │
├─────────────────────────────────────────────────────────────────┤
│                    REPOSITORIES (2 interfaces + 2 impl)           │
│  PageRepository      — PageRepositoryInterface                  │
│  ElementRepository   — ElementRepositoryInterface               │
├─────────────────────────────────────────────────────────────────┤
│                    API RESOURCES (3 classes)                      │
│  PageResource        — transforma Page para API REST            │
│  PageCollection      — coleção paginada de páginas              │
│  ElementResource     — transforma Element com árvore recursiva  │
├─────────────────────────────────────────────────────────────────┤
│                     JOBS (4 classes)                              │
│  ImportHtmlJob        — importa HTML em background via Queue    │
│  ClearPageCacheJob    — limpa cache ($page->touch() + tags)     │
│  AutoSaveRevisionJob  — cria revisão automática a cada 60s     │
│  ProcessFormSubmissionJob — notificações de formulário async    │
├─────────────────────────────────────────────────────────────────┤
│                     WIDGETS (30 classes)                          │
│  BaseWidget (abstract) → Heading, Text, Image, Button           │
│                        → Section, Column, InnerSection           │
│                        → Callout, Table, Math, Vídeo             │
│                        → Divider, Spacer, Icon, Gallery          │
│                        → Form, Tabs, Accordion                   │
│                        → Counter, ProgressBar, SocialIcons       │
│                        → IconBox, ImageBox, Testimonial          │
│                        → PriceTable, Countdown, GoogleMaps       │
│                        → Carousel, MegaMenu, Lottie               │
├─────────────────────────────────────────────────────────────────┤
│                     DATABASE (SQLite)                             │
│  pages → elements (árvore via parent_id) → revisions            │
│  form_submissions → custom_fonts → global_widgets               │
│  popups → theme_templates → personal_access_tokens (Sanctum)    │
└─────────────────────────────────────────────────────────────────┘</pre>
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Árvore de Diretórios do Page Builder</h3>
                <table class="widget-table">
                    <tr><th>Caminho</th><th>Arquivos</th><th>Descrição</th></tr>
                    <tr><td><code>app/Http/Controllers/PageBuilder/</code></td><td>12</td><td>PageController, ElementController, RevisionController, FormController, CollaborationController, HtmlImportController, ThemeTemplateController, CustomFontController, GlobalWidgetController, PopupController, DynamicTagController, FindReplaceController</td></tr>
                    <tr><td><code>app/Http/Controllers/Api/</code></td><td>2</td><td>PageApiController, ElementApiController (REST API)</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Core/</code></td><td>8</td><td>PageBuilderService, ElementManager, WidgetManager, Renderer, TemplateManager, CollaborationService, HtmlImportService, HtmlSanitizer</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Widgets/</code></td><td>31</td><td>BaseWidget + 30 widgets concretos</td></tr>
                    <tr><td><code>app/Services/PageBuilder/DynamicTags/</code></td><td>1+</td><td>DynamicTagService</td></tr>
                    <tr><td><code>app/Services/PageBuilder/GlobalWidget/</code></td><td>1+</td><td>GlobalWidgetService</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Popup/</code></td><td>1+</td><td>PopupService</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Theme/</code></td><td>1+</td><td>ThemeService</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Security/</code></td><td>1+</td><td>HtmlSanitizer</td></tr>
                    <tr><td><code>app/Contracts/Repositories/</code></td><td>2</td><td>PageRepositoryInterface, ElementRepositoryInterface</td></tr>
                    <tr><td><code>app/Repositories/</code></td><td>2</td><td>PageRepository, ElementRepository</td></tr>
                    <tr><td><code>app/Http/Resources/</code></td><td>3</td><td>PageResource, PageCollection, ElementResource</td></tr>
                    <tr><td><code>app/Jobs/</code></td><td>4</td><td>ImportHtmlJob, ClearPageCacheJob, AutoSaveRevisionJob, ProcessFormSubmissionJob</td></tr>
                    <tr><td><code>app/Providers/</code></td><td>PageBuilderServiceProvider</td><td>Registra singletons, rotas, views e config</td></tr>
                    <tr><td><code>config/page-builder.php</code></td><td>1</td><td>Configuração: lista de widgets habilitados, config de cache</td></tr>
                    <tr><td><code>database/migrations/</code></td><td>13</td><td>users, cache, jobs, pages, elements, revisions, form_submissions, add_role_to_users, personal_access_tokens, custom_fonts, global_widgets, popups, theme_templates</td></tr>
                    <tr><td><code>routes/page-builder.php</code></td><td>1</td><td>100+ rotas (pages, elements, revisions, themes, popups, global-widgets, fonts, collab, html-import, find-replace, dynamic-tags)</td></tr>
                    <tr><td><code>routes/api.php</code></td><td>1</td><td>API REST com Sanctum (tokens, pages, elements)</td></tr>
                    <tr><td><code>resources/views/</code></td><td>22</td><td>Blade views (editor + 7 partials, pages, auth, tutorial, popups, theme-templates, layouts)</td></tr>
                    <tr><td><code>resources/js/editor/</code></td><td>8</td><td>Módulos ES: state, utils, canvas, history, navigator, dragdrop (SortableJS), html-import, css</td></tr>
                    <tr><td><code>tests/Unit/</code></td><td>4</td><td>BaseWidgetTest, PageBuilderServiceTest, TemplateManagerTest, ExampleTest</td></tr>
                    <tr><td><code>tests/Feature/</code></td><td>4</td><td>PageControllerTest, ElementControllerTest, RevisionControllerTest, ExampleTest</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Injeção de Dependências (Providers)</h3>
                <p>O <code>PageBuilderServiceProvider</code> registra todos os serviços como <strong>singletons</strong> (uma instância compartilhada):</p>
                <ul>
                    <li><code>WidgetManager</code> — lê <code>config/page-builder.php</code> e registra os 30 widgets</li>
                    <li><code>ElementManager</code> — CRUD elementos, buildTree() para construir árvore a partir do banco</li>
                    <li><code>Renderer</code> — recebe WidgetManager, renderiza árvore de elementos → HTML</li>
                    <li><code>PageBuilderService</code> — orquestra os três serviços acima</li>
                    <li><code>TemplateManager</code> — singleton independente, sem dependências</li>
                    <li><code>CollaborationService</code> — usa Cache para presença e bloqueio de elementos</li>
                </ul>
                <p>O <code>AppServiceProvider</code> registra os repositórios com binding de interface para implementação:</p>
                <ul>
                    <li><code>PageRepositoryInterface → PageRepository</code></li>
                    <li><code>ElementRepositoryInterface → ElementRepository</code></li>
                </ul>
                <p>O <code>bootstrap/app.php</code> registra middlewares customizados:</p>
                <ul>
                    <li><code>InjectThemeTemplates</code> — middleware global</li>
                    <li><code>ContentSecurityPolicy</code> — middleware do grupo <code>web</code></li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Fluxo de Dados Principal</h3>
                <ol>
                    <li><strong>Criar página</strong> → <code>PageController::store()</code> → <code>PageBuilderService::createPage()</code> → insere na tabela <code>pages</code></li>
                    <li><strong>Abrir editor</strong> → <code>PageController::edit()</code> → renderiza <code>editor.blade.php</code> → JS busca <code>GET /elements</code></li>
                    <li><strong>Arrastar widget</strong> → JS envia <code>POST /elements</code> → <code>ElementController::store()</code> → insere na tabela <code>elements</code></li>
                    <li><strong>Editar configuração</strong> → JS envia <code>PUT /elements/{id}/settings</code> → atualiza JSON <code>settings</code></li>
                    <li><strong>Salvar</strong> → <code>PUT /pages/{id}</code> → <code>PageBuilderService::updatePage()</code> → cria <code>Revision</code></li>
                    <li><strong>Renderizar</strong> → <code>Renderer::render()</code> → <code>loadElementTree()</code> carrega todos elementos em <strong>1 query</strong>, monta árvore em memória → gera HTML com estilos inline</li>
                    <li><strong>Cache</strong> → ao alterar página/elemento, <code>clearPageCache()</code> faz <code>$page->touch()</code> (nova chave de cache) + <code>Cache::tags()</code> flush</li>
                    <li><strong>Importar HTML</strong> → <code>HtmlImportController::import()</code> → <code>ImportHtmlJob</code> (Queue) → converte HTML em widgets</li>
                    <li><strong>Colaboração</strong> → <code>CollaborationController::join()</code> → <code>CollaborationService</code> → Cache para presença</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Sistema de Widgets</h3>
                <p>Cada widget é uma classe que implementa <code>WidgetInterface</code> e estende <code>BaseWidget</code>. Cada um define:</p>
                <ul>
                    <li><code>__construct()</code> — tipo, label, ícone, categorias, configurações padrão</li>
                    <li><code>controls()</code> — array de configurações editáveis no painel</li>
                    <li><code>render()</code> — gera o HTML de exibição final (front-end)</li>
                    <li><code>renderEditor()</code> — gera o HTML para o canvas do editor (com atributos <code>data-element-id</code>)</li>
                </ul>
                <p>O <code>WidgetManager</code> registra widgets dinamicamente a partir do arquivo de config, permitindo habilitar/desabilitar widgets sem modificar código.</p>
            </div>
        </section>

        {{-- DATABASE --}}
        <section id="database" class="step">
            <h2>20. Banco de Dados</h2>
            <div class="step-body">
                <p>O projeto usa <strong>SQLite</strong> por padrão (configurado em <code>.env</code> como <code>DB_CONNECTION=sqlite</code>), mas é compatível com MySQL e MariaDB. São 9 tabelas principais além das padrão do Laravel (users, cache, jobs, personal_access_tokens):</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Tabela <code>projects</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Dono do projeto (cascade delete) — projetos são privados por usuário</td></tr>
                    <tr><td><code>name</code></td><td>string</td><td>Nome do projeto</td></tr>
                    <tr><td><code>slug</code></td><td>string</td><td>Slug único por usuário (unique <code>user_id + slug</code>)</td></tr>
                    <tr><td><code>description</code></td><td>text (nullable)</td><td>Descrição do projeto</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Configurações do projeto</td></tr>
                    <tr><td><code>is_default</code></td><td>boolean</td><td>Marca o projeto padrão "Meus Projetos" (não pode ser excluído)</td></tr>
                    <tr><td><code>created_at / updated_at</code></td><td>timestamps</td><td>Datas de criação e atualização</td></tr>
                    <tr><td><code>deleted_at</code></td><td>softDeletes</td><td>Exclusão lógica</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Migração de dados:</strong> A migration <code>create_projects_table</code> cria automaticamente um projeto padrão <strong>"Meus Projetos"</strong> para cada usuário e move suas páginas, temas e popups existentes para ele. Ao excluir um projeto, os itens são reatribuídos ao projeto padrão (<code>nullOnDelete</code> + reassign no service).
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Coluna <code>project_id</code></h3>
                <p>As tabelas <code>pages</code>, <code>theme_templates</code> e <code>popups</code> possuem uma coluna <code>project_id</code> (foreignId nullable, <code>nullOnDelete</code>) que agrupa o item em um projeto. As listagens aceitam o filtro <code>?project={id}</code>.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>pages</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Referência ao usuário dono (cascade delete)</td></tr>
                    <tr><td><code>project_id</code></td><td>foreignId (nullable)</td><td>Projeto ao qual a página pertence (nullOnDelete)</td></tr>
                    <tr><td><code>title</code></td><td>string</td><td>Título da página</td></tr>
                    <tr><td><code>slug</code></td><td>string (unique)</td><td>Slug para URLs (gerado automaticamente)</td></tr>
                    <tr><td><code>status</code></td><td>string</td><td>"draft" ou "published" (padrão: draft)</td></tr>
                    <tr><td><code>content</code></td><td>longText (nullable)</td><td>Conteúdo HTML renderizado</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Configurações: container_width, page_background, content_padding, css_custom</td></tr>
                    <tr><td><code>meta_data</code></td><td>json (nullable)</td><td>Metadados (SEO, tags, etc.)</td></tr>
                    <tr><td><code>template</code></td><td>string (nullable)</td><td>Nome do template aplicado</td></tr>
                    <tr><td><code>created_at / updated_at</code></td><td>timestamps</td><td>Datas de criação e atualização</td></tr>
                    <tr><td><code>deleted_at</code></td><td>softDeletes</td><td>Exclusão lógica</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>elements</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>page_id</code></td><td>foreignId</td><td>Página pai (cascade delete)</td></tr>
                    <tr><td><code>parent_id</code></td><td>foreignId (nullable)</td><td>Elemento pai — cria a <strong>árvore</strong> (Section → Column → Widget)</td></tr>
                    <tr><td><code>uuid</code></td><td>uuid (unique)</td><td>Identificador único universal para cada elemento</td></tr>
                    <tr><td><code>type</code></td><td>string</td><td>Tipo do widget: heading, text, image, button, section, column</td></tr>
                    <tr><td><code>name</code></td><td>string</td><td>Nome exibido no editor (ex: "Heading", "Section")</td></tr>
                    <tr><td><code>order</code></td><td>integer</td><td>Ordem dentro do pai (padrão: 0)</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Configurações do widget (varia por tipo)</td></tr>
                    <tr><td><code>content</code></td><td>json (nullable)</td><td>Conteúdo (textos, URLs, etc.)</td></tr>
                    <tr><td><code>styles</code></td><td>json (nullable)</td><td>Estilos CSS personalizados</td></tr>
                    <tr><td><code>responsive_settings</code></td><td>json (nullable)</td><td>Configurações por breakpoint</td></tr>
                    <tr><td><code>animation</code></td><td>json (nullable)</td><td>Animações de entrada</td></tr>
                    <tr><td><code>effects</code></td><td>json (nullable)</td><td>Efeitos visuais</td></tr>
                    <tr><td><code>column_size</code></td><td>string</td><td>Largura da coluna (padrão: "col-12")</td></tr>
                    <tr><td><code>css_classes</code></td><td>json (nullable)</td><td>Classes CSS adicionais</td></tr>
                    <tr><td><code>css_id</code></td><td>string (nullable)</td><td>ID CSS personalizado</td></tr>
                    <tr><td><code>deleted_at</code></td><td>softDeletes</td><td>Exclusão lógica</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Árvore de Elementos:</strong> A estrutura hierárquica é formada pelo campo <code>parent_id</code>. Uma Seção contém Colunas, que contêm widgets. Exemplo: Section (parent_id=null) → Column (parent_id=section.id) → Heading (parent_id=column.id).
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>revisions</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>page_id</code></td><td>foreignId</td><td>Página revisada (cascade delete)</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Usuário que fez a revisão</td></tr>
                    <tr><td><code>version</code></td><td>string(20)</td><td>Versão da revisão (ex: "1.0", "2.3")</td></tr>
                    <tr><td><code>label</code></td><td>string (nullable)</td><td>Label descritivo (ex: "Versão inicial", "Auto-save")</td></tr>
                    <tr><td><code>type</code></td><td>string</td><td>"manual" ou "auto_save" (padrão: manual)</td></tr>
                    <tr><td><code>content</code></td><td>longText (nullable)</td><td>Snapshot do conteúdo HTML da página</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Snapshot das configurações da página</td></tr>
                    <tr><td><code>meta_data</code></td><td>json (nullable)</td><td>Snapshot dos metadados</td></tr>
                    <tr><td><code>diff</code></td><td>json (nullable)</td><td>Diff em relação à revisão anterior</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>form_submissions</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>page_id</code></td><td>foreignId</td><td>Página do formulário (cascade delete)</td></tr>
                    <tr><td><code>form_name</code></td><td>string</td><td>Nome do formulário (padrão: "Contact Form")</td></tr>
                    <tr><td><code>data</code></td><td>json</td><td>Dados enviados pelo formulário</td></tr>
                    <tr><td><code>ip_address</code></td><td>string(45) (nullable)</td><td>IP do remetente</td></tr>
                    <tr><td><code>user_agent</code></td><td>string (nullable)</td><td>User agent do navegador</td></tr>
                    <tr><td><code>created_at / updated_at</code></td><td>timestamps</td><td>Datas de criação e atualização</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>users</code> (coluna adicional)</h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>role</code></td><td>string</td><td>Função do usuário (padrão: "editor"). Adicionada via migration <code>add_role_to_users</code></td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>personal_access_tokens</code> (Sanctum)</h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>tokenable_type</code></td><td>string</td><td>Tipo do modelo (User)</td></tr>
                    <tr><td><code>tokenable_id</code></td><td>bigint</td><td>ID do modelo</td></tr>
                    <tr><td><code>name</code></td><td>string</td><td>Nome do token</td></tr>
                    <tr><td><code>token</code></td><td>string</td><td>Hash do token (SHA-256)</td></tr>
                    <tr><td><code>abilities</code></td><td>json</td><td>Permissões do token</td></tr>
                    <tr><td><code>last_used_at</code></td><td>timestamp (nullable)</td><td>Último uso</td></tr>
                    <tr><td><code>expires_at</code></td><td>timestamp (nullable)</td><td>Data de expiração</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>custom_fonts</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Dono da fonte</td></tr>
                    <tr><td><code>name</code></td><td>string</td><td>Nome da fonte</td></tr>
                    <tr><td><code>family</code></td><td>string</td><td>Família tipográfica</td></tr>
                    <tr><td><code>weight</code></td><td>string</td><td>Peso (normal, bold, etc.)</td></tr>
                    <tr><td><code>style</code></td><td>string</td><td>Estilo (normal, italic)</td></tr>
                    <tr><td><code>files</code></td><td>json</td><td>Arquivos da fonte (ttf, woff, woff2)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>global_widgets</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Dono do widget</td></tr>
                    <tr><td><code>type</code></td><td>string</td><td>Tipo do widget (heading, text, etc.)</td></tr>
                    <tr><td><code>name</code></td><td>string</td><td>Nome do widget global</td></tr>
                    <tr><td><code>settings</code></td><td>json</td><td>Configurações do widget</td></tr>
                    <tr><td><code>content</code></td><td>json (nullable)</td><td>Conteúdo do widget</td></tr>
                    <tr><td><code>styles</code></td><td>json (nullable)</td><td>Estilos do widget</td></tr>
                    <tr><td><code>activity</code></td><td>json (nullable)</td><td>Dados de atividade</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>popups</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Dono do popup</td></tr>
                    <tr><td><code>project_id</code></td><td>foreignId (nullable)</td><td>Projeto ao qual o popup pertence (nullOnDelete)</td></tr>
                    <tr><td><code>title</code></td><td>string</td><td>Título do popup</td></tr>
                    <tr><td><code>slug</code></td><td>string (unique)</td><td>Slug único</td></tr>
                    <tr><td><code>status</code></td><td>string</td><td>draft / published</td></tr>
                    <tr><td><code>content</code></td><td>json (nullable)</td><td>Conteúdo do popup</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Configurações visuais</td></tr>
                    <tr><td><code>triggers</code></td><td>json (nullable)</td><td>Gatilhos de exibição</td></tr>
                    <tr><td><code>conditions</code></td><td>json (nullable)</td><td>Condições de exibição</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>theme_templates</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Dono do template</td></tr>
                    <tr><td><code>project_id</code></td><td>foreignId (nullable)</td><td>Projeto ao qual o template pertence (nullOnDelete)</td></tr>
                    <tr><td><code>title</code></td><td>string</td><td>Título do template</td></tr>
                    <tr><td><code>slug</code></td><td>string (unique)</td><td>Slug único</td></tr>
                    <tr><td><code>type</code></td><td>string</td><td>Tipo (header, footer, single, archive, 404, search_results)</td></tr>
                    <tr><td><code>status</code></td><td>string</td><td>draft / published</td></tr>
                    <tr><td><code>content</code></td><td>json (nullable)</td><td>Conteúdo do template</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Configurações</td></tr>
                    <tr><td><code>conditions</code></td><td>json (nullable)</td><td>Condições de exibição</td></tr>
                </table>
            </div>
        </section>

        {{-- ROUTES --}}
        <section id="routes" class="step">
            <h2>21. Rotas</h2>
            <div class="step-body">
                <p>Todas as rotas do page builder ficam em <code>routes/page-builder.php</code>, protegidas pelo middleware <code>web</code> + <code>auth</code>, com prefixo <code>/page-builder</code>.</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Rotas de Páginas</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages</code></td><td>PageController@index</td><td>Listar páginas</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/create</code></td><td>PageController@create</td><td>Formulário de criação</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages</code></td><td>PageController@store</td><td>Criar página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/editor</code></td><td>PageController@edit</td><td>Abrir editor visual</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/pages/{id}</code></td><td>PageController@update</td><td>Atualizar página</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/pages/{id}</code></td><td>PageController@destroy</td><td>Excluir página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/render</code></td><td>PageController@render</td><td>Renderizar HTML da página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/data</code></td><td>PageController@getData</td><td>Dados JSON da página</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/publish</code></td><td>PageController@publish</td><td>Publicar</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/unpublish</code></td><td>PageController@unpublish</td><td>Despublicar</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/duplicate</code></td><td>PageController@duplicate</td><td>Duplicar página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/export</code></td><td>PageController@export</td><td>Exportar JSON</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/import</code></td><td>PageController@import</td><td>Importar JSON</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/templates</code></td><td>PageController@listTemplates</td><td>Listar templates</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/apply-template</code></td><td>PageController@applyTemplate</td><td>Aplicar template</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/pages/{id}/layout</code></td><td>PageController@updateLayout</td><td>Atualizar layout</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Projetos</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/projects</code></td><td>ProjectController@index</td><td>Listar projetos do usuário</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/projects</code></td><td>ProjectController@store</td><td>Criar projeto</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/projects/{project}</code></td><td>ProjectController@update</td><td>Atualizar projeto</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/projects/{project}</code></td><td>ProjectController@destroy</td><td>Excluir projeto (reassign ao padrão)</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/projects/{project}/move</code></td><td>ProjectController@move</td><td>Mover páginas/temas/popups para o projeto</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Elementos</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/elements</code></td><td>ElementController@index</td><td>Listar elementos da página</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements</code></td><td>ElementController@store</td><td>Criar elemento</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/elements/{id}</code></td><td>ElementController@show</td><td>Ver elemento</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/elements/{id}</code></td><td>ElementController@update</td><td>Atualizar elemento</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/elements/{id}</code></td><td>ElementController@destroy</td><td>Excluir elemento</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/elements/{id}/duplicate</code></td><td>ElementController@duplicate</td><td>Duplicar elemento</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements/reorder</code></td><td>ElementController@reorder</td><td>Reordenar elementos</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/elements/{id}/move</code></td><td>ElementController@move</td><td>Mover elemento</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/elements/{id}/settings</code></td><td>ElementController@updateSettings</td><td>Atualizar configurações</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/elements/{id}/styles</code></td><td>ElementController@updateStyles</td><td>Atualizar estilos</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/elements/{id}/render</code></td><td>ElementController@renderElement</td><td>Renderizar elemento</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/elements/{id}/controls</code></td><td>ElementController@controls</td><td>Controles do elemento</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/widgets/{type}/controls</code></td><td>ElementController@widgetControls</td><td>Controles por tipo de widget</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/upload</code></td><td>ElementController@uploadImage</td><td>Upload de imagem</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/upload-video</code></td><td>ElementController@uploadVideo</td><td>Upload de vídeo</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Revisões</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/revisions</code></td><td>RevisionController@index</td><td>Listar revisões</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/revisions/{id}</code></td><td>RevisionController@show</td><td>Ver revisão</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/revisions/{id}/restore</code></td><td>RevisionController@restore</td><td>Restaurar revisão</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/revisions/{id}/diff</code></td><td>RevisionController@diff</td><td>Ver diff</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/revisions/{id}</code></td><td>RevisionController@destroy</td><td>Excluir revisão</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/revisions/prune</code></td><td>RevisionController@prune</td><td>Limpar revisões antigas</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/revisions/auto-save</code></td><td>RevisionController@autoSave</td><td>Auto-save (a cada 60s)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Colaboração</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/collab/join</code></td><td>CollaborationController@join</td><td>Entrar na sessão</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/collab/leave</code></td><td>CollaborationController@leave</td><td>Sair da sessão</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/collab/heartbeat</code></td><td>CollaborationController@heartbeat</td><td>Heartbeat com cursor</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/collab/users</code></td><td>CollaborationController@activeUsers</td><td>Usuários ativos</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements/{elementId}/lock</code></td><td>CollaborationController@lockElement</td><td>Bloquear elemento</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements/{elementId}/unlock</code></td><td>CollaborationController@unlockElement</td><td>Desbloquear elemento</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Importação HTML</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>POST</td><td><code>/page-builder/html-import</code></td><td>HtmlImportController@import</td><td>Importar HTML (textarea ou URL)</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/html-import/fetch</code></td><td>HtmlImportController@fetch</td><td>Buscar HTML de uma URL</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Formulários</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/form/submit</code></td><td>FormController@submit</td><td>Enviar formulário</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/form/submissions</code></td><td>FormController@submissions</td><td>Listar envios</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Dynamic Tags</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/dynamic-tags</code></td><td>DynamicTagController@index</td><td>Listar dynamic tags disponíveis</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Find &amp; Replace</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>POST</td><td><code>/page-builder/find-replace/search</code></td><td>FindReplaceController@search</td><td>Buscar conteúdo nas páginas</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/find-replace/replace</code></td><td>FindReplaceController@replace</td><td>Substituir conteúdo nas páginas</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Custom Fonts</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/fonts</code></td><td>CustomFontController@index</td><td>Listar fontes</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/fonts</code></td><td>CustomFontController@store</td><td>Upload de nova fonte</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/fonts/{id}</code></td><td>CustomFontController@update</td><td>Atualizar fonte</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/fonts/{id}</code></td><td>CustomFontController@destroy</td><td>Excluir fonte</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/fonts/{id}/download/{format}</code></td><td>CustomFontController@download</td><td>Download da fonte (ttf/woff/woff2)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Theme Templates</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/themes</code></td><td>ThemeTemplateController@index</td><td>Listar templates de tema</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/themes/create</code></td><td>ThemeTemplateController@create</td><td>Formulário de criação</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/themes</code></td><td>ThemeTemplateController@store</td><td>Criar template de tema</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/themes/{id}/edit</code></td><td>ThemeTemplateController@edit</td><td>Formulário de edição</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/themes/{id}</code></td><td>ThemeTemplateController@update</td><td>Atualizar template</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/themes/{id}</code></td><td>ThemeTemplateController@destroy</td><td>Excluir template</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/themes/{id}/editor</code></td><td>ThemeTemplateController@editor</td><td>Abrir editor visual do tema</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/themes/{id}/render</code></td><td>ThemeTemplateController@render</td><td>Renderizar template</td></tr>
                    <tr><td>GET/PUT</td><td><code>/page-builder/themes/{id}/conditions</code></td><td>ThemeTemplateController@editConditions</td><td>Gerenciar condições</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/themes/{id}/publish</code></td><td>ThemeTemplateController@publish</td><td>Publicar template</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/themes/{id}/unpublish</code></td><td>ThemeTemplateController@unpublish</td><td>Despublicar template</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Global Widgets</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/global-widgets</code></td><td>GlobalWidgetController@index</td><td>Listar widgets globais</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/global-widgets</code></td><td>GlobalWidgetController@store</td><td>Criar widget global</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/global-widgets/{id}</code></td><td>GlobalWidgetController@show</td><td>Ver widget global</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/global-widgets/{id}</code></td><td>GlobalWidgetController@update</td><td>Atualizar widget global</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/global-widgets/{id}</code></td><td>GlobalWidgetController@destroy</td><td>Excluir widget global</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/global-widgets/{id}/render</code></td><td>GlobalWidgetController@render</td><td>Renderizar widget global</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Popups</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/popups</code></td><td>PopupController@index</td><td>Listar popups</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/popups/create</code></td><td>PopupController@create</td><td>Formulário de criação</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/popups</code></td><td>PopupController@store</td><td>Criar popup</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/popups/{id}/edit</code></td><td>PopupController@edit</td><td>Formulário de edição</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/popups/{id}</code></td><td>PopupController@update</td><td>Atualizar popup</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/popups/{id}</code></td><td>PopupController@destroy</td><td>Excluir popup</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/popups/{id}/editor</code></td><td>PopupController@editor</td><td>Abrir editor do popup</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/popups/{id}/render</code></td><td>PopupController@render</td><td>Renderizar popup</td></tr>
                    <tr><td>GET/PUT</td><td><code>/page-builder/popups/{id}/triggers</code></td><td>PopupController@getTriggers</td><td>Gerenciar gatilhos</td></tr>
                    <tr><td>GET/PUT</td><td><code>/page-builder/popups/{id}/conditions</code></td><td>PopupController@getConditions</td><td>Gerenciar condições</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/popups/{id}/publish</code></td><td>PopupController@publish</td><td>Publicar popup</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/popups/{id}/unpublish</code></td><td>PopupController@unpublish</td><td>Despublicar popup</td></tr>
                </table>
            </div>
        </section>

        {{-- API REST --}}
        <section id="api" class="step">
            <h2>22. API REST (Sanctum)</h2>
            <div class="step-body">
                <p>O Page Builder expõe uma <strong>API REST</strong> completa com autenticação via <strong>Laravel Sanctum</strong>. Isso permite integração com aplicativos móveis, SPAs externas e automações.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Autenticação</h3>
                <p>A API usa tokens de acesso pessoal (Personal Access Tokens) do Sanctum. Para obter um token:</p>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0"><code>POST /api/tokens
{
    "email": "admin@sistema.local",
    "password": "password",
    "device_name": "Minha App"
}

// Resposta:
{
    "token": "1|abc123...",
    "user": { "id": 1, "name": "...", "email": "..." }
}</code></pre>

                <p>Use o token no header <code>Authorization: Bearer {token}</code> em todas as requisições.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Endpoints da API</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Descrição</th></tr>
                    <tr><td>POST</td><td><code>/api/tokens</code></td><td>Criar token de acesso</td></tr>
                    <tr><td>DELETE</td><td><code>/api/tokens</code></td><td>Revogar token atual</td></tr>
                    <tr><td>GET</td><td><code>/api/user</code></td><td>Obter dados do usuário autenticado</td></tr>
                    <tr><td>GET</td><td><code>/api/pages</code></td><td>Listar páginas do usuário (paginado)</td></tr>
                    <tr><td>POST</td><td><code>/api/pages</code></td><td>Criar nova página</td></tr>
                    <tr><td>GET</td><td><code>/api/pages/{id}</code></td><td>Obter página com árvore de elementos</td></tr>
                    <tr><td>PUT</td><td><code>/api/pages/{id}</code></td><td>Atualizar página</td></tr>
                    <tr><td>DELETE</td><td><code>/api/pages/{id}</code></td><td>Excluir página</td></tr>
                    <tr><td>GET</td><td><code>/api/pages/{id}/elements</code></td><td>Listar elementos da página</td></tr>
                    <tr><td>POST</td><td><code>/api/pages/{id}/elements</code></td><td>Criar elemento na página</td></tr>
                    <tr><td>GET</td><td><code>/api/elements/{id}</code></td><td>Obter elemento</td></tr>
                    <tr><td>PUT</td><td><code>/api/elements/{id}</code></td><td>Atualizar elemento</td></tr>
                    <tr><td>DELETE</td><td><code>/api/elements/{id}</code></td><td>Excluir elemento</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Exemplo com cURL</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0"><code># Listar páginas
curl -H "Authorization: Bearer 1|abc123..." \
     http://localhost:8000/api/pages

# Criar página
curl -X POST -H "Authorization: Bearer 1|abc123..." \
     -H "Content-Type: application/json" \
     -d '{"title":"Minha Página","status":"draft"}' \
     http://localhost:8000/api/pages

# Criar elemento
curl -X POST -H "Authorization: Bearer 1|abc123..." \
     -H "Content-Type: application/json" \
     -d '{"type":"heading","settings":{"title":"Olá Mundo","tag":"h1"}}' \
     http://localhost:8000/api/pages/1/elements</code></pre>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> A API filtra páginas pelo <code>user_id</code> do token — cada usuário só vê suas próprias páginas. Use <code>?per_page=50</code> para ajustar a páginação.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">API Resources (Transformers)</h3>
                <p>As respostas da API usam <strong>Laravel API Resources</strong> para transformar os modelos em JSON estruturado:</p>
                <ul>
                    <li><code>PageResource</code> — expõe id, title, slug, status, settings, meta_data, timestamps. Inclui <code>elements</code> (árvore recursiva via <code>ElementResource</code>) quando a relação está carregada.</li>
                    <li><code>PageCollection</code> — wrapper para coleções paginadas de <code>PageResource</code>.</li>
                    <li><code>ElementResource</code> — expõe uuid, type, name, settings, content, styles, children recursivos.</li>
                </ul>
                <p>Exemplo de resposta <code>GET /api/pages/{id}</code>:</p>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0"><code>{
    "page": {
        "id": 1,
        "title": "Minha Página",
        "slug": "minha-pagina",
        "status": "draft",
        "settings": { "container_width": "1140px" },
        "elements": [
            {
                "id": "uuid-1234",
                "type": "section",
                "name": "Section",
                "settings": {},
                "children": [
                    {
                        "id": "uuid-5678",
                        "type": "heading",
                        "name": "Heading",
                        "settings": { "title": "Olá Mundo" },
                        "children": []
                    }
                ]
            }
        ]
    }
}</code></pre>
            </div>
        </section>

        {{-- JOBS --}}
        <section id="jobs" class="step">
            <h2>23. Jobs &amp; Filas</h2>
            <div class="step-body">
                <p>O projeto usa o sistema de <strong>Jobs do Laravel</strong> para processar tarefas pesadas em segundo plano via Queue.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Jobs Disponíveis</h3>
                <table class="widget-table">
                    <tr><th>Job</th><th>Descrição</th><th>Timeout</th><th>Tentativas</th></tr>
                    <tr><td><code>ImportHtmlJob</code></td><td>Converte HTML externo em elementos do page builder e cria a página. Processado via <code>HtmlImportController::import()</code>.</td><td>60s</td><td>3</td></tr>
                    <tr><td><code>ClearPageCacheJob</code></td><td>Invalida o cache da página via <code>$page->touch()</code> (nova chave de cache) + <code>Cache::tags()</code> flush. Pode ser dispatchado manualmente.</td><td>10s</td><td>1</td></tr>
                    <tr><td><code>AutoSaveRevisionJob</code></td><td>Cria uma revisão automática (<code>type: auto_save</code>) com snapshot do conteúdo e configurações da página.</td><td>30s</td><td>3</td></tr>
                    <tr><td><code>ProcessFormSubmissionJob</code></td><td>Processa notificações de formulário em background (email para admin). Dispatchado após <code>FormController::submit()</code>.</td><td>30s</td><td>3</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Configuração da Queue</h3>
                <p>No arquivo <code>.env</code>, configure:</p>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0"><code># Opções: sync (síncrono), database, redis, sqs, etc.
QUEUE_CONNECTION=sync

# Para produção com Redis:
QUEUE_CONNECTION=redis</code></pre>

                <p>Para processar as filas em background:</p>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0"><code>php artisan queue:work
php artisan queue:work --tries=3
php artisan queue:listen --timeout=60</code></pre>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> Em desenvolvimento, use <code>QUEUE_CONNECTION=sync</code> para processar jobs imediatamente. Em produção, use <code>redis</code> ou <code>database</code> para processamento assíncrono. O comando <code>php artisan queue:work</code> deve rodar como um daemon (via Supervisor, systemd, etc.).
                </div>
            </div>
        </section>

        {{-- QUALITY --}}
        <section id="quality" class="step">
            <h2>24. Qualidade &amp; Testes</h2>
            <div class="step-body">
                <p>O projeto tem uma suíte completa de testes automatizados com <strong>155 testes</strong> rodando em SQLite em memória (mais 7 testes de frontend com Vitest).</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Resumo da Qualidade</h3>
                <table class="widget-table">
                    <tr><th>Métrica</th><th>Valor</th><th>Avaliação</th></tr>
                    <tr><td>Testes totais (PHP)</td><td>155</td><td style="color:green">Boa cobertura</td></tr>
                    <tr><td>Testes unitários</td><td>61</td><td style="color:green">Widgets, Services, Templates, Colaboração</td></tr>
                    <tr><td>Testes de feature</td><td>94</td><td style="color:green">Controllers, Projetos, Auth, API, Fluxos</td></tr>
                    <tr><td>Testes de frontend (Vitest)</td><td>7</td><td style="color:green">Módulos JS do editor</td></tr>
                    <tr><td>Cobertura de controllers</td><td>6</td><td style="color:green">Page, Element, Revision, Form, HtmlImport, Project testados</td></tr>
                    <tr><td>BaseWidgetTest</td><td>13</td><td style="color:green">Infraestrutura base do widget (getters, validação, sanitização)</td></tr>
                    <tr><td>Sanitização de XSS</td><td>Implementada</td><td style="color:green">PageBuilderService + HtmlSanitizer (central)</td></tr>
                    <tr><td>Autorização (Policy)</td><td>Implementada</td><td style="color:green">PagePolicy + ProjectPolicy + ownership em todos os controllers</td></tr>
                    <tr><td>Proteção SSRF</td><td>Implementada</td><td style="color:green">HtmlImportService::assertSafeUrl (bloqueio de IPs privados/reservados, redirects manuais)</td></tr>
                    <tr><td>Tratamento de erros JS</td><td>14 fetch()</td><td style="color:green">Todos com .catch() + toast</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Testes Unitários (61)</h3>
                <table class="widget-table">
                    <tr><th>Classe de Teste</th><th>Testes</th><th>Cobre</th></tr>
                    <tr><td><code>BaseWidgetTest</code></td><td>13</td><td>Getters, validação, prepareSettings, sanitização, isContainer, isDynamic</td></tr>
                    <tr><td><code>PageBuilderServiceTest</code></td><td>19</td><td>Create, update, sanitize, elements, revisions, export/import, render</td></tr>
                    <tr><td><code>TemplateManagerTest</code></td><td>18</td><td>Listagem, aplicação, importação, edge cases, estrutura de templates</td></tr>
                    <tr><td><code>CollaborationServiceTest</code></td><td>10</td><td>Presença, bloqueio de elementos, cursores, limpeza de cache</td></tr>
                    <tr><td><code>ExampleTest</code></td><td>1</td><td>Skeleton padrão do Laravel</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Testes de Feature (94)</h3>
                <table class="widget-table">
                    <tr><th>Classe de Teste</th><th>Testes</th><th>Cobre</th></tr>
                    <tr><td><code>PageControllerTest</code></td><td>24</td><td>CRUD, auth, publish, templates, export/import, duplicate</td></tr>
                    <tr><td><code>ElementControllerTest</code></td><td>14</td><td>CRUD, auth, reorder, move, settings, styles, controls</td></tr>
                    <tr><td><code>ApiTest</code></td><td>11</td><td>Tokens Sanctum, CRUD de páginas e elementos via API</td></tr>
                    <tr><td><code>AuthTest</code></td><td>10</td><td>Registro, login, logout, rotas protegidas</td></tr>
                    <tr><td><code>RevisionControllerTest</code></td><td>9</td><td>List, show, restore, diff, delete, prune, auto-save, auth, IDOR</td></tr>
                    <tr><td><code>ProjectControllerTest</code></td><td>9</td><td>CRUD de projetos, projeto padrão, move, autorização entre usuários</td></tr>
                    <tr><td><code>FormControllerTest</code></td><td>7</td><td>Envio de formulários, validação, página publicada, armazenamento</td></tr>
                    <tr><td><code>HtmlImportControllerTest</code></td><td>5</td><td>Importação de HTML via URL e textarea, jobs, validação SSRF</td></tr>
                    <tr><td><code>StarterTemplatesSmokeTest</code></td><td>4</td><td>Smoke test dos templates iniciais</td></tr>
                    <tr><td><code>ExampleTest</code></td><td>1</td><td>Skeleton padrão do Laravel</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Como Rodar os Testes</h3>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0">
<pre style="margin:0"># Rodar todos os testes
php artisan test

# Rodar apenas os testes unitários
php artisan test --testsuite=Unit

# Rodar com verbose
php artisan test --verbose

# Rodar os testes de frontend (Vitest)
npm test</pre>
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Cache de Renderização</h3>
                <p>O projeto tem um sistema opcional de cache para páginas renderizadas, configurado em <code>config/page-builder.php</code> via variáveis de ambiente:</p>

                <table class="widget-table">
                    <tr><th>Variável</th><th>Default</th><th>Descrição</th></tr>
                    <tr><td><code>PAGE_BUILDER_CACHE</code></td><td><code>false</code></td><td>Habilita/desabilita cache de renderização</td></tr>
                    <tr><td><code>PAGE_BUILDER_CACHE_TTL</code></td><td><code>3600</code></td><td>Tempo de vida do cache em segundos (1 hora)</td></tr>
                </table>

                <div class="tip">
                    <strong>&#128161; Importante:</strong> Por padrão, o cache está <strong>desligado</strong>. Cada visualização re-renderiza a página do banco de dados. Se você não adicionou <code>PAGE_BUILDER_CACHE=true</code> no <code>.env</code>, não existe cache para limpar.
                </div>

                <h4 style="font-size:.95rem;margin-top:1rem;margin-bottom:.5rem">Como funciona a invalidação de cache</h4>
                <p>A chave de cache inclui o timestamp de <code>updated_at</code> da página. Ao modificar a página ou seus elementos, o método <code>clearPageCache()</code> executa:</p>
                <ol>
                    <li><code>$page->touch()</code> — atualiza <code>updated_at</code>, fazendo a próxima renderização usar uma chave de cache diferente (invalidação implícita)</li>
                    <li><code>Cache::tags("page:{id}")->flush()</code> — limpa entradas por tag quando o driver suporta (Redis, Memcached)</li>
                </ol>
                <p>A invalidação acontece automaticamente ao:</p>
                <ul>
                    <li>Salvar/atualizar uma página (<code>PageBuilderService::updatePage()</code>)</li>
                    <li>Adicionar, atualizar, remover ou duplicar um widget</li>
                    <li>Restaurar uma revisão</li>
                </ul>

                <h4 style="font-size:.95rem;margin-top:1rem;margin-bottom:.5rem">Como limpar o cache manualmente</h4>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0">
<pre style="margin:0"># Limpar TODO o cache do Laravel (inclui páginas)
php artisan cache:clear

# Limpar cache de página específica com tags (Redis)
php artisan tinker
Cache::tags("page:1")->flush();

# Alternativa: forçar nova renderização
$page = App\Models\Page::find(1);
$page->touch();</pre>
                </div>

                <h4 style="font-size:.95rem;margin-top:1rem;margin-bottom:.5rem">Resumo do fluxo de cache</h4>
                <table class="widget-table">
                    <tr><th>Ação</th><th>Cache invalidado?</th><th>Mecanismo</th></tr>
                    <tr><td>Salvar página</td><td style="color:green">✅ Sim</td><td><code>$page->touch()</code> + tags flush</td></tr>
                    <tr><td>Editar widget</td><td style="color:green">✅ Sim</td><td><code>$page->touch()</code> + tags flush</td></tr>
                    <tr><td>Remover widget</td><td style="color:green">✅ Sim</td><td><code>$page->touch()</code> + tags flush</td></tr>
                    <tr><td>Restaurar revisão</td><td style="color:green">✅ Sim</td><td><code>$page->touch()</code> + tags flush</td></tr>
                    <tr><td><code>php artisan cache:clear</code></td><td style="color:green">✅ Tudo</td><td>Limpa todo o cache</td></tr>
                </table>
            </div>
        </section>

        {{-- IMPROVEMENTS --}}
        <section id="improvements" class="step">
            <h2>25. Melhorias Propostas</h2>
            <div class="step-body">
                <p>O projeto já tem uma base sólida com 155 testes, sanitização XSS, autorização por Policy e tratamento de erros no JS. O plano de melhorias <strong>IMPROVEMENTS.md</strong> contém 74 passos organizados em 12 fases. Abaixo está o status atual de cada fase:</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Status das Fases</h3>
                <table class="widget-table">
                    <tr><th>Fase</th><th>Passos</th><th>Descrição</th><th>Status</th></tr>
                    <tr><td>1</td><td>1–8</td><td>Novos Widgets: Vídeo, Divisor, Espaçador, Ícone, Galeria, Form, Tabs, Accordion</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>2</td><td>9–13</td><td>Style Tab: Fundo, Borda, Tipografia, Hover, abas Content/Style/Advanced</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>3</td><td>14–17</td><td>Navigator: Árvore de elementos, drag-and-drop, rename, context menu</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>4</td><td>18–23</td><td>Controles Avançados: Margem/Padding, Z-Index, CSS custom, Animações, Responsividade</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>5</td><td>24–31</td><td>UX do Editor: Live preview, Ctrl+Z, context menu, zoom, fullscreen, search</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>6</td><td>32–35</td><td>Atalhos: Ctrl+D, Copy/Paste Styles, Command Finder, Multi-select</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>7</td><td>36–38</td><td>Temas e Presets: Starter Templates, Color Presets, Typography Presets</td><td style="color:#d97706;font-weight:700">&#9202; Pendente</td></tr>
                    <tr><td>8</td><td>39–42</td><td>Responsividade: Breakpoints visuais, estilos responsivos por widget</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>9</td><td>43–53</td><td>Widgets Avançados: Counter, Progress Bar, Social Icons, Icon Box, Image Box, Testimonial, Price Table, Countdown, Google Maps, Carousel, Inner Section</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>10</td><td>54–65</td><td>Features Elementor-Pro: Gradient, Overlay, Shape Dividers, Vídeo BG, Parallax, Scroll Animations, Sticky, Position, Image Filters, Button Gradient, Text Shadow/Stroke, Column Resize</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>11</td><td>66–68</td><td>Sistema Global: Global Colors, Global Fonts, Responsive Tabs</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                    <tr><td>12</td><td>69–74</td><td>UX Avançada: Command Finder, Revision History, Save as Template, Column Structure, ARIA Accessibility, Keyboard Navigation</td><td style="color:green;font-weight:700">&#10003; Implementado</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Funcionalidades Adicionais Implementadas (fora do IMPROVEMENTS.md)</h3>
                <ul>
                    <li><strong>30 widgets</strong> — Heading, Text, Image, Button, Section, Column, Inner Section, Callout, Table, Math, Vídeo, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion, Counter, Progress Bar, Social Icons, Icon Box, Image Box, Testimonial, Price Table, Countdown, Google Maps, Carousel, Mega Menu, Lottie</li>
                    <li><strong>27 tipos de controles</strong> — text, number, textarea, select, color, boolean, url, image, vídeo, wysiwyg, icon, gallery, repeater, typography, background, border, box_shadow, dimensions, hover, custom_css, animation, visibility, gradient, scroll_animation, text_shadow, text_stroke, column_width</li>
                    <li><strong>Projetos</strong> — organização privada por usuário de páginas, temas e popups; projeto padrão "Meus Projetos" (não excluível), migração automática de dados existentes, seletor de projeto nas listagens e movimentação de itens via <code>projects.move</code></li>
                    <li><strong>Theme Builder</strong> — templates de tema (header, footer, single, archive, 404, search_results) com condições</li>
                    <li><strong>Popups</strong> — criação e edição com gatilhos e condições de exibição</li>
                    <li><strong>Global Widgets</strong> — widgets reutilizáveis em várias páginas</li>
                    <li><strong>Custom Fonts</strong> — upload de fontes (ttf, woff, woff2)</li>
                    <li><strong>Dynamic Tags</strong> — conteúdo dinâmico do sistema</li>
                    <li><strong>Find &amp; Replace</strong> — busca e substituição</li>
                    <li><strong>Colaboração em tempo real</strong> — presença, bloqueio de elementos, cursores (via Cache)</li>
                    <li><strong>Importação de HTML</strong> — importar páginas externas via URL ou textarea, processamento via Job</li>
                    <li><strong>API REST com Sanctum</strong> — autenticação token-based, CRUD completo de páginas e elementos</li>
                    <li><strong>Jobs de fila</strong> — ImportHtmlJob, ClearPageCacheJob, AutoSaveRevisionJob, ProcessFormSubmissionJob</li>
                    <li><strong>PagePolicy</strong> — autorização por dono da página</li>
                    <li><strong>ProjectPolicy</strong> — autorização por dono do projeto</li>
                    <li><strong>Sanitização XSS central</strong> — <code>PageBuilderService::sanitizeContent()</code>, <code>sanitizeSettings()</code>, <code>sanitizeStyles()</code> e <code>sanitizeUrl()</code> aplicados a elementos, páginas, templates, snapshots e importação</li>
                    <li><strong>Endurecimento do HtmlSanitizer</strong> — remoção de handlers de evento citados/não citados, <code>srcdoc</code> e URLs perigosas (<code>javascript:</code>, <code>vbscript:</code>, <code>data:text/html</code>, <code>data:image/svg+xml</code>)</li>
                    <li><strong>Proteção SSRF</strong> — <code>HtmlImportService</code> com redirects manuais (máx. 3) e validação de URL (bloqueio de IPs privados/reservados, localhost, <code>.local</code>, <code>.internal</code>)</li>
                    <li><strong>Ownership em todos os controllers</strong> — FindReplace, ThemeTemplate, Popup, GlobalWidget, CustomFont, Collaboration e uploads (validação de <code>page_id</code> + elemento pertencente à página)</li>
                    <li><strong>Correção de IDOR em revisões</strong> — <code>restore</code>/<code>diff</code> validam que a revisão pertence à página</li>
                    <li><strong>Formulários públicos</strong> — rota de envio fora do grupo <code>auth</code>, com <code>throttle:form-submit</code>, exigindo página <code>published</code> e <code>strip_tags</code> nos dados</li>
                    <li><strong>CSRF automático</strong> — <code>&lt;meta name=&quot;csrf-token&quot;&gt;</code> injetado pelo Renderer e pelo ThemeService</li>
                    <li><strong>Revisões com snapshot de elementos</strong> — coluna <code>elements</code> (json) com remapeamento de IDs e UUIDs únicos ao restaurar</li>
                    <li><strong>Sanitização XSS</strong> — <code>sanitizeContent()</code> e <code>sanitizeSettings()</code></li>
                    <li><strong>Tratamento de erros JS</strong> — 14 chamadas fetch() com .catch()</li>
                    <li><strong>Content Security Policy (CSP)</strong> — middleware <code>ContentSecurityPolicy</code> no grupo <code>web</code></li>
                    <li><strong>HtmlSanitizer</strong> — serviço dedicado para sanitizar CSS, JS e URLs</li>
                    <li><strong>Repository Pattern</strong> — <code>PageRepository</code> e <code>ElementRepository</code> com interfaces injetáveis</li>
                    <li><strong>API Resources</strong> — <code>PageResource</code>, <code>PageCollection</code>, <code>ElementResource</code> para respostas REST estruturadas</li>
                    <li><strong>N+1 eliminado no Renderer</strong> — <code>loadElementTree()</code> carrega todos elementos em 1 query</li>
                    <li><strong>Cache invalidation robusta</strong> — <code>$page->touch()</code> + <code>Cache::tags()</code> ao invés de key registry quebrado</li>
                    <li><strong>Cache no GlobalWidgetService</strong> — <code>getAll()</code> cacheado com <code>clearCache()</code> nas mutações</li>
                    <li><strong>ProcessFormSubmissionJob</strong> — notificações de formulário em background</li>
                    <li><strong>155 testes automatizados (PHP) + 7 (Vitest)</strong> — cobertura completa de controllers, services e widgets</li>
                    <li><strong>Editor WYSIWYG</strong> — toolbar rich-text com imagens, vídeos, listas</li>
                    <li><strong>Edição inline</strong> — preserva HTML (innerHTML) ao invés de textContent</li>
                    <li><strong>Upload de imagens no texto</strong> — botão + Ctrl+V no editor WYSIWYG</li>
                    <li><strong>Vídeos YouTube</strong> — embed responsivo com privacidade (youtube-nocookie.com)</li>
                    <li><strong>Busca de widgets</strong> — barra de pesquisa no painel esquerdo com filtro em tempo real</li>
                    <li><strong>Device frames</strong> — bezel de celular/tablet no modo responsivo</li>
                    <li><strong>Painéis redimensionáveis</strong> — arrastar bordas para ajustar largura dos painéis</li>
                    <li><strong>Multi-select</strong> — Ctrl+Click para selecionar vários elementos, duplicar/excluir em lote</li>
                    <li><strong>Tour de onboarding</strong> — tour guiado de 10 passos na primeira vez</li>
                    <li><strong>Command Finder</strong> — Ctrl+K para buscar ações, widgets e configurações</li>
                    <li><strong>Revision History</strong> — histórico de revisões com diff visual e restauração</li>
                    <li><strong>Save as Template</strong> — exportar página como template JSON reutilizável</li>
                    <li><strong>Column Structure Picker</strong> — 6 opções de layout ao criar seção</li>
                    <li><strong>ARIA Accessibility</strong> — roles semânticos, navegação por Tab, focus-visible</li>
                    <li><strong>Global Colors &amp; Fonts</strong> — paleta de cores e fontes do site</li>
                    <li><strong>Responsive Tabs</strong> — valores por breakpoint com fallback</li>
                    <li><strong>Parallax &amp; Vídeo Background</strong> — efeito parallax e vídeos de fundo</li>
                    <li><strong>Shape Dividers</strong> — 10 tipos de divisores SVG</li>
                    <li><strong>Background Overlay</strong> — cor overlay com blend modes</li>
                    <li><strong>Scroll Animations</strong> — fade, zoom, slide ativados no scroll</li>
                    <li><strong>Image Filters</strong> — 9 filtros CSS + rotação + flip</li>
                    <li><strong>Gradient Background</strong> — gradientes lineares e radiais</li>
                    <li><strong>Text Shadow &amp; Stroke</strong> — sombra e contorno de texto</li>
                    <li><strong>Sticky Position</strong> — seções sticky com offset</li>
                    <li><strong>Column Resize</strong> — slider percentual 1-100%</li>
                </ul>

                <div class="tip">
                    <strong>&#128209; Plano Completo:</strong> O arquivo <code>IMPROVEMENTS.md</code> na raiz do projeto contém <strong>74 passos detalhados</strong> organizados em 12 fases. Cada passo pode ser testado individualmente. 11 de 12 fases já estão implementadas (apenas a Fase 7 — Temas e Presets — permanece pendente).
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Resumo do Plano (74 Passos)</h3>
                <table class="widget-table">
                    <tr><th>Fase</th><th>Passos</th><th>Descrição</th><th>Status</th></tr>
                    <tr><td>1</td><td>1–8</td><td>Novos Widgets: Vídeo, Divisor, Espaçador, Ícone, Galeria, Form, Tabs, Accordion</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>2</td><td>9–13</td><td>Style Tab: Fundo, Borda, Tipografia, Hover</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>3</td><td>14–17</td><td>Navigator: Árvore, drag-and-drop, rename, context menu</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>4</td><td>18–23</td><td>Controles Avançados: Margem/Padding, Z-Index, CSS, Animações</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>5</td><td>24–31</td><td>UX do Editor: Preview, Ctrl+Z, context menu, zoom, fullscreen</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>6</td><td>32–35</td><td>Atalhos: Ctrl+D, Copy/Paste Styles, Finder, Multi-select</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>7</td><td>36–38</td><td>Temas e Presets</td><td style="color:#d97706">&#9202;</td></tr>
                    <tr><td>8</td><td>39–42</td><td>Responsividade completa</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>9</td><td>43–53</td><td>Widgets Avançados (11 widgets)</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>10</td><td>54–65</td><td>Features Elementor-Pro (12 features)</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>11</td><td>66–68</td><td>Sistema Global (cores, fontes, responsive tabs)</td><td style="color:green">&#10003;</td></tr>
                    <tr><td>12</td><td>69–74</td><td>UX Avançada (finder, revisões, templates, acessibilidade)</td><td style="color:green">&#10003;</td></tr>
                </table>
            </div>
        </section>

        {{-- MOODLE --}}
        <section id="moodle" class="step">
            <h2>26. Uso com Moodle 4.5+</h2>
            <div class="step-body">
                <p>O Page Builder pode ser integrado ao <strong>Moodle 4.5+</strong> para criar páginas ricas dentro da sua plataforma de aprendizado. Abaixo estão as instruções detalhadas, começando com um exemplo simples passo a passo.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">26.1 Passo a Passo — Exemplo Simples: "Aula 01"</h3>
                <p>Vamos criar uma página de aula e colá-la no Moodle como um recurso <strong>Página</strong> usando o HTML renderizado. Todo o fluxo leva menos de 5 minutos.</p>
                <ol>
                    <li><strong>Criar a página:</strong> no Page Builder, clique em <strong>"Criar Página"</strong>, defina o nome <code>Aula 01 — Introdução ao Page Builder</code> e o slug <code>aula-01</code>. Escolha o template <strong>Blank</strong> (ou outro, se preferir começar de um modelo).</li>
                    <li><strong>Montar o conteúdo:</strong> no editor, adicione os seguintes widgets (veja as seções 4, 5 e 6 do tutorial):
                        <ul>
                            <li><strong>Heading</strong> H1: <em>"Bem-vindo à Aula 01"</em>;</li>
                            <li><strong>Text</strong>: um parágrafo de boas-vindas;</li>
                            <li><strong>Callout</strong> (type <code>info</code>): <em>"Leia o material indicado antes da aula"</em>;</li>
                            <li><strong>Math</strong>: a fórmula <code>\int_0^1 x^2\,dx = \frac{1}{3}</code> (renderizada via KaTeX);</li>
                            <li><strong>Table</strong>: um quadro de horários da semana.</li>
                        </ul>
                    </li>
                    <li><strong>Copiar o HTML:</strong> na barra de ferramentas do editor, clique no botão <strong>"Copy HTML"</strong> (ícone de clipboard). A notificação <em>"HTML copiado!"</em> confirma que o HTML do conteúdo (sem o container do site, com CSS inline) está na área de transferência.</li>
                    <li><strong>Criar o recurso no Moodle:</strong> no seu curso Moodle 4.5+, ative a edição e clique em <strong>"Adicionar um recurso ou atividade"</strong> → escolha <strong>Página</strong>.</li>
                    <li><strong>Configurar o recurso:</strong> em <strong>Nome</strong> digite <code>Aula 01</code>. No campo <strong>Conteúdo</strong>, clique no botão <strong>&lt;&gt;</strong> (Mostrar código-fonte / HTML) do editor, cole o conteúdo com <kbd>Ctrl+V</kbd> e clique novamente em <strong>&lt;&gt;</strong> para voltar ao modo visual.</li>
                    <li><strong>Salvar:</strong> salve o recurso e visualize o curso. A página aparecerá com títulos, cores, callout, fórmula KaTeX (salva como HTML estático) e tabela — tudo preservado, sem nenhum plugin no Moodle.</li>
                    <li><strong>Atualizar depois:</strong> sempre que editar a página no Page Builder, basta refazer o passo 3 e colar novamente no mesmo recurso Moodle.</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Por que usar "Copy HTML"?</strong> O botão consulta <code>/page-builder/pages/&#123;id&#125;/render?format=inner</code> e copia apenas o conteúdo da página, sem o cabeçalho/rodapé do site e sem o container — ideal para colar dentro do editor do Moodle. O CSS é todo inline, então nada "quebra" ao colar.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">26.2 Copiar HTML para o Moodle (recomendado)</h3>
                <ol>
                    <li>Crie sua página no Page Builder normalmente (use um modelo pronto ou comece do zero). Os modelos são fixos e nunca são alterados ao criar uma nova página — o sistema faz uma cópia dos dados para a página nova.</li>
                    <li>No editor visual, clique no botão <strong>"Copy HTML"</strong> (ícone de clipboard) na barra de ferramentas. Uma notificação "HTML copiado!" será exibida.</li>
                    <li>O HTML renderizado da página (com CSS inline) está na sua área de transferência.</li>
                    <li>No Moodle, edite um recurso do tipo <strong>Página (Page)</strong> ou <strong>Livro (Book)</strong>.</li>
                    <li>No editor do Moodle, clique no botão <strong>&lt;&gt;</strong> (Mostrar código fonte / HTML) e cole o conteúdo com <kbd>Ctrl+V</kbd>.</li>
                    <li>Salve a página. O conteúdo será exibido com todos os estilos preservados.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">26.3 Exportar como JSON (backup ou transferência entre instalações)</h3>
                <ol>
                    <li>Na lista de páginas do Page Builder, clique em <strong>"Export"</strong> na página desejada. Um arquivo <code>.json</code> será baixado com toda a estrutura da página.</li>
                    <li>Para importar em outra instalação do Page Builder, clique em <strong>"Import"</strong> na lista de páginas, faça upload do arquivo <code>.json</code> e a página será recriada.</li>
                    <li>Após importar, abra a página no editor e use o <strong>"Copy HTML"</strong> (passo 26.2) para colar o conteúdo no Moodle.</li>
                </ol>

<h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">26.4 Renderização como Página Moodle (iframe)</h3>
                <ol>
                    <li><strong>URL pública:</strong> toda página <strong>publicada</strong> tem um endereço público em <code>/p/&#123;slug&#125;</code> (ex.: <code>/p/aula-01</code>), que renderiza a página completa com o tema (cabeçalho, rodapé e popups).</li>
                    <li><strong>Incorporar no Moodle:</strong> crie um recurso <strong>URL</strong> apontando para <code>/p/aula-01</code>, ou adicione um bloco <strong>HTML</strong> com <code>&lt;iframe src="/p/aula-01"&gt;</code>. A rota pública não envia <code>X-Frame-Options</code>, então funciona dentro do Moodle.</li>
                    <li><strong>Conteúdo apenas:</strong> para obter só o HTML do conteúdo (sem o tema), use o endpoint <code>/page-builder/pages/&#123;id&#125;/render?format=inner</code> (requer login) ou acrescente <code>&amp;theme=none</code> para um HTML ainda mais limpo, apenas com os elementos e estilos inline. Esse endpoint envia <code>X-Frame-Options: SAMEORIGIN</code>, portanto não use iframe nele — use a URL pública <code>/p/&#123;slug&#125;</code> para incorporar por frame.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">26.5 Widgets Educacionais no Moodle</h3>
                <p>Os widgets educacionais (Callout, Table, Math) são especialmente úteis para criar conteúdo de cursos no Moodle:</p>
                <ul>
                    <li><strong>Callout:</strong> Caixas de destaque para definições, teoremas, exercícios, avisos e dicas. Cada tipo tem cores e ícones automáticos. O HTML renderizado mantém todos os estilos inline, compatível com o Moodle.</li>
                    <li><strong>Table:</strong> Tabelas estilizadas para dados, comparativos e exercícios. O CSS inline garante que as bordas e cores funcionem no editor do Moodle.</li>
                    <li><strong>Math (LaTeX):</strong> Fórmulas matemáticas renderizadas via KaTeX. No Moodle, a fórmula é salva como HTML estático com SVG/PNG, sem precisar do KaTeX no Moodle. Para fórmulas inline no texto, use o botão &#945; no editor WYSIWYG.</li>
                </ul>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para cursos de matemática, combine os três widgets: use <strong>Callout (type=theorem)</strong> para enunciar teoremas, <strong>Math</strong> para as fórmulas em destaque, e <strong>Callout (type=exercise)</strong> para propostas de exercícios. Insira tabelas com <strong>Table</strong> para organizar dados e comparativos.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">26.6 Dicas para Moodle</h3>
                <ul>
                    <li><strong>Estilos inline:</strong> Todo o CSS gerado pelo Page Builder é inline (atributo <code>style</code>), garantindo compatibilidade máxima com o editor do Moodle — nenhum plugin adicional é necessário.</li>
                    <li><strong>Imagens:</strong> Use URLs públicas para imagens (ex.: placehold.co ou imagens hospedadas). Imagens enviadas para <code>storage/app/public/</code> no Page Builder não serão acessíveis pelo Moodle.</li>
                    <li><strong>Imagens no texto:</strong> O editor de texto (WYSIWYG) permite inserir imagens diretamente no conteúdo. Faça upload pela toolbar ou cole (Ctrl+V). As imagens ficam com CSS inline, compatíveis com o Moodle.</li>
                    <li><strong>Vídeos YouTube:</strong> Na toolbar do editor de texto, clique no botão <strong>&#9654;</strong> (vermelho) e cole a URL do vídeo. O embed é inserido como <code>&lt;iframe&gt;</code> com privacidade ativada (<code>youtube-nocookie.com</code>), recomendado pelo Moodle.</li>
                    <li><strong>Fórmulas matemáticas:</strong> O Page Builder usa KaTeX para renderizar LaTeX no editor. Ao copiar o HTML, as fórmulas são salvas como HTML estático — o Moodle não precisa de KaTeX instalado. Para fórmulas inline no parágrafo, use o botão &#945; no editor WYSIWYG.</li>
                    <li><strong>Responsividade:</strong> O HTML gerado mantém a responsividade. Teste em diferentes dispositivos após colar no Moodle.</li>
                    <li><strong>Limitação de largura:</strong> O Moodle pode aplicar estilos próprios de container. Use o parâmetro <code>?format=inner</code> para obter apenas o conteúdo bruto e ajuste margens no Moodle se necessário.</li>
                </ul>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O fluxo mais comum é: crie a página no Page Builder &rarr; clique em "Copy HTML" &rarr; cole no Moodle no modo HTML. Simples e rápido!
                </div>
            </div>
        </section>

        <div class="tutorial-footer">
            <p>Pronto! Você está pronto para criar páginas com o construtor visual.</p>
            <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">Criar sua Primeira Página &rarr;</a>
        </div>
    </div>

    <style>
        .tutorial { max-width: 920px; }
        .tutorial h1 { font-size: 1.8rem; }
        .toc { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.2rem 1.5rem; margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: .5rem 1.2rem; align-items: center; }
        .toc strong { font-size: .85rem; }
        .toc a { font-size: .85rem; color: #007bff; text-decoration: none; }
        .toc a:hover { text-decoration: underline; }
        .step { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 1.5rem 2rem; margin-bottom: 1.5rem; }
        .step h2 { font-size: 1.25rem; margin-bottom: .75rem; padding-bottom: .5rem; border-bottom: 2px solid #007bff; display: inline-block; }
        .step-body { font-size: .95rem; line-height: 1.6; }
        .step-body p { margin-bottom: .75rem; }
        .step-body ul, .step-body ol { margin-bottom: .75rem; padding-left: 1.5rem; }
        .step-body li { margin-bottom: .3rem; }
        .step-body table { margin: .75rem 0; }
        .widget-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .widget-table th, .widget-table td { padding: .5rem .75rem; border: 1px solid #eee; text-align: left; vertical-align: top; }
        .widget-table th { background: #f8f9fa; font-weight: 600; }
        .tip { background: #e7f3ff; border-left: 4px solid #007bff; padding: .75rem 1rem; border-radius: 4px; font-size: .88rem; margin: .75rem 0; }
        .panel-layout-ill { display: flex; gap: .5rem; margin: .75rem 0; }
        .panel-ill { flex: 1; text-align: center; padding: 1rem .5rem; border-radius: 6px; font-size: .8rem; }
        .panel-ill.left { background: #1e1e2d; color: #fff; }
        .panel-ill.center { background: #fff; border: 2px solid #333; color: #333; }
        .panel-ill.right { background: #f0f0f0; color: #333; }
        .ill-flow { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; background: #f8f9fa; padding: .75rem 1rem; border-radius: 6px; font-size: .85rem; }
        .ill-flow span { white-space: nowrap; }
        .ill-flow span:nth-child(even) { font-size: 1.2rem; color: #007bff; }
        .ill-preview { margin: .75rem 0; }
        kbd { background: #f0f0f0; border: 1px solid #ccc; border-radius: 3px; padding: .1rem .4rem; font-size: .8rem; font-family: inherit; }
        .tutorial-footer { text-align: center; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .tutorial-footer p { margin-bottom: 1rem; font-size: 1.05rem; }
    </style>
@endsection
