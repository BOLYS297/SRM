@extends('layouts.portal')

@section('title', 'Depot de requete | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Depot</span>
            <h1>Nouvelle requete</h1>
            <p>Remplis les informations. Le systeme place la requete en attente.</p>
        </div>
        <div class="hint">Delai cible: 72h</div>
    </div>

    <div class="grid two">
        <section class="card">
            <form id="depotForm" class="form-grid">
                <div>
                    <label for="objet">Objet</label>
                    <input id="objet" name="objet" type="text" required>
                </div>
                <div>
                    <label for="type_requete_id">Type de requete</label>
                    <select id="type_requete_id" name="type_requete_id" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>
                <div>
                    <label for="description">Description (optionnel)</label>
                    <textarea id="description" name="description" placeholder="Explique ta demande"></textarea>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="annee_depot">Annee depot</label>
                        <input id="annee_depot" name="annee_depot" type="text" placeholder="2024-2025" required>
                    </div>
                    <div>
                        <label for="filiere_depot">Filiere</label>
                        <input id="filiere_depot" name="filiere_depot" type="text" placeholder="INFO" required>
                    </div>
                </div>
                <div class="form-grid two">
                    <div>
                        <label for="niveau_depot">Niveau</label>
                        <input id="niveau_depot" name="niveau_depot" type="text" placeholder="N2" required>
                    </div>
                    <div>
                        <label for="date_depot">Date depot</label>
                        <input id="date_depot" name="date_depot" type="text" readonly>
                    </div>
                </div>
                <div>
                    <label for="piece_jointe">Piece jointe (optionnel)</label>
                    <input id="piece_jointe" name="piece_jointe" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="hint">Formats acceptes: PDF, JPG, PNG. Taille max 5 Mo.</div>
                </div>
                <div id="depotMessage" class="hint"></div>
                <button class="btn primary" type="submit">Deposer la requete</button>
            </form>
        </section>
        <section class="card accent">
            <h2>Parcours standard</h2>
            <p>Conseil orientation → Service courrier → Direction → DA → Departement → Cellule info → Scolarite</p>
            <p class="hint">Le delai est mesure entre depot et decision finale.</p>
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

            const dateInput = document.getElementById('date_depot');
            const now = new Date();
            const iso = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                .toISOString()
                .slice(0, 19)
                .replace('T', ' ');
            dateInput.value = iso;

            const typeSelect = document.getElementById('type_requete_id');
            const message = document.getElementById('depotMessage');

            async function loadTypes() {
                const response = await apiFetch('/types-requetes');
                if (!response.ok) {
                    typeSelect.innerHTML = '<option value="">Erreur chargement</option>';
                    return;
                }
                const data = await response.json();
                typeSelect.innerHTML = '<option value="">Choisir</option>';
                data.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.libelle;
                    typeSelect.appendChild(option);
                });
            }

            loadTypes();

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
                    message.textContent = 'Erreur depot. Verifie les champs.';
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
                        message.textContent = 'Requete deposee, mais piece jointe en erreur.';
                        form.reset();
                        form.date_depot.value = iso;
                        return;
                    }
                }
                form.reset();
                form.date_depot.value = iso;
                message.textContent = 'Requete deposee avec succes.';
            });
        });
    </script>
@endpush
