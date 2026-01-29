@extends('layouts.portal')

@section('title', 'Dashboard etudiant | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Etudiant</span>
            <h1>Mon tableau de bord</h1>
            <p>Resume de tes requetes recentes.</p>
        </div>
        <button id="refreshEtudiantDashboard" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Statistiques</h2>
            <div id="statsEtudiant" class="grid two"></div>
        </section>
        <section class="card accent">
            <h2>Dernieres requetes</h2>
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

            const statsEtudiant = document.getElementById('statsEtudiant');
            const recentEtudiant = document.getElementById('recentEtudiant');
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
                    statsEtudiant.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                const stats = data.stats || {};
                statsEtudiant.innerHTML = [
                    statCard('Total', stats.total || 0),
                    statCard('En attente', stats.en_attente || 0),
                    statCard('En traitement', stats.en_traitement || 0),
                    statCard('Traitee', stats.traitee || 0),
                    statCard('Rejetee', stats.rejetee || 0),
                ].join('');

                recentEtudiant.innerHTML = (data.recents || []).map((item) => `
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
