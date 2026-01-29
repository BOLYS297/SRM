@extends('layouts.portal')

@section('title', 'Gestion types de requete | SRM')

@section('content')
    <div class="page-head">
        <div>
            <span class="tag">Agent</span>
            <h1>Types de requete</h1>
            <p>Parametre les types et leurs delais cibles.</p>
        </div>
        <button id="refreshTypes" class="btn ghost">Actualiser</button>
    </div>

    <div class="grid two">
        <section class="card">
            <h2>Nouveau type</h2>
            <form id="typeForm" class="form-grid">
                <div>
                    <label for="libelle">Libelle</label>
                    <input id="libelle" name="libelle" type="text" required>
                </div>
                <div>
                    <label for="delai_cible_hrs">Delai cible (heures)</label>
                    <input id="delai_cible_hrs" name="delai_cible_hrs" type="number" min="1" max="999" value="72" required>
                </div>
                <div id="typeMessage" class="hint"></div>
                <button class="btn primary" type="submit">Enregistrer</button>
                <button class="btn ghost hidden" type="button" id="cancelEditType">Annuler</button>
            </form>
        </section>
        <section class="card accent">
            <h2>Liste des types</h2>
            <div id="typesList" class="list"></div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const role = getRole();
            if (role !== 'agent') {
                location.href = '/connexion';
                return;
            }

            const form = document.getElementById('typeForm');
            const message = document.getElementById('typeMessage');
            const list = document.getElementById('typesList');
            const refreshBtn = document.getElementById('refreshTypes');
            const cancelEdit = document.getElementById('cancelEditType');
            let editingId = null;

            function resetForm() {
                form.reset();
                form.delai_cible_hrs.value = 72;
                editingId = null;
                cancelEdit.classList.add('hidden');
            }

            async function loadTypes() {
                const response = await apiFetch('/types-requetes');
                if (!response.ok) {
                    list.innerHTML = '<p class="hint">Erreur chargement.</p>';
                    return;
                }
                const data = await response.json();
                list.innerHTML = data.map((item) => `
                    <article class="req-card">
                        <div class="req-head">
                            <div>
                                <strong>${item.libelle}</strong>
                                <div class="hint">Delai: ${item.delai_cible_hrs}h</div>
                            </div>
                            <div>
                                <button class="btn ghost" data-action="edit" data-id="${item.id}">Editer</button>
                                <button class="btn ghost" data-action="delete" data-id="${item.id}">Supprimer</button>
                            </div>
                        </div>
                    </article>
                `).join('');
            }

            list.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const id = button.getAttribute('data-id');
                const action = button.getAttribute('data-action');
                if (action === 'delete') {
                    const response = await apiFetch(`/types-requetes/${id}`, { method: 'DELETE' });
                    if (response.ok) {
                        loadTypes();
                    }
                    return;
                }
                if (action === 'edit') {
                    const response = await apiFetch(`/types-requetes/${id}`);
                    if (!response.ok) return;
                    const data = await response.json();
                    editingId = data.id;
                    form.libelle.value = data.libelle;
                    form.delai_cible_hrs.value = data.delai_cible_hrs;
                    cancelEdit.classList.remove('hidden');
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                message.textContent = '';
                const payload = {
                    libelle: form.libelle.value.trim(),
                    delai_cible_hrs: Number(form.delai_cible_hrs.value),
                };
                const method = editingId ? 'PUT' : 'POST';
                const url = editingId ? `/types-requetes/${editingId}` : '/types-requetes';
                const response = await apiFetch(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                if (!response.ok) {
                    message.textContent = 'Erreur enregistrement.';
                    return;
                }
                resetForm();
                loadTypes();
            });

            cancelEdit.addEventListener('click', resetForm);
            refreshBtn.addEventListener('click', loadTypes);
            loadTypes();
        });
    </script>
@endpush
