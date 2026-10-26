@extends('page-builder.layouts.app')

@section('title', 'Temas')

@section('content')
<div class="container">

    <div class="pb-hero">
        <div class="pb-hero-orb pb-hero-orb-1"></div>
        <div class="pb-hero-orb pb-hero-orb-2"></div>
        <div class="pb-hero-orb pb-hero-orb-3"></div>
        <div class="pb-hero-content">
            <div>
                <div class="pb-hero-badge">Construtor de temas</div>
                <h1 class="pb-hero-title">Temas do Site</h1>
                <p class="pb-hero-sub">Crie e gerencie modelos de cabeçalho, rodapé e layouts para personalizar o site</p>
            </div>
            <a href="{{ route('page-builder.themes.create') }}" class="pb-hero-btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Novo modelo
            </a>
        </div>
        <div class="pb-stats">
            <div class="pb-stat">
                <span class="pb-stat-number">{{ $templates->count() }}</span>
                <span class="pb-stat-label">Total</span>
            </div>
            <div class="pb-stat-divider"></div>
            <div class="pb-stat">
                <span class="pb-stat-number pb-stat-published">{{ $templates->where('status','published')->count() }}</span>
                <span class="pb-stat-label">Publicados</span>
            </div>
            <div class="pb-stat-divider"></div>
            <div class="pb-stat">
                <span class="pb-stat-number pb-stat-draft">{{ $templates->where('status','draft')->count() }}</span>
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

    @php
        $typeOrder = ['header', 'footer', 'single', 'archive', '404', 'search_results'];
        $typeLabels = [
            'header' => ['label' => 'Header', 'desc' => 'Topo do site com logo, menu e navegação'],
            'footer' => ['label' => 'Footer', 'desc' => 'Rodapé do site com copyright e links'],
            'single' => ['label' => 'Single', 'desc' => 'Layout para páginas individuais'],
            'archive' => ['label' => 'Archive', 'desc' => 'Listagem de páginas e posts'],
            '404' => ['label' => 'Página 404', 'desc' => 'Página personalizada para erro 404'],
            'search_results' => ['label' => 'Busca', 'desc' => 'Layout para resultados de busca'],
        ];
        $typeIcons = [
            'header' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>',
            'footer' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="21" x2="9" y2="15"/></svg>',
            'single' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
            'archive' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>',
            '404' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
            'search_results' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        ];
    @endphp

    @if($templates->isEmpty())
        <div class="pb-empty">
            <div class="pb-empty-visual">
                <div class="pb-empty-circle">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="color:#6366f1"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
            </div>
            <h2 class="pb-empty-title">Nenhum modelo ainda</h2>
            <p class="pb-empty-text">Crie modelos de cabeçalho, rodapé e layouts para personalizar a aparência do seu site com o construtor visual.</p>
            <a href="{{ route('page-builder.themes.create') }}" class="pb-empty-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Criar o primeiro modelo
            </a>
        </div>
    @else
        <div class="pb-toolbar">
            <div class="pb-search">
                <svg class="pb-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="search-input" placeholder="Buscar modelos..." class="pb-search-input">
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

        @foreach($typeOrder as $type)
            @php $info = $typeLabels[$type]; @endphp
            @if(isset($grouped[$type]) && $grouped[$type]->isNotEmpty())
                <div class="pb-type-section" data-type="{{ $type }}">
                    <div class="pb-type-header">
                        <span class="pb-type-icon">{!! $typeIcons[$type] !!}</span>
                        <h2 class="pb-type-title">{{ $info['label'] }}</h2>
                        <span class="pb-type-count">{{ $grouped[$type]->count() }}</span>
                        <p class="pb-type-desc">{{ $info['desc'] }}</p>
                        <a href="{{ route('page-builder.themes.create') }}?type={{ $type }}" class="pb-type-add" title="Criar {{ $info['label'] }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </a>
                    </div>

                    <div class="pb-type-grid">
                        @foreach($grouped[$type] as $template)
                            @php
                                $conditionLabels = collect($template->conditions ?? [])->map(function($c) {
                                    $labels = \App\Services\PageBuilder\Theme\ThemeService::conditionOptions();
                                    $t = $c['type'] ?? '';
                                    $label = $labels[$t] ?? $t;
                                    if ($t === 'specific' && !empty($c['value'])) $label .= ': ' . $c['value'];
                                    return $label;
                                })->filter()->implode(', ');
                                if (empty($template->conditions)) $conditionLabels = 'Todas as páginas';
                            @endphp
                            <div class="pb-page-card" data-title="{{ strtolower($template->title) }}">
                                <div class="pb-card-preview">
                                    <div class="pb-card-preview-icon">{!! $typeIcons[$type] !!}</div>
                                    <a href="{{ route('page-builder.themes.editor', $template) }}" class="pb-card-preview-edit">
                                        Abrir no Editor
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    </a>
                                </div>
                                <div class="pb-card-body">
                                    <div class="pb-card-title-row">
                                        <h3 class="pb-card-title">{{ $template->title }}</h3>
                                        <div class="pb-card-dropdown">
                                            <button class="pb-card-more" onclick="toggleDropdown(this)" title="Mais opções">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                            </button>
                                            <div class="pb-dropdown-menu">
                                                <a href="{{ route('page-builder.themes.editor', $template) }}" class="pb-dropdown-item">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    Editar
                                                </a>
                                                <a href="{{ route('page-builder.themes.conditions', $template) }}" class="pb-dropdown-item">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                                    Condições
                                                </a>
                                                <a href="{{ route('page-builder.themes.render', $template) }}?t={{ time() }}" class="pb-dropdown-item" target="_blank">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    Visualizar
                                                </a>
                                                <div class="pb-dropdown-divider"></div>
                                                @if($template->isDraft())
                                                    <form action="{{ route('page-builder.themes.publish', $template) }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <button type="submit" class="pb-dropdown-item">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                            Publicar
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('page-builder.themes.unpublish', $template) }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <button type="submit" class="pb-dropdown-item">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                                                            Despublicar
                                                        </button>
                                                    </form>
                                                @endif
                                                <div class="pb-dropdown-divider"></div>
                                                <form action="{{ route('page-builder.themes.destroy', $template) }}" method="POST" style="display:inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="pb-dropdown-item pb-dropdown-danger" onclick="return confirm('Excluir &quot;{{ $template->title }}&quot;?')">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                        Excluir
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="pb-card-slug">{{ $info['label'] }}</span>
                                </div>
                                <div class="pb-card-footer">
                                    <div class="pb-card-status">
                                        <span class="badge badge-{{ $template->status === 'published' ? 'published' : 'draft' }}">
                                            {{ $template->status === 'published' ? 'Publicado' : 'Rascunho' }}
                                        </span>
                                        <span class="pb-card-date" title="{{ $template->updated_at->format('d/m/Y H:i') }}">
                                            {{ $template->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div style="font-size:.68rem;color:#94a3b8;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $conditionLabels }}">
                                        {{ $conditionLabels }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif
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

.pb-toolbar { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.5rem; }
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

.pb-type-section { margin-bottom: 2.5rem; }
.pb-type-header {
    display: flex; align-items: center; gap: .6rem; margin-bottom: 1rem;
    padding-bottom: .6rem; border-bottom: 1px solid #e2e8f0;
}
.pb-type-icon {
    display: flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 10px;
    background: linear-gradient(135deg, #eef2ff, #e0e7ff); color: #6366f1;
    flex-shrink: 0;
}
.pb-type-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
.pb-type-count {
    font-size: .7rem; font-weight: 600; color: #6366f1;
    background: #eef2ff; padding: .05rem .5rem; border-radius: 8px;
    line-height: 1.6;
}
.pb-type-desc {
    font-size: .78rem; color: #94a3b8; margin-left: auto;
    display: none;
}
.pb-type-header:hover .pb-type-desc { display: block; }
.pb-type-add {
    display: flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 6px;
    color: #94a3b8; transition: all .15s; flex-shrink: 0;
}
.pb-type-add:hover { background: #eef2ff; color: #6366f1; }
.pb-type-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;
}

.pb-page-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    display: flex; flex-direction: column; transition: all .25s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
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
    .pb-type-grid { grid-template-columns: 1fr; }
    .pb-toolbar { flex-direction: column; align-items: stretch; }
    .pb-search { max-width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
    const csrf = '{{ csrf_token() }}';

    function setView(mode) {
        document.querySelectorAll('.pb-view-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.pb-view-btn[data-view="${mode}"]`).classList.add('active');
    }

    function toggleDropdown(btn) {
        const menu = btn.nextElementSibling;
        document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => { if (m !== menu) m.classList.remove('show'); });
        menu.classList.toggle('show');
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.pb-card-dropdown')) {
            document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => m.classList.remove('show'));
        }
    });

    document.getElementById('search-input')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.pb-type-section').forEach(section => {
            let visible = 0;
            section.querySelectorAll('.pb-page-card').forEach(card => {
                const title = card.dataset.title || '';
                const match = title.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            section.style.display = visible > 0 ? '' : 'none';
        });
    });
</script>
@endpush
@endsection