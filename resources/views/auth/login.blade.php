@extends('layouts.portal', ['hideNav' => true])

@section('title', 'Connexion | SRM')

@section('content')
    <div class="grid two">
        <section class="card accent">
            <span class="tag">Plateforme officielle</span>
            <h1>Portail des requetes des étudiantes</h1>
            <p>Depose une requete, suis son parcours, et recois une decision dans le delai cible.</p>
            <div class="form-grid">
                <div>
                    <h2>Ce que tu peux faire</h2>
                    <p>- Deposer une requete pour certificat, duplicata, correction, ou autre.</p>
                    <p>- Suivre l'etat de traitement par service.</p>
                    <p>- Mettre a jour ton telephone, email, mot de passe.</p>
                </div>
            </div>
        </section>
        <section class="card">
            <h2>Connexion</h2>
            <p class="hint">Utilise le compte cree par ton service.</p>
            <form id="loginForm" class="form-grid">
                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email">
                </div>
                <div>
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <div id="loginError" class="hint"></div>
                <button class="btn primary" type="submit">Se connecter</button>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('loginForm');
            const error = document.getElementById('loginError');
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                error.textContent = '';
                const payload = {
                    email: form.email.value.trim(),
                    password: form.password.value,
                };
                const response = await apiFetch('/login', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    error.textContent = 'Identifiants invalides.';
                    return;
                }
                const data = await response.json();
                setToken(data.token);
                setRole(data.user.role);
                setServiceId(data.user.service_id);
                const target = data.user.role === 'agent' ? '/agent/dashboard' : '/etudiant/dashboard';
                location.href = target;
            });
        });
    </script>
@endpush
