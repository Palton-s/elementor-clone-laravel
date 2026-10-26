@extends('page-builder.layouts.app')

@section('title', 'Onde exibir - ' . $themeTemplate->title)

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Onde exibir: {{ $themeTemplate->title }}</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">
                Defina em quais páginas este modelo de <strong>{{ $themeTemplate->getTypeLabel() }}</strong> aparece
            </p>
        </div>
        <div style="display:flex;gap:.5rem">
            <a href="{{ route('page-builder.themes.editor', $themeTemplate) }}" class="btn btn-info">✏️ Editar</a>
            <a href="{{ route('page-builder.themes.index') }}" class="btn btn-secondary">&larr; Back</a>
        </div>
    </div>

    <div class="card" style="max-width:700px">
        <div id="conditions-app">
            <div style="display:flex;flex-direction:column;gap:.75rem" id="conditions-list">
            </div>

            <div style="display:flex;gap:.5rem;margin-top:1rem">
                <select id="condition-type" class="form-control form-select" style="flex:1">
                    <option value="">Selecione onde exibir...</option>
                </select>
                <div id="condition-value-wrap" style="display:none;flex:1">
                    <select id="condition-value" class="form-control form-select">
                        <option value="">Selecione a página...</option>
                    </select>
                </div>
                <button id="add-condition" class="btn btn-secondary" disabled>+ Adicionar</button>
            </div>

            <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0;display:flex;gap:.75rem">
                <button id="save-conditions" class="btn btn-primary">Salvar</button>
                <span id="save-status" style="font-size:.82rem;color:#64748b;align-self:center"></span>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem">
        <div class="card" style="background:#f8fafc;padding:1rem">
            <h3 style="font-size:.9rem;font-weight:600;margin-bottom:.5rem">Como funciona</h3>
            <ul style="font-size:.82rem;color:#64748b;line-height:1.8;padding-left:1.25rem">
                <li><strong>Site inteiro</strong> — aparece em todas as páginas</li>
                <li><strong>Página inicial</strong> — aparece só na página inicial</li>
                <li><strong>Todas as páginas</strong> — aparece em todas as páginas de conteúdo</li>
                <li><strong>Todas as páginas individuais</strong> — aparece em todas as páginas e artigos individuais</li>
                <li><strong>Página específica</strong> — aparece só na página escolhida</li>
            </ul>
            <p style="font-size:.78rem;color:#94a3b8;margin-top:.5rem">
                Com várias regras, basta UMA delas valer para o modelo ser usado.
                Se mais de um modelo servir, vale o primeiro da lista.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const conditionsList = document.getElementById('conditions-list');
    const conditionType = document.getElementById('condition-type');
    const conditionValueWrap = document.getElementById('condition-value-wrap');
    const conditionValue = document.getElementById('condition-value');
    const addBtn = document.getElementById('add-condition');
    const saveBtn = document.getElementById('save-conditions');
    const saveStatus = document.getElementById('save-status');

    let conditions = [];
    let conditionOptions = {};
    let availablePages = [];

    function fetchData() {
        fetch('{{ route('page-builder.themes.conditions.data', $themeTemplate) }}')
            .then(r => r.json())
            .then(data => {
                conditions = data.conditions || [];
                conditionOptions = data.options || {};
                availablePages = data.pages || [];
                renderTypeOptions();
                renderConditions();
            });
    }

    function renderTypeOptions() {
        conditionType.innerHTML = '<option value="">Selecione onde exibir...</option>';
        for (const [key, label] of Object.entries(conditionOptions)) {
            const opt = document.createElement('option');
            opt.value = key;
            opt.textContent = label;
            conditionType.appendChild(opt);
        }

        conditionValue.innerHTML = '<option value="">Selecione a página...</option>';
        availablePages.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.slug;
            opt.textContent = p.title;
            conditionValue.appendChild(opt);
        });
    }

    function renderConditions() {
        if (conditions.length === 0) {
            conditionsList.innerHTML = '<div style="padding:1rem;text-align:center;color:#94a3b8;font-size:.85rem;background:#f8fafc;border-radius:8px">Nenhuma regra — o modelo aparece em todas as páginas</div>';
            return;
        }

        conditionsList.innerHTML = '';
        conditions.forEach((cond, idx) => {
            const typeLabel = conditionOptions[cond.type] || cond.type;
            const valueLabel = cond.type === 'specific' ? (': ' + (cond.value || '')) : '';
            const displayLabel = typeLabel + valueLabel;

            const card = document.createElement('div');
            card.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:.6rem .75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px';

            const icon = document.createElement('span');
            icon.textContent = '✅';
            icon.style.fontSize = '.9rem';

            const label = document.createElement('span');
            label.style.cssText = 'flex:1;font-size:.85rem;font-weight:500;color:#1e293b';
            label.textContent = displayLabel;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = '×';
            removeBtn.style.cssText = 'background:none;border:none;font-size:1.2rem;color:#ef4444;cursor:pointer;padding:0 .25rem';
            removeBtn.onclick = () => {
                conditions.splice(idx, 1);
                renderConditions();
            };

            card.appendChild(icon);
            card.appendChild(label);
            card.appendChild(removeBtn);
            conditionsList.appendChild(card);
        });
    }

    conditionType.addEventListener('change', function() {
        const showValue = this.value === 'specific';
        conditionValueWrap.style.display = showValue ? 'block' : 'none';
        addBtn.disabled = !this.value;
    });

    addBtn.addEventListener('click', function() {
        const type = conditionType.value;
        if (!type) return;

        const value = type === 'specific' ? conditionValue.value : null;
        conditions.push({ type, value });
        renderConditions();

        conditionType.value = '';
        conditionValueWrap.style.display = 'none';
        addBtn.disabled = true;
    });

    saveBtn.addEventListener('click', function() {
        saveBtn.disabled = true;
        saveStatus.textContent = 'Salvando...';

        fetch('{{ route('page-builder.themes.conditions.update', $themeTemplate) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ conditions }),
        })
        .then(r => r.json())
        .then(data => {
            saveStatus.textContent = '✅ ' + (data.message || 'Saved!');
            conditions = data.conditions || [];
            setTimeout(() => { saveStatus.textContent = ''; }, 3000);
        })
        .catch(err => {
            saveStatus.textContent = '❌ Erro: ' + err.message;
        })
        .finally(() => {
            saveBtn.disabled = false;
        });
    });

    fetchData();
});
</script>
@endpush
@endsection
