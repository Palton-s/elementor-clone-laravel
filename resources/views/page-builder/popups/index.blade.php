@extends('page-builder.layouts.app')

@section('title', 'Popups')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Popups</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">Gerenciar popups, modais e notificações do site</p>
        </div>
        <a href="{{ route('page-builder.popups.create') }}" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Novo Popup
        </a>
    </div>

    @if(session('success'))
        <div class="toast toast-success" id="session-toast">
            <span>{{ session('success') }}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @php
        $typeLabels = [
            'popup' => ['label' => 'Popup', 'icon' => '💬', 'desc' => 'Popup modal centralizado'],
            'modal' => ['label' => 'Modal', 'icon' => '🪟', 'desc' => 'Janela modal personalizada'],
        ];
    @endphp

    @if($popups->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">💬</div>
            <h2>Nenhum popup criado</h2>
            <p>Crie popups e modais para engajar visitantes com promocoes, avisos e formularios.</p>
            <a href="{{ route('page-builder.popups.create') }}" class="btn btn-primary">Criar Primeiro Popup</a>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1rem">
            @foreach($popups as $popup)
                @php
                    $info = $typeLabels[$popup->type] ?? ['label' => ucfirst($popup->type), 'icon' => '📄', 'desc' => ''];
                    $triggerLabels = collect($popup->triggers ?? [])->map(function($t) {
                        $types = \App\Models\Popup::triggerTypes();
                        return $types[$t['type']]['label'] ?? $t['type'];
                    })->filter()->implode(', ');
                    if (empty($popup->triggers)) $triggerLabels = 'Nenhum gatilho';
                @endphp
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.04);display:flex;flex-direction:column;gap:.65rem">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start">
                        <div>
                            <div style="font-weight:600;color:#0f172a;font-size:.95rem">{{ $popup->title }}</div>
                            <div style="display:flex;gap:.35rem;margin-top:.25rem;flex-wrap:wrap">
                                <span class="badge {{ $popup->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $popup->status }}</span>
                                <span style="background:#eef2ff;color:#6366f1;padding:.1rem .45rem;border-radius:4px;font-size:.7rem;font-weight:500">{{ $info['label'] }}</span>
                            </div>
                        </div>
                        <div style="display:flex;gap:.25rem">
                            <a href="{{ route('page-builder.popups.editor', $popup) }}" class="btn btn-sm btn-info" title="Editar no construtor">✏️</a>
                            <a href="{{ route('page-builder.popups.render', $popup) }}?t={{ time() }}" target="_blank" class="btn btn-sm btn-secondary" title="Visualizar">👁️</a>
                            <form action="{{ route('page-builder.popups.destroy', $popup) }}" method="POST" style="display:inline" onsubmit="return confirm('Excluir este popup?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir">🗑️</button>
                            </form>
                        </div>
                    </div>
                    <div style="font-size:.78rem;color:#64748b;display:flex;flex-direction:column;gap:.15rem">
                        <span>Exibir: <strong>{{ $triggerLabels }}</strong></span>
                        <span>Updated: {{ $popup->updated_at->diffForHumans() }}</span>
                    </div>
                    <div style="display:flex;gap:.35rem;margin-top:.25rem">
                        @if($popup->isDraft())
                            <form action="{{ route('page-builder.popups.publish', $popup) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">📢 Publicar</button>
                            </form>
                        @else
                            <form action="{{ route('page-builder.popups.unpublish', $popup) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary">⏸️ Despublicar</button>
                            </form>
                        @endif
                        <a href="{{ route('page-builder.popups.edit', $popup) }}" class="btn btn-sm btn-secondary" title="Configurações">⚙️</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@if(session('success'))
<script>
    setTimeout(() => { const t = document.getElementById('session-toast'); if(t) t.remove(); }, 5000);
</script>
@endif
@endsection
