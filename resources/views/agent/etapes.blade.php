@extends('layouts.portal')

@section('title', 'Gestion etapes | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Etapes de traitement</h1>
            <p>Date entree et date sortie sont calculees automatiquement par le systeme.</p>
        </div>
        <button id="refreshEtapes" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Traiter une requete</h2>
            <form id="etapeForm" class="form-grid">
                <div>
                    <label for="requete_id">Requete</label>
                    <select id="requete_id" name="requete_id" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>
                <div>
                    <label for="service_suivant_id">Service suivant (optionnel)</label>
                    <select id="service_suivant_id" name="service_suivant_id">
                        <option value="">Aucun</option>
                    </select>
                </div>
                <div>
                    <label for="action">Action</label>
                    <input id="action" name="action" type="text" required>
                </div>
                <div>
                    <label for="observation">Observation</label>
                    <textarea id="observation" name="observation"></textarea>
                </div>
                <div class="hint">
                    - date_entree = date de depot etudiant (premier service) ou date d'envoi du service precedent.
                    <br>
                    - date_sortie = date de validation du traitement dans ce formulaire.
                    <br>
                    - si "service suivant" est vide, le systeme applique automatiquement le circuit du type de requete.
                </div>
                <div id="etapeMessage" class="hint"></div>
                <button class="btn primary" type="submit">Valider le traitement</button>
                <button class="btn ghost hidden" type="button" id="cancelEditEtape">Annuler edition</button>
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
            if (!guardAgentFeature('process_etapes')) {
                return;
            }

            const form = document.getElementById('etapeForm');
            const message = document.getElementById('etapeMessage');
            const list = document.getElementById('etapesList');
            const refreshBtn = document.getElementById('refreshEtapes');
            const cancelEdit = document.getElementById('cancelEditEtape');
            const serviceId = getServiceId();
            let editingId = null;

            function resetForm() {
                form.reset();
                editingId = null;
                form.requete_id.disabled = false;
                cancelEdit.classList.add('hidden');
                message.textContent = '';
            }

            async function loadRefs() {
                const [servicesRes, requetesRes] = await Promise.all([
                    apiFetch('/services'),
                    apiFetch('/requetes'),
                ]);
                if (servicesRes.ok) {
                    const services = await servicesRes.json();
                    const suivantOptions = ['<option value="">Aucun</option>']
                        .concat(services
                            .filter((s) => String(s.id) !== String(serviceId))
                            .map((s) => `<option value="${s.id}">${s.nom_service}</option>`));
                    form.service_suivant_id.innerHTML = suivantOptions.join('');
                }
                if (requetesRes.ok) {
                    const requetes = await requetesRes.json();
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
                    const sortie = item.date_sortie ? formatDate(item.date_sortie) : 'En cours';
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
                            <div class="hint">Entree: ${formatDate(item.date_entree)} | Sortie: ${sortie}</div>
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
                    form.requete_id.disabled = true;
                    form.action.value = data.action;
                    form.observation.value = data.observation || '';
                    form.service_suivant_id.value = '';
                    cancelEdit.classList.remove('hidden');
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';

                let payload;
                let method;
                let url;

                if (editingId) {
                    payload = {
                        action: form.action.value.trim(),
                        observation: form.observation.value.trim(),
                        service_suivant_id: form.service_suivant_id.value ? Number(form.service_suivant_id.value) : null,
                    };
                    method = 'PUT';
                    url = `/etape-traitements/${editingId}`;
                } else {
                    payload = {
                        requete_id: Number(form.requete_id.value),
                        action: form.action.value.trim(),
                        observation: form.observation.value.trim(),
                        service_suivant_id: form.service_suivant_id.value ? Number(form.service_suivant_id.value) : null,
                    };
                    method = 'POST';
                    url = '/etape-traitements';
                }

                const response = await apiFetch(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    message.textContent = 'Erreur enregistrement.';
                    return;
                }
                resetForm();
                await loadRefs();
                loadEtapes();
            });

            cancelEdit.addEventListener('click', () => {
                resetForm();
                loadRefs();
            });
            refreshBtn.addEventListener('click', loadEtapes);
            loadRefs().then(loadEtapes);
        });
    </script>
@endpush
