@extends('page-builder.layouts.app')

@section('title', 'Quando exibir - ' . $popup->title)

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Quando exibir: {{ $popup->title }}</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">
                Defina quando este <strong>{{ $popup->type === 'modal' ? 'modal' : 'popup' }}</strong> deve aparecer
            </p>
        </div>
        <div style="display:flex;gap:.5rem">
            <a href="{{ route('page-builder.popups.editor', $popup) }}" class="btn btn-info">✏️ Editar</a>
            <a href="{{ route('page-builder.popups.index') }}" class="btn btn-secondary">&larr; Back</a>
        </div>
    </div>

    <div class="card" style="max-width:700px">
        <div id="triggers-app">
            <div style="display:flex;flex-direction:column;gap:.75rem" id="triggers-list">
            </div>

            <div style="display:flex;gap:.5rem;margin-top:1rem">
                <select id="trigger-type" class="form-control form-select" style="flex:1">
                    <option value="">Selecione quando exibir...</option>
                </select>
                <div id="trigger-value-wrap" style="display:none;flex:1">
                    <input type="text" id="trigger-value" class="form-control" placeholder="Valor">
                </div>
                <button id="add-trigger" class="btn btn-secondary" disabled>+ Adicionar</button>
            </div>

            <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0;display:flex;gap:.75rem">
                <button id="save-triggers" class="btn btn-primary">Salvar</button>
                <span id="save-status" style="font-size:.82rem;color:#64748b;align-self:center"></span>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem">
        <div class="card" style="background:#f8fafc;padding:1rem">
            <h3 style="font-size:.9rem;font-weight:600;margin-bottom:.5rem">Como funciona</h3>
            <ul style="font-size:.82rem;color:#64748b;line-height:1.8;padding-left:1.25rem">
                <li><strong>Ao carregar a página</strong> — o popup aparece assim que a página abre</li>
                <li><strong>Após um tempo</strong> — o popup aparece depois de alguns segundos</li>
                <li><strong>Ao rolar</strong> — o popup aparece quando a pessoa rola além de uma porcentagem da página</li>
                <li><strong>Intenção de saída</strong> — o popup aparece quando o mouse sai da janela (a pessoa vai fechar a página)</li>
                <li><strong>Ao clicar</strong> — o popup aparece quando a pessoa clica num elemento que corresponde a um seletor CSS</li>
            </ul>
            <p style="font-size:.78rem;color:#94a3b8;margin-top:.5rem">
                Com várias regras, basta UMA delas acontecer para o popup aparecer.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggersList = document.getElementById('triggers-list');
    const triggerType = document.getElementById('trigger-type');
    const triggerValueWrap = document.getElementById('trigger-value-wrap');
    const triggerValue = document.getElementById('trigger-value');
    const addBtn = document.getElementById('add-trigger');
    const saveBtn = document.getElementById('save-triggers');
    const saveStatus = document.getElementById('save-status');

    let triggers = [];
    let triggerTypes = {};

    function fetchData() {
        fetch('{{ route('page-builder.popups.triggers', $popup) }}')
            .then(r => r.json())
            .then(data => {
                triggers = data.triggers || [];
                triggerTypes = data.triggerTypes || {};
                renderTypeOptions();
                renderTriggers();
            });
    }

    function renderTypeOptions() {
        triggerType.innerHTML = '<option value="">Selecione quando exibir...</option>';
        for (const [key, config] of Object.entries(triggerTypes)) {
            const opt = document.createElement('option');
            opt.value = key;
            opt.textContent = config.label;
            opt.dataset.hasValue = config.has_value ? 'true' : 'false';
            opt.dataset.placeholder = config.placeholder || '';
            triggerType.appendChild(opt);
        }
    }

    function renderTriggers() {
        if (triggers.length === 0) {
            triggersList.innerHTML = '<div style="padding:1rem;text-align:center;color:#94a3b8;font-size:.85rem;background:#f8fafc;border-radius:8px">Nenhuma regra — o popup não vai aparecer sozinho</div>';
            return;
        }

        triggersList.innerHTML = '';
        triggers.forEach((t, idx) => {
            const config = triggerTypes[t.type] || {};
            const displayLabel = config.label + (t.value ? ': ' + t.value : '');

            const card = document.createElement('div');
            card.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:.6rem .75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px';

            const icon = document.createElement('span');
            icon.textContent = '⚡';
            icon.style.fontSize = '.9rem';

            const label = document.createElement('span');
            label.style.cssText = 'flex:1;font-size:.85rem;font-weight:500;color:#1e293b';
            label.textContent = displayLabel;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = '×';
            removeBtn.style.cssText = 'background:none;border:none;font-size:1.2rem;color:#ef4444;cursor:pointer;padding:0 .25rem';
            removeBtn.onclick = () => {
                triggers.splice(idx, 1);
                renderTriggers();
            };

            card.appendChild(icon);
            card.appendChild(label);
            card.appendChild(removeBtn);
            triggersList.appendChild(card);
        });
    }

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

    saveBtn.addEventListener('click', function() {
        saveBtn.disabled = true;
        saveStatus.textContent = 'Salvando...';

        fetch('{{ route('page-builder.popups.triggers.update', $popup) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ triggers }),
        })
        .then(r => r.json())
        .then(data => {
            saveStatus.textContent = '✅ ' + (data.message || 'Saved!');
            triggers = data.triggers || [];
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
