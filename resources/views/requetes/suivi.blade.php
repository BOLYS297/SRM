@extends('layouts.portal')

@section('title', 'Suivi des requêtes | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag" data-i18n="tracking.tag">Suivi</span>
            <h1 data-i18n="tracking.title">État de mes requêtes</h1>
            <p data-i18n="tracking.subtitle">Consulte le statut et les détails.</p>
        </div>
        <div class="form-grid two">
            <div>
                <label for="statusFilter" data-i18n="tracking.status_filter">Filtre statut</label>
                <select id="statusFilter">
                    <option value="" data-i18n="tracking.all">Tous</option>
                    <option value="en_attente" data-i18n="status.en_attente">En attente</option>
                    <option value="en_traitement" data-i18n="status.en_traitement">En traitement</option>
                    <option value="traitee" data-i18n="status.traitee">Traitée</option>
                    <option value="rejetee" data-i18n="status.rejetee">Rejetée</option>
                </select>
            </div>
            <div>
                <label for="searchBox" data-i18n="tracking.search">Recherche</label>
                <input id="searchBox" type="text" data-i18n-placeholder="tracking.search_placeholder" placeholder="Objet ou type">
            </div>
        </div>
        <div class="form-grid two">
            <div>
                <label for="serviceFilter" data-i18n="tracking.service_filter">Filtre service</label>
                <select id="serviceFilter">
                    <option value="" data-i18n="tracking.all_services">Tous les services</option>
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

            applyI18n();

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
                    message.textContent = __('tracking.none_found');
                    return;
                }
                message.textContent = '';
                list.innerHTML = filtered.map((item) => {
                    const typeLabel = item.type_requete ? item.type_requete.libelle : __('request.unknown_type');
                    const decision = item.decision ? formatDecision(item.decision.resultat) : __('tracking.in_progress');
                    const statusValue = item.statut || 'en_attente';
                    return `
                        <article class="req-card">
                            <div class="req-head">
                                <div>
                                    <strong>${escapeHtml(item.objet || __('request.no_subject'))}</strong>
                                    <div class="hint">${escapeHtml(typeLabel)}</div>
                                </div>
                                <span class="status ${escapeHtml(statusValue)}">${escapeHtml(formatStatus(statusValue))}</span>
                            </div>
                            <div class="hint">${__('request.deposit_date')}: ${escapeHtml(formatDate(item.date_depot))}</div>
                            <div class="hint">${__('tracking.decision')}: ${escapeHtml(decision)}</div>
                            <button class="btn ghost" data-action="details" data-id="${item.id}">${__('action.view_details')}</button>
                            <div id="details-${item.id}" class="hint"></div>
                        </article>
                    `;
                }).join('');
            }

            async function loadRequetes() {
                const selectedServiceId = serviceFilter.value;
                const url = selectedServiceId ? `/requetes?service_id=${selectedServiceId}` : '/requetes';
                const response = await apiFetch(url);
                if (!response.ok) {
                    message.textContent = __('common.error_loading');
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
                serviceFilter.innerHTML = `<option value="">${__('tracking.all_services')}</option>`;
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
                panel.textContent = __('common.loading');
                const response = await apiFetch(`/requetes/${id}`);
                if (!response.ok) {
                    panel.textContent = __('tracking.detail_error');
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
                                    <div class="timeline-meta">${escapeHtml(etape.service ? etape.service.nom_service : __('tracking.service'))}</div>
                                    <div class="timeline-meta">${__('tracking.entry')}: ${escapeHtml(formatDate(etape.date_entree))}</div>
                                    <div class="timeline-meta">${__('tracking.exit')}: ${escapeHtml(formatDate(etape.date_sortie))}</div>
                                </div>
                            `).join('')}
                        </div>
                    `
                    : `<div class="hint">${__('tracking.no_steps')}</div>`;

                const piecesHtml = pieces.length
                    ? `
                        <div class="list" style="margin-top: 12px;">
                            ${pieces.map((piece) => `
                                <div class="req-card">
                                    <div class="req-head">
                                        <div>
                                            <strong>${escapeHtml(piece.nom_fichier)}</strong>
                                            <div class="hint">${escapeHtml(piece.type_piece || __('request.file'))}</div>
                                        </div>
                                        <a class="btn ghost" href="${escapeHtml(piece.url || piece.chemin_fichier)}" target="_blank" rel="noopener">${__('common.download')}</a>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `
                    : `<div class="hint">${__('tracking.no_attachments')}</div>`;

                panel.innerHTML = `
                    <div>
                        <strong>${__('tracking.history')}</strong>
                        ${timelineHtml}
                    </div>
                    <div style="margin-top: 12px;">
                        <strong>${__('tracking.attachments')}</strong>
                        ${piecesHtml}
                    </div>
                `;
            });

            statusFilter.addEventListener('change', render);
            searchBox.addEventListener('input', render);
            serviceFilter.addEventListener('change', loadRequetes);

            loadServices();
            loadRequetes();
            window.addEventListener('srm:language-changed', () => {
                applyI18n();
                loadServices();
                render();
            });
        });
    </script>
@endpush
