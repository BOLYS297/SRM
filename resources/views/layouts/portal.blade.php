<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Portail SRM')</title>

        {{-- PWA Meta Tags --}}
        <meta name="theme-color" content="#ef6b3a">
        <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
        <meta name="description" content="Système de Gestion des Requêtes Étudiantes - Soumettez et suivez vos demandes académiques">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="SRM">

        {{-- Icons --}}
        <link rel="manifest" href="/manifest.json">
        <link rel="icon" type="image/png" href="/images/icons/icon-512">
        <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|archivo:400,500,600" rel="stylesheet">
        <style>
            :root {
                --bg: #f4efe5;
                --bg-2: #e6f1ea;
                --ink: #1f2a28;
                --muted: #6a7a74;
                --accent: #ef6b3a;
                --accent-2: #1f7a6c;
                --surface: #fffaf2;
                --line: #e5d9c8;
                --shadow: 0 16px 40px rgba(26, 31, 28, 0.12);
                --radius: 18px;
                --font-display: "Space Grotesk", "Trebuchet MS", sans-serif;
                --font-body: "Archivo", "Trebuchet MS", sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                color: var(--ink);
                font-family: var(--font-body);
                background:
                    radial-gradient(1200px 420px at 85% -10%, rgba(31, 122, 108, 0.18), transparent 60%),
                    radial-gradient(800px 420px at 0% 10%, rgba(239, 107, 58, 0.14), transparent 60%),
                    linear-gradient(140deg, var(--bg), var(--bg-2));
                min-height: 100vh;
            }

            .ambient {
                position: fixed;
                inset: 0;
                pointer-events: none;
                background-image:
                    linear-gradient(transparent 31px, rgba(31, 122, 108, 0.06) 32px),
                    linear-gradient(90deg, transparent 31px, rgba(239, 107, 58, 0.06) 32px);
                background-size: 64px 64px;
                opacity: 0.35;
            }

            .topbar {
                position: sticky;
                top: 0;
                z-index: 10;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 18px 6vw;
                background: rgba(255, 250, 242, 0.9);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid var(--line);
            }

            .brand {
                display: flex;
                gap: 12px;
                align-items: center;
                font-family: var(--font-display);
                font-weight: 700;
                letter-spacing: -0.02em;
                text-decoration: none;
            }

            .logo {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                border-radius: 12px;
                background: linear-gradient(140deg, var(--accent), #f59f45);
                color: #fff;
                font-size: 18px;
                box-shadow: var(--shadow);
            }

            .brand small {
                display: block;
                font-size: 12px;
                color: var(--muted);
                font-weight: 500;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            .nav {
                display: flex;
                gap: 18px;
                font-weight: 600;
                flex: 1 1 auto;
                min-width: 260px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav a {
                text-decoration: none;
                color: var(--ink);
                padding: 8px 12px;
                border-radius: 999px;
                transition: background 0.2s ease, color 0.2s ease;
            }

            .nav a:hover {
                background: rgba(31, 122, 108, 0.12);
                color: var(--accent-2);
            }

            .auth {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-shrink: 0;
            }

            .pill {
                border-radius: 999px;
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 600;
                background: rgba(31, 122, 108, 0.12);
                color: var(--accent-2);
            }

            .pill.offline {
                background: rgba(239, 107, 58, 0.15);
                color: #a53d1b;
            }

            .btn {
                border: none;
                cursor: pointer;
                padding: 10px 16px;
                border-radius: 12px;
                font-weight: 600;
                font-family: var(--font-body);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .btn.primary {
                background: linear-gradient(140deg, var(--accent), #f59f45);
                color: #fff;
                box-shadow: 0 12px 24px rgba(239, 107, 58, 0.25);
            }

            .btn.ghost {
                background: #fff;
                color: var(--ink);
                border: 1px solid var(--line);
            }

            .btn.lang-switch {
                background: linear-gradient(135deg, var(--accent-2), #1d6b60);
                color: #fff;
                border: 1px solid rgba(255, 255, 255, 0.2);
                min-width: 88px;
                box-shadow: 0 10px 20px rgba(31, 122, 108, 0.3);
            }

            .btn:hover {
                transform: translateY(-1px);
            }

            .content {
                padding: 32px 6vw 72px;
                max-width: 1200px;
                margin: 0 auto;
            }

            .page-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 24px;
                margin-bottom: 24px;
            }

            h1 {
                font-family: var(--font-display);
                font-size: clamp(28px, 4vw, 44px);
                margin: 0 0 10px;
                letter-spacing: -0.03em;
            }

            h2 {
                font-family: var(--font-display);
                font-size: 22px;
                margin: 0 0 8px;
            }

            p {
                margin: 0 0 10px;
                color: var(--muted);
                line-height: 1.6;
            }

            .grid {
                display: grid;
                gap: 20px;
            }

            .grid.two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .card {
                background: var(--surface);
                border-radius: var(--radius);
                border: 1px solid var(--line);
                box-shadow: var(--shadow);
                padding: 22px;
                animation: rise 0.6s ease;
            }

            .card.accent {
                background: linear-gradient(140deg, rgba(31, 122, 108, 0.1), rgba(239, 107, 58, 0.08)), var(--surface);
            }

            .tag {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                border-radius: 999px;
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 600;
                background: rgba(31, 122, 108, 0.12);
                color: var(--accent-2);
            }

            label {
                display: block;
                font-weight: 600;
                margin-bottom: 6px;
            }

            input, select, textarea {
                width: 100%;
                padding: 12px 14px;
                border-radius: 12px;
                border: 1px solid var(--line);
                background: #fff;
                font-family: var(--font-body);
                font-size: 14px;
            }

            .password-wrap {
                position: relative;
                width: 100%;
            }

            .password-wrap input {
                padding-right: 92px;
            }

            .password-toggle {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                border: none;
                background: transparent;
                color: var(--accent-2);
                font-weight: 700;
                font-size: 12px;
                cursor: pointer;
                padding: 4px 6px;
                border-radius: 8px;
            }

            .password-toggle:hover {
                background: rgba(31, 122, 108, 0.1);
            }

            textarea {
                min-height: 120px;
                resize: vertical;
            }

            .form-grid {
                display: grid;
                gap: 16px;
            }

            .form-grid.two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .hint {
                font-size: 12px;
                color: var(--muted);
            }

            .list {
                display: grid;
                gap: 14px;
            }

            .timeline {
                display: grid;
                gap: 12px;
                border-left: 2px solid rgba(31, 122, 108, 0.2);
                padding-left: 18px;
                margin-top: 10px;
            }

            .timeline-item {
                position: relative;
                padding-left: 8px;
            }

            .timeline-item::before {
                content: "";
                position: absolute;
                left: -26px;
                top: 6px;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: var(--accent-2);
                box-shadow: 0 0 0 4px rgba(31, 122, 108, 0.15);
            }

            .timeline-meta {
                font-size: 12px;
                color: var(--muted);
            }

            .req-card {
                display: grid;
                gap: 10px;
                padding: 16px;
                border-radius: 14px;
                border: 1px solid var(--line);
                background: #fff;
            }

            .req-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
            }

            .status {
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .status.en_attente {
                background: rgba(239, 107, 58, 0.15);
                color: #a53d1b;
            }

            .status.en_traitement {
                background: rgba(31, 122, 108, 0.18);
                color: #1f7a6c;
            }

            .status.traitee {
                background: rgba(60, 126, 77, 0.18);
                color: #2a6e43;
            }

            .status.rejetee {
                background: rgba(74, 45, 45, 0.16);
                color: #613030;
            }

            .footer {
                padding: 24px 6vw 36px;
                color: var(--muted);
                font-size: 12px;
                text-align: center;
            }

            .hidden {
                display: none;
            }

            @keyframes rise {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Hide role-based nav links until JS determines the role (prevents flicker) */
            .nav-hidden {
                display: none !important;
            }

            /* PWA Offline Styles */
            body.is-offline {
                opacity: 0.85;
            }

            body.is-offline::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: repeating-linear-gradient(
                    90deg,
                    #ef6b3a,
                    #ef6b3a 10px,
                    #a53d1b 10px,
                    #a53d1b 20px
                );
                z-index: 9999;
                animation: pulse 2s infinite;
            }

            .pill.offline {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: rgba(239, 107, 58, 0.15) !important;
                color: #a53d1b !important;
            }

            .pill.offline::before {
                content: '';
                display: inline-block;
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: #ef6b3a;
                animation: pulse 1.5s infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.5;
                }
            }

            @media (max-width: 960px) {
                .grid.two,
                .form-grid.two {
                    grid-template-columns: 1fr;
                }

                .topbar {
                    flex-wrap: wrap;
                    gap: 12px;
                }
            }
        </style>
    </head>
    <body>
        @php($hideNav = $hideNav ?? false)
        <div class="ambient"></div>
        <header class="topbar">
            <a href="{{ route('home') }}" class="brand" style="text-decoration: none;">
                <img src="{{ asset('WhatsApp Image 2026-01-24 at 08.20.02.jpeg') }}" alt="Logo IUT Douala" width="20%" height="20%">
                <div>
                    <span data-i18n="brand.title">SRM-Student Request Manager</span>
                    <small data-i18n="brand.subtitle">IUT Douala</small>
                </div>
            </a>
            @if (!$hideNav)
                <nav class="nav">
                    <a href="/agent/dashboard" class="nav-hidden" data-role="agent" data-agent-feature="dashboard" data-i18n="nav.dashboard">Tableau de bord</a>
                    <a href="/etudiant/dashboard" class="nav-hidden" data-role="etudiant" data-i18n="nav.dashboard">Tableau de bord</a>
                    <a href="/requetes/depot" class="nav-hidden" data-role="etudiant" data-i18n="nav.depot">D&eacute;p&ocirc;t</a>
                    <a href="/requetes/suivi" class="nav-hidden" data-role="etudiant" data-i18n="nav.suivi">Suivi</a>
                    <a href="/profil" class="nav-hidden" data-role="etudiant" data-i18n="nav.profil">Profil</a>
                    <a href="/agent/services" class="nav-hidden" data-role="agent" data-agent-feature="manage_services" data-i18n="nav.services">Services</a>
                    <a href="/agent/types" class="nav-hidden" data-role="agent" data-agent-feature="manage_types" data-i18n="nav.types">Types</a>
                    <a href="/agent/agents" class="nav-hidden" data-role="agent" data-agent-feature="manage_agents" data-i18n="nav.agents">Agents</a>
                    {{-- <a href="/agent/etudiants" class="nav-hidden" data-role="agent" data-agent-feature="manage_etudiants" data-i18n="nav.etudiants">&Eacute;tudiants</a> --}}
                    <a href="/agent/historique" class="nav-hidden" data-role="agent" data-agent-feature="process_etapes" data-i18n="nav.historique">Historique</a>
                    {{-- <a href="/agent/decisions" class="nav-hidden" data-role="agent" data-agent-feature="decision_finale" data-i18n="nav.decisions">D&eacute;cisions</a> --}}
                </nav>
            @endif
            <div class="auth">
                <button id="langToggleBtn" class="btn lang-switch" type="button" data-i18n-title="lang.toggle_title" title="Changer de langue">FR | EN</button>
                
                <button id="logoutBtn" class="btn ghost" data-i18n="btn.logout">D&eacute;connexion</button>
            </div>
        </header>
        <main class="content">
            @yield('content')
        </main>
        <footer class="footer">
            <span data-i18n="footer.text">Syst&egrave;me de Requ&ecirc;tes &Eacute;tudiantes - IUT Douala</span>
        </footer>
        <script>
            const API_BASE = '/api';

            const I18N = {
                fr: {
                    'action.cancel': 'Annuler',
                    'action.delete': 'Supprimer',
                    'action.edit': 'Éditer',
                    'action.login': 'Se connecter',
                    'action.mark_read': 'Marquer comme lu',
                    'action.refresh': 'Actualiser',
                    'action.reject': 'Rejeter',
                    'action.save': 'Enregistrer',
                    'action.submit_registration': 'Soumettre l\'inscription',
                    'action.submit_request': 'Déposer la requête',
                    'action.validate': 'Valider',
                    'action.view_details': 'Voir les détails',
                    'agent.alumni_validation': 'Validation des inscriptions d\'anciens étudiants',
                    'agent.no_alumni_pending': 'Aucune inscription d\'ancien étudiant en attente de validation.',
                    'agent.dashboard_title': 'Espace ',
                    'agent.focus': 'Indicateurs cibles',
                    'agent.next_service': 'Service suivant',
                    'agent.no_extra_indicator': 'Aucun indicateur supplémentaire pour ce rôle.',
                    'agent.no_request_waiting': 'Aucune requête en attente dans votre service.',
                    'agent.process_error': 'Erreur de traitement.',
                    'agent.reject_reason_prompt': 'Motif du rejet (obligatoire) :',
                    'agent.reject_reason_required': 'Rejet annulé : motif obligatoire.',
                    'agent.requests_in_progress': 'requêtes en cours',
                    'agent.role_actions': 'Actions de mon rôle',
                    'agent.service_load': 'Charge du service',
                    'agent.stats': 'Statistiques',
                    'agent.tag': 'Agent',
                    'agent.to_process': 'Requêtes à traiter',
                    'auth.connected': 'Connecté',
                    'auth.connected_role': 'Connecté ({role})',
                    'auth.connected_service': 'Connecté ({service})',
                    'auth.offline': '',
                    'brand.subtitle': 'IUT Douala',
                    'brand.title': 'SRM-Student Request Manager',
                    'btn.hide': 'Masquer',
                    'btn.logout': 'Déconnexion',
                    'btn.show': 'Afficher',
                    'common.choose': 'Choisir',
                    'common.choose_department': 'Choisir la filière',
                    'common.details': 'Détails',
                    'common.download': 'Télécharger',
                    'common.error_loading': 'Erreur de chargement.',
                    'common.loading': 'Chargement...',
                    'common.no': 'Non',
                    'common.no_data': 'Aucune donnée.',
                    'common.none': 'Aucun',
                    'common.open': 'Ouvrir',
                    'common.unknown': 'Inconnu',
                    'common.yes': 'Oui',
                    'decision.defavorable': 'Défavorable',
                    'decision.favorable': 'Favorable',
                    'decision.incomplet': 'Incomplet',
                    'depot.allowed_formats': 'Formats acceptés : PDF, JPG, PNG. Taille max 5 Mo.',
                    'depot.attachment_error': 'Requête déposée, mais erreur de pièce jointe.',
                    'depot.deadline_note': 'Le délai est mesuré entre dépôt et décision finale.',
                    'depot.description_placeholder': 'Explique ta demande',
                    'depot.filiere_load_error': 'Impossible de charger ta filière.',
                    'depot.filiere_missing': 'Ta filière est vide. Demande à un agent de la renseigner.',
                    'depot.prefilled_major': 'Pré-rempli depuis ton profil étudiant.',
                    'depot.standard_path': 'Service courrier -> Direction -> DA -> Département cible (selon filière) -> Cellule info -> Scolarité',
                    'depot.standard_path_title': 'Parcours standard',
                    'depot.submit_error': 'Erreur de dépôt. Vérifie les champs.',
                    'depot.submit_success': 'Requête déposée avec succès.',
                    'depot.subtitle': 'Remplis les informations. Le système place la requête en attente.',
                    'depot.tag': 'Dépôt',
                    'depot.target_delay': 'Délai cible : 72h',
                    'depot.title': 'Nouvelle requête',
                    'field.academic_year': 'Année de dépôt',
                    'field.account_name_optional': 'Nom du compte (optionnel)',
                    'field.attachment_optional': 'Pièces jointes (optionnelles)',
                    'field.birth_date': 'Date de naissance',
                    'field.department': 'Filière',
                    'field.deposit_date': 'Date de dépôt',
                    'field.description': 'Description',
                    'field.description_optional': 'Description (optionnelle)',
                    'field.email': 'Email',
                    'field.first_name': 'Prénom',
                    'field.last_name': 'Nom',
                    'field.level': 'Niveau',
                    'field.login_email': 'Email de connexion',
                    'field.matricule': 'Matricule',
                    'field.password': 'Mot de passe',
                    'field.phone': 'Téléphone',
                    'field.request_type': 'Type de requête',
                    'field.student': 'Étudiant',
                    'field.subject': 'Objet',
                    'footer.text': 'Système de Requêtes Étudiantes - IUT Douala',
                    'history.comment': 'Observation',
                    'history.empty': 'Aucun historique pour le moment.',
                    'history.entry_date': 'Date d\'entrée',
                    'history.exit_date': 'Date de sortie',
                    'history.subtitle': 'Requêtes déjà traitées par votre service.',
                    'history.title': 'Historique du service',
                    'lang.title_to_en': 'Switch to English',
                    'lang.title_to_fr': 'Basculer en français',
                    'lang.toggle_title': 'Basculer la langue',
                    'login.feature_1': '- Déposer une requête pour certificat, duplicata, correction, ou autre.',
                    'login.feature_2': '- Suivre l\'état de traitement par service.',
                    'login.feature_3': '- Mettre à jour ton téléphone, email, mot de passe.',
                    'login.features_title': 'Ce que tu peux faire',
                    'login.form_hint': 'Utilise le compte créé par ton service.',
                    'login.form_title': 'Connexion',
                    'login.invalid_credentials': 'Identifiants invalides.',
                    'login.no_account': 'Ancien étudiant non inscrit ?',
                    'login.register_link': 'Créer un compte',
                    'login.subtitle': 'Dépose une requête, suis son parcours et reçois une décision dans le délai cible.',
                    'login.tag': 'Plateforme officielle',
                    'login.title': 'Portail des requêtes étudiantes',
                    'nav.agents': 'Agents',
                    'register.back_login': 'Retour à la connexion',
                    'register.form_hint': 'Veuillez renseigner des informations exactes pour faciliter la validation.',
                    'register.form_title': 'Créer mon compte',
                    'register.info_1': '- Remplissez correctement vos informations académiques et personnelles.',
                    'register.info_2': '- À la soumission, votre compte sera créé en état inactif (désactivé).',
                    'register.info_3': '- Les membres de la cellule informatique vérifieront vos données (concordance du matricule, nom, filière, ...).',
                    'register.info_4': '- Une fois validé, vous recevrez l\'accès pour déposer et suivre vos requêtes.',
                    'register.info_title': 'Processus de validation',
                    'register.subtitle': 'Cette section est dédiée aux anciens étudiants de l\'IUT de Douala non pré-inscrits.',
                    'register.tag': 'Espace Anciens Élèves',
                    'register.title': 'Rejoindre la plateforme SRM',
                    'nav.dashboard': 'Tableau de bord',
                    'nav.decisions': 'Décisions',
                    'nav.depot': 'Dépôt',
                    'nav.etudiants': 'Étudiants',
                    'nav.historique': 'Historique',
                    'nav.profil': 'Profil',
                    'nav.services': 'Services',
                    'nav.suivi': 'Suivi',
                    'nav.types': 'Types',
                    'profile.confirm_password': 'Confirmer le mot de passe',
                    'profile.new_password': 'Nouveau mot de passe',
                    'profile.no_notifications': 'Aucune notification.',
                    'profile.notifications': 'Notifications',
                    'profile.read': 'Lu',
                    'profile.subtitle': 'Modifie ton téléphone, email ou mot de passe.',
                    'profile.tag': 'Profil',
                    'profile.title': 'Mes paramètres',
                    'profile.update_error': 'Erreur de mise à jour.',
                    'profile.updated': 'Profil mis à jour.',
                    'request.attachments': 'Pièces jointes',
                    'request.attachments_none': 'Pièces jointes : aucune',
                    'request.deposit_date': 'Dépôt',
                    'request.file': 'Fichier',
                    'request.label': 'Requête',
                    'request.no_description': 'Aucune description',
                    'request.no_subject': 'Sans objet',
                    'request.student': 'Étudiant',
                    'request.unknown_type': 'Type inconnu',
                    'role.agent': 'agent',
                    'role.etudiant': 'étudiant',
                    'stats.total': 'Total',
                    'stats.total_to_process': 'Total à traiter',
                    'status.en_attente': 'En attente',
                    'status.en_traitement': 'En traitement',
                    'status.rejetee': 'Rejetée',
                    'status.traitee': 'Traitée',
                    'student.dashboard_subtitle': 'Résumé de tes requêtes récentes.',
                    'student.dashboard_title': 'Mon tableau de bord',
                    'student.identity': 'Étudiant : {prenom} {nom} ({matricule})',
                    'student.latest_requests': 'Dernières requêtes',
                    'student.no_recent_requests': 'Aucune requête récente.',
                    'student.stats': 'Statistiques',
                    'student.tag': 'Étudiant',
                    'students.account': 'Compte',
                    'students.account_created': 'Compte créé.',
                    'students.account_error': 'Erreur de création du compte.',
                    'students.choose_student': 'Choisir un étudiant.',
                    'students.create_account': 'Créer le compte',
                    'students.list': 'Liste des étudiants',
                    'students.new': 'Nouvel étudiant',
                    'students.save_error': 'Erreur d\'enregistrement.',
                    'students.subtitle': 'Créer un étudiant et son compte de connexion.',
                    'students.title': 'Étudiants',
                    'tracking.all': 'Tous',
                    'tracking.all_services': 'Tous les services',
                    'tracking.attachments': 'Pièces jointes',
                    'tracking.decision': 'Décision',
                    'tracking.decision_date': 'Date de décision',
                    'tracking.detail_error': 'Erreur de détail.',
                    'tracking.entry': 'Entrée',
                    'tracking.exit': 'Sortie',
                    'tracking.history': 'Historique',
                    'tracking.in_progress': 'En cours',
                    'tracking.no_attachments': 'Aucune pièce jointe.',
                    'tracking.no_steps': 'Aucune étape enregistrée.',
                    'tracking.none_found': 'Aucune requête trouvée.',
                    'tracking.rejection_reason': 'Motif du rejet',
                    'tracking.search': 'Recherche',
                    'tracking.search_placeholder': 'Objet ou type',
                    'tracking.service': 'Service',
                    'tracking.service_filter': 'Filtre service',
                    'tracking.status_filter': 'Filtre statut',
                    'tracking.subtitle': 'Consulte le statut et les détails.',
                    'tracking.tag': 'Suivi',
                    'tracking.title': 'État de mes requêtes',
                },
                en: {
                    'action.cancel': 'Cancel',
                    'action.delete': 'Delete',
                    'action.edit': 'Edit',
                    'action.login': 'Sign in',
                    'action.mark_read': 'Mark as read',
                    'action.refresh': 'Refresh',
                    'action.reject': 'Reject',
                    'action.save': 'Save',
                    'action.submit_registration': 'Submit registration',
                    'action.submit_request': 'Submit request',
                    'action.validate': 'Approve',
                    'action.view_details': 'View details',
                    'agent.alumni_validation': 'Alumni registration validation',
                    'agent.no_alumni_pending': 'No pending alumni registrations for validation.',
                    'agent.dashboard_subtitle': 'Detailed view of requests to process in your service.',
                    'agent.dashboard_title': ' Dashboard',
                    'agent.focus': 'Target indicators',
                    'agent.next_service': 'Next service',
                    'agent.no_extra_indicator': 'No additional indicators for this role.',
                    'agent.no_request_waiting': 'No pending requests in your service.',
                    'agent.process_error': 'Processing error.',
                    'agent.reject_reason_prompt': 'Rejection reason (required):',
                    'agent.reject_reason_required': 'Rejection canceled: reason required.',
                    'agent.requests_in_progress': 'requests in progress',
                    'agent.role_actions': 'My role actions',
                    'agent.service_load': 'Service workload',
                    'agent.stats': 'Statistics',
                    'agent.tag': 'Agent',
                    'agent.to_process': 'Requests to process',
                    'auth.connected': 'Online',
                    'auth.connected_role': 'Online ({role})',
                    'auth.connected_service': 'Online ({service})',
                    'auth.offline': 'Offline',
                    'brand.subtitle': 'IUT Douala',
                    'brand.title': 'SRM-Student Request Manager',
                    'btn.hide': 'Hide',
                    'btn.logout': 'Log out',
                    'btn.show': 'Show',
                    'common.choose': 'Choose',
                    'common.choose_department': 'Choose major',
                    'common.details': 'Details',
                    'common.download': 'Download',
                    'common.error_loading': 'Loading error.',
                    'common.loading': 'Loading...',
                    'common.no': 'No',
                    'common.no_data': 'No data.',
                    'common.none': 'None',
                    'common.open': 'Open',
                    'common.unknown': 'Unknown',
                    'common.yes': 'Yes',
                    'decision.defavorable': 'Rejected',
                    'decision.favorable': 'Approved',
                    'decision.incomplet': 'Incomplete',
                    'depot.allowed_formats': 'Accepted formats: PDF, JPG, PNG. Max size 5 MB.',
                    'depot.attachment_error': 'Request submitted, but attachment upload failed.',
                    'depot.deadline_note': 'The delay is measured between submission and final decision.',
                    'depot.description_placeholder': 'Describe your request',
                    'depot.filiere_load_error': 'Unable to load your major.',
                    'depot.filiere_missing': 'Your major is empty. Ask an agent to set it.',
                    'depot.prefilled_major': 'Pre-filled from your student profile.',
                    'depot.standard_path': 'Mail service -> Management -> Deputy Director -> Target department (according to major) -> IT unit -> Registrar',
                    'depot.standard_path_title': 'Standard path',
                    'depot.submit_error': 'Submission error. Check your fields.',
                    'depot.submit_success': 'Request submitted successfully.',
                    'depot.subtitle': 'Fill in the information. The system places the request in pending mode.',
                    'depot.tag': 'Submit',
                    'depot.target_delay': 'Target delay: 72h',
                    'depot.title': 'New request',
                    'field.academic_year': 'Academic year',
                    'field.account_name_optional': 'Account name (optional)',
                    'field.attachment_optional': 'Attachments (optional)',
                    'field.birth_date': 'Birth date',
                    'field.department': 'Major',
                    'field.deposit_date': 'Deposit date',
                    'field.description': 'Description',
                    'field.description_optional': 'Description (optional)',
                    'field.email': 'Email',
                    'field.first_name': 'First name',
                    'field.last_name': 'Last name',
                    'field.level': 'Level',
                    'field.login_email': 'Login email',
                    'field.matricule': 'Student ID',
                    'field.password': 'Password',
                    'field.phone': 'Phone',
                    'field.request_type': 'Request type',
                    'field.student': 'Student',
                    'field.subject': 'Subject',
                    'footer.text': 'Student Request Management System - IUT Douala',
                    'history.comment': 'Comment',
                    'history.empty': 'No history yet.',
                    'history.entry_date': 'Entry date',
                    'history.exit_date': 'Exit date',
                    'history.subtitle': 'Requests already processed by your service.',
                    'history.title': 'Service history',
                    'lang.title_to_en': 'Switch to English',
                    'lang.title_to_fr': 'Switch to French',
                    'lang.toggle_title': 'Switch language',
                    'login.feature_1': '- Submit a request for certificate, duplicate, correction, and more.',
                    'login.feature_2': '- Track processing status by service.',
                    'login.feature_3': '- Update your phone, email and password.',
                    'login.features_title': 'What you can do',
                    'login.form_hint': 'Use the account created by your service.',
                    'login.form_title': 'Sign in',
                    'login.invalid_credentials': 'Invalid credentials.',
                    'login.no_account': 'Former student not registered?',
                    'login.register_link': 'Create an account',
                    'login.subtitle': 'Submit a request, track its route and receive a decision within the target delay.',
                    'login.tag': 'Official platform',
                    'login.title': 'Student requests portal',
                    'nav.agents': 'Agents',
                    'register.back_login': 'Back to sign in',
                    'register.form_hint': 'Please provide accurate information to facilitate validation.',
                    'register.form_title': 'Create my account',
                    'register.info_1': '- Fill in your academic and personal information accurately.',
                    'register.info_2': '- Upon submission, your account will be created in an inactive (disabled) state.',
                    'register.info_3': '- IT support members will verify your data (student ID, name, major, etc.).',
                    'register.info_4': '- Once validated, you will receive access to submit and track your requests.',
                    'register.info_title': 'Validation process',
                    'register.subtitle': 'This section is dedicated to former IUT Douala students who are not pre-registered.',
                    'register.tag': 'Alumni Space',
                    'register.title': 'Join the SRM platform',
                    'nav.dashboard': 'Dashboard',
                    'nav.decisions': 'Decisions',
                    'nav.depot': 'Submit',
                    'nav.etudiants': 'Students',
                    'nav.historique': 'History',
                    'nav.profil': 'Profile',
                    'nav.services': 'Services',
                    'nav.suivi': 'Tracking',
                    'nav.types': 'Types',
                    'profile.confirm_password': 'Confirm password',
                    'profile.new_password': 'New password',
                    'profile.no_notifications': 'No notifications.',
                    'profile.notifications': 'Notifications',
                    'profile.read': 'Read',
                    'profile.subtitle': 'Update your phone, email or password.',
                    'profile.tag': 'Profile',
                    'profile.title': 'My settings',
                    'profile.update_error': 'Update error.',
                    'profile.updated': 'Profile updated.',
                    'request.attachments': 'Attachments',
                    'request.attachments_none': 'Attachments: none',
                    'request.deposit_date': 'Submitted',
                    'request.file': 'File',
                    'request.label': 'Request',
                    'request.no_description': 'No description',
                    'request.no_subject': 'No subject',
                    'request.student': 'Student',
                    'request.unknown_type': 'Unknown type',
                    'role.agent': 'agent',
                    'role.etudiant': 'student',
                    'stats.total': 'Total',
                    'stats.total_to_process': 'Total to process',
                    'status.en_attente': 'Pending',
                    'status.en_traitement': 'In progress',
                    'status.rejetee': 'Rejected',
                    'status.traitee': 'Processed',
                    'student.dashboard_subtitle': 'Summary of your recent requests.',
                    'student.dashboard_title': 'My dashboard',
                    'student.identity': 'Student: {prenom} {nom} ({matricule})',
                    'student.latest_requests': 'Latest requests',
                    'student.no_recent_requests': 'No recent requests.',
                    'student.stats': 'Statistics',
                    'student.tag': 'Student',
                    'students.account': 'Account',
                    'students.account_created': 'Account created.',
                    'students.account_error': 'Account creation error.',
                    'students.choose_student': 'Choose a student.',
                    'students.create_account': 'Create account',
                    'students.list': 'Students list',
                    'students.new': 'New student',
                    'students.save_error': 'Save error.',
                    'students.subtitle': 'Create a student and login account.',
                    'students.title': 'Students',
                    'tracking.all': 'All',
                    'tracking.all_services': 'All services',
                    'tracking.attachments': 'Attachments',
                    'tracking.decision': 'Decision',
                    'tracking.decision_date': 'Decision date',
                    'tracking.detail_error': 'Detail loading error.',
                    'tracking.entry': 'Entry',
                    'tracking.exit': 'Exit',
                    'tracking.history': 'History',
                    'tracking.in_progress': 'In progress',
                    'tracking.no_attachments': 'No attachment.',
                    'tracking.no_steps': 'No recorded step.',
                    'tracking.none_found': 'No requests found.',
                    'tracking.rejection_reason': 'Rejection reason',
                    'tracking.search': 'Search',
                    'tracking.search_placeholder': 'Subject or type',
                    'tracking.service': 'Service',
                    'tracking.service_filter': 'Service filter',
                    'tracking.status_filter': 'Status filter',
                    'tracking.subtitle': 'Check status and details.',
                    'tracking.tag': 'Tracking',
                    'tracking.title': 'Status of my requests',
                    'agent.workspace.courrier.title': 'Mail service',
                    'agent.workspace.courrier.description': 'Registers new requests and forwards them to the next service.',
                    'agent.workspace.scolarite.title': 'Registrar office',
                    'agent.workspace.scolarite.description': 'Finalizes dossiers and issues the final decision.',
                    'agent.workspace.conseil_orientation.title': 'Orientation council',
                    'agent.workspace.conseil_orientation.description': 'Receives submissions and starts the first step of the workflow.',
                    'agent.workspace.direction.title': 'Management',
                    'agent.workspace.direction.description': 'Approves and grades requests before routing.',
                    'agent.workspace.da.title': 'Deputy management',
                    'agent.workspace.da.description': 'Grades requests and forwards them to the relevant departments.',
                    'agent.workspace.departement.title': 'Department',
                    'agent.workspace.departement.description': 'Reviews academic files before technical correction.',
                    'agent.workspace.cellule_info.title': 'IT support',
                    'agent.workspace.cellule_info.description': 'Applies corrections and returns the dossier to the registrar.',
                    'agent.workspace.enseignant.title': 'Teacher',
                    'agent.workspace.enseignant.description': 'Handles pedagogical requests forwarded by the department.',
                    'agent.workspace.autre.title': 'Agent workspace',
                    'agent.workspace.autre.description': 'Handles the requests assigned to your service.',
                },
            };

            // Ensure I18N.en has all keys from I18N.fr
            if (I18N.en) {
                Object.keys(I18N.fr).forEach(key => {
                    if (!(key in I18N.en)) {
                        I18N.en[key] = I18N.fr[key];
                    }
                });
            } else {
                I18N.en = { ...I18N.fr };
            }

            function getLang() {
                return localStorage.getItem('ui_lang') === 'en' ? 'en' : 'fr';
            }

            function setLang(lang) {
                const normalized = lang === 'en' ? 'en' : 'fr';
                localStorage.setItem('ui_lang', normalized);
                applyI18n();
                window.dispatchEvent(new Event('srm:language-changed'));
            }

            function __(key, vars = {}) {
                const lang = getLang();
                const dict = I18N[lang] || I18N.fr;
                const fallback = I18N.fr[key] ?? key;
                let value = dict[key] ?? fallback;
                Object.keys(vars).forEach((name) => {
                    value = value.replaceAll(`{${name}}`, String(vars[name] ?? ''));
                });
                return value;
            }

            function applyI18n(root = document) {
                document.documentElement.lang = getLang();

                root.querySelectorAll('[data-i18n]').forEach((element) => {
                    const key = element.getAttribute('data-i18n');
                    if (!key) return;
                    // Use textContent only if no child elements, otherwise use firstChild to avoid breaking icons
                    if (element.children.length === 0) {
                        element.textContent = __(key);
                    } else {
                        // Find and update the first text node
                        for (const node of element.childNodes) {
                            if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
                                node.textContent = __(key);
                                break;
                            }
                        }
                    }
                });

                root.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
                    const key = element.getAttribute('data-i18n-placeholder');
                    if (!key) return;
                    element.setAttribute('placeholder', __(key));
                });

                root.querySelectorAll('[data-i18n-title]').forEach((element) => {
                    const key = element.getAttribute('data-i18n-title');
                    if (!key) return;
                    element.setAttribute('title', __(key));
                });

                updateLangToggleUI();
                refreshPasswordToggleLabels();
                updateAuthUI();
            }

            window.__ = __;
            window.getLang = getLang;
            window.setLang = setLang;
            window.applyI18n = applyI18n;

            function getToken() {
                return localStorage.getItem('api_token');
            }

            function setToken(token) {
                localStorage.setItem('api_token', token);
            }

            function clearToken() {
                localStorage.removeItem('api_token');
                localStorage.removeItem('user_role');
                localStorage.removeItem('service_id');
                localStorage.removeItem('service_nom');
                localStorage.removeItem('service_type');
                localStorage.removeItem('service_key');
                localStorage.removeItem('agent_features');
            }

            function setRole(role) {
                if (role) {
                    localStorage.setItem('user_role', role);
                }
            }

            function getRole() {
                return localStorage.getItem('user_role');
            }

            function setServiceId(serviceId) {
                if (serviceId) {
                    localStorage.setItem('service_id', String(serviceId));
                }
            }

            function getServiceId() {
                return localStorage.getItem('service_id');
            }

            function setServiceNom(serviceName) {
                if (serviceName) {
                    localStorage.setItem('service_nom', String(serviceName));
                }
            }

            function getServiceNom() {
                return localStorage.getItem('service_nom');
            }

            function setServiceType(serviceType) {
                if (serviceType) {
                    localStorage.setItem('service_type', String(serviceType));
                }
            }

            function getServiceType() {
                return localStorage.getItem('service_type');
            }

            function setServiceKey(serviceKey) {
                if (serviceKey) {
                    localStorage.setItem('service_key', String(serviceKey));
                }
            }

            function getServiceKey() {
                return localStorage.getItem('service_key');
            }

            function setAgentFeatures(features) {
                if (!Array.isArray(features)) return;
                localStorage.setItem('agent_features', JSON.stringify(features));
            }

            function getAgentFeatures() {
                const raw = localStorage.getItem('agent_features');
                if (!raw) {
                    return getRole() === 'agent' ? ['dashboard', 'suivi_requetes', 'process_etapes'] : [];
                }
                try {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        return parsed;
                    }
                    return getRole() === 'agent' ? ['dashboard', 'suivi_requetes', 'process_etapes'] : [];
                } catch (_error) {
                    return getRole() === 'agent' ? ['dashboard', 'suivi_requetes', 'process_etapes'] : [];
                }
            }

            function hasAgentFeature(feature) {
                if (!feature) return true;
                const role = getRole();
                if (role !== 'agent') return false;
                return getAgentFeatures().includes(feature);
            }

            function guardAgentFeature(feature) {
                if (getRole() !== 'agent') {
                    location.href = '/connexion';
                    return false;
                }
                if (feature && !hasAgentFeature(feature)) {
                    location.href = '/agent/dashboard';
                    return false;
                }
                return true;
            }

            async function apiFetch(path, options = {}) {
                const headers = new Headers(options.headers || {});
                headers.set('Accept', 'application/json');
                if (options.body && !(options.body instanceof FormData) && !headers.has('Content-Type')) {
                    headers.set('Content-Type', 'application/json');
                }
                const token = getToken();
                if (token) {
                    headers.set('Authorization', `Bearer ${token}`);
                }
                const response = await fetch(`${API_BASE}${path}`, {
                    ...options,
                    headers,
                });
                if (response.status === 401) {
                    clearToken();
                    if (!location.pathname.includes('/connexion')) {
                        location.href = '/connexion';
                    }
                }
                return response;
            }

            function formatDate(value) {
                if (!value) return '-';
                const normalized = typeof value === 'string' ? value.replace(' ', 'T') : value;
                const date = new Date(normalized);
                if (Number.isNaN(date.getTime())) return value;
                return date.toLocaleString(getLang() === 'en' ? 'en-US' : 'fr-FR', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }

            function formatStatus(status) {
                return __(`status.${status}`);
            }

            function formatDecision(resultat) {
                return __(`decision.${resultat}`);
            }

            function updateLangToggleUI() {
                const button = document.getElementById('langToggleBtn');
                if (!button) return;
                const lang = getLang();
                if (lang === 'fr') {
                    button.textContent = 'FR | EN';
                    button.title = __('lang.title_to_en');
                } else {
                    button.textContent = 'EN | FR';
                    button.title = __('lang.title_to_fr');
                }
            }

            function updateAuthUI() {
                const logoutBtn = document.getElementById('logoutBtn');
                if (!logoutBtn) return;
                const token = getToken();
                if (token) {
                    logoutBtn.classList.remove('hidden');
                    logoutBtn.textContent = __('btn.logout');
                } else {
                    logoutBtn.classList.add('hidden');
                }
            }

            function updateNavByRole() {
                const role = getRole();
                const token = getToken();
                document.querySelectorAll('[data-role]').forEach((link) => {
                    const allowed = link.getAttribute('data-role');
                    if (!allowed) {
                        link.classList.remove('nav-hidden');
                        return;
                    }
                    if (!role || !token) {
                        link.classList.add('nav-hidden');
                        return;
                    }
                    const roles = allowed.split(',').map((value) => value.trim());
                    if (roles.includes(role)) {
                        const feature = link.getAttribute('data-agent-feature');
                        if (role === 'agent' && feature && !hasAgentFeature(feature)) {
                            link.classList.add('nav-hidden');
                            return;
                        }
                        link.classList.remove('nav-hidden');
                    } else {
                        link.classList.add('nav-hidden');
                    }
                });
            }

            function refreshPasswordToggleLabels() {
                document.querySelectorAll('.password-wrap').forEach((wrap) => {
                    const input = wrap.querySelector('input[type="password"], input[type="text"]');
                    const button = wrap.querySelector('.password-toggle');
                    if (!input || !button) return;
                    button.textContent = input.type === 'password' ? __('btn.show') : __('btn.hide');
                });
            }

            function initPasswordToggles() {
                document.querySelectorAll('input[type="password"][data-password-toggle]').forEach((input) => {
                    if (input.dataset.passwordToggleInit === '1') {
                        return;
                    }
                    const wrapper = document.createElement('div');
                    wrapper.className = 'password-wrap';
                    input.parentNode.insertBefore(wrapper, input);
                    wrapper.appendChild(input);

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'password-toggle';
                    button.textContent = __('btn.show');
                    wrapper.appendChild(button);

                    button.addEventListener('click', () => {
                        const isHidden = input.type === 'password';
                        input.type = isHidden ? 'text' : 'password';
                        button.textContent = isHidden ? __('btn.hide') : __('btn.show');
                    });

                    input.dataset.passwordToggleInit = '1';
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                initPasswordToggles();
                applyI18n();
                updateNavByRole();

                const langToggleBtn = document.getElementById('langToggleBtn');
                if (langToggleBtn) {
                    langToggleBtn.addEventListener('click', () => {
                        setLang(getLang() === 'fr' ? 'en' : 'fr');
                    });
                }

                const logoutBtn = document.getElementById('logoutBtn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', async () => {
                        const token = getToken();
                        if (!token) {
                            location.href = '/connexion';
                            return;
                        }
                        await apiFetch('/logout', { method: 'POST' });
                        clearToken();
                        location.href = '/connexion';
                    });
                }
            });

            // ============================================
            // PUSH: base64url (clé VAPID) -> Uint8Array
            // ============================================
            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const rawData = atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }

            // ============================================
            // SERVICE WORKER REGISTRATION
            // ============================================
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', async () => {
                    try {
                        const registration = await navigator.serviceWorker.register('/sw.js');
                        console.log('[APP] Service Worker registered successfully:', registration);

                        // ============================================
                        // PUSH NOTIFICATION SUBSCRIPTION
                        // ============================================
                        try {
                            const vapidKey = document
                                .querySelector('meta[name="vapid-public-key"]')
                                ?.getAttribute('content');

                            if ('PushManager' in window && 'Notification' in window && vapidKey && getToken()) {
                                let permission = Notification.permission;
                                if (permission === 'default') {
                                    permission = await Notification.requestPermission();
                                }

                                if (permission === 'granted') {
                                    // pushManager.subscribe exige un service worker actif
                                    const readyRegistration = await navigator.serviceWorker.ready;
                                    let subscription = await readyRegistration.pushManager.getSubscription();
                                    if (!subscription) {
                                        subscription = await readyRegistration.pushManager.subscribe({
                                            userVisibleOnly: true,
                                            applicationServerKey: urlBase64ToUint8Array(vapidKey),
                                        });
                                    }

                                    const pushResponse = await apiFetch('/push-subscribe', {
                                        method: 'POST',
                                        body: JSON.stringify(subscription),
                                    });

                                    if (pushResponse.ok) {
                                        console.log('[APP] Abonnement push enregistré côté serveur');
                                    } else {
                                        console.warn('[APP] Échec enregistrement abonnement push:', pushResponse.status);
                                    }
                                }
                            }
                        } catch (pushError) {
                            console.warn('[APP] Push subscription failed:', pushError);
                        }

                        // Listen for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            console.log('[APP] Service Worker update available');

                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'activated') {
                                    console.log('[APP] New Service Worker activated');
                                    // Optional: Show update notification to user
                                    notifyUserAboutUpdate();
                                }
                            });
                        });

                        // Check for updates periodically
                        setInterval(() => {
                            registration.update().catch((error) => {
                                console.log('[APP] Failed to check for SW updates:', error);
                            });
                        }, 60000); // Check every minute

                        // Listen for messages from Service Worker
                        navigator.serviceWorker.addEventListener('message', (event) => {
                            const { type, data } = event.data;
                            console.log('[APP] Message from SW:', type, data);

                            if (type === 'CACHE_CLEARED') {
                                console.log('[APP] Cache was cleared by SW');
                            }

                            if (type === 'SYNC_COMPLETE') {
                                console.log('[APP] Background sync completed');
                                window.dispatchEvent(new CustomEvent('srm:sync-complete', { detail: data }));
                            }
                        });

                    } catch (error) {
                        console.error('[APP] Service Worker registration failed:', error);
                    }
                });
            } else {
                console.warn('[APP] Service Workers not supported in this browser');
            }

            // ============================================
            // OFFLINE DETECTION
            // ============================================
            window.addEventListener('online', () => {
                console.log('[APP] Connection restored');
                document.body.classList.remove('is-offline');

                // Notify user
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('SRM', {
                        body: 'Vous êtes de nouveau connecté à Internet',
                        icon: '/images/icons/icon-192x192.png',
                    });
                }
            });

            window.addEventListener('offline', () => {
                console.log('[APP] Connection lost');
                document.body.classList.add('is-offline');
            });

            // Set initial offline state
            if (!navigator.onLine) {
                document.body.classList.add('is-offline');
            }

            // ============================================
            // PWA INSTALL PROMPT
            // ============================================
            let installPrompt;

            window.addEventListener('beforeinstallprompt', (event) => {
                console.log('[APP] beforeinstallprompt event triggered');
                // Prevent the mini-infobar from appearing on mobile
                event.preventDefault();
                // Store the event for later use
                installPrompt = event;
                // Show install button if not already installed
                showInstallButton();
            });

            window.addEventListener('appinstalled', () => {
                console.log('[APP] PWA was installed');
                hideInstallButton();
                installPrompt = null;
            });

            function showInstallButton() {
                const btn = document.getElementById('install-pwa-btn');
                if (btn) {
                    btn.style.display = 'flex';
                    btn.addEventListener('click', async () => {
                        if (installPrompt) {
                            installPrompt.prompt();
                            const { outcome } = await installPrompt.userChoice;
                            console.log(`[APP] User response: ${outcome}`);
                            installPrompt = null;
                            hideInstallButton();
                        }
                    });
                }
            }

            function hideInstallButton() {
                const btn = document.getElementById('install-pwa-btn');
                if (btn) {
                    btn.style.display = 'none';
                }
            }

            // ============================================
            // NOTIFICATION PERMISSION REQUEST
            // ============================================
            if ('Notification' in window && Notification.permission === 'default') {
                // Only request once per session
                if (!sessionStorage.getItem('notification-prompt-shown')) {
                    setTimeout(() => {
                        Notification.requestPermission().then((permission) => {
                            console.log('[APP] Notification permission:', permission);
                            sessionStorage.setItem('notification-prompt-shown', 'true');
                        });
                    }, 3000); // Wait 3 seconds before prompting
                }
            }

            // ============================================
            // HELPER FUNCTION - Notify about update
            // ============================================
            function notifyUserAboutUpdate() {
                const notification = document.createElement('div');
                notification.className = 'pwa-update-notification';
                notification.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #1f7a6c; color: white; border-radius: 8px; margin: 12px; font-size: 14px;">
                        <span>✓ Une nouvelle version est disponible</span>
                        <button onclick="window.location.reload()" style="background: #ef6b3a; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">Recharger</button>
                    </div>
                `;
                document.body.insertBefore(notification, document.body.firstChild);
            }
        </script>

        @stack('scripts')
    </body>
</html>
