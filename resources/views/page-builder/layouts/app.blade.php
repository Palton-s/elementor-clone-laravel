<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Page Builder') - CEAD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1e293b; min-height: 100vh; }
        a { text-decoration: none; }
        button { font-family: inherit; }

        .navbar {
            background: rgba(255,255,255,.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0; padding: 0 2rem; height: 60px;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 50;
        }
        .navbar-brand a { font-weight: 700; color: #0f172a; font-size: 1.15rem; letter-spacing: -.5px; display: flex; align-items: center; gap: .6rem; }
        .navbar-brand .unb-logo { height: 28px; width: auto; display: block; }
        .navbar-brand .brand-text { font-size: 1.1rem; font-weight: 700; color: #0f172a; letter-spacing: -.3px; }
        .navbar-brand .brand-unb { font-size: .65rem; font-weight: 600; color: #64748b; background: #f1f5f9; padding: .1rem .45rem; border-radius: 4px; letter-spacing: .3px; }
        .navbar-menu { display: flex; align-items: center; gap: .25rem; }
        .nav-link {
            text-decoration: none; color: #64748b; padding: .45rem .75rem; border-radius: 6px;
            font-size: .875rem; font-weight: 500; transition: all .15s;
        }
        .nav-link:hover { color: #0f172a; background: #f1f5f9; }
        .nav-link-primary {
            background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff !important; padding: .45rem 1rem;
            border-radius: 6px; font-weight: 600; box-shadow: 0 1px 3px rgba(99,102,241,.3);
        }
        .nav-link-primary:hover { background: linear-gradient(135deg,#4f46e5,#7c3aed) !important; box-shadow: 0 4px 12px rgba(99,102,241,.4) !important; transform: translateY(-1px); }

        .account-chip {
            display: flex; align-items: center; gap: .5rem; margin-right: .25rem;
            padding: .3rem .6rem; border-radius: 8px;
            background: #f8fafc; border: 1px solid #e2e8f0;
        }
        .account-chip-avatar {
            width: 26px; height: 26px; border-radius: 50%;
            background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .7rem; font-weight: 700;
        }
        .account-chip-name { font-size: .82rem; font-weight: 600; color: #0f172a; }
        .account-chip-role {
            font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
            padding: .15rem .5rem; border-radius: 20px;
        }
        .account-chip-role-admin { background: #dcfce7; color: #166534; }
        .account-chip-role-editor { background: #e0e7ff; color: #3730a3; }
        .account-chip-role-viewer { background: #fef9c3; color: #854d0e; }

        .main-content { padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }

        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;
        }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; letter-spacing: -.5px; }

        .btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .45rem 1rem; border-radius: 6px; text-decoration: none;
            font-size: .875rem; font-weight: 500; border: 1px solid transparent;
            cursor: pointer; transition: all .15s; white-space: nowrap;
        }
        .btn-primary { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; box-shadow: 0 1px 3px rgba(99,102,241,.3); }
        .btn-primary:hover { box-shadow: 0 4px 12px rgba(99,102,241,.4); transform: translateY(-1px); }
        .btn-success { background: #22c55e; color: #fff; }
        .btn-success:hover { background: #16a34a; }
        .btn-secondary { background: #fff; color: #475569; border-color: #e2e8f0; }
        .btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: .3rem .6rem; font-size: .8rem; border-radius: 5px; }
        .btn-info { background: #fff; color: #6366f1; border-color: #e0e7ff; }
        .btn-info:hover { background: #eef2ff; border-color: #6366f1; }

        .card {
            background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.03);
            border: 1px solid #e2e8f0; padding: 1.5rem;
        }

        .table-wrapper {
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
            overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .table { width: 100%; border-collapse: collapse; }
        .table th {
            padding: .75rem 1rem; text-align: left; font-weight: 600; font-size: .78rem;
            color: #64748b; text-transform: uppercase; letter-spacing: .5px;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        .table td { padding: .8rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: .875rem; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr { transition: background .15s; }
        .table tbody tr:hover { background: #f8fafc; }
        .th-actions, .td-actions { text-align: right; }
        .td-title { font-weight: 600; color: #0f172a; }
        .td-date { white-space: nowrap; color: #94a3b8; font-size: .8rem; }

        code {
            background: #f1f5f9; padding: .1rem .45rem; border-radius: 4px;
            font-size: .8rem; color: #475569; font-family: ui-monospace, monospace;
        }

        .badge {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .15rem .55rem; border-radius: 20px; font-size: .72rem;
            font-weight: 600; text-transform: capitalize;
        }
        .badge::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; }
        .badge-draft { background: #fef3c7; color: #92400e; }
        .badge-draft::before { background: #f59e0b; }
        .badge-published { background: #dcfce7; color: #166534; }
        .badge-published::before { background: #22c55e; }

        .actions { display: flex; gap: .35rem; flex-wrap: wrap; justify-content: flex-end; }
        .inline-form { display: inline; }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: .35rem; font-size: .875rem; color: #334155; }
        .form-control {
            width: 100%; padding: .6rem .75rem; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: .9rem; transition: all .2s; background: #fff;
        }
        .form-control:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .form-select { cursor: pointer; appearance: auto; }
        .form-row { display: flex; gap: 1rem; }
        .form-row .form-group { flex: 1; }

        .error { color: #ef4444; font-size: .82rem; margin-top: .3rem; }
        .required { color: #ef4444; }
        .optional { color: #94a3b8; font-weight: 400; font-size: .82rem; }

        .toast {
            position: fixed; top: 1.5rem; right: 1.5rem;
            padding: .85rem 1.2rem; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,.1);
            display: flex; align-items: center; gap: .75rem; z-index: 1000;
            animation: slideInToast .3s cubic-bezier(.16,1,.3,1);
            font-size: .875rem; font-weight: 500;
        }
        .toast-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .toast-close {
            background: none; border: none; font-size: 1.3rem; cursor: pointer;
            color: inherit; margin-left: auto; padding: 0 .2rem; opacity: .6; transition: opacity .15s;
        }
        .toast-close:hover { opacity: 1; }
        @keyframes slideInToast { from { transform: translateX(100%) scale(.95); opacity: 0; } to { transform: translateX(0) scale(1); opacity: 1; } }

        .empty-state {
            text-align: center; padding: 4rem 2rem; background: #fff;
            border-radius: 12px; border: 1px solid #e2e8f0;
        }
        .empty-icon { font-size: 3.5rem; margin-bottom: 1rem; opacity: .5; }
        .empty-state h2 { font-size: 1.25rem; margin-bottom: .5rem; color: #0f172a; }
        .empty-state p { color: #64748b; margin-bottom: 1.5rem; }

        .pagination { margin-top: 1.5rem; }
        .pagination nav > div { display: flex; justify-content: center; gap: .25rem; }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 .5rem;
            border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none;
            color: #475569; background: #fff; font-size: .85rem; font-weight: 500; transition: all .15s;
        }
        .pagination a:hover { background: #f8fafc; border-color: #cbd5e1; }
        .pagination span[aria-current] { background: #6366f1; color: #fff; border-color: #6366f1; }

        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(4px); animation: fadeIn .2s;
        }
        .modal-content {
            background: #fff; border-radius: 16px; width: 90%; max-width: 480px;
            box-shadow: 0 16px 48px rgba(0,0,0,.15); animation: modalIn .25s cubic-bezier(.16,1,.3,1);
        }
        .modal-header {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; align-items: center;
        }
        .modal-header h3 { font-size: 1.05rem; font-weight: 600; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; transition: color .15s; padding: 0 .2rem; }
        .modal-close:hover { color: #475569; }
        .modal-body { padding: 1.5rem; }
        .modal-body input[type="file"] { margin-top: .5rem; }
        .modal-footer {
            padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0;
            display: flex; justify-content: flex-end; gap: .75rem;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes modalIn { from { transform: scale(.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="{{ route('page-builder.pages.index') }}">
                <svg class="unb-logo" viewBox="0 0 1200 600" xmlns="http://www.w3.org/2000/svg">
                    <path d="M543.64 122.52c-32 23.07-66.98 43.06-104.68 59.37-18.63 8.06-38.71 13.63-59.85 16.27-15.04 1.88-30.35 2.83-45.88 2.83v123.27h308.17V51.67l-97.75 70.6-0.01-0.01" fill="#008940"/>
                    <path d="M105.19 122.52c32.01 23.07 66.99 43.06 104.69 59.37 18.63 8.06 38.7 13.63 59.86 16.27 15.03 1.88 30.34 2.83 45.88 2.83v123.27H7.43V51.67l97.75 70.6 0.01-0.01" fill="#008940"/>
                    <path d="M333.22 7.27v176.1c14.79 0 29.38-0.91 43.69-2.68 19.45-2.44 37.91-7.56 55.04-14.97 36.52-15.79 70.38-35.14 101.37-57.49l108.07-78.05V7.27H333.22" fill="#133e79"/>
                    <path d="M315.62 7.27v176.1c-14.8 0-29.38-0.91-43.69-2.68-19.46-2.44-37.92-7.56-55.05-14.97-36.51-15.79-70.37-35.14-101.37-57.49L7.43 30.18V7.27h308.19" fill="#133e79"/>
                </svg>
                <span class="brand-text">CEAD</span>
                <span class="brand-unb">UNB</span>
            </a>
        </div>
        <div class="navbar-menu">
            <a href="{{ route('tutorial') }}" target="_blank" class="nav-link">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                Tutorial
            </a>
            <a href="{{ route('page-builder.pages.index') }}" class="nav-link">Páginas</a>
            <a href="{{ route('page-builder.themes.index') }}" class="nav-link">Temas</a>
            <a href="{{ route('page-builder.projects.index') }}" class="nav-link">Projetos</a>
            <a href="{{ route('page-builder.pages.create') }}" class="nav-link nav-link-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nova Página
            </a>
            @if(auth()->check())
                <div class="account-chip" title="{{ auth()->user()->email }}">
                    <span class="account-chip-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="account-chip-name">{{ auth()->user()->name }}</span>
                    <span class="account-chip-role account-chip-role-{{ auth()->user()->role }}">{{ auth()->user()->role }}</span>
                </div>
            @endif
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="nav-link" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-weight:500">Sair</button>
            </form>
        </div>
    </nav>

    <main class="main-content">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
