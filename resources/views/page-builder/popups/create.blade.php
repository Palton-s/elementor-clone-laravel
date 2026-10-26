@extends('page-builder.layouts.app')

@section('title', isset($popup) ? 'Editar popup' : 'Novo popup')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>{{ isset($popup) ? 'Editar popup' : 'Novo popup' }}</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">Defina o tipo do popup, quando ele aparece e o seu conteúdo</p>
        </div>
        <a href="{{ route('page-builder.popups.index') }}" class="btn btn-secondary">
            &larr; Back
        </a>
    </div>

    <div class="card">
        <form action="{{ isset($popup) ? route('page-builder.popups.update', $popup) : route('page-builder.popups.store') }}" method="POST">
            @csrf
            @if(isset($popup)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label for="title">Título <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="{{ old('title', $popup->title ?? '') }}"
                           placeholder="Ex: Newsletter Signup, Special Offer">
                </div>

                <div class="form-group" style="flex:1">
                    <label for="type">Tipo <span class="required">*</span></label>
                    <select id="type" name="type" class="form-control form-select" required>
                        <option value="">Selecione o tipo...</option>
                        @foreach($types as $key)
                            <option value="{{ $key }}" {{ (old('type', $popup->type ?? request('type', '')) === $key) ? 'selected' : '' }}>
                                {{ ucfirst($key) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!isset($popup))
            <div class="form-group">
                <label for="template">Modelo inicial <span class="optional">(pre-preenche o conteúdo)</span></label>
                <select id="template" name="template" class="form-control form-select">
                    <option value="">-- Em branco (sem conteúdo) --</option>
                    @foreach($popupTemplates as $key => $tmpl)
                        <option value="{{ $key }}" {{ request('template') === $key ? 'selected' : '' }}>
                            {{ $tmpl['name'] }} — {{ $tmpl['description'] }}
                        </option>
                    @endforeach
                </select>
                <p style="font-size:.78rem;color:#94a3b8;margin-top:.35rem">
                    O modelo inicial também define largura e posição padrão do popup.
                </p>
            </div>
            @endif

            @if(!isset($popup))
            <div class="form-group">
                <label>Quando exibir</label>
                <div id="triggers-list" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.75rem"></div>
                <div style="display:flex;gap:.5rem">
                    <select id="trigger-type" class="form-control form-select" style="flex:1">
                        <option value="">Selecione quando exibir...</option>
                        @foreach($triggerTypes as $key => $config)
                            <option value="{{ $key }}" data-has-value="{{ $config['has_value'] ? 'true' : 'false' }}" data-placeholder="{{ $config['placeholder'] ?? '' }}">
                                {{ $config['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="trigger-value-wrap" style="display:none;flex:1">
                        <input type="text" id="trigger-value" class="form-control" placeholder="Valor">
                    </div>
                    <button type="button" id="add-trigger" class="btn btn-secondary" disabled>+ Adicionar</button>
                </div>
            </div>
            @endif

            @if(!isset($popup))
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
                    {{ isset($popup) ? 'Salvar popup' : 'Criar popup' }}
                </button>
                @if(!isset($popup))
                    <button type="submit" name="next" value="editor" class="btn btn-primary" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
                        Criar e abrir o editor
                    </button>
                @endif
                <a href="{{ route('page-builder.popups.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@if(!isset($popup))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggersList = document.getElementById('triggers-list');
    const triggerType = document.getElementById('trigger-type');
    const triggerValueWrap = document.getElementById('trigger-value-wrap');
    const triggerValue = document.getElementById('trigger-value');
    const addBtn = document.getElementById('add-trigger');
    const form = document.querySelector('form');
    let triggers = [];

    triggerType.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const hasValue = opt.dataset.hasValue === 'true';
        triggerValueWrap.style.display = hasValue ? 'block' : 'none';
        if (hasValue) {
            triggerValue.placeholder = opt.dataset.placeholder || 'Valor';
        }
        addBtn.disabled = !this.value;
    });

    addBtn.addEventListener('click', function() {
        const type = triggerType.value;
        if (!type) return;
        const opt = triggerType.options[triggerType.selectedIndex];
        const hasValue = opt.dataset.hasValue === 'true';
        const value = hasValue ? triggerValue.value : null;
        triggers.push({ type, value });
        renderTriggers();
        triggerType.value = '';
        triggerValueWrap.style.display = 'none';
        triggerValue.value = '';
        addBtn.disabled = true;
    });

    function renderTriggers() {
        triggersList.innerHTML = '';
        if (triggers.length === 0) {
            triggersList.innerHTML = '<div style="padding:.5rem;text-align:center;color:#94a3b8;font-size:.82rem;background:#f8fafc;border-radius:6px">Nenhum gatilho configurado</div>';
            return;
        }
        triggers.forEach((t, idx) => {
            const opt = triggerType.querySelector(`option[value="${t.type}"]`);
            const label = opt ? opt.textContent : t.type;
            const displayLabel = label + (t.value ? ': ' + t.value : '');
            const card = document.createElement('div');
            card.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px';
            card.innerHTML = `
                <span>⚡</span>
                <span style="flex:1;font-size:.82rem;font-weight:500;color:#1e293b">${displayLabel}</span>
                <button type="button" class="remove-trigger" style="background:none;border:none;font-size:1.1rem;color:#ef4444;cursor:pointer;padding:0 .25rem">&times;</button>
            `;
            card.querySelector('.remove-trigger').onclick = () => {
                triggers.splice(idx, 1);
                renderTriggers();
            };
            triggersList.appendChild(card);
        });
    }

    form.addEventListener('submit', function() {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'triggers';
        input.value = JSON.stringify(triggers);
        form.appendChild(input);
    });

    renderTriggers();
});
</script>
@endpush
@endif
@endsection
