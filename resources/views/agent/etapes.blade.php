@extends('layouts.portal')

@section('title', 'Gestion etapes | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Etapes de traitement</h1>
            <p>Suivi des actions par service.</p>
        </div>
        <button id="refreshEtapes" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Nouvelle etape</h2>
            <form id="etapeForm" class="form-grid">
                <div>
                    <label for="requete_id">Requete</label>
                    <select id="requete_id" name="requete_id" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>
                <div>
                    <label for="service_id">Service</label>
                    <select id="service_id" name="service_id" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>
                <div>
                    <label for="service_suivant_id">Service suivant (optionnel)</label>
                    <select id="service_suivant_id" name="service_suivant_id">
                        <option value="">Aucun</option>
                    </select>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="ordre_etape">Ordre</label>
                        <input id="ordre_etape" name="ordre_etape" type="number" min="1" required>
                    </div>
                    <div>
                        <label for="action">Action</label>
                        <input id="action" name="action" type="text" required>
                    </div>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="date_entree">Date entree</label>
                        <input id="date_entree" name="date_entree" type="datetime-local" required>
                    </div>
                    <div>
                        <label for="date_sortie">Date sortie</label>
                        <input id="date_sortie" name="date_sortie" type="datetime-local">
                    </div>
                </div>
                <div>
                    <label for="observation">Observation</label>
                    <textarea id="observation" name="observation"></textarea>
                </div>
                <div id="etapeMessage" class="hint"></div>
                <button class="btn primary" type="submit">Enregistrer</button>
                <button class="btn ghost hidden" type="button" id="cancelEditEtape">Annuler</button>
            </form>
        </section>
        <section class="card accent">
            <h2>Liste des etapes</h2>
            <div id="etapesList" class="list"></div>
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

            const form = document.getElementById('etapeForm');
            const message = document.getElementById('etapeMessage');
            const list = document.getElementById('etapesList');
            const refreshBtn = document.getElementById('refreshEtapes');
            const cancelEdit = document.getElementById('cancelEditEtape');
            let editingId = null;
            let requetes = [];
            let services = [];
            const serviceId = getServiceId();

            function toApiDate(value) {
                if (!value) return null;
                return value.replace('T', ' ');
            }

            function toInputDate(value) {
                if (!value) return '';
                return value.replace(' ', 'T').slice(0, 16);
            }

            function resetForm() {
                form.reset();
                editingId = null;
                cancelEdit.classList.add('hidden');
                if (serviceId) {
                    form.service_id.value = serviceId;
                }
            }

            async function loadRefs() {
                const [servicesRes, requetesRes] = await Promise.all([
                    apiFetch('/services'),
                    apiFetch('/requetes'),
                ]);
                if (servicesRes.ok) {
                    services = await servicesRes.json();
                    const filtered = serviceId ? services.filter((s) => String(s.id) === String(serviceId)) : services;
                    const options = ['<option value="">Choisir</option>']
                        .concat(filtered.map((s) => `<option value="${s.id}">${s.nom_service}</option>`));
                    form.service_id.innerHTML = options.join('');
                    if (serviceId) {
                        form.service_id.value = serviceId;
                        form.service_id.disabled = true;
                    }

                    const suivantOptions = ['<option value="">Aucun</option>']
                        .concat(services
                            .filter((s) => String(s.id) !== String(serviceId))
                            .map((s) => `<option value="${s.id}">${s.nom_service}</option>`));
                    form.service_suivant_id.innerHTML = suivantOptions.join('');
                }
                if (requetesRes.ok) {
                    requetes = await requetesRes.json();
                    const options = ['<option value="">Choisir</option>']
                        .concat(requetes.map((r) => `<option value="${r.id}">#${r.id} - ${r.objet || 'Sans objet'}</option>`));
                    form.requete_id.innerHTML = options.join('');
                }
            }

            async function loadEtapes() {
                const response = await apiFetch('/etape-traitements');
                if (!response.ok) {
                    list.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                list.innerHTML = data.map((item) => {
                    const serviceName = item.service ? item.service.nom_service : 'Service';
                    const requeteLabel = item.requete ? `#${item.requete.id} ${item.requete.objet || ''}` : `#${item.requete_id}`;
                    return `
                        <article class="req-card">
                            <div class="req-head">
                                <div>
                                    <strong>${serviceName}</strong>
                                    <div class="hint">${requeteLabel}</div>
                                </div>
                                <div>
                                    <button class="btn ghost" data-action="edit" data-id="${item.id}">Editer</button>
                                    <button class="btn ghost" data-action="delete" data-id="${item.id}">Supprimer</button>
                                </div>
                            </div>
                            <div class="hint">Ordre: ${item.ordre_etape} | Action: ${item.action}</div>
                            <div class="hint">Entree: ${formatDate(item.date_entree)} | Sortie: ${formatDate(item.date_sortie)}</div>
                        </article>
                    `;
                }).join('');
            }

            list.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const id = button.getAttribute('data-id');
                const action = button.getAttribute('data-action');
                if (action === 'delete') {
                    const response = await apiFetch(`/etape-traitements/${id}`, { method: 'DELETE' });
                    if (response.ok) {
                        loadEtapes();
                    }
                    return;
                }
                if (action === 'edit') {
                    const response = await apiFetch(`/etape-traitements/${id}`);
                    if (!response.ok) return;
                    const data = await response.json();
                    editingId = data.id;
                    form.requete_id.value = data.requete_id;
                    form.service_id.value = data.service_id;
                    form.ordre_etape.value = data.ordre_etape;
                    form.action.value = data.action;
                    form.date_entree.value = toInputDate(data.date_entree);
                    form.date_sortie.value = toInputDate(data.date_sortie);
                    form.observation.value = data.observation || '';
                    cancelEdit.classList.remove('hidden');
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';
                const payload = {
                    requete_id: Number(form.requete_id.value),
                    service_id: Number(form.service_id.value || serviceId),
                    service_suivant_id: form.service_suivant_id.value ? Number(form.service_suivant_id.value) : null,
                    ordre_etape: Number(form.ordre_etape.value),
                    action: form.action.value.trim(),
                    date_entree: toApiDate(form.date_entree.value),
                    date_sortie: toApiDate(form.date_sortie.value) || null,
                    observation: form.observation.value.trim(),
                };
                const method = editingId ? 'PUT' : 'POST';
                const url = editingId ? `/etape-traitements/${editingId}` : '/etape-traitements';
                const response = await apiFetch(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    message.textContent = 'Erreur enregistrement.';
                    return;
                }
                resetForm();
                loadEtapes();
            });

            cancelEdit.addEventListener('click', resetForm);
            refreshBtn.addEventListener('click', loadEtapes);
            loadRefs().then(loadEtapes);
        });
    </script>
@endpush
