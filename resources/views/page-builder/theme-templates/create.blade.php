@extends('page-builder.layouts.app')

@section('title', isset($themeTemplate) ? 'Editar modelo do tema' : 'Novo modelo do tema')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>{{ isset($themeTemplate) ? 'Editar modelo do tema' : 'Novo modelo do tema' }}</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">Defina o tipo do modelo, onde ele aparece e a página com o conteúdo</p>
        </div>
        <a href="{{ route('page-builder.themes.index') }}" class="btn btn-secondary">
            &larr; Back
        </a>
    </div>

    <div class="card">
        <form action="{{ isset($themeTemplate) ? route('page-builder.themes.update', $themeTemplate) : route('page-builder.themes.store') }}" method="POST">
            @csrf
            @if(isset($themeTemplate)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label for="title">Título <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="{{ old('title', $themeTemplate->title ?? '') }}"
                           placeholder="Ex: Cabeçalho principal, Rodapé do blog">
                </div>

                <div class="form-group" style="flex:1">
                    <label for="type">Tipo <span class="required">*</span></label>
                    <select id="type" name="type" class="form-control form-select" required>
                        <option value="">Selecione o tipo...</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ (old('type', $themeTemplate->type ?? request('type', '')) === $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!isset($themeTemplate))
            <div class="form-group">
                <label for="template">Modelo inicial <span class="optional">(pre-preenche o conteúdo)</span></label>
                <select id="template" name="template" class="form-control form-select">
                    <option value="">-- Em branco (sem conteúdo) --</option>
                    @foreach($themeTemplates as $key => $tmpl)
                        <option value="{{ $key }}" {{ request('template') === $key ? 'selected' : '' }}>
                            {{ $tmpl['name'] }} — {{ $tmpl['description'] }}
                        </option>
                    @endforeach
                </select>
                <p style="font-size:.78rem;color:#94a3b8;margin-top:.35rem">
                    Um conteúdo básico é inserido automaticamente no editor. Só se aplica quando uma nova página é criada.
                </p>
            </div>
            @endif

            <div class="form-group">
                <label for="page_id">Página com o conteúdo <span class="optional">(opcional)</span></label>
                <select id="page_id" name="page_id" class="form-control form-select">
                    <option value="">-- Criar uma página em branco --</option>
                    @foreach($pages as $p)
                        <option value="{{ $p->id }}" {{ old('page_id', $themeTemplate->page_id ?? '') == $p->id ? 'selected' : '' }}>
                            {{ $p->title }}
                        </option>
                    @endforeach
                </select>
                <p style="font-size:.78rem;color:#94a3b8;margin-top:.35rem">
                    Se nenhuma página for escolhida, uma nova é criada automaticamente.
                </p>
            </div>

            @if(!isset($themeTemplate))
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control form-select">
                    <option value="draft" selected>Rascunho</option>
                    <option value="published">Publicado</option>
                </select>
            </div>
            @endif

            <div style="display:flex;gap:.75rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0">
                <button type="submit" class="btn btn-primary">
                    {{ isset($themeTemplate) ? 'Salvar modelo' : 'Criar modelo' }}
                </button>
                @if(!isset($themeTemplate))
                    <button type="submit" name="next" value="editor" class="btn btn-primary" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
                        Criar e abrir o editor
                    </button>
                @endif
                <a href="{{ route('page-builder.themes.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@if(!isset($themeTemplate))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const templateSelect = document.getElementById('template');
    const starterTypes = @json(array_keys($themeTemplates));

    typeSelect.addEventListener('change', function() {
        if (!this.value) return;
        if (starterTypes.includes(this.value)) {
            templateSelect.value = this.value;
        }
    });
});
</script>
@endpush
@endif
@endsection
