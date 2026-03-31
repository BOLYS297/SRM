<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accueil | SRM IUT Douala</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|archivo:400,500,600" rel="stylesheet">
    <style>
        :root {
            --ink: #1f2a28;
            --muted: #657872;
            --accent: #ef6b3a;
            --accent-2: #1f7a6c;
            --surface: #fffaf2;
            --line: #e6dccf;
            --bg: #f4efe5;
            --display: "Space Grotesk", "Trebuchet MS", sans-serif;
            --body: "Archivo", "Trebuchet MS", sans-serif;
            --shadow: 0 20px 48px rgba(18, 23, 21, 0.16);
            --radius: 18px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: var(--body);
        }

        .hero {
            position: relative;
            min-height: 86vh;
            width: 100%;
            overflow: hidden;
            background-image:
                linear-gradient(118deg, rgba(16, 28, 25, 0.68), rgba(31, 122, 108, 0.34) 48%, rgba(239, 107, 58, 0.24)),
                url("{{ asset('WhatsApp Image 2026-03-31 at 00.32.07.jpeg') }}");
            background-position: center;
            background-size: cover;
            display: flex;
            align-items: center;
        }

        .hero-shell {
            position: relative;
            width: min(1240px, 100%);
            margin: 0 auto;
            padding: 20px 6vw 72px;
        }

        .topbar {
            position: absolute;
            top: 20px;
            left: 6vw;
            right: 6vw;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            z-index: 4;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.28);
            text-decoration: none;
        }

        .brand img {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.84);
            object-fit: cover;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.28);
        }

        .brand strong {
            display: block;
            font-family: var(--display);
            font-size: clamp(16px, 2vw, 22px);
            letter-spacing: -0.02em;
        }

        .brand span {
            display: block;
            font-size: 13px;
            opacity: 0.95;
            max-width: 520px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            border: 0;
            border-radius: 12px;
            padding: 12px 18px;
            font-family: var(--body);
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn.primary {
            background: linear-gradient(135deg, var(--accent), #f59f45);
            color: #fff;
            box-shadow: 0 12px 28px rgba(239, 107, 58, 0.35);
        }

        .btn.lang {
            background: rgba(12, 20, 18, 0.62);
            border: 1px solid rgba(255, 255, 255, 0.88);
            color: #fff;
            padding: 11px 14px;
            min-width: 84px;
            backdrop-filter: blur(4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
        }

        .hero-copy {
            position: relative;
            z-index: 3;
            margin-top: 88px;
            max-width: 900px;
            color: #fff;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.34);
        }

        .hero-copy h1 {
            margin: 0 0 14px;
            font-family: var(--display);
            font-size: clamp(36px, 6vw, 72px);
            line-height: 0.98;
            letter-spacing: -0.03em;
        }

        .hero-copy p {
            margin: 0;
            max-width: 760px;
            font-size: clamp(16px, 2vw, 20px);
            line-height: 1.62;
            opacity: 0.98;
        }

        .section-shell {
            width: min(1240px, 100%);
            margin: 0 auto;
            padding: 26px 6vw 0;
        }

        .section-head {
            margin-bottom: 14px;
        }

        .section-head h2 {
            margin: 0 0 4px;
            font-family: var(--display);
            font-size: clamp(24px, 3vw, 34px);
            letter-spacing: -0.02em;
        }

        .section-head p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 26px;
        }

        .metric {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            box-shadow: var(--shadow);
        }

        .metric strong {
            display: block;
            font-family: var(--display);
            font-size: clamp(26px, 4vw, 36px);
            letter-spacing: -0.02em;
            color: var(--accent-2);
        }

        .metric span {
            color: var(--muted);
            font-size: 13px;
        }

        .workflow {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 18px;
            margin-bottom: 34px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 22px;
        }

        .card h3 {
            margin: 0 0 10px;
            font-family: var(--display);
            font-size: 24px;
            letter-spacing: -0.02em;
        }

        .timeline {
            display: grid;
            gap: 11px;
            border-left: 2px solid rgba(31, 122, 108, 0.28);
            padding-left: 16px;
            margin-top: 10px;
        }

        .timeline div {
            position: relative;
            color: var(--muted);
            line-height: 1.5;
        }

        .timeline div::before {
            content: "";
            position: absolute;
            left: -22px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent-2);
            box-shadow: 0 0 0 4px rgba(31, 122, 108, 0.14);
        }

        .chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .chip-list span {
            border-radius: 999px;
            padding: 7px 10px;
            background: rgba(31, 122, 108, 0.1);
            color: #255d53;
            font-size: 12px;
            font-weight: 700;
        }

        .foot {
            padding: 12px 6vw 32px;
            text-align: center;
            color: #6b7d77;
            font-size: 13px;
        }

        @media (max-width: 1100px) {
            .metrics { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 860px) {
            .workflow { grid-template-columns: 1fr; }
            .metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hero { min-height: 78vh; }
            .topbar { position: static; margin-bottom: 24px; }
            .hero-copy { margin-top: 0; }
        }

        @media (max-width: 560px) {
            .metrics { grid-template-columns: 1fr; }
            .brand span { display: none; }
            .top-actions .btn { padding: 10px 12px; }
        }
    </style>
</head>
<body>
@php
    $nbRequetes = \App\Models\Requete::count();
    $nbTraitees = \App\Models\Requete::where('statut', 'traitee')->count();
    $nbDepartements = \App\Models\Service::where('type_service', 'Departement')->count();
    $nbTypes = \App\Models\TypeRequete::count();
    $nbEtudiants = \App\Models\Etudiant::count();
@endphp

<section class="hero">
    <div class="hero-shell">
        <header class="topbar">
            <a href="{{ route('home') }}" ">
                <div class="brand">
                    <img src="{{ asset('WhatsApp Image 2026-01-24 at 08.20.02.jpeg') }}" alt="Logo IUT Douala">
                    <div>
                        <strong data-i18n="brand.title">SRM - Student Request Manager</strong>
                        <span data-i18n="brand.desc">Application de gestion des requêtes étudiantes de l'IUT de Douala.</span>
                    </div>
                </div>
            </a>
            <div class="top-actions">
                <button id="langToggle" type="button" class="btn lang">FR | EN</button>
                <a class="btn primary" href="{{ route('login') }}" data-i18n="cta.login">Se connecter</a>
            </div>
        </header>

        <div class="hero-copy">
            <h1 data-i18n="hero.title">La gestion des requêtes étudiantes, enfin fluide et moderne.</h1>
            <p data-i18n="hero.text">
                Le SRM digitalise tout le parcours d'une requête : dépôt, orientation automatique vers le bon service,
                traitement par les agents concernés, historique des actions et décision finale notifiée à l'étudiant.
                Un circuit fiable, lisible et rapide pour toute la communauté de l'IUT de Douala.
            </p>
        </div>
    </div>
</section>

<section class="section-shell">
    <div class="section-head">
        <h2 data-i18n="metrics.title">Chiffres clés de la plateforme</h2>
        <p data-i18n="metrics.desc">Un aperçu direct de l'activité et de la couverture fonctionnelle du SRM.</p>
    </div>

    <div class="metrics">
        <article class="metric"><strong>{{ $nbRequetes }}</strong><span data-i18n="metrics.total">Requêtes enregistrées</span></article>
        <article class="metric"><strong>{{ $nbTraitees }}</strong><span data-i18n="metrics.done">Requêtes traitées</span></article>
        <article class="metric"><strong>{{ $nbDepartements }}</strong><span data-i18n="metrics.depts">Départements couverts</span></article>
        <article class="metric"><strong>{{ $nbTypes }}</strong><span data-i18n="metrics.types">Types de requêtes</span></article>
        <article class="metric"><strong>{{ $nbEtudiants }}</strong><span data-i18n="metrics.students">Étudiants inscrits</span></article>
    </div>
</section>

<section class="section-shell">
    <div class="section-head">
        <h2 data-i18n="how.title">Fonctionnement de l'application</h2>
        <p data-i18n="how.desc">Le système reproduit le circuit administratif réel, avec une traçabilité complète.</p>
    </div>

    <div class="workflow">
        <article class="card">
            <h3 data-i18n="flow.title">Circuit de traitement</h3>
            <div class="timeline">
                <div><strong data-i18n="flow.1t">1. Dépôt étudiant :</strong> <span data-i18n="flow.1d">l'étudiant soumet sa requête et ses pièces jointes.</span></div>
                <div><strong data-i18n="flow.2t">2. Service courrier :</strong> <span data-i18n="flow.2d">enregistrement et orientation initiale du dossier.</span></div>
                <div><strong data-i18n="flow.3t">3. Services métier :</strong> <span data-i18n="flow.3d">traitement, validation ou rejet motivé à chaque étape.</span></div>
                <div><strong data-i18n="flow.4t">4. Service final :</strong> <span data-i18n="flow.4d">décision favorable/défavorable et clôture du dossier.</span></div>
                <div><strong data-i18n="flow.5t">5. Notification :</strong> <span data-i18n="flow.5d">l'étudiant reçoit le résultat et peut consulter l'historique.</span></div>
            </div>
        </article>

        <article class="card" style="background: linear-gradient(145deg, rgba(31,122,108,0.12), rgba(239,107,58,0.1));">
            <h3 data-i18n="principles.title">Principes du SRM</h3>
            <div class="timeline" style="border-left-color: rgba(239,107,58,0.32);">
                <div><strong data-i18n="principles.1t">Routage intelligent :</strong> <span data-i18n="principles.1d">vers le département selon la filière de l'étudiant.</span></div>
                <div><strong data-i18n="principles.2t">Espaces dédiés :</strong> <span data-i18n="principles.2d">chaque agent traite uniquement les dossiers de son service.</span></div>
                <div><strong data-i18n="principles.3t">Transparence :</strong> <span data-i18n="principles.3d">chaque action est datée (entrée/sortie).</span></div>
                <div><strong data-i18n="principles.4t">Objectif qualité :</strong> <span data-i18n="principles.4d">délai cible de traitement fixé à 72h.</span></div>
            </div>

            <div class="chip-list">
                <span>Certificat de scolarité</span>
                <span>Duplicata</span>
                <span>Syllabus</span>
                <span>Correction d'identité</span>
                <span>Absence note CC</span>
                <span>Anomalies PV</span>
            </div>
        </article>
    </div>
</section>

<footer class="foot" data-i18n="footer">Système de Requêtes Étudiantes - IUT de Douala</footer>

<script>
    const I18N = {
        fr: {
            'brand.title': 'SRM - Student Request Manager',
            'brand.desc': "Application de gestion des requêtes étudiantes de l'IUT de Douala.",
            'cta.login': 'Se connecter',
            'hero.title': 'La gestion des requêtes étudiantes, enfin fluide et moderne.',
            'hero.text': "Le SRM digitalise tout le parcours d'une requête : dépôt, orientation automatique vers le bon service, traitement par les agents concernés, historique des actions et décision finale notifiée à l'étudiant. Un circuit fiable, lisible et rapide pour toute la communauté de l'IUT de Douala.",
            'metrics.title': 'Chiffres clés de la plateforme',
            'metrics.desc': "Un aperçu direct de l'activité et de la couverture fonctionnelle du SRM.",
            'metrics.total': 'Requêtes enregistrées',
            'metrics.done': 'Requêtes traitées',
            'metrics.depts': 'Départements couverts',
            'metrics.types': 'Types de requêtes',
            'metrics.students': 'Étudiants inscrits',
            'how.title': "Fonctionnement de l'application",
            'how.desc': 'Le système reproduit le circuit administratif réel, avec une traçabilité complète.',
            'flow.title': 'Circuit de traitement',
            'flow.1t': '1. Dépôt étudiant :',
            'flow.1d': "l'étudiant soumet sa requête et ses pièces jointes.",
            'flow.2t': '2. Service courrier :',
            'flow.2d': 'enregistrement et orientation initiale du dossier.',
            'flow.3t': '3. Services métier :',
            'flow.3d': 'traitement, validation ou rejet motivé à chaque étape.',
            'flow.4t': '4. Service final :',
            'flow.4d': 'décision favorable/défavorable et clôture du dossier.',
            'flow.5t': '5. Notification :',
            'flow.5d': "l'étudiant reçoit le résultat et peut consulter l'historique.",
            'principles.title': 'Principes du SRM',
            'principles.1t': 'Routage intelligent :',
            'principles.1d': "vers le département selon la filière de l'étudiant.",
            'principles.2t': 'Espaces dédiés :',
            'principles.2d': 'chaque agent traite uniquement les dossiers de son service.',
            'principles.3t': 'Transparence :',
            'principles.3d': 'chaque action est datée (entrée/sortie).',
            'principles.4t': 'Objectif qualité :',
            'principles.4d': 'délai cible de traitement fixé à 72h.',
            'footer': 'Système de Requêtes Étudiantes - IUT de Douala'
        },
        en: {
            'brand.title': 'SRM - Student Request Manager',
            'brand.desc': 'Student request management application for IUT Douala.',
            'cta.login': 'Log in',
            'hero.title': 'Student request management, finally smooth and modern.',
            'hero.text': 'SRM digitizes the full lifecycle of a request: submission, automatic routing to the right service, processing by the relevant agents, full action history, and final decision sent to the student. A reliable and fast workflow for the whole IUT Douala community.',
            'metrics.title': 'Platform key figures',
            'metrics.desc': 'A direct overview of activity and functional coverage.',
            'metrics.total': 'Registered requests',
            'metrics.done': 'Processed requests',
            'metrics.depts': 'Covered departments',
            'metrics.types': 'Request types',
            'metrics.students': 'Registered students',
            'how.title': 'How the application works',
            'how.desc': 'The system mirrors the real administrative workflow with full traceability.',
            'flow.title': 'Processing flow',
            'flow.1t': '1. Student submission:',
            'flow.1d': 'the student submits a request with attachments.',
            'flow.2t': '2. Mail service:',
            'flow.2d': 'registration and initial routing of the file.',
            'flow.3t': '3. Business services:',
            'flow.3d': 'processing, approval, or rejection with reason at each step.',
            'flow.4t': '4. Final service:',
            'flow.4d': 'final decision and request closure.',
            'flow.5t': '5. Notification:',
            'flow.5d': 'the student receives the result and can view the history.',
            'principles.title': 'SRM principles',
            'principles.1t': 'Smart routing:',
            'principles.1d': "to the department based on the student's major.",
            'principles.2t': 'Dedicated spaces:',
            'principles.2d': 'each agent only handles requests assigned to their service.',
            'principles.3t': 'Transparency:',
            'principles.3d': 'every action is timestamped (entry/exit).',
            'principles.4t': 'Quality target:',
            'principles.4d': 'processing target set to 72 hours.',
            'footer': 'Student Request Management System - IUT Douala'
        }
    };

    function getLang() {
        return localStorage.getItem('ui_lang') === 'en' ? 'en' : 'fr';
    }

    function setLang(lang) {
        localStorage.setItem('ui_lang', lang === 'en' ? 'en' : 'fr');
        applyTranslations();
    }

    function t(key) {
        const lang = getLang();
        return (I18N[lang] && I18N[lang][key]) || (I18N.fr[key] || key);
    }

    function applyTranslations() {
        const lang = getLang();
        document.documentElement.lang = lang;
        document.querySelectorAll('[data-i18n]').forEach((el) => {
            const key = el.getAttribute('data-i18n');
            el.textContent = t(key);
        });

        const toggle = document.getElementById('langToggle');
        if (toggle) {
            toggle.textContent = lang === 'fr' ? 'FR | EN' : 'EN | FR';
            toggle.title = lang === 'fr' ? 'Switch to English' : 'Passer en français';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        applyTranslations();
        const toggle = document.getElementById('langToggle');
        if (toggle) {
            toggle.addEventListener('click', () => {
                setLang(getLang() === 'fr' ? 'en' : 'fr');
            });
        }
    });
</script>
</body>
</html>
