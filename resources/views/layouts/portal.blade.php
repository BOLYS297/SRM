<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Portail SRM')</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|archivo:400,500,600" rel="stylesheet">
        <style>
            :root {
                --bg: #f4efe5;
                --bg-2: #e6f1ea;
                --ink: #1f2a28;
                --muted: #6a7a74;
                --accent: #ef6b3a;
                --accent-2: #1f7a6c;
                --surface: #fffaf2;
                --line: #e5d9c8;
                --shadow: 0 16px 40px rgba(26, 31, 28, 0.12);
                --radius: 18px;
                --font-display: "Space Grotesk", "Trebuchet MS", sans-serif;
                --font-body: "Archivo", "Trebuchet MS", sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                color: var(--ink);
                font-family: var(--font-body);
                background:
                    radial-gradient(1200px 420px at 85% -10%, rgba(31, 122, 108, 0.18), transparent 60%),
                    radial-gradient(800px 420px at 0% 10%, rgba(239, 107, 58, 0.14), transparent 60%),
                    linear-gradient(140deg, var(--bg), var(--bg-2));
                min-height: 100vh;
            }

            .ambient {
                position: fixed;
                inset: 0;
                pointer-events: none;
                background-image:
                    linear-gradient(transparent 31px, rgba(31, 122, 108, 0.06) 32px),
                    linear-gradient(90deg, transparent 31px, rgba(239, 107, 58, 0.06) 32px);
                background-size: 64px 64px;
                opacity: 0.35;
            }

            .topbar {
                position: sticky;
                top: 0;
                z-index: 10;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 18px 6vw;
                background: rgba(255, 250, 242, 0.9);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid var(--line);
            }

            .brand {
                display: flex;
                gap: 12px;
                align-items: center;
                font-family: var(--font-display);
                font-weight: 700;
                letter-spacing: -0.02em;
            }

            .logo {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                border-radius: 12px;
                background: linear-gradient(140deg, var(--accent), #f59f45);
                color: #fff;
                font-size: 18px;
                box-shadow: var(--shadow);
            }

            .brand small {
                display: block;
                font-size: 12px;
                color: var(--muted);
                font-weight: 500;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            .nav {
                display: flex;
                gap: 18px;
                font-weight: 600;
            }

            .nav a {
                text-decoration: none;
                color: var(--ink);
                padding: 8px 12px;
                border-radius: 999px;
                transition: background 0.2s ease, color 0.2s ease;
            }

            .nav a:hover {
                background: rgba(31, 122, 108, 0.12);
                color: var(--accent-2);
            }

            .auth {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .pill {
                border-radius: 999px;
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 600;
                background: rgba(31, 122, 108, 0.12);
                color: var(--accent-2);
            }

            .pill.offline {
                background: rgba(239, 107, 58, 0.15);
                color: #a53d1b;
            }

            .btn {
                border: none;
                cursor: pointer;
                padding: 10px 16px;
                border-radius: 12px;
                font-weight: 600;
                font-family: var(--font-body);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .btn.primary {
                background: linear-gradient(140deg, var(--accent), #f59f45);
                color: #fff;
                box-shadow: 0 12px 24px rgba(239, 107, 58, 0.25);
            }

            .btn.ghost {
                background: #fff;
                color: var(--ink);
                border: 1px solid var(--line);
            }

            .btn:hover {
                transform: translateY(-1px);
            }

            .content {
                padding: 32px 6vw 72px;
                max-width: 1200px;
                margin: 0 auto;
            }

            .page-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 24px;
                margin-bottom: 24px;
            }

            h1 {
                font-family: var(--font-display);
                font-size: clamp(28px, 4vw, 44px);
                margin: 0 0 10px;
                letter-spacing: -0.03em;
            }

            h2 {
                font-family: var(--font-display);
                font-size: 22px;
                margin: 0 0 8px;
            }

            p {
                margin: 0 0 10px;
                color: var(--muted);
                line-height: 1.6;
            }

            .grid {
                display: grid;
                gap: 20px;
            }

            .grid.two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .card {
                background: var(--surface);
                border-radius: var(--radius);
                border: 1px solid var(--line);
                box-shadow: var(--shadow);
                padding: 22px;
                animation: rise 0.6s ease;
            }

            .card.accent {
                background: linear-gradient(140deg, rgba(31, 122, 108, 0.1), rgba(239, 107, 58, 0.08)), var(--surface);
            }

            .tag {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                border-radius: 999px;
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 600;
                background: rgba(31, 122, 108, 0.12);
                color: var(--accent-2);
            }

            label {
                display: block;
                font-weight: 600;
                margin-bottom: 6px;
            }

            input, select, textarea {
                width: 100%;
                padding: 12px 14px;
                border-radius: 12px;
                border: 1px solid var(--line);
                background: #fff;
                font-family: var(--font-body);
                font-size: 14px;
            }

            textarea {
                min-height: 120px;
                resize: vertical;
            }

            .form-grid {
                display: grid;
                gap: 16px;
            }

            .form-grid.two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .hint {
                font-size: 12px;
                color: var(--muted);
            }

            .list {
                display: grid;
                gap: 14px;
            }

            .timeline {
                display: grid;
                gap: 12px;
                border-left: 2px solid rgba(31, 122, 108, 0.2);
                padding-left: 18px;
                margin-top: 10px;
            }

            .timeline-item {
                position: relative;
                padding-left: 8px;
            }

            .timeline-item::before {
                content: "";
                position: absolute;
                left: -26px;
                top: 6px;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: var(--accent-2);
                box-shadow: 0 0 0 4px rgba(31, 122, 108, 0.15);
            }

            .timeline-meta {
                font-size: 12px;
                color: var(--muted);
            }

            .req-card {
                display: grid;
                gap: 10px;
                padding: 16px;
                border-radius: 14px;
                border: 1px solid var(--line);
                background: #fff;
            }

            .req-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
            }

            .status {
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .status.en_attente {
                background: rgba(239, 107, 58, 0.15);
                color: #a53d1b;
            }

            .status.en_traitement {
                background: rgba(31, 122, 108, 0.18);
                color: #1f7a6c;
            }

            .status.traitee {
                background: rgba(60, 126, 77, 0.18);
                color: #2a6e43;
            }

            .status.rejetee {
                background: rgba(74, 45, 45, 0.16);
                color: #613030;
            }

            .footer {
                padding: 24px 6vw 36px;
                color: var(--muted);
                font-size: 12px;
                text-align: center;
            }

            .hidden {
                display: none;
            }

            @keyframes rise {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (max-width: 960px) {
                .grid.two,
                .form-grid.two {
                    grid-template-columns: 1fr;
                }

                .topbar {
                    flex-wrap: wrap;
                    gap: 12px;
                }
            }
        </style>
    </head>
    <body>
        @php($hideNav = $hideNav ?? false)
        <div class="ambient"></div>
        <header class="topbar">
            <div class="brand">
                <img src="{{asset('WhatsApp Image 2026-01-24 at 08.20.02.jpeg')}}" alt="logo" width="20%" height="20%">
                <div>
                    Portail Requetes
                    <small>IUT Douala</small>
                </div>
            </div>
            @if (!$hideNav)
                <nav class="nav">
                    <a href="/agent/dashboard" data-role="agent">Dashboard</a>
                    <a href="/etudiant/dashboard" data-role="etudiant">Dashboard</a>
                    <a href="/requetes/depot" data-role="etudiant">Depot</a>
                    <a href="/requetes/suivi" data-role="agent,etudiant">Suivi</a>
                    <a href="/profil" data-role="etudiant">Profil</a>
                    <a href="/agent/services" data-role="agent">Services</a>
                    <a href="/agent/types" data-role="agent">Types</a>
                    <a href="/agent/agents" data-role="agent">Agents</a>
                    <a href="/agent/etudiants" data-role="agent">Etudiants</a>
                    <a href="/agent/etapes" data-role="agent">Etapes</a>
                    <a href="/agent/decisions" data-role="agent">Decisions</a>
                </nav>
            @endif
            <div class="auth">
                <span id="authState" class="pill offline">Hors ligne</span>
                <button id="logoutBtn" class="btn ghost">Deconnexion</button>
            </div>
        </header>
        <main class="content">
            @yield('content')
        </main>
        <footer class="footer">
            Systeme de Requetes Etudiantes - IUT Douala
        </footer>
        <script>
            const API_BASE = '/api';

            function getToken() {
                return localStorage.getItem('api_token');
            }

            function setToken(token) {
                localStorage.setItem('api_token', token);
            }

            function clearToken() {
                localStorage.removeItem('api_token');
                localStorage.removeItem('user_role');
                localStorage.removeItem('service_id');
            }

            function setRole(role) {
                if (role) {
                    localStorage.setItem('user_role', role);
                }
            }

            function getRole() {
                return localStorage.getItem('user_role');
            }

            function setServiceId(serviceId) {
                if (serviceId) {
                    localStorage.setItem('service_id', String(serviceId));
                }
            }

            function getServiceId() {
                return localStorage.getItem('service_id');
            }

            async function apiFetch(path, options = {}) {
                const headers = new Headers(options.headers || {});
                headers.set('Accept', 'application/json');
                if (options.body && !(options.body instanceof FormData) && !headers.has('Content-Type')) {
                    headers.set('Content-Type', 'application/json');
                }
                const token = getToken();
                if (token) {
                    headers.set('Authorization', `Bearer ${token}`);
                }
                const response = await fetch(`${API_BASE}${path}`, {
                    ...options,
                    headers,
                });
                if (response.status === 401) {
                    clearToken();
                    if (!location.pathname.includes('/connexion')) {
                        location.href = '/connexion';
                    }
                }
                return response;
            }

            function formatDate(value) {
                if (!value) return '-';
                const normalized = typeof value === 'string' ? value.replace(' ', 'T') : value;
                const date = new Date(normalized);
                if (Number.isNaN(date.getTime())) return value;
                return date.toLocaleString('fr-FR', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }

            function updateAuthUI() {
                const state = document.getElementById('authState');
                const logoutBtn = document.getElementById('logoutBtn');
                const token = getToken();
                const role = getRole();
                if (!state || !logoutBtn) return;
                if (token) {
                    state.textContent = role ? `Connecte (${role})` : 'Connecte';
                    state.classList.remove('offline');
                    logoutBtn.classList.remove('hidden');
                } else {
                    state.textContent = 'Hors ligne';
                    state.classList.add('offline');
                    logoutBtn.classList.add('hidden');
                }
            }

            function updateNavByRole() {
                const role = getRole();
                const token = getToken();
                document.querySelectorAll('[data-role]').forEach((link) => {
                    const allowed = link.getAttribute('data-role');
                    if (!allowed) return;
                    if (!role || !token) {
                        link.classList.add('hidden');
                        return;
                    }
                    const roles = allowed.split(',').map((value) => value.trim());
                    if (roles.includes(role)) {
                        link.classList.remove('hidden');
                    } else {
                        link.classList.add('hidden');
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                updateAuthUI();
                updateNavByRole();
                const logoutBtn = document.getElementById('logoutBtn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', async () => {
                        const token = getToken();
                        if (!token) {
                            location.href = '/connexion';
                            return;
                        }
                        await apiFetch('/logout', { method: 'POST' });
                        clearToken();
                        location.href = '/connexion';
                    });
                }
            });
        </script>
        @stack('scripts')
    </body>
</html>
