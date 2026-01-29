@extends('layouts.portal')

@section('title', 'Profil etudiant | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Profil</span>
            <h1>Mes parametres</h1>
            <p>Modifie ton telephone, email, ou mot de passe.</p>
        </div>
    </div>

    <section class="card">
        <form id="profilForm" class="form-grid two">
            <div>
                <label for="telephone">Telephone</label>
                <input id="telephone" name="telephone" type="text" placeholder="699000111">
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email">
            </div>
            <div>
                <label for="password">Nouveau mot de passe</label>
                <input id="password" name="password" type="password">
            </div>
            <div>
                <label for="password_confirmation">Confirmer mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password">
            </div>
            <div id="profilMessage" class="hint"></div>
            <button class="btn primary" type="submit">Enregistrer</button>
        </form>
    </section>

    <section class="card accent" style="margin-top: 24px;">
        <h2>Notifications</h2>
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

            const form = document.getElementById('profilForm');
            const message = document.getElementById('profilMessage');
            const notificationsList = document.getElementById('notificationsList');

            async function loadProfil() {
                const response = await apiFetch('/etudiants/me');
                if (!response.ok) {
                    message.textContent = 'Erreur chargement.';
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
                    message.textContent = 'Erreur mise a jour.';
                    return;
                }
                form.password.value = '';
                form.password_confirmation.value = '';
                message.textContent = 'Profil mis a jour.';
            });

            async function loadNotifications() {
                const response = await apiFetch('/notifications');
                if (!response.ok) {
                    notificationsList.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                if (!data.length) {
                    notificationsList.innerHTML = '<p class="hint">Aucune notification.</p>';
                    return;
                }
                notificationsList.innerHTML = data.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>Requete #${item.requete_id}</strong>
                                <div class="hint">${item.message}</div>
                            </div>
                            <div>
                                ${item.read_at ? '<span class="pill">Lu</span>' : '<button class="btn ghost" data-action="read" data-id="' + item.id + '">Marquer lu</button>'}
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
        });
    </script>
@endpush
