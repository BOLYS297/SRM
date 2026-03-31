@extends('layouts.portal')

@section('title', 'Tableau de bord étudiant | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag" data-i18n="student.tag">Étudiant</span>
            <h1 data-i18n="student.dashboard_title">Mon tableau de bord</h1>
            <p id="etudiantIdentity" data-i18n="student.dashboard_subtitle">Résumé de tes requêtes récentes.</p>
        </div>
        <button id="refreshEtudiantDashboard" class="btn ghost" data-i18n="action.refresh">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2 data-i18n="student.stats">Statistiques</h2>
            <div id="statsEtudiant" class="grid two"></div>
        </section>
        <section class="card accent">
            <h2 data-i18n="student.latest_requests">Dernières requêtes</h2>
            <div id="recentEtudiant" class="list"></div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (getRole() !== 'etudiant') {
                location.href = '/connexion';
                return;
            }

            applyI18n();

            const statsEtudiant = document.getElementById('statsEtudiant');
            const recentEtudiant = document.getElementById('recentEtudiant');
            const etudiantIdentity = document.getElementById('etudiantIdentity');
            const refreshBtn = document.getElementById('refreshEtudiantDashboard');

            function statCard(label, value) {
                return `
                    <article class="req-card">
                        <div class="hint">${label}</div>
                        <strong style="font-size: 26px;">${value}</strong>
                    </article>
                `;
            }

            async function loadDashboard() {
                const response = await apiFetch('/dashboard/etudiant');
                if (!response.ok) {
                    statsEtudiant.innerHTML = `<p class="hint">${__('common.error_loading')}</p>`;
                    return;
                }
                const data = await response.json();
                if (data.etudiant) {
                    etudiantIdentity.textContent = __('student.identity', {
                        prenom: data.etudiant.prenom,
                        nom: data.etudiant.nom,
                        matricule: data.etudiant.matricule,
                    });
                }
                const stats = data.stats || {};
                statsEtudiant.innerHTML = [
                    statCard(__('stats.total'), stats.total || 0),
                    statCard(__('status.en_attente'), stats.en_attente || 0),
                    statCard(__('status.en_traitement'), stats.en_traitement || 0),
                    statCard(__('status.traitee'), stats.traitee || 0),
                    statCard(__('status.rejetee'), stats.rejetee || 0),
                ].join('');

                recentEtudiant.innerHTML = (data.recents || []).map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.objet || __('request.no_subject')}</strong>
                                <div class="hint">${item.type_requete ? item.type_requete.libelle : __('request.unknown_type')}</div>
                            </div>
                            <span class="status ${item.statut}">${formatStatus(item.statut)}</span>
                        </div>
                        <div class="hint">${__('request.deposit_date')}: ${formatDate(item.date_depot)}</div>
                    </article>
                `).join('');

                if (!recentEtudiant.innerHTML) {
                    recentEtudiant.innerHTML = `<p class="hint">${__('student.no_recent_requests')}</p>`;
                }
            }

            refreshBtn.addEventListener('click', loadDashboard);
            loadDashboard();
            window.addEventListener('srm:language-changed', loadDashboard);
        });
    </script>
@endpush
