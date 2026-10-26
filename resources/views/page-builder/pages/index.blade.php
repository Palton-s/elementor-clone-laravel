@extends('page-builder.layouts.app')

@section('title', 'Páginas')

@section('content')
    <div class="container">

        <div class="pb-hero">
            <div class="pb-hero-orb pb-hero-orb-1"></div>
            <div class="pb-hero-orb pb-hero-orb-2"></div>
            <div class="pb-hero-orb pb-hero-orb-3"></div>
            <div class="pb-hero-content">
                <div>
                    <div class="pb-hero-badge">Construtor de páginas</div>
                    <h1 class="pb-hero-title">Suas Páginas</h1>
                    <p class="pb-hero-sub">Gerencie, importe e publique suas páginas no construtor visual</p>
                </div>
                <div style="display:flex;gap:.6rem;align-items:center;position:relative;z-index:2">
                    @if(isset($projects) && $projects->count())
                        <select onchange="if(this.value){window.location='{{ route('page-builder.pages.index') }}?project='+this.value}else{window.location='{{ route('page-builder.pages.index') }}'}"
                                class="pb-hero-btn-ghost" style="padding:.5rem .75rem;cursor:pointer">
                            <option value="">Todos os projetos</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" @selected(($projectId ?? null) == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button onclick="openHtmlImportModal()" class="pb-hero-btn-ghost">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Importar HTML
                    </button>
                </div>
            </div>
            <div class="pb-account">
                <span class="pb-account-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="pb-account-meta">
                    <span class="pb-account-name">{{ auth()->user()->name }}</span>
                    <span class="pb-account-email">{{ auth()->user()->email }}</span>
                </div>
                <span class="pb-account-role pb-account-role-{{ auth()->user()->role }}">{{ auth()->user()->role }}</span>
            </div>
            <div class="pb-stats">
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $pages->total() }}</span>
                    <span class="pb-stat-label">Total</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number pb-stat-published">{{ $pages->getCollection()->where('status','published')->count() }}</span>
                    <span class="pb-stat-label">Publicadas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number pb-stat-draft">{{ $pages->getCollection()->where('status','draft')->count() }}</span>
                    <span class="pb-stat-label">Rascunhos</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="toast toast-success" id="success-toast">
                <span>&#10003;</span>
                <span>{{ session('success') }}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        @if($pages->count())
            <div class="pb-toolbar">
                <div class="pb-search">
                    <svg class="pb-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="search-input" placeholder="Buscar páginas..." class="pb-search-input">
                </div>
                <div class="pb-view-toggle">
                    <button class="pb-view-btn active" data-view="grid" onclick="setView('grid')" title="Grade">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    </button>
                    <button class="pb-view-btn" data-view="list" onclick="setView('list')" title="Lista">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </button>
                </div>
            </div>

            <div id="pages-grid" class="pb-pages-grid">
                @foreach($pages as $page)
                    <div class="pb-page-card" data-title="{{ strtolower($page->title) }}" data-slug="{{ strtolower($page->slug) }}">
                        <div class="pb-card-preview">
                            <div class="pb-card-preview-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            </div>
                            <a href="{{ route('page-builder.editor', $page) }}" class="pb-card-preview-edit">
                                Abrir no Editor
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                        <div class="pb-card-body">
                            <div class="pb-card-title-row">
                                <h3 class="pb-card-title">{{ $page->title }}</h3>
                                <div class="pb-card-dropdown">
                                    <button class="pb-card-more" onclick="toggleDropdown(this)" title="Mais opções">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                    </button>
                                    <div class="pb-dropdown-menu">
                                        <a href="{{ route('page-builder.editor', $page) }}" class="pb-dropdown-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Editar
                                        </a>
                                        <a href="{{ route('page-builder.render', $page) }}" class="pb-dropdown-item" target="_blank">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            Visualizar
                                        </a>
                                        <button class="pb-dropdown-item" onclick="duplicatePage({{ $page->id }})">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                            Duplicar
                                        </button>
                                        <div class="pb-dropdown-divider"></div>
                                        <a href="{{ route('page-builder.pages.export', $page) }}" class="pb-dropdown-item" download>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Exportar JSON
                                        </a>
                                        <button class="pb-dropdown-item" onclick="copyHtml({{ $page->id }})">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                            Copiar HTML
                                        </button>
                                        <button class="pb-dropdown-item" onclick="openImportModal({{ $page->id }})">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            Importar JSON
                                        </button>
                                        <div class="pb-dropdown-divider"></div>
                                        <form action="{{ route('page-builder.pages.destroy', $page) }}" method="POST" class="inline-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="pb-dropdown-item pb-dropdown-danger" onclick="return confirm('Excluir &quot;{{ $page->title }}&quot;?')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <span class="pb-card-slug">/{{ $page->slug }}</span>
                        </div>
                        <div class="pb-card-footer">
                            <div class="pb-card-status">
                                <span class="badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}">
                                    {{ $page->status === 'published' ? 'Publicada' : 'Rascunho' }}
                                </span>
                                <span class="pb-card-date" title="{{ $page->updated_at->format('d/m/Y H:i') }}">
                                    {{ $page->updated_at->diffForHumans() }}
                                </span>
                            </div>
                            <a href="{{ route('page-builder.editor', $page) }}" class="pb-card-edit-btn">
                                Editar
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="pages-list" class="pb-pages-list" style="display:none">
                @foreach($pages as $page)
                    <div class="pb-list-row" data-title="{{ strtolower($page->title) }}" data-slug="{{ strtolower($page->slug) }}">
                        <div class="pb-list-info">
                            <div class="pb-list-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div class="pb-list-text">
                                <span class="pb-list-title">{{ $page->title }}</span>
                                <div class="pb-list-meta">
                                    <span class="badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}" style="font-size:.65rem">
                                        {{ $page->status === 'published' ? 'Publicada' : 'Rascunho' }}
                                    </span>
                                    <span class="pb-list-slug">/{{ $page->slug }}</span>
                                    <span class="pb-card-date" style="white-space:nowrap">{{ $page->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="pb-list-actions">
                            <a href="{{ route('page-builder.editor', $page) }}" class="btn btn-sm btn-primary">Editar</a>
                            <a href="{{ route('page-builder.render', $page) }}" class="btn btn-sm btn-secondary" target="_blank">Ver</a>
                            <div class="pb-card-dropdown">
                                <button class="pb-card-more" onclick="toggleDropdown(this)" title="Mais opções">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                </button>
                                <div class="pb-dropdown-menu">
                                    <button class="pb-dropdown-item" onclick="duplicatePage({{ $page->id }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        Duplicar
                                    </button>
                                    <a href="{{ route('page-builder.pages.export', $page) }}" class="pb-dropdown-item" download>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        Exportar JSON
                                    </button>
                                    <button class="pb-dropdown-item" onclick="copyHtml({{ $page->id }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        Copiar HTML
                                    </button>
                                    <div class="pb-dropdown-divider"></div>
                                    <form action="{{ route('page-builder.pages.destroy', $page) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pb-dropdown-item pb-dropdown-danger" onclick="return confirm('Excluir &quot;{{ $page->title }}&quot;?')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pagination">
                {{ $pages->links() }}
            </div>
        @else
            <div class="pb-empty">
                <div class="pb-empty-visual">
                    <div class="pb-empty-circle">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="color:#6366f1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <h2 class="pb-empty-title">Nenhuma página ainda</h2>
                <p class="pb-empty-text">Crie sua primeira página para começar a construir conteúdo visual com o page builder.</p>
                <a href="{{ route('page-builder.pages.create') }}" class="pb-empty-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Criar Primeira Página
                </a>
            </div>
        @endif
    </div>

    <div id="import-modal" style="display:none">
        <div class="modal-overlay" onclick="closeImportModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>Importar Página</h3>
                    <button class="modal-close" onclick="closeImportModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="color:#64748b;margin-bottom:1rem">Selecione um arquivo .json exportado de outra página para importar.</p>
                    <input type="file" id="import-file" accept=".json" style="display:block;padding:.5rem;border:2px dashed #e2e8f0;border-radius:8px;width:100%;cursor:pointer">
                    <div id="import-error" style="color:#ef4444;margin-top:.5rem;display:none;font-size:.85rem"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeImportModal()">Cancelar</button>
                    <button class="btn btn-primary" onclick="importPage()" id="import-btn">Importar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="html-import-modal" style="display:none">
        <div class="modal-overlay" onclick="closeHtmlImportModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width:700px">
                <div class="modal-header">
                    <h3>Importar HTML de Site Externo</h3>
                    <button class="modal-close" onclick="closeHtmlImportModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="color:#64748b;margin-bottom:1rem;font-size:.9rem">
                        Cole o HTML de qualquer página web ou informe a URL. O conteúdo será convertido automaticamente em widgets editáveis.
                    </p>

                    <div style="margin-bottom:1rem">
                        <label style="display:block;font-weight:600;margin-bottom:.4rem;font-size:.875rem;color:#374151">URL do Site <span style="color:#94a3b8;font-weight:400">(opcional)</span></label>
                        <div style="display:flex;gap:.5rem">
                            <input type="url" id="html-import-url" placeholder="https://exemplo.com/pagina" style="flex:1;padding:.5rem .75rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem">
                            <button type="button" class="btn btn-secondary" onclick="fetchUrlHtml()" id="html-fetch-btn" style="white-space:nowrap;font-size:.85rem">Buscar</button>
                        </div>
                        <div id="html-fetch-status" style="font-size:.8rem;margin-top:.3rem;display:none"></div>
                    </div>

                    <div style="text-align:center;margin-bottom:.75rem;font-size:.8rem;color:#94a3b8">— ou cole o HTML diretamente —</div>

                    <div style="margin-bottom:1rem">
                        <label style="display:block;font-weight:600;margin-bottom:.4rem;font-size:.875rem;color:#374151">HTML do Conteúdo</label>
                        <textarea id="html-import-content" rows="12" placeholder="&lt;h1&gt;Titulo da Pagina&lt;/h1&gt;&#10;&lt;p&gt;Conteudo aqui...&lt;/p&gt;&#10;&lt;img src=&quot;...&quot;&gt;" style="width:100%;padding:.65rem;border:1px solid #e2e8f0;border-radius:8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.82rem;resize:vertical;line-height:1.5;background:#f8fafc;color:#1e293b"></textarea>
                    </div>

                    <div style="margin-bottom:.5rem">
                        <label style="display:block;font-weight:600;margin-bottom:.4rem;font-size:.875rem;color:#374151">Título da Página <span style="color:#94a3b8;font-weight:400">(opcional — detectado automaticamente)</span></label>
                        <input type="text" id="html-import-title" placeholder="Ex: Página Importada" style="width:100%;padding:.5rem .75rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem">
                    </div>

                    <div id="html-import-error" style="color:#ef4444;margin-top:.5rem;display:none;font-size:.85rem;padding:.5rem;background:#fef2f2;border-radius:6px"></div>
                    <div id="html-import-success" style="color:#166534;margin-top:.5rem;display:none;font-size:.85rem;padding:.5rem;background:#f0fdf4;border-radius:6px"></div>
                </div>
                <div class="modal-footer" style="display:flex;gap:.5rem;justify-content:flex-end">
                    <button class="btn btn-secondary" onclick="closeHtmlImportModal()">Cancelar</button>
                    <button class="btn btn-primary" onclick="submitHtmlImport()" id="html-import-btn">Importar e Editar</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .pb-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
            border-radius: 16px; padding: 1.75rem 2rem 1.5rem; margin-bottom: 1.5rem;
            color: #fff; position: relative; overflow: hidden;
        }
        .pb-hero-orb {
            position: absolute; border-radius: 50%; filter: blur(60px); opacity: .15;
            animation: orbFloat 8s ease-in-out infinite alternate;
        }
        .pb-hero-orb-1 { width: 300px; height: 300px; background: #818cf8; top: -100px; right: -50px; animation-delay: 0s; }
        .pb-hero-orb-2 { width: 200px; height: 200px; background: #a78bfa; bottom: -80px; left: 10%; animation-delay: 2s; }
        .pb-hero-orb-3 { width: 150px; height: 150px; background: #c4b5fd; top: 20%; right: 30%; animation-delay: 4s; opacity: .1; }
        @keyframes orbFloat {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(10px, -10px) scale(1.05); }
        }
        .pb-hero-content { display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
        .pb-hero-badge {
            display: inline-block; font-size: .65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .6rem;
            background: rgba(255,255,255,.08); padding: .25rem .7rem; border-radius: 20px;
            border: 1px solid rgba(255,255,255,.1);
        }
        .pb-hero-title {
            font-size: 1.6rem; font-weight: 800; letter-spacing: -.5px; margin-bottom: .25rem;
            background: linear-gradient(135deg, #fff 30%, #c4b5fd 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .pb-hero-sub { font-size: .85rem; opacity: .7; font-weight: 400; }
        .pb-hero-btn-ghost {
            display: inline-flex; align-items: center; gap: .45rem;
            background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.2);
            padding: .55rem 1.1rem; border-radius: 8px; font-size: .82rem; font-weight: 500;
            cursor: pointer; transition: all .2s; backdrop-filter: blur(8px);
        }
        .pb-hero-btn-ghost:hover {
            background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.35);
            transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,.2);
        }
        .pb-stats {
            display: flex; align-items: center; gap: 1.25rem; margin-top: 1.25rem;
            position: relative; z-index: 1;
        }
        .pb-stat { display: flex; flex-direction: column; }
        .pb-stat-number { font-size: 1.3rem; font-weight: 700; line-height: 1; }
        .pb-stat-label { font-size: .68rem; opacity: .55; margin-top: .2rem; text-transform: uppercase; letter-spacing: .8px; font-weight: 500; }
        .pb-stat-divider { width: 1px; height: 32px; background: rgba(255,255,255,.15); }
        .pb-stat-published { color: #86efac; }
        .pb-stat-draft { color: #fcd34d; }
        .pb-account {
            display: flex; align-items: center; gap: .6rem; margin-top: 1rem;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
            padding: .5rem .8rem; border-radius: 10px; width: fit-content; position: relative; z-index: 1;
        }
        .pb-account-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg,#a78bfa,#818cf8);
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .pb-account-meta { display: flex; flex-direction: column; line-height: 1.25; }
        .pb-account-name { font-size: .85rem; font-weight: 600; color: #fff; }
        .pb-account-email { font-size: .72rem; color: rgba(255,255,255,.55); }
        .pb-account-role {
            font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
            padding: .2rem .6rem; border-radius: 20px; margin-left: .35rem;
        }
        .pb-account-role-admin { background: rgba(134,239,172,.15); color: #86efac; border: 1px solid rgba(134,239,172,.3); }
        .pb-account-role-editor { background: rgba(129,140,248,.15); color: #c7d2fe; border: 1px solid rgba(129,140,248,.3); }
        .pb-account-role-viewer { background: rgba(253,224,71,.12); color: #fde047; border: 1px solid rgba(253,224,71,.25); }

        .pb-toolbar { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
        .pb-search { position: relative; flex: 1; max-width: 360px; }
        .pb-search-icon {
            position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none;
        }
        .pb-search-input {
            width: 100%; padding: .55rem .75rem .55rem 2.4rem; border: 1px solid #e2e8f0; border-radius: 10px;
            font-size: .85rem; background: #fff; transition: all .2s; color: #1e293b;
        }
        .pb-search-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .pb-search-input::placeholder { color: #94a3b8; }
        .pb-view-toggle { display: flex; gap: 2px; background: #f1f5f9; border-radius: 8px; padding: 3px; }
        .pb-view-btn {
            padding: .4rem; border: none; border-radius: 6px; cursor: pointer;
            background: transparent; color: #94a3b8; transition: all .15s;
            display: flex; align-items: center; justify-content: center;
        }
        .pb-view-btn.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

        .pb-pages-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
        .pb-page-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            display: flex; flex-direction: column; transition: all .25s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            position: relative; z-index: 1;
        }
        .pb-page-card.dropdown-open { z-index: 100; }
        .pb-page-card:hover {
            border-color: #c7d2fe; box-shadow: 0 8px 30px rgba(99,102,241,.12);
            transform: translateY(-3px);
        }
        .pb-card-preview {
            height: 100px; background: linear-gradient(135deg, #f1f5f9 0%, #e8ecf1 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border-radius: 13px 13px 0 0; position: relative; overflow: hidden;
            transition: all .25s;
        }
        .pb-card-preview::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 40%, rgba(99,102,241,.06) 0%, transparent 60%);
        }
        .pb-card-preview-icon { color: #cbd5e1; transition: all .25s; position: relative; z-index: 1; }
        .pb-page-card:hover .pb-card-preview-icon { color: #a5b4fc; transform: scale(1.1); }
        .pb-card-preview-edit {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; text-align: center; padding: .45rem; font-size: .75rem;
            font-weight: 600; display: flex; align-items: center; justify-content: center; gap: .35rem;
            transform: translateY(100%); transition: transform .25s cubic-bezier(.4,0,.2,1);
        }
        .pb-page-card:hover .pb-card-preview-edit { transform: translateY(0); }
        .pb-card-body { flex: 1; padding: 1rem 1.25rem .5rem; }
        .pb-card-title-row { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; }
        .pb-card-title {
            font-size: 1rem; font-weight: 600; color: #0f172a; line-height: 1.4;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;
        }
        .pb-card-slug {
            font-size: .75rem; color: #94a3b8; font-family: ui-monospace, monospace;
            display: block; margin-top: .2rem;
        }
        .pb-card-footer {
            display: flex; justify-content: space-between; align-items: center;
            padding: .7rem 1.25rem; border-top: 1px solid #f1f5f9;
        }
        .pb-card-status { display: flex; align-items: center; gap: .6rem; }
        .pb-card-date { font-size: .72rem; color: #94a3b8; }
        .pb-card-edit-btn {
            display: inline-flex; align-items: center; gap: .3rem;
            font-size: .78rem; font-weight: 600; color: #6366f1;
            text-decoration: none; padding: .3rem .65rem; border-radius: 6px;
            transition: all .15s;
        }
        .pb-card-edit-btn:hover { background: #eef2ff; }

        .pb-card-dropdown { position: relative; }
        .pb-card-more {
            background: none; border: none; color: #94a3b8;
            cursor: pointer; padding: .3rem; border-radius: 6px; transition: all .15s;
            display: flex; align-items: center;
        }
        .pb-card-more:hover { background: #f1f5f9; color: #475569; }
        .pb-dropdown-menu {
            display: none; position: absolute; top: 100%; right: 0; z-index: 30;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,.12); min-width: 200px; padding: .35rem;
        }
        .pb-dropdown-menu.show { display: block; }
        .pb-dropdown-item {
            display: flex; align-items: center; gap: .5rem; width: 100%;
            padding: .5rem .75rem; border: none; background: none; cursor: pointer;
            font-size: .82rem; color: #475569; border-radius: 8px; text-align: left;
            text-decoration: none; transition: background .1s; font-family: inherit;
        }
        .pb-dropdown-item:hover { background: #f1f5f9; }
        .pb-dropdown-danger { color: #ef4444; }
        .pb-dropdown-danger:hover { background: #fef2f2; }
        .pb-dropdown-divider { border: none; border-top: 1px solid #f1f5f9; margin: .25rem 0; }

        .pb-pages-list { display: flex; flex-direction: column; gap: .5rem; }
        .pb-list-row {
            display: flex; justify-content: space-between; align-items: center;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: .85rem 1.25rem; transition: all .2s; gap: 1.5rem;
            position: relative; z-index: 1;
        }
        .pb-list-row:hover { border-color: #c7d2fe; box-shadow: 0 2px 12px rgba(99,102,241,.06); }
        .pb-list-row.dropdown-open { z-index: 100; }
        .pb-list-info { display: flex; align-items: center; gap: .75rem; flex: 1; }
        .pb-list-icon {
            width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9;
            display: flex; align-items: center; justify-content: center; color: #6366f1;
            flex-shrink: 0;
        }
        .pb-list-text { flex: 1 1 auto; }
        .pb-list-title {
            font-weight: 600; font-size: .92rem; color: #0f172a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;
        }
        .pb-list-meta { display: flex; align-items: center; gap: .5rem; margin-top: .2rem; flex-wrap: nowrap; overflow: hidden; min-width: 0; }
        .pb-list-slug {
            font-size: .72rem; color: #94a3b8; font-family: ui-monospace, monospace;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
            max-width: 200px;
        }
        .pb-list-actions { display: flex; align-items: center; gap: .25rem; flex-shrink: 0; white-space: nowrap; }

        .pb-empty {
            text-align: center; padding: 4rem 2rem; background: #fff;
            border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .pb-empty-visual { margin-bottom: 1.5rem; }
        .pb-empty-circle {
            display: inline-flex; align-items: center; justify-content: center;
            width: 100px; height: 100px; border-radius: 50%;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            transition: transform .3s;
        }
        .pb-empty-circle:hover { transform: scale(1.05); }
        .pb-empty-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .5rem; }
        .pb-empty-text { color: #64748b; margin-bottom: 1.5rem; max-width: 380px; margin-left: auto; margin-right: auto; line-height: 1.6; font-size: .9rem; }
        .pb-empty-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .6rem 1.5rem; font-size: .9rem; font-weight: 600;
            background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;
            border-radius: 10px; text-decoration: none;
            box-shadow: 0 2px 8px rgba(99,102,241,.3);
            transition: all .2s;
        }
        .pb-empty-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,.35); }

        @media (max-width: 768px) {
            .pb-hero { padding: 1.5rem; border-radius: 14px; }
            .pb-hero-content { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .pb-hero-title { font-size: 1.5rem; }
            .pb-hero-btn-ghost { width: 100%; justify-content: center; }
            .pb-stats { gap: 1rem; }
            .pb-stat-number { font-size: 1.3rem; }
            .pb-pages-grid { grid-template-columns: 1fr; }
            .pb-toolbar { flex-direction: column; align-items: stretch; }
            .pb-search { max-width: 100%; }
            .pb-list-row { flex-direction: column; align-items: flex-start; gap: .75rem; }
            .pb-list-actions { width: 100%; justify-content: flex-end; }
            .pb-list-meta { flex-wrap: wrap; }
            .pb-list-slug { white-space: normal; }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        const csrf = '{{ csrf_token() }}';

        function setView(mode) {
            document.querySelectorAll('.pb-view-btn').forEach(b => b.classList.remove('active'));
            document.querySelector(`.pb-view-btn[data-view="${mode}"]`).classList.add('active');
            document.getElementById('pages-grid').style.display = mode === 'grid' ? '' : 'none';
            document.getElementById('pages-list').style.display = mode === 'list' ? '' : 'none';
        }

        function closeDropdowns(except) {
            document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => {
                if (m === except) return;
                m.classList.remove('show');
                const container = m.closest('.pb-page-card, .pb-list-row');
                if (container) container.classList.remove('dropdown-open');
            });
        }

        function toggleDropdown(btn) {
            const menu = btn.nextElementSibling;
            const container = btn.closest('.pb-page-card, .pb-list-row');
            closeDropdowns(menu);
            menu.classList.toggle('show');
            if (container) container.classList.toggle('dropdown-open', menu.classList.contains('show'));
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.pb-card-dropdown')) {
                closeDropdowns();
            }
        });

        document.getElementById('search-input')?.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#pages-grid .pb-page-card, #pages-list .pb-list-row').forEach(card => {
                const title = card.dataset.title || '';
                const slug = card.dataset.slug || '';
                card.style.display = (title.includes(q) || slug.includes(q)) ? '' : 'none';
            });
        });

        function duplicatePage(id) {
            closeDropdowns();
            fetch(`/page-builder/pages/${id}/duplicate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
            })
            .then(r => r.json())
            .then(() => { showToast('Página duplicada!'); location.reload(); })
            .catch(() => showToast('Falha ao duplicar', true));
        }

        function copyHtml(id) {
            closeDropdowns();
            fetch(`/page-builder/pages/${id}/render?format=inner`)
                .then(r => r.text())
                .then(html => {
                    navigator.clipboard.writeText(html).then(() => {
                        showToast('HTML copiado!');
                    }).catch(() => {
                        const ta = document.createElement('textarea');
                        ta.value = html;
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();
                        showToast('HTML copiado!');
                    });
                })
                .catch(() => showToast('Falha ao copiar HTML', true));
        }

        function showToast(msg, isError) {
            const t = document.createElement('div');
            t.className = 'toast ' + (isError ? 'toast-error' : 'toast-success');
            t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;animation:slideInToast .3s cubic-bezier(.16,1,.3,1);padding:.75rem 1.25rem;border-radius:10px;font-size:.875rem;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.1);display:flex;align-items:center;gap:.5rem';
            if (isError) {
                t.style.background = '#fef2f2'; t.style.color = '#991b1b'; t.style.border = '1px solid #fecaca';
            } else {
                t.style.background = '#dcfce7'; t.style.color = '#166534'; t.style.border = '1px solid #bbf7d0';
            }
            t.textContent = (isError ? '\u274C ' : '\u2705 ') + msg;
            document.body.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }, 3000);
        }

        let importPageId = null;

        function openImportModal(id) {
            closeDropdowns();
            importPageId = id || null;
            document.getElementById('import-modal').style.display = '';
            document.getElementById('import-file').value = '';
            document.getElementById('import-error').style.display = 'none';
            document.getElementById('import-btn').disabled = false;
        }

        function closeImportModal() {
            document.getElementById('import-modal').style.display = 'none';
            importPageId = null;
        }

        function importPage() {
            const fileInput = document.getElementById('import-file');
            const file = fileInput.files[0];
            if (!file) {
                document.getElementById('import-error').textContent = 'Selecione um arquivo.';
                document.getElementById('import-error').style.display = '';
                return;
            }
            const btn = document.getElementById('import-btn');
            btn.disabled = true;
            btn.textContent = 'Importando...';
            const reader = new FileReader();
            reader.onload = function(e) {
                let data;
                try { data = JSON.parse(e.target.result); } catch {
                    document.getElementById('import-error').textContent = 'Arquivo JSON inválido.';
                    document.getElementById('import-error').style.display = '';
                    btn.disabled = false; btn.textContent = 'Importar';
                    return;
                }
                fetch('/page-builder/pages/import', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ data }),
                })
                .then(r => r.json())
                .then(() => { showToast('Página importada!'); location.reload(); })
                .catch(() => {
                    document.getElementById('import-error').textContent = 'Falha na importação.';
                    document.getElementById('import-error').style.display = '';
                    btn.disabled = false; btn.textContent = 'Importar';
                });
            };
            reader.readAsText(file);
        }

        function openHtmlImportModal() {
            document.getElementById('html-import-modal').style.display = '';
            document.getElementById('html-import-url').value = '';
            document.getElementById('html-import-content').value = '';
            document.getElementById('html-import-title').value = '';
            document.getElementById('html-import-error').style.display = 'none';
            document.getElementById('html-import-success').style.display = 'none';
            document.getElementById('html-fetch-status').style.display = 'none';
            document.getElementById('html-import-btn').disabled = false;
        }

        function closeHtmlImportModal() {
            document.getElementById('html-import-modal').style.display = 'none';
        }

        function fetchUrlHtml() {
            const url = document.getElementById('html-import-url').value.trim();
            if (!url) {
                document.getElementById('html-import-error').textContent = 'Informe uma URL válida.';
                document.getElementById('html-import-error').style.display = '';
                return;
            }
            const btn = document.getElementById('html-fetch-btn');
            const status = document.getElementById('html-fetch-status');
            btn.disabled = true;
            btn.textContent = 'Buscando...';
            status.style.display = '';
            status.style.color = '#64748b';
            status.textContent = 'Baixando conteúdo da URL...';

            fetch('/page-builder/html-import/fetch?url=' + encodeURIComponent(url), {
                headers: { 'X-CSRF-TOKEN': csrf }
            })
            .then(r => r.json())
            .then(data => {
                if (data.html) {
                    document.getElementById('html-import-content').value = data.html;
                    status.style.color = '#166534';
                    status.textContent = 'HTML baixado com sucesso (' + (data.size > 1024 ? Math.round(data.size/1024) + 'KB' : data.size + 'B') + '). Revise e clique em Importar.';
                    document.getElementById('html-import-error').style.display = 'none';
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
                btn.disabled = false;
                btn.textContent = 'Buscar';
            });
        }

        function submitHtmlImport() {
            const url = document.getElementById('html-import-url').value.trim();
            const html = document.getElementById('html-import-content').value.trim();
            const title = document.getElementById('html-import-title').value.trim();
            const errorEl = document.getElementById('html-import-error');
            const successEl = document.getElementById('html-import-success');

            errorEl.style.display = 'none';
            successEl.style.display = 'none';

            if (!url && !html) {
                errorEl.textContent = 'Forneça uma URL ou cole o HTML.';
                errorEl.style.display = '';
                return;
            }

            const btn = document.getElementById('html-import-btn');
            btn.disabled = true;
            btn.textContent = 'Importando...';

            const body = {};
            if (url) body.url = url;
            if (html) body.html = html;
            if (title) body.title = title;

            fetch('/page-builder/html-import', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(body),
            })
            .then(r => r.text().then(text => ({ status: r.status, text })))
            .then(({ status, text }) => {
                let data;
                try { data = JSON.parse(text); } catch { data = null; }
                if (status >= 200 && status < 300 && data && data.redirect_url) {
                    successEl.textContent = 'Importação concluída! ' + (data.widgets_count || 0) + ' widgets criados. Redirecionando...';
                    successEl.style.display = '';
                    setTimeout(() => { window.location.href = data.redirect_url; }, 1200);
                } else {
                    const msg = data && data.message ? data.message : 'Erro HTTP ' + status;
                    errorEl.textContent = msg;
                    errorEl.style.display = '';
                    btn.disabled = false;
                    btn.textContent = 'Importar e Editar';
                }
            })
            .catch(err => {
                errorEl.textContent = 'Falha na comunicação: ' + (err.message || err);
                errorEl.style.display = '';
                btn.disabled = false;
                btn.textContent = 'Importar e Editar';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('success-toast');
            if (toast) {
                setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(() => toast.remove(), 300); }, 4000);
            }
        });
    </script>
    @endpush
@endsection
