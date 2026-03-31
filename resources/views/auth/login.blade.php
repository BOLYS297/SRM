@extends('layouts.portal', ['hideNav' => true])

@section('title', 'Connexion | SRM')

@section('content')
    <div class="grid two">
        <section class="card accent">
            <span class="tag" data-i18n="login.tag">Plateforme officielle</span>
            <h1 data-i18n="login.title">Portail des requêtes étudiantes</h1>
            <p data-i18n="login.subtitle">Dépose une requête, suis son parcours et reçois une décision dans le délai cible.</p>
            <div class="form-grid">
                <div>
                    <h2 data-i18n="login.features_title">Ce que tu peux faire</h2>
                    <p data-i18n="login.feature_1">- Déposer une requête pour certificat, duplicata, correction, ou autre.</p>
                    <p data-i18n="login.feature_2">- Suivre l'état de traitement par service.</p>
                    <p data-i18n="login.feature_3">- Mettre à jour ton téléphone, email, mot de passe.</p>
                </div>
            </div>
        </section>
        <section class="card">
            <h2 data-i18n="login.form_title">Connexion</h2>
            <p class="hint" data-i18n="login.form_hint">Utilise le compte créé par ton service.</p>
            <form id="loginForm" class="form-grid">
                <div>
                    <label for="email" data-i18n="field.email">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email">
                </div>
                <div>
                    <label for="password" data-i18n="field.password">Mot de passe</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" data-password-toggle>
                </div>
                <div id="loginError" class="hint"></div>
                <button class="btn primary" type="submit" data-i18n="action.login">Se connecter</button>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            applyI18n();

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
                    error.textContent = __('login.invalid_credentials');
                    return;
                }
                const data = await response.json();
                setToken(data.token);
                setRole(data.user.role);
                setServiceId(data.user.service_id);
                setServiceNom(data.user.service_nom);
                setServiceType(data.user.service_type);
                setServiceKey(data.user.service_key);
                setAgentFeatures(data.user.features || []);
                const target = data.user.role === 'agent' ? '/agent/dashboard' : '/etudiant/dashboard';
                location.href = target;
            });
        });
    </script>
@endpush
