@extends('layouts.portal')

@section('title', 'Gestion agents | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Agents</h1>
            <p>Creer des agents et les rattacher a un service.</p>
        </div>
        <button id="refreshAgents" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Nouvel agent</h2>
            <form id="agentForm" class="form-grid">
                <div>
                    <label for="name">Nom complet</label>
                    <input id="name" name="name" type="text" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="service_id">Service</label>
                        <select id="service_id" name="service_id" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div>
                        <label for="password">Mot de passe</label>
                        <input id="password" name="password" type="password" required>
                    </div>
                </div>
                <div id="agentMessage" class="hint"></div>
                <button class="btn primary" type="submit">Enregistrer</button>
                <button class="btn ghost hidden" type="button" id="cancelAgent">Annuler</button>
            </form>
        </section>

        <section class="card accent">
            <h2>Liste des agents</h2>
            <div id="agentsList" class="list"></div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (getRole() !== 'agent') {
                location.href = '/connexion';
                return;
            }

            const form = document.getElementById('agentForm');
            const message = document.getElementById('agentMessage');
            const list = document.getElementById('agentsList');
            const refreshBtn = document.getElementById('refreshAgents');
            const cancelBtn = document.getElementById('cancelAgent');
            let editingId = null;
            let services = [];

            function resetForm() {
                form.reset();
                editingId = null;
                cancelBtn.classList.add('hidden');
                form.password.required = true;
            }

            async function loadServices() {
                const response = await apiFetch('/services');
                if (!response.ok) {
                    return;
                }
                services = await response.json();
                form.service_id.innerHTML = ['<option value="">Choisir</option>']
                    .concat(services.map((s) => `<option value="${s.id}">${s.nom_service}</option>`))
                    .join('');
            }

            async function loadAgents() {
                const response = await apiFetch('/agents');
                if (!response.ok) {
                    list.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                list.innerHTML = data.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.name}</strong>
                                <div class="hint">${item.email}</div>
                                <div class="hint">Service: ${item.service ? item.service.nom_service : '-'}</div>
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
                    const response = await apiFetch(`/agents/${id}`, { method: 'DELETE' });
                    if (response.ok) {
                        loadAgents();
                    }
                    return;
                }
                if (action === 'edit') {
                    const response = await apiFetch(`/agents/${id}`);
                    if (!response.ok) return;
                    const data = await response.json();
                    editingId = data.id;
                    form.name.value = data.name;
                    form.email.value = data.email;
                    form.service_id.value = data.service_id;
                    form.password.value = '';
                    form.password.required = false;
                    cancelBtn.classList.remove('hidden');
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';
                const payload = {
                    name: form.name.value.trim(),
                    email: form.email.value.trim(),
                    service_id: Number(form.service_id.value),
                };
                if (form.password.value) {
                    payload.password = form.password.value;
                }
                const method = editingId ? 'PUT' : 'POST';
                const url = editingId ? `/agents/${editingId}` : '/agents';
                const response = await apiFetch(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    message.textContent = 'Erreur enregistrement.';
                    return;
                }
                resetForm();
                loadAgents();
            });

            cancelBtn.addEventListener('click', resetForm);
            refreshBtn.addEventListener('click', loadAgents);

            loadServices().then(loadAgents);
        });
    </script>
@endpush
