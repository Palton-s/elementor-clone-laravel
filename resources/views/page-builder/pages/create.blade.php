@extends('page-builder.layouts.app')

@section('title', 'Create Page')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Criar Nova Página</h1>
            <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">&larr; Voltar para Páginas</a>
        </div>

        <div class="card" style="max-width:700px">
            <form action="{{ route('page-builder.pages.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="title">Título <span class="required">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="Ex: Início, Sobre, Contato" required autofocus>
                    @error('title')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Situação</label>
                        <select name="status" id="status" class="form-control form-select">
                            <option value="draft">Rascunho</option>
                            <option value="published">Publicado</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Modelo <span class="optional">(opcional — pré-preenche a página)</span></label>
                    <div class="template-grid">
                        <label class="template-card {{ old('template') === '' || old('template') === null ? 'selected' : '' }}">
                            <input type="radio" name="template" value="" {{ old('template') === '' || old('template') === null ? 'checked' : '' }}>
                            <div class="template-card-preview blank">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                            </div>
                            <div class="template-card-info">
                                <strong>Página em Branco</strong>
                                <span>Começar do zero</span>
                            </div>
                        </label>
                        @foreach($templates as $key => $name)
                            <label class="template-card {{ old('template') === $key ? 'selected' : '' }}">
                                <input type="radio" name="template" value="{{ $key }}" {{ old('template') === $key ? 'checked' : '' }}>
                                <div class="template-card-preview {{ $key }}">
                                    @if($key === 'landing')
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12h18M3 6h18M3 18h12"/><circle cx="19" cy="18" r="2"/></svg>
                                    @elseif($key === 'about')
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M20 21c0-4.418-3.582-8-8-8s-8 3.582-8 8"/></svg>
                                    @elseif($key === 'contact')
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                    @elseif($key === 'moodle-course')
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                    @else
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                    @endif
                                </div>
                                <div class="template-card-info">
                                    <strong>{{ $name }}</strong>
                                    <span>
                                        @if($key === 'landing')Topo com chamada para ação
                                        @elseif($key === 'about')Apresentação da empresa
                                        @elseif($key === 'contact')Formulário de contato
                                        @elseif($key === 'showcase')Tema escuro futurista: topo com vídeo, galeria e carrossel, imagens ampliáveis, preços, depoimentos, animação, formulário, mapa e rodapé
                                        @elseif($key === 'moodle-course')Curso educacional completo para Moodle
                                        @else&#8205;@endif
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex;gap:.75rem;align-items:center;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0">
                    <button type="submit" name="_redirect" value="index" class="btn btn-secondary">Criar &amp; Voltar</button>
                    <button type="submit" name="_redirect" value="editor" class="btn btn-primary">Criar &amp; Abrir Editor</button>
                    <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary" style="margin-left:auto">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .template-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-top: .3rem; }
        .template-card {
            display: block; cursor: pointer; border: 2px solid #e2e8f0; border-radius: 10px;
            overflow: hidden; transition: all .2s; background: #fff;
        }
        .template-card:hover { border-color: #6366f1; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99,102,241,.12); }
        .template-card.selected { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.2); }
        .template-card input[type="radio"] { display: none; }
        .template-card-preview {
            height: 90px; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; background: #f8fafc; color: #64748b;
        }
        .template-card-preview.landing { background: linear-gradient(135deg,#1e1b4b,#312e81); color: #a5b4fc; }
        .template-card-preview.about { background: linear-gradient(135deg,#f0f9ff,#e0f2fe); color: #0369a1; }
        .template-card-preview.contact { background: linear-gradient(135deg,#f8fafc,#f1f5f9); color: #475569; }
        .template-card-preview.showcase { background: linear-gradient(135deg,#0f172a,#1e293b); color: #94a3b8; }
        .template-card-preview.moodle-course { background: linear-gradient(135deg,#1d3b5c,#2a5a84); color: #f39c12; }
        .template-card-info { padding: .65rem .75rem; }
        .template-card-info strong { display: block; font-size: .85rem; margin-bottom: .15rem; color: #0f172a; }
        .template-card-info span { font-size: .75rem; color: #64748b; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', function(e) {
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
    });
    </script>
@endsection
