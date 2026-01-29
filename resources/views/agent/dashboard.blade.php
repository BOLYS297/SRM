@extends('layouts.portal')

@section('title', 'Dashboard agent | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Dashboard requetes</h1>
            <p>Vue rapide sur les volumes et les services.</p>
        </div>
        <button id="refreshDashboard" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Statistiques</h2>
            <div id="statsGrid" class="grid two"></div>
        </section>
        <section class="card accent">
            <h2>Requetes par service</h2>
            <div id="servicesStats" class="list"></div>
        </section>
    </div>

    <section class="card" style="margin-top: 24px;">
        <h2>Requetes recentes</h2>
        <div id="recentList" class="list"></div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (getRole() !== 'agent') {
                location.href = '/connexion';
                return;
            }

            const statsGrid = document.getElementById('statsGrid');
            const servicesStats = document.getElementById('servicesStats');
            const recentList = document.getElementById('recentList');
            const refreshBtn = document.getElementById('refreshDashboard');

            function statCard(label, value) {
                return `
                    <article class="req-card">
                        <div class="hint">${label}</div>
                        <strong style="font-size: 26px;">${value}</strong>
                    </article>
                `;
            }

            async function loadDashboard() {
                const response = await apiFetch('/dashboard/agent');
                if (!response.ok) {
                    statsGrid.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                const stats = data.stats || {};
                statsGrid.innerHTML = [
                    statCard('Total', stats.total || 0),
                    statCard('En attente', stats.en_attente || 0),
                    statCard('En traitement', stats.en_traitement || 0),
                    statCard('Traitee', stats.traitee || 0),
                    statCard('Rejetee', stats.rejetee || 0),
                ].join('');

                servicesStats.innerHTML = (data.par_service || []).map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.nom_service}</strong>
                                <div class="hint">${item.total_requetes || 0} requetes</div>
                            </div>
                        </div>
                    </article>
                `).join('');

                recentList.innerHTML = (data.recents || []).map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.objet || 'Sans objet'}</strong>
                                <div class="hint">${item.type_requete ? item.type_requete.libelle : 'Type inconnu'}</div>
                            </div>
                            <span class="status ${item.statut}">${item.statut}</span>
                        </div>
                        <div class="hint">Depot: ${formatDate(item.date_depot)}</div>
                    </article>
                `).join('');
            }

            refreshBtn.addEventListener('click', loadDashboard);
            loadDashboard();
        });
    </script>
@endpush
