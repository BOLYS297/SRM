@extends('layouts.portal')

@section('title', 'Historique service | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag" data-i18n="agent.tag">Agent</span>
            <h1 data-i18n="history.title">Historique du service</h1>
            <p data-i18n="history.subtitle">Requêtes déjà traitées par votre service.</p>
        </div>
        <button id="refreshHistorique" class="btn ghost" data-i18n="action.refresh">Actualiser</button>
    </div>

    <section class="card">
        <div id="historiqueList" class="list"></div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!guardAgentFeature('process_etapes')) {
                return;
            }

            applyI18n();

            const list = document.getElementById('historiqueList');
            const refreshBtn = document.getElementById('refreshHistorique');

            function escapeHtml(value) {
                return (value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            async function loadHistorique() {
                const response = await apiFetch('/etape-traitements');
                if (!response.ok) {
                    list.innerHTML = `<p class="hint">${__('common.error_loading')}</p>`;
                    return;
                }

                const rows = await response.json();
                const closed = (rows || []).filter((row) => !!row.date_sortie);

                list.innerHTML = closed.map((row) => {
                    const sujet = row.requete ? row.requete.objet : `${__('request.label')} #${row.requete_id}`;
                    return `
                        <article class="req-card">
                            <div class="req-head">
                                <strong>${escapeHtml(sujet || __('request.no_subject'))}</strong>
                                <span class="status traitee">${escapeHtml(row.action || __('status.traitee'))}</span>
                            </div>
                            <div class="hint">${__('history.entry_date')}: ${formatDate(row.date_entree)}</div>
                            <div class="hint">${__('history.exit_date')}: ${formatDate(row.date_sortie)}</div>
                            <div class="hint">${__('history.comment')}: ${escapeHtml(row.observation || '-')}</div>
                        </article>
                    `;
                }).join('');

                if (!list.innerHTML) {
                    list.innerHTML = `<p class="hint">${__('history.empty')}</p>`;
                }
            }

            refreshBtn.addEventListener('click', loadHistorique);
            loadHistorique();
            window.addEventListener('srm:language-changed', loadHistorique);
        });
    </script>
@endpush
