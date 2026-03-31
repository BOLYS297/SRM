@extends('layouts.portal')

@section('title', 'Gestion étudiants | SRM')

@section('content')
    @php($filieres = \App\Support\FiliereCatalog::all())

    <div class="page-head">
        <div>
            <span class="tag" data-i18n="agent.tag">Agent</span>
            <h1 data-i18n="students.title">Étudiants</h1>
            <p data-i18n="students.subtitle">Créer un étudiant et son compte de connexion.</p>
        </div>
        <button id="refreshEtudiants" class="btn ghost" data-i18n="action.refresh">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2 data-i18n="students.new">Nouvel étudiant</h2>
            <form id="etudiantForm" class="form-grid">
                <div class="form-grid two">
                    <div>
                        <label for="matricule" data-i18n="field.matricule">Matricule</label>
                        <input id="matricule" name="matricule" type="text" required>
                    </div>
                    <div>
                        <label for="date_naissance" data-i18n="field.birth_date">Date de naissance</label>
                        <input id="date_naissance" name="date_naissance" type="date" required>
                    </div>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="nom" data-i18n="field.last_name">Nom</label>
                        <input id="nom" name="nom" type="text" required>
                    </div>
                    <div>
                        <label for="prenom" data-i18n="field.first_name">Prénom</label>
                        <input id="prenom" name="prenom" type="text" required>
                    </div>
                </div>
                <div>
                    <label for="filiere" data-i18n="field.department">Filière</label>
                    <select id="filiere" name="filiere" required>
                        <option value="" data-i18n="common.choose_department">Choisir la filière</option>
                        @foreach ($filieres as $filiere)
                            <option value="{{ $filiere['code'] }}">{{ $filiere['code'] }} - {{ $filiere['libelle'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="telephone" data-i18n="field.phone">Téléphone</label>
                        <input id="telephone" name="telephone" type="text">
                    </div>
                    <div>
                        <label for="email" data-i18n="field.email">Email</label>
                        <input id="email" name="email" type="email">
                    </div>
                </div>
                <div id="etudiantMessage" class="hint"></div>
                <button class="btn primary" type="submit" data-i18n="action.save">Enregistrer</button>
                <button class="btn ghost hidden" type="button" id="cancelEtudiant" data-i18n="action.cancel">Annuler</button>
            </form>
        </section>

        <section class="card accent">
            <h2 data-i18n="students.create_account">Créer un compte</h2>
            <form id="compteForm" class="form-grid">
                <div>
                    <label for="etudiant_id" data-i18n="field.student">Étudiant</label>
                    <select id="etudiant_id" name="etudiant_id" required>
                        <option value="" data-i18n="common.loading">Chargement...</option>
                    </select>
                </div>
                <div>
                    <label for="compte_email" data-i18n="field.login_email">Email de connexion</label>
                    <input id="compte_email" name="compte_email" type="email" required>
                </div>
                <div>
                    <label for="compte_password" data-i18n="field.password">Mot de passe</label>
                    <input id="compte_password" name="compte_password" type="password" required data-password-toggle>
                </div>
                <div>
                    <label for="compte_name" data-i18n="field.account_name_optional">Nom du compte (optionnel)</label>
                    <input id="compte_name" name="compte_name" type="text">
                </div>
                <div id="compteMessage" class="hint"></div>
                <button class="btn primary" type="submit" data-i18n="students.create_account">Créer le compte</button>
            </form>
        </section>
    </div>

    <section class="card" style="margin-top: 24px;">
        <h2 data-i18n="students.list">Liste des étudiants</h2>
        <div id="etudiantsList" class="list"></div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!guardAgentFeature('manage_etudiants')) {
                return;
            }

            applyI18n();

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
                const options = [`<option value="">${__('common.choose')}</option>`]
                    .concat(etudiants.map((item) => `
                        <option value="${item.id}">#${item.id} ${item.prenom} ${item.nom}</option>
                    `));
                select.innerHTML = options.join('');
            }

            async function loadEtudiants() {
                const response = await apiFetch('/etudiants');
                if (!response.ok) {
                    etudiantsList.innerHTML = `<p class="hint">${__('common.error_loading')}</p>`;
                    return;
                }
                etudiants = await response.json();
                renderSelect();
                etudiantsList.innerHTML = etudiants.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.prenom} ${item.nom}</strong>
                                <div class="hint">${__('field.matricule')}: ${item.matricule} | ${__('field.department')}: ${item.filiere || '-'}</div>
                                <div class="hint">${__('field.phone')}: ${item.telephone || '-'} | ${__('field.email')}: ${item.email || '-'}</div>
                                <div class="hint">${__('students.account')}: ${item.user ? __('common.yes') : __('common.no')}</div>
                            </div>
                            <div>
                                <button class="btn ghost" data-action="edit" data-id="${item.id}">${__('action.edit')}</button>
                                <button class="btn ghost" data-action="delete" data-id="${item.id}">${__('action.delete')}</button>
                                <button class="btn ghost" data-action="compte" data-id="${item.id}">${__('students.create_account')}</button>
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
                    etudiantForm.filiere.value = data.filiere || '';
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
                    filiere: etudiantForm.filiere.value,
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
                    etudiantMessage.textContent = __('students.save_error');
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
                    compteMessage.textContent = __('students.choose_student');
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
                    compteMessage.textContent = __('students.account_error');
                    return;
                }
                compteForm.reset();
                compteMessage.textContent = __('students.account_created');
                loadEtudiants();
            });

            cancelEtudiant.addEventListener('click', resetEtudiantForm);
            refreshBtn.addEventListener('click', loadEtudiants);

            loadEtudiants();
            window.addEventListener('srm:language-changed', loadEtudiants);
        });
    </script>
@endpush
