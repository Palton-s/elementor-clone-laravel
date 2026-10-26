@extends('page-builder.layouts.app')

@section('title', 'Projetos')

@section('content')
    <div class="container">
        <div class="pb-hero">
            <div class="pb-hero-orb pb-hero-orb-1"></div>
            <div class="pb-hero-orb pb-hero-orb-2"></div>
            <div class="pb-hero-orb pb-hero-orb-3"></div>
            <div class="pb-hero-content">
                <div>
                    <div class="pb-hero-badge">Central de projetos</div>
                    <h1 class="pb-hero-title">Meus Projetos</h1>
                    <p class="pb-hero-sub">Selecione um projeto para gerenciar páginas, temas e popups</p>
                </div>
                <div style="display:flex;gap:.6rem;align-items:center;position:relative;z-index:2">
                    <button onclick="document.getElementById('create-project-modal').style.display='flex'" class="pb-hero-btn-ghost" style="display:flex;align-items:center;gap:.4rem">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Novo Projeto
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
                    <span class="pb-stat-number">{{ $projects->count() }}</span>
                    <span class="pb-stat-label">Projetos</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $projects->sum('pages_count') }}</span>
                    <span class="pb-stat-label">Páginas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $projects->sum('theme_templates_count') }}</span>
                    <span class="pb-stat-label">Temas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $projects->sum('popups_count') }}</span>
                    <span class="pb-stat-label">Popups</span>
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

        @if($projects->count())
            <div class="pb-pages-grid" style="margin-top:1.5rem">
                @foreach($projects as $project)
                    <a href="{{ route('page-builder.projects.dashboard', $project) }}" class="pb-page-card" style="text-decoration:none">
                        <div class="pb-card-preview">
                            <div class="pb-card-preview-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <span class="pb-card-preview-edit">
                                Abrir Projeto
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </span>
                        </div>
                        <div class="pb-card-body">
                            <div class="pb-card-title-row">
                                <h3 class="pb-card-title">{{ $project->name }}</h3>
                                <div style="display:flex;gap:.4rem;align-items:center">
                                    @if($project->is_default)
                                        <span class="badge badge-published">padrão</span>
                                    @endif
                                    @unless($project->is_default)
                                        <button class="pb-card-more" type="button" onclick="event.preventDefault(); event.stopPropagation(); deleteProject({{ $project->id }}, '{{ addslashes($project->name) }}')" title="Excluir projeto">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    @endunless
                                </div>
                            </div>
                            @if($project->description)
                                <span class="pb-card-slug">{{ Str::limit($project->description, 80) }}</span>
                            @else
                                <span class="pb-card-slug">Sem descrição</span>
                            @endif
                        </div>
                        <div class="pb-card-footer">
                            <div class="pb-card-status">
                                <span class="pb-card-date">{{ $project->pages_count }} páginas • {{ $project->theme_templates_count }} temas • {{ $project->popups_count }} popups</span>
                            </div>
                            <span class="pb-card-edit-btn">Abrir</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">&#128193;</div>
                <h2>Nenhum projeto</h2>
                <p>Crie seu primeiro projeto para organizar páginas, temas e popups.</p>
                <button onclick="document.getElementById('create-project-modal').style.display='flex'" class="btn btn-primary" style="margin-top:1rem">Criar primeiro projeto</button>
            </div>
        @endif
    </div>

    <div id="create-project-modal" class="modal-overlay" style="display:none">
        <div class="modal-content" style="max-width:520px">
            <div class="modal-header">
                <h3>Criar novo projeto</h3>
                <button class="modal-close" onclick="document.getElementById('create-project-modal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="project-name">Nome do projeto <span class="required">*</span></label>
                    <input type="text" id="project-name" class="form-control" placeholder="Ex.: Site Institucional">
                </div>
                <div class="form-group">
                    <label for="project-location">Local do projeto <span class="optional">(opcional)</span></label>
                    <input type="text" id="project-location" class="form-control" placeholder="Ex.: D:\Meus Projetos\Site-Institucional">
                    <small style="color:#94a3b8;font-size:.75rem;display:block;margin-top:.35rem">Campo apenas informativo.</small>
                </div>
                <div class="form-group">
                    <label for="project-description">Descrição <span class="optional">(opcional)</span></label>
                    <textarea id="project-description" class="form-control" rows="3" placeholder="Descreva o objetivo deste projeto"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="document.getElementById('create-project-modal').style.display='none'">Cancelar</button>
                <button class="btn btn-primary" onclick="createProject()">Criar projeto</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF = '{{ csrf_token() }}';

        function createProject() {
            const name = document.getElementById('project-name').value.trim();
            const location = document.getElementById('project-location').value.trim();
            const description = document.getElementById('project-description').value.trim();
            if (!name) { alert('Informe um nome para o projeto'); return; }

            fetch('{{ route('page-builder.projects.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ name, location, description }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.project) { window.location.href = '/page-builder/projects/' + data.project.id + '/dashboard'; }
                else { alert(data.message || 'Erro ao criar projeto'); }
            })
            .catch(() => alert('Erro ao criar projeto'));
        }

        function deleteProject(id, name) {
            if (!confirm('Excluir o projeto "' + name + '"? Os itens serão movidos para o projeto padrão.')) return;
            fetch('/page-builder/projects/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => location.reload())
            .catch(() => alert('Erro ao excluir projeto'));
        }
    </script>
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
@endsection

