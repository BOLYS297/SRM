@extends('layouts.portal')

@section('title', 'Gestion services | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Gestion des services</h1>
            <p>Ajoute, modifie ou supprime les services du circuit.</p>
        </div>
        <button id="refreshServices" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Nouveau service</h2>
            <form id="serviceForm" class="form-grid">
                <div>
                    <label for="nom_service">Nom du service</label>
                    <input id="nom_service" name="nom_service" type="text" required>
                </div>
                <div>
                    <label for="type_service">Type (optionnel)</label>
                    <input id="type_service" name="type_service" type="text">
                </div>
                <div id="serviceMessage" class="hint"></div>
                <button class="btn primary" type="submit">Enregistrer</button>
                <button class="btn ghost hidden" type="button" id="cancelEdit">Annuler</button>
            </form>
        </section>
        <section class="card accent">
            <h2>Liste des services</h2>
            <div id="servicesList" class="list"></div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const role = getRole();
            if (role !== 'agent') {
                location.href = '/connexion';
                return;
            }

            const form = document.getElementById('serviceForm');
            const message = document.getElementById('serviceMessage');
            const list = document.getElementById('servicesList');
            const refreshBtn = document.getElementById('refreshServices');
            const cancelEdit = document.getElementById('cancelEdit');
            let editingId = null;

            function resetForm() {
                form.reset();
                editingId = null;
                cancelEdit.classList.add('hidden');
            }

            async function loadServices() {
                const response = await apiFetch('/services');
                if (!response.ok) {
                    list.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                list.innerHTML = data.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.nom_service}</strong>
                                <div class="hint">${item.type_service || 'Non defini'}</div>
                            </div>
                            <div>
                                <button class="btn ghost" data-action="edit" data-id="${item.id}">Editer</button>
                                <button class="btn ghost" data-action="delete" data-id="${item.id}">Supprimer</button>
                            </div>
                        </div>
                    </article>
                `).join('');
            }

            list.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const id = button.getAttribute('data-id');
                const action = button.getAttribute('data-action');
                if (action === 'delete') {
                    const response = await apiFetch(`/services/${id}`, { method: 'DELETE' });
                    if (response.ok) {
                        loadServices();
                    }
                    return;
                }
                if (action === 'edit') {
                    const response = await apiFetch(`/services/${id}`);
                    if (!response.ok) return;
                    const data = await response.json();
                    editingId = data.id;
                    form.nom_service.value = data.nom_service;
                    form.type_service.value = data.type_service || '';
                    cancelEdit.classList.remove('hidden');
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';
                const payload = {
                    nom_service: form.nom_service.value.trim(),
                    type_service: form.type_service.value.trim(),
                };
                const method = editingId ? 'PUT' : 'POST';
                const url = editingId ? `/services/${editingId}` : '/services';
                const response = await apiFetch(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    message.textContent = 'Erreur enregistrement.';
                    return;
                }
                resetForm();
                loadServices();
            });

            cancelEdit.addEventListener('click', resetForm);
            refreshBtn.addEventListener('click', loadServices);
            loadServices();
        });
    </script>
@endpush
