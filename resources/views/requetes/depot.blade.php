@extends('layouts.portal')

@section('title', 'Dépôt de requête | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag" data-i18n="depot.tag">Dépôt</span>
            <h1 data-i18n="depot.title">Nouvelle requête</h1>
            <p data-i18n="depot.subtitle">Remplis les informations. Le système place la requête en attente.</p>
        </div>
        <div class="hint" data-i18n="depot.target_delay">Délai cible : 72h</div>
    </div>

    <div class="grid two">
        <section class="card">
            <form id="depotForm" class="form-grid">
                <div>
                    <label for="objet" data-i18n="field.subject">Objet</label>
                    <input id="objet" name="objet" type="text" required>
                </div>
                <div>
                    <label for="type_requete_id" data-i18n="field.request_type">Type de requête</label>
                    <select id="type_requete_id" name="type_requete_id" required>
                        <option value="" data-i18n="common.loading">Chargement...</option>
                    </select>
                </div>
                <div>
                    <label for="description" data-i18n="field.description_optional">Description (optionnelle)</label>
                    <textarea id="description" name="description" data-i18n-placeholder="depot.description_placeholder" placeholder="Explique ta demande"></textarea>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="annee_depot" data-i18n="field.academic_year">Année de dépôt</label>
                        <input id="annee_depot" name="annee_depot" type="text" placeholder="2024-2025" required>
                    </div>
                    <div>
                        <label for="filiere_depot" data-i18n="field.department">Filière</label>
                        <input id="filiere_depot" name="filiere_depot" type="text" placeholder="GI" readonly required>
                        <div class="hint" data-i18n="depot.prefilled_major">Pré-rempli depuis ton profil étudiant.</div>
                    </div>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="niveau_depot" data-i18n="field.level">Niveau</label>
                        <input id="niveau_depot" name="niveau_depot" type="text" placeholder="N2" required>
                    </div>
                    <div>
                        <label for="date_depot" data-i18n="field.deposit_date">Date de dépôt</label>
                        <input id="date_depot" name="date_depot" type="text" readonly>
                    </div>
                </div>
                <div>
                    <label for="piece_jointe" data-i18n="field.attachment_optional">Pièce jointe (optionnelle)</label>
                    <input id="piece_jointe" name="piece_jointe" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="hint" data-i18n="depot.allowed_formats">Formats acceptés : PDF, JPG, PNG. Taille max 5 Mo.</div>
                </div>
                <div id="depotMessage" class="hint"></div>
                <button id="depotSubmit" class="btn primary" type="submit" data-i18n="action.submit_request">Déposer la requête</button>
            </form>
        </section>
        <section class="card accent">
            <h2 data-i18n="depot.standard_path_title">Parcours standard</h2>
            <p data-i18n="depot.standard_path">Service courrier -> Direction -> DA -> Département cible (selon filière) -> Cellule info -> Scolarité</p>
            <p class="hint" data-i18n="depot.deadline_note">Le délai est mesuré entre dépôt et décision finale.</p>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = getToken();
            if (!token || getRole() !== 'etudiant') {
                location.href = '/connexion';
                return;
            }

            applyI18n();

            const dateInput = document.getElementById('date_depot');
            const now = new Date();
            const iso = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                .toISOString()
                .slice(0, 19)
                .replace('T', ' ');
            dateInput.value = iso;

            const typeSelect = document.getElementById('type_requete_id');
            const filiereInput = document.getElementById('filiere_depot');
            const submitButton = document.getElementById('depotSubmit');
            const message = document.getElementById('depotMessage');

            async function loadTypes() {
                const response = await apiFetch('/types-requetes');
                if (!response.ok) {
                    typeSelect.innerHTML = `<option value="">${__('common.error_loading')}</option>`;
                    return;
                }
                const data = await response.json();
                typeSelect.innerHTML = `<option value="">${__('common.choose')}</option>`;
                data.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.libelle;
                    typeSelect.appendChild(option);
                });
            }

            async function loadStudentFiliere() {
                const response = await apiFetch('/etudiants/me');
                if (!response.ok) {
                    submitButton.disabled = true;
                    message.textContent = __('depot.filiere_load_error');
                    return;
                }

                const data = await response.json();
                const filiere = data && data.etudiant ? (data.etudiant.filiere || '').trim() : '';
                if (!filiere) {
                    submitButton.disabled = true;
                    message.textContent = __('depot.filiere_missing');
                    return;
                }

                filiereInput.value = filiere;
            }

            loadTypes();
            loadStudentFiliere();

            const form = document.getElementById('depotForm');
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';
                const payload = {
                    date_depot: form.date_depot.value,
                    objet: form.objet.value.trim(),
                    description: form.description.value.trim(),
                    annee_depot: form.annee_depot.value.trim(),
                    filiere_depot: form.filiere_depot.value.trim(),
                    niveau_depot: form.niveau_depot.value.trim(),
                    type_requete_id: Number(form.type_requete_id.value),
                };
                const response = await apiFetch('/requetes', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    const errorData = await response.json().catch(() => null);
                    message.textContent = errorData && errorData.message ? errorData.message : __('depot.submit_error');
                    return;
                }
                const data = await response.json();
                const file = form.piece_jointe.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('requete_id', data.id);
                    formData.append('fichier', file);
                    const uploadResponse = await apiFetch('/pieces-jointes', {
                        method: 'POST',
                        body: formData,
                    });
                    if (!uploadResponse.ok) {
                        message.textContent = __('depot.attachment_error');
                        const currentFiliere = filiereInput.value;
                        form.reset();
                        form.date_depot.value = iso;
                        form.filiere_depot.value = currentFiliere;
                        return;
                    }
                }
                const currentFiliere = filiereInput.value;
                form.reset();
                form.date_depot.value = iso;
                form.filiere_depot.value = currentFiliere;
                message.textContent = __('depot.submit_success');
            });
        });
    </script>
@endpush
