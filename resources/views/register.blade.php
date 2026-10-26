<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; padding: 1rem;
        }
        .auth-container { width: 100%; max-width: 420px; }
        .auth-brand { text-align: center; margin-bottom: 2rem; }
        .auth-brand-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 48px; height: 48px; background: linear-gradient(135deg,#6366f1,#8b5cf6);
            border-radius: 14px; margin-bottom: .75rem;
        }
        .auth-brand-icon svg { width: 24px; height: 24px; color: #fff; }
        .auth-brand h1 { color: #f1f5f9; font-size: 1.25rem; font-weight: 700; }
        .auth-brand p { color: #64748b; font-size: .875rem; margin-top: .25rem; }
        .card {
            background: rgba(30,41,59,.6); backdrop-filter: blur(16px);
            border: 1px solid rgba(99,102,241,.15); padding: 2rem;
            border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,.3);
        }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: .4rem; font-weight: 500; font-size: .875rem; color: #cbd5e1; }
        input {
            width: 100%; padding: .65rem .8rem;
            background: rgba(15,23,42,.6); border: 1px solid rgba(99,102,241,.15);
            border-radius: 10px; color: #f1f5f9; font-size: .9rem;
            transition: all .2s; font-family: inherit;
        }
        input::placeholder { color: #475569; }
        input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        button[type="submit"] {
            width: 100%; padding: .65rem; background: linear-gradient(135deg,#6366f1,#8b5cf6);
            color: #fff; border: none; border-radius: 10px; font-size: .95rem;
            font-weight: 600; cursor: pointer; transition: all .2s; font-family: inherit;
        }
        button[type="submit"]:hover { box-shadow: 0 4px 16px rgba(99,102,241,.4); transform: translateY(-1px); }
        .error {
            background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2);
            color: #fca5a5; padding: .65rem .8rem; border-radius: 10px;
            margin-bottom: 1rem; font-size: .85rem;
        }
        .error div { margin-bottom: .2rem; }
        .error div:last-child { margin-bottom: 0; }
        .auth-footer { text-align: center; margin-top: 1.25rem; font-size: .875rem; color: #64748b; }
        .auth-footer a { color: #818cf8; text-decoration: none; font-weight: 500; transition: color .15s; }
        .auth-footer a:hover { color: #a5b4fc; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <h1>{{ config('app.name') }}</h1>
            <p>Crie sua conta gratuita</p>
        </div>
        <div class="card">
            @if($errors->any())
                <div class="error">
                    @foreach($errors->all() as $error)
                        <div>&#9888; {{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Seu nome" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="seu@email.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" name="password" id="password" placeholder="Mínimo de 8 caracteres" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Senha</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Repita a senha" required>
                </div>
                <button type="submit">Criar Conta</button>
            </form>
            <div class="auth-footer">
                Já tem conta? <a href="{{ route('login') }}">Entrar</a>
            </div>
        </div>
    </div>
</body>
</html>
