@extends('layouts.portal')

@section('title', 'Suivi des requetes | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Suivi</span>
            <h1>Etat de mes requetes</h1>
            <p>Consulte le statut et les details.</p>
        </div>
        <div class="form-grid two">
            <div>
                <label for="statusFilter">Filtre statut</label>
                <select id="statusFilter">
                    <option value="">Tous</option>
                    <option value="en_attente">En attente</option>
                    <option value="en_traitement">En traitement</option>
                    <option value="traitee">Traitee</option>
                    <option value="rejetee">Rejetee</option>
                </select>
            </div>
            <div>
                <label for="searchBox">Recherche</label>
                <input id="searchBox" type="text" placeholder="Objet ou type">
            </div>
        </div>
        <div class="form-grid two">
            <div>
                <label for="serviceFilter">Filtre service</label>
                <select id="serviceFilter">
                    <option value="">Tous les services</option>
                </select>
            </div>
        </div>
    </div>

    <section class="card">
        <div id="requetesList" class="list"></div>
        <div id="suiviMessage" class="hint"></div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = getToken();
            const role = getRole();
            if (!token || (role !== 'agent' && role !== 'etudiant')) {
                location.href = '/connexion';
                return;
            }

            const list = document.getElementById('requetesList');
            const message = document.getElementById('suiviMessage');
            const statusFilter = document.getElementById('statusFilter');
            const searchBox = document.getElementById('searchBox');
            const serviceFilter = document.getElementById('serviceFilter');
            let items = [];
            const serviceId = getServiceId();

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function render() {
                const term = searchBox.value.trim().toLowerCase();
                const status = statusFilter.value;
                const filtered = items.filter((item) => {
                    const statusOk = !status || item.statut === status;
                    const label = `${item.objet || ''} ${(item.type_requete && item.type_requete.libelle) || ''}`.toLowerCase();
                    const termOk = !term || label.includes(term);
                    return statusOk && termOk;
                });

                if (!filtered.length) {
                    list.innerHTML = '';
                    message.textContent = 'Aucune requete trouvee.';
                    return;
                }
                message.textContent = '';
                list.innerHTML = filtered.map((item) => {
                    const typeLabel = item.type_requete ? item.type_requete.libelle : 'Type inconnu';
                    const decision = item.decision ? item.decision.resultat : 'En cours';
                    const statusValue = item.statut || 'en_attente';
                    return `
                        <article class="req-card">
                            <div class="req-head">
                                <div>
                                    <strong>${escapeHtml(item.objet || 'Sans objet')}</strong>
                                    <div class="hint">${escapeHtml(typeLabel)}</div>
                                </div>
                                <span class="status ${escapeHtml(statusValue)}">${escapeHtml(statusValue)}</span>
                            </div>
                            <div class="hint">Depot: ${escapeHtml(formatDate(item.date_depot))}</div>
                            <div class="hint">Decision: ${escapeHtml(decision)}</div>
                            <button class="btn ghost" data-action="details" data-id="${item.id}">Voir details</button>
                            <div id="details-${item.id}" class="hint"></div>
                        </article>
                    `;
                }).join('');
            }

            async function loadRequetes() {
                const serviceId = serviceFilter.value;
                const url = serviceId ? `/requetes?service_id=${serviceId}` : '/requetes';
                const response = await apiFetch(url);
                if (!response.ok) {
                    message.textContent = 'Erreur chargement.';
                    return;
                }
                items = await response.json();
                render();
            }

            async function loadServices() {
                const response = await apiFetch('/services');
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                const filtered = role === 'agent' && serviceId
                    ? data.filter((service) => String(service.id) === String(serviceId))
                    : data;
                serviceFilter.innerHTML = '<option value="">Tous les services</option>';
                filtered.forEach((service) => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.nom_service;
                    serviceFilter.appendChild(option);
                });
                if (role === 'agent' && serviceId) {
                    serviceFilter.value = serviceId;
                }
            }

            list.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-action="details"]');
                if (!button) return;
                const id = button.getAttribute('data-id');
                const panel = document.getElementById(`details-${id}`);
                panel.textContent = 'Chargement...';
                const response = await apiFetch(`/requetes/${id}`);
                if (!response.ok) {
                    panel.textContent = 'Erreur detail.';
                    return;
                }
                const data = await response.json();
                const etapes = data.etape_traitements || [];
                const pieces = data.pieces_jointes || [];
                const timelineHtml = etapes.length
                    ? `
                        <div class="timeline">
                            ${etapes.map((etape) => `
                                <div class="timeline-item">
                                    <strong>${escapeHtml(etape.action)}</strong>
                                    <div class="timeline-meta">${escapeHtml(etape.service ? etape.service.nom_service : 'Service')}</div>
                                    <div class="timeline-meta">Entree: ${escapeHtml(formatDate(etape.date_entree))}</div>
                                    <div class="timeline-meta">Sortie: ${escapeHtml(formatDate(etape.date_sortie))}</div>
                                </div>
                            `).join('')}
                        </div>
                    `
                    : '<div class="hint">Aucune etape enregistree.</div>';

                const piecesHtml = pieces.length
                    ? `
                        <div class="list" style="margin-top: 12px;">
                            ${pieces.map((piece) => `
                                <div class="req-card">
                                    <div class="req-head">
                                        <div>
                                            <strong>${escapeHtml(piece.nom_fichier)}</strong>
                                            <div class="hint">${escapeHtml(piece.type_piece || 'Fichier')}</div>
                                        </div>
                                        <a class="btn ghost" href="${escapeHtml(piece.url || piece.chemin_fichier)}" target="_blank" rel="noopener">Telecharger</a>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `
                    : '<div class="hint">Aucune piece jointe.</div>';

                panel.innerHTML = `
                    <div>
                        <strong>Historique</strong>
                        ${timelineHtml}
                    </div>
                    <div style="margin-top: 12px;">
                        <strong>Pieces jointes</strong>
                        ${piecesHtml}
                    </div>
                `;
            });

            statusFilter.addEventListener('change', render);
            searchBox.addEventListener('input', render);
            serviceFilter.addEventListener('change', loadRequetes);

            loadServices();
            loadRequetes();
        });
    </script>
@endpush
