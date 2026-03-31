@extends('layouts.portal')

@section('title', 'Profil étudiant | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag" data-i18n="profile.tag">Profil</span>
            <h1 data-i18n="profile.title">Mes paramètres</h1>
            <p data-i18n="profile.subtitle">Modifie ton téléphone, email ou mot de passe.</p>
        </div>
    </div>

    <section class="card">
        <form id="profilForm" class="form-grid two">
            <div>
                <label for="telephone" data-i18n="field.phone">Téléphone</label>
                <input id="telephone" name="telephone" type="text" placeholder="699000111">
            </div>
            <div>
                <label for="email" data-i18n="field.email">Email</label>
                <input id="email" name="email" type="email">
            </div>
            <div>
                <label for="password" data-i18n="profile.new_password">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" data-password-toggle>
            </div>
            <div>
                <label for="password_confirmation" data-i18n="profile.confirm_password">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" data-password-toggle>
            </div>
            <div id="profilMessage" class="hint"></div>
            <button class="btn primary" type="submit" data-i18n="action.save">Enregistrer</button>
        </form>
    </section>

    <section class="card accent" style="margin-top: 24px;">
        <h2 data-i18n="profile.notifications">Notifications</h2>
        <div id="notificationsList" class="list"></div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = getToken();
            if (!token || getRole() !== 'etudiant') {
                location.href = '/connexion';
                return;
            }

            applyI18n();

            const form = document.getElementById('profilForm');
            const message = document.getElementById('profilMessage');
            const notificationsList = document.getElementById('notificationsList');

            async function loadProfil() {
                const response = await apiFetch('/etudiants/me');
                if (!response.ok) {
                    message.textContent = __('common.error_loading');
                    return;
                }
                const data = await response.json();
                form.telephone.value = data.etudiant.telephone || '';
                form.email.value = data.user.email || '';
            }

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';
                const payload = {
                    telephone: form.telephone.value.trim(),
                    email: form.email.value.trim(),
                };
                if (form.password.value) {
                    payload.password = form.password.value;
                    payload.password_confirmation = form.password_confirmation.value;
                }
                const response = await apiFetch('/etudiants/me', {
                    method: 'PATCH',
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    message.textContent = __('profile.update_error');
                    return;
                }
                form.password.value = '';
                form.password_confirmation.value = '';
                message.textContent = __('profile.updated');
            });

            async function loadNotifications() {
                const response = await apiFetch('/notifications');
                if (!response.ok) {
                    notificationsList.innerHTML = `<p class="hint">${__('common.error_loading')}</p>`;
                    return;
                }
                const data = await response.json();
                if (!data.length) {
                    notificationsList.innerHTML = `<p class="hint">${__('profile.no_notifications')}</p>`;
                    return;
                }
                notificationsList.innerHTML = data.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${__('request.label')} #${item.requete_id}</strong>
                                <div class="hint">${item.message}</div>
                            </div>
                            <div>
                                ${item.read_at ? '<span class="pill">' + __('profile.read') + '</span>' : '<button class="btn ghost" data-action="read" data-id="' + item.id + '">' + __('action.mark_read') + '</button>'}
                            </div>
                        </div>
                        <div class="hint">${formatDate(item.created_at)}</div>
                    </article>
                `).join('');
            }

            notificationsList.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-action="read"]');
                if (!button) return;
                const id = button.getAttribute('data-id');
                const response = await apiFetch(`/notifications/${id}`, { method: 'PATCH' });
                if (response.ok) {
                    loadNotifications();
                }
            });

            loadProfil();
            loadNotifications();
            window.addEventListener('srm:language-changed', loadNotifications);
        });
    </script>
@endpush
