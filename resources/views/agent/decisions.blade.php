@extends('layouts.portal')

@section('title', 'Gestion decisions | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Decisions</h1>
            <p>Attribue une decision et notifie automatiquement l'etudiant.</p>
        </div>
        <button id="refreshDecisions" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Nouvelle decision</h2>
            <form id="decisionForm" class="form-grid">
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
                <div class="form-grid two">
                    <div>
                        <label for="resultat">Resultat</label>
                        <select id="resultat" name="resultat" required>
                            <option value="favorable">Favorable</option>
                            <option value="defavorable">Defavorable</option>
                            <option value="incomplet">Incomplet</option>
                        </select>
                    </div>
                    <div>
                        <label for="date_decision">Date decision</label>
                        <input id="date_decision" name="date_decision" type="datetime-local" required>
                    </div>
                </div>
                <div>
                    <label for="motif">Motif (optionnel)</label>
                    <textarea id="motif" name="motif"></textarea>
                </div>
                <div id="decisionMessage" class="hint"></div>
                <button class="btn primary" type="submit">Enregistrer</button>
                <button class="btn ghost hidden" type="button" id="cancelDecision">Annuler</button>
            </form>
        </section>
        <section class="card accent">
            <h2>Liste des decisions</h2>
            <div id="decisionsList" class="list"></div>
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

            const form = document.getElementById('decisionForm');
            const message = document.getElementById('decisionMessage');
            const list = document.getElementById('decisionsList');
            const refreshBtn = document.getElementById('refreshDecisions');
            const cancelBtn = document.getElementById('cancelDecision');
            let editingId = null;
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
                cancelBtn.classList.add('hidden');
                const now = new Date();
                form.date_decision.value = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                    .toISOString()
                    .slice(0, 16);
            }

            async function loadRefs() {
                const [servicesRes, requetesRes] = await Promise.all([
                    apiFetch('/services'),
                    apiFetch('/requetes'),
                ]);
                if (servicesRes.ok) {
                    const services = await servicesRes.json();
                    const filtered = serviceId ? services.filter((s) => String(s.id) === String(serviceId)) : services;
                    form.service_id.innerHTML = ['<option value="">Choisir</option>']
                        .concat(filtered.map((s) => `<option value="${s.id}">${s.nom_service}</option>`))
                        .join('');
                    if (serviceId) {
                        form.service_id.value = serviceId;
                        form.service_id.disabled = true;
                    }
                }
                if (requetesRes.ok) {
                    const requetes = await requetesRes.json();
                    form.requete_id.innerHTML = ['<option value="">Choisir</option>']
                        .concat(requetes.map((r) => `<option value="${r.id}">#${r.id} - ${r.objet || 'Sans objet'}</option>`))
                        .join('');
                }
            }

            async function loadDecisions() {
                const response = await apiFetch('/decisions');
                if (!response.ok) {
                    list.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                list.innerHTML = data.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>Requete #${item.requete_id}</strong>
                                <div class="hint">${item.resultat}</div>
                            </div>
                            <div>
                                <button class="btn ghost" data-action="edit" data-id="${item.id}">Editer</button>
                                <button class="btn ghost" data-action="delete" data-id="${item.id}">Supprimer</button>
                            </div>
                        </div>
                        <div class="hint">Decision: ${formatDate(item.date_decision)}</div>
                    </article>
                `).join('');
            }

            list.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const id = button.getAttribute('data-id');
                const action = button.getAttribute('data-action');
                if (action === 'delete') {
                    const response = await apiFetch(`/decisions/${id}`, { method: 'DELETE' });
                    if (response.ok) {
                        loadDecisions();
                    }
                    return;
                }
                if (action === 'edit') {
                    const response = await apiFetch(`/decisions/${id}`);
                    if (!response.ok) return;
                    const data = await response.json();
                    editingId = data.id;
                    form.requete_id.value = data.requete_id;
                    form.service_id.value = data.service_id;
                    form.resultat.value = data.resultat;
                    form.date_decision.value = toInputDate(data.date_decision);
                    form.motif.value = data.motif || '';
                    cancelBtn.classList.remove('hidden');
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';
                const payload = {
                    requete_id: Number(form.requete_id.value),
                    service_id: Number(form.service_id.value || serviceId),
                    resultat: form.resultat.value,
                    date_decision: toApiDate(form.date_decision.value),
                    motif: form.motif.value.trim(),
                };
                const method = editingId ? 'PUT' : 'POST';
                const url = editingId ? `/decisions/${editingId}` : '/decisions';
                const response = await apiFetch(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    message.textContent = 'Erreur enregistrement.';
                    return;
                }
                resetForm();
                loadDecisions();
            });

            cancelBtn.addEventListener('click', resetForm);
            refreshBtn.addEventListener('click', loadDecisions);
            loadRefs().then(() => {
                resetForm();
                loadDecisions();
            });
        });
    </script>
@endpush
