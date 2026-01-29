@extends('layouts.portal')

@section('title', 'Gestion etudiants | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Etudiants</h1>
            <p>Creer un etudiant et son compte de connexion.</p>
        </div>
        <button id="refreshEtudiants" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Nouvel etudiant</h2>
            <form id="etudiantForm" class="form-grid">
                <div class="form-grid two">
                    <div>
                        <label for="matricule">Matricule</label>
                        <input id="matricule" name="matricule" type="text" required>
                    </div>
                    <div>
                        <label for="date_naissance">Date naissance</label>
                        <input id="date_naissance" name="date_naissance" type="date" required>
                    </div>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="nom">Nom</label>
                        <input id="nom" name="nom" type="text" required>
                    </div>
                    <div>
                        <label for="prenom">Prenom</label>
                        <input id="prenom" name="prenom" type="text" required>
                    </div>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="telephone">Telephone</label>
                        <input id="telephone" name="telephone" type="text">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email">
                    </div>
                </div>
                <div id="etudiantMessage" class="hint"></div>
                <button class="btn primary" type="submit">Enregistrer</button>
                <button class="btn ghost hidden" type="button" id="cancelEtudiant">Annuler</button>
            </form>
        </section>

        <section class="card accent">
            <h2>Creer un compte</h2>
            <form id="compteForm" class="form-grid">
                <div>
                    <label for="etudiant_id">Etudiant</label>
                    <select id="etudiant_id" name="etudiant_id" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>
                <div>
                    <label for="compte_email">Email connexion</label>
                    <input id="compte_email" name="compte_email" type="email" required>
                </div>
                <div>
                    <label for="compte_password">Mot de passe</label>
                    <input id="compte_password" name="compte_password" type="password" required>
                </div>
                <div>
                    <label for="compte_name">Nom compte (optionnel)</label>
                    <input id="compte_name" name="compte_name" type="text">
                </div>
                <div id="compteMessage" class="hint"></div>
                <button class="btn primary" type="submit">Creer compte</button>
            </form>
        </section>
    </div>

    <section class="card" style="margin-top: 24px;">
        <h2>Liste des etudiants</h2>
        <div id="etudiantsList" class="list"></div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (getRole() !== 'agent') {
                location.href = '/connexion';
                return;
            }

            const etudiantForm = document.getElementById('etudiantForm');
            const etudiantMessage = document.getElementById('etudiantMessage');
            const etudiantsList = document.getElementById('etudiantsList');
            const refreshBtn = document.getElementById('refreshEtudiants');
            const cancelEtudiant = document.getElementById('cancelEtudiant');

            const compteForm = document.getElementById('compteForm');
            const compteMessage = document.getElementById('compteMessage');

            let editingId = null;
            let etudiants = [];

            function resetEtudiantForm() {
                etudiantForm.reset();
                editingId = null;
                cancelEtudiant.classList.add('hidden');
            }

            function renderSelect() {
                const select = document.getElementById('etudiant_id');
                const options = ['<option value="">Choisir</option>']
                    .concat(etudiants.map((item) => `
                        <option value="${item.id}">#${item.id} ${item.prenom} ${item.nom}</option>
                    `));
                select.innerHTML = options.join('');
            }

            async function loadEtudiants() {
                const response = await apiFetch('/etudiants');
                if (!response.ok) {
                    etudiantsList.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                etudiants = await response.json();
                renderSelect();
                etudiantsList.innerHTML = etudiants.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.prenom} ${item.nom}</strong>
                                <div class="hint">Matricule: ${item.matricule} | Tel: ${item.telephone || '-'}</div>
                                <div class="hint">Email: ${item.email || '-'}</div>
                                <div class="hint">Compte: ${item.user ? 'Oui' : 'Non'}</div>
                            </div>
                            <div>
                                <button class="btn ghost" data-action="edit" data-id="${item.id}">Editer</button>
                                <button class="btn ghost" data-action="delete" data-id="${item.id}">Supprimer</button>
                                <button class="btn ghost" data-action="compte" data-id="${item.id}">Compte</button>
                            </div>
                        </div>
                    </article>
                `).join('');
            }

            etudiantsList.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const id = button.getAttribute('data-id');
                const action = button.getAttribute('data-action');

                if (action === 'delete') {
                    const response = await apiFetch(`/etudiants/${id}`, { method: 'DELETE' });
                    if (response.ok) {
                        loadEtudiants();
                    }
                    return;
                }

                if (action === 'edit') {
                    const response = await apiFetch(`/etudiants/${id}`);
                    if (!response.ok) return;
                    const data = await response.json();
                    editingId = data.id;
                    etudiantForm.matricule.value = data.matricule;
                    etudiantForm.nom.value = data.nom;
                    etudiantForm.prenom.value = data.prenom;
                    etudiantForm.date_naissance.value = data.date_naissance;
                    etudiantForm.telephone.value = data.telephone || '';
                    etudiantForm.email.value = data.email || '';
                    cancelEtudiant.classList.remove('hidden');
                    return;
                }

                if (action === 'compte') {
                    compteForm.etudiant_id.value = id;
                    compteForm.compte_email.focus();
                }
            });

            etudiantForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                etudiantMessage.textContent = '';
                const payload = {
                    matricule: etudiantForm.matricule.value.trim(),
                    nom: etudiantForm.nom.value.trim(),
                    prenom: etudiantForm.prenom.value.trim(),
                    date_naissance: etudiantForm.date_naissance.value,
                    telephone: etudiantForm.telephone.value.trim(),
                    email: etudiantForm.email.value.trim(),
                };
                const method = editingId ? 'PUT' : 'POST';
                const url = editingId ? `/etudiants/${editingId}` : '/etudiants';
                const response = await apiFetch(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    etudiantMessage.textContent = 'Erreur enregistrement.';
                    return;
                }
                resetEtudiantForm();
                loadEtudiants();
            });

            compteForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                compteMessage.textContent = '';
                const etudiantId = compteForm.etudiant_id.value;
                if (!etudiantId) {
                    compteMessage.textContent = 'Choisir un etudiant.';
                    return;
                }
                const payload = {
                    email: compteForm.compte_email.value.trim(),
                    password: compteForm.compte_password.value,
                    name: compteForm.compte_name.value.trim(),
                };
                const response = await apiFetch(`/etudiants/${etudiantId}/compte`, {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    compteMessage.textContent = 'Erreur creation compte.';
                    return;
                }
                compteForm.reset();
                compteMessage.textContent = 'Compte cree.';
                loadEtudiants();
            });

            cancelEtudiant.addEventListener('click', resetEtudiantForm);
            refreshBtn.addEventListener('click', loadEtudiants);

            loadEtudiants();
        });
    </script>
@endpush
