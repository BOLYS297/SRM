@extends('layouts.portal')

@section('title', 'Tableau de bord agent | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag" data-i18n="agent.tag">Agent</span>
            <h1 id="workspaceTitle" data-i18n="agent.dashboard_title">Tableau de bord des requêtes</h1>
            <p id="workspaceDescription" data-i18n="agent.dashboard_subtitle">Vue détaillée des requêtes à traiter dans votre service.</p>
        </div>
        <button id="refreshDashboard" class="btn ghost" data-i18n="action.refresh">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2 data-i18n="agent.stats">Statistiques</h2>
            <div id="statsGrid" class="grid two"></div>
        </section>
        <section class="card accent">
            <h2 data-i18n="agent.service_load">Charge du service</h2>
            <div id="servicesStats" class="list"></div>
        </section>
    </div>

    <section class="card" style="margin-top: 24px;">
        <h2 data-i18n="agent.role_actions">Actions de mon rôle</h2>
        <div id="actionsList" class="list"></div>
    </section>

    <section class="card" style="margin-top: 24px;">
        <h2 data-i18n="agent.focus">Indicateurs cibles</h2>
        <div id="focusList" class="grid two"></div>
    </section>

    <section class="card" style="margin-top: 24px;">
        <h2 data-i18n="agent.to_process">Requêtes à traiter</h2>
        <div id="traitementMessage" class="hint"></div>
        <div id="toProcessList" class="list"></div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!guardAgentFeature('dashboard')) {
                return;
            }

            applyI18n();

            const statsGrid = document.getElementById('statsGrid');
            const servicesStats = document.getElementById('servicesStats');
            const actionsList = document.getElementById('actionsList');
            const focusList = document.getElementById('focusList');
            const toProcessList = document.getElementById('toProcessList');
            const traitementMessage = document.getElementById('traitementMessage');
            const refreshBtn = document.getElementById('refreshDashboard');
            const workspaceTitle = document.getElementById('workspaceTitle');
            const workspaceDescription = document.getElementById('workspaceDescription');

            function statCard(label, value) {
                return `
                    <article class="req-card">
                        <div class="hint">${label}</div>
                        <strong style="font-size: 26px;">${value}</strong>
                    </article>
                `;
            }

            function escapeHtml(value) {
                return (value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function renderPieces(item) {
                const pieces = item.pieces_jointes || [];
                if (pieces.length === 0) {
                    return `<div class="hint">${__('request.attachments_none')}</div>`;
                }
                return `
                    <div class="hint">${__('request.attachments')}:</div>
                    <ul style="margin: 6px 0 0 18px; color: var(--muted);">
                        ${pieces.map((piece) => {
                            const url = piece.url || piece.chemin_fichier || '#';
                            const name = piece.nom_fichier || piece.type_piece || __('request.file');
                            return `<li><a href="${escapeHtml(url)}" target="_blank" rel="noopener">${escapeHtml(name)}</a></li>`;
                        }).join('')}
                    </ul>
                `;
            }

            async function traiterRequete(requeteId, action) {
                let motif = null;
                if (action === 'rejeter') {
                    motif = window.prompt(__('agent.reject_reason_prompt'));
                    if (!motif || !motif.trim()) {
                        traitementMessage.textContent = __('agent.reject_reason_required');
                        return;
                    }
                    motif = motif.trim();
                }

                traitementMessage.textContent = '';
                const response = await apiFetch(`/requetes/${requeteId}/traiter`, {
                    method: 'POST',
                    body: JSON.stringify({ action, motif }),
                });
                if (!response.ok) {
                    const payload = await response.json().catch(() => ({}));
                    traitementMessage.textContent = payload.message || __('agent.process_error');
                    return;
                }
                const payload = await response.json();
                traitementMessage.textContent = payload.next_service
                    ? `${payload.message} ${__('agent.next_service')}: ${payload.next_service}.`
                    : payload.message;
                await loadDashboard();
            }

            async function loadDashboard() {
                const response = await apiFetch('/dashboard/agent');
                if (!response.ok) {
                    statsGrid.innerHTML = `<p class="hint">${__('common.error_loading')}</p>`;
                    return;
                }
                const data = await response.json();
                const workspace = data.workspace || {};
                const stats = data.stats || {};

                if (workspace.title) {
                    workspaceTitle.textContent = workspace.title;
                }
                if (workspace.description) {
                    workspaceDescription.textContent = workspace.description;
                }
                if (Array.isArray(workspace.features)) {
                    setAgentFeatures(workspace.features);
                }
                if (workspace.service_key) {
                    setServiceKey(workspace.service_key);
                }
                if (workspace.service_nom) {
                    setServiceNom(workspace.service_nom);
                }
                if (workspace.service_type) {
                    setServiceType(workspace.service_type);
                }
                updateNavByRole();
                updateAuthUI();

                statsGrid.innerHTML = [
                    statCard(__('stats.total_to_process'), stats.total || 0),
                    statCard(__('status.en_attente'), stats.en_attente || 0),
                    statCard(__('status.en_traitement'), stats.en_traitement || 0),
                    statCard(__('status.traitee'), stats.traitee || 0),
                    statCard(__('status.rejetee'), stats.rejetee || 0),
                ].join('');

                servicesStats.innerHTML = (data.par_service || []).map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.nom_service}</strong>
                                <div class="hint">${item.total_requetes || 0} ${__('agent.requests_in_progress')}</div>
                            </div>
                        </div>
                    </article>
                `).join('');

                actionsList.innerHTML = (workspace.quick_actions || []).map((action) => `
                    <article class="req-card">
                        <div class="req-head">
                            <strong>${action.label}</strong>
                            <a class="btn ghost" href="${action.url}">${__('common.open')}</a>
                        </div>
                    </article>
                `).join('');

                focusList.innerHTML = (data.focus || []).map((item) => statCard(item.label, item.value || 0)).join('');
                if (!focusList.innerHTML) {
                    focusList.innerHTML = `<p class="hint">${__('agent.no_extra_indicator')}</p>`;
                }

                toProcessList.innerHTML = (data.a_traiter || []).map((item) => {
                    const nomEtudiant = item.etudiant
                        ? `${item.etudiant.prenom || ''} ${item.etudiant.nom || ''}`.trim()
                        : '-';
                    return `
                        <article class="req-card">
                            <div class="req-head">
                                <div>
                                    <strong>${escapeHtml(item.objet || __('request.no_subject'))}</strong>
                                    <div class="hint">${__('field.request_type')}: ${escapeHtml(item.type_requete ? item.type_requete.libelle : __('request.unknown_type'))}</div>
                                    <div class="hint">${__('request.student')}: ${escapeHtml(nomEtudiant)}</div>
                                    <div class="hint">${__('request.deposit_date')}: ${formatDate(item.date_depot)}</div>
                                </div>
                                <span class="status ${item.statut}">${formatStatus(item.statut)}</span>
                            </div>
                            <div class="hint" style="margin-top: 8px;">${__('field.description')}: ${escapeHtml(item.description || __('request.no_description'))}</div>
                            <div style="margin-top: 8px;">${renderPieces(item)}</div>
                            <div style="margin-top: 10px; display: flex; gap: 10px;">
                                <button class="btn primary" data-action="valider" data-id="${item.id}">${__('action.validate')}</button>
                                <button class="btn ghost" data-action="rejeter" data-id="${item.id}">${__('action.reject')}</button>
                            </div>
                        </article>
                    `;
                }).join('');

                if (!toProcessList.innerHTML) {
                    toProcessList.innerHTML = `<p class="hint">${__('agent.no_request_waiting')}</p>`;
                }
            }

            toProcessList.addEventListener('click', async (event) => {
                const btn = event.target.closest('button[data-action][data-id]');
                if (!btn) return;
                const action = btn.getAttribute('data-action');
                const id = Number(btn.getAttribute('data-id'));
                if (!id || !['valider', 'rejeter'].includes(action)) return;
                await traiterRequete(id, action);
            });

            refreshBtn.addEventListener('click', loadDashboard);
            loadDashboard();
            window.addEventListener('srm:language-changed', loadDashboard);
        });
    </script>
@endpush
