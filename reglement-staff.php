<?php require_once __DIR__ . '/includes/vite.php'; ?>
<!DOCTYPE html>

<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1ee6a0">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="South District">
  <link rel="manifest" href="manifest.php">

  <!-- OpenGraph / Discord -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="South District RP">
  
  
  

  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image">
  
  
  <meta name="twitter:image" content="https://www.south-district.fr/assets/hero-bg.png">

  
  <link rel="icon" href="assets/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32.png">
  <link rel="icon" type="image/png" sizes="48x48" href="assets/favicon.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png">
  <?= vite('js/site-guard.js') ?>
<?= vite('css/home.css') ?>
    <?= vite('css/account.css') ?>
<?= vite('css/pages.css') ?>
  <?= vite('css/reglement.css') ?>
  <?= vite('css/admin-mode.css') ?>

  <title>Règlement Staff - South District RP</title>
  <meta property="og:title" content="Règlement Staff - South District RP">
  <meta property="og:description" content="Règles internes et lignes de conduite pour les membres du staff de South District RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Règlement Staff - South District RP">
  <meta name="twitter:description" content="Règles internes et lignes de conduite pour les membres du staff de South District RP.">
  <meta name="description" content="Règles internes et lignes de conduite pour les membres du staff de South District RP.">
  <meta property="og:image" content="https://www.south-district.fr/assets/hero-bg.png">
  
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "WebSite",
        "name": "South District RP",
        "url": "https://www.south-district.fr",
        "description": "Serveur GTA V Roleplay FiveM (Strict RP)."
      },
      {
        "@type": "Organization",
        "name": "South District RP",
        "url": "https://www.south-district.fr",
        "logo": "https://www.south-district.fr/assets/favicon.png",
        "sameAs": [
          "https://discord.gg/south-district",
          "https://www.tiktok.com/@southdistrict",
          "https://www.twitch.tv/southdistrict"
        ]
      },
      {
        "@type": "VideoGame",
        "name": "GTA V Roleplay - South District RP",
        "description": "Serveur GTA 5 RP immersif sur FiveM.",
        "url": "https://www.south-district.fr",
        "playMode": "MultiPlayer",
        "applicationCategory": "Game"
      }
    ]
  }
  </script>
</head>
<body class="cp-home" data-admin-page="reglement-staff" data-admin-permission="edit_reglement_staff">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page cp-hero--reglement">
      <div class="cp-hero__panel reglement-layout">

        <aside class="reglement-sidebar" aria-label="Navigation du règlement staff">
          <div class="reglement-sidebar__head">
            <p class="reglement-sidebar__title">Sommaire</p>
            <button type="button" class="reglement-resume">Reprendre la lecture</button>
          </div>
          <nav class="reglement-sidebar__nav">
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Général</p>
              <a href="#lexique-rp" class="reglement-nav__item" data-section="lexique-rp" style="--reg-accent:#f5c842"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Lexique RP</span></a>
            </div>
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Conduite</p>
              <a href="#comportement" class="reglement-nav__item" data-section="comportement" style="--reg-accent:#1ee6a0"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Comportement staff</span></a>
              <a href="#activite" class="reglement-nav__item" data-section="activite" style="--reg-accent:#1ee6a0"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Activité staff</span></a>
            </div>
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Modération</p>
              <a href="#tickets" class="reglement-nav__item" data-section="tickets" style="--reg-accent:#38bdf8"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Gestion tickets</span></a>
              <a href="#reports" class="reglement-nav__item" data-section="reports" style="--reg-accent:#38bdf8"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Reports</span></a>
              <a href="#permissions" class="reglement-nav__item" data-section="permissions" style="--reg-accent:#38bdf8"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Permissions</span></a>
            </div>
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Encadrement</p>
              <a href="#confidentialite" class="reglement-nav__item" data-section="confidentialite" style="--reg-accent:#f59e0b"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Confidentialité</span></a>
              <a href="#sanctions-staff" class="reglement-nav__item" data-section="sanctions-staff" style="--reg-accent:#f59e0b"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Sanctions staff</span></a>
              <a href="#fautes-graves" class="reglement-nav__item" data-section="fautes-graves" style="--reg-accent:#ef4444"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Fautes graves</span></a>
            </div>
          </nav>
        </aside>

        <div class="reglement-body">
          <div class="cp-page__scroll" id="reglement-scroll" data-reglement-scope="staff">
            <div class="cp-page__content">

              <!-- LEXIQUE RP -->
              <section class="reg-section is-current" id="lexique-rp" data-theme="yellow">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Staff</span>
                      <span class="reg-section__id-page">1 / 9</span>
                    </div>
                    <span class="reg-section__tag">Lexique · Termes du roleplay</span>
                    <h2 class="reg-section__title">Règlement Staff</h2>
                    <p class="reg-section__intro">Serveur RP sérieux · Staff encadré · Règlement officiel. Définitions des termes utilisés en rôleplay.</p>
                  </div>
                </header>
                <div class="reg-lexique">
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Fear RP</span><p class="reg-lexique__def">Réagir à la peur et au danger de façon réaliste quand la situation l'exige.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">No Pain</span><p class="reg-lexique__def">Ignorer ses blessures ou la douleur de son personnage. Strictement interdit.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Metagaming</span><p class="reg-lexique__def">Utiliser des informations HRP (Discord, stream, vocal) pour agir en RP.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Powergaming</span><p class="reg-lexique__def">Forcer des actions irréalistes ou impossibles sur un autre joueur.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">NLR</span><p class="reg-lexique__def">Oublier les événements liés à sa mort. New Life Rule · règle fondamentale du serveur.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">FreeKill</span><p class="reg-lexique__def">Tuer un joueur sans aucune raison RP valable. Sanctionné immédiatement.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Combat Logging</span><p class="reg-lexique__def">Quitter une scène RP pour l'éviter. Comportement interdit et passible de ban.</p></div>
                </div>
              </section>

              <!-- COMPORTEMENT STAFF -->
              <section class="reg-section" id="comportement" data-theme="green">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Conduite</span>
                      <span class="reg-section__id-page">2 / 9</span>
                    </div>
                    <span class="reg-section__tag">Comportement staff</span>
                    <h2 class="reg-section__title">Conduite &amp; attitude</h2>
                    <p class="reg-section__intro">Conduite et attitude attendues de chaque membre du staff en service.</p>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Respect obligatoire</h3><p>Respect envers tous les joueurs, membres du staff et administrateurs · en jeu comme sur Discord.</p></div><span class="reg-rules__badge">Essentiel</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Impartialité totale</h3><p>Aucun favoritisme dans les décisions. Traite chaque situation avec la même rigueur, quels que soient les joueurs impliqués.</p></div><span class="reg-rules__badge">Neutre</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Professionnalisme constant</h3><p>Reste professionnel en permanence lorsque tu es en service. Tu représentes South District RP.</p></div><span class="reg-rules__badge">Staff</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">04</span><div class="reg-rules__body"><h3>Aucun abus de pouvoir</h3><p>L'abus de pouvoir staff est strictement interdit et passible d'une exclusion immédiate du staff.</p></div><span class="reg-rules__badge">Interdit</span></li>
                </ol>
              </section>

              <!-- GESTION TICKETS -->
              <section class="reg-section" id="tickets" data-theme="blue">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Modération</span>
                      <span class="reg-section__id-page">3 / 9</span>
                    </div>
                    <span class="reg-section__tag">Gestion tickets</span>
                    <h2 class="reg-section__title">Procédures &amp; protocoles</h2>
                    <p class="reg-section__intro">Procédures et protocoles de traitement des tickets joueurs.</p>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>écouter les deux parties</h3><p>écoute chaque partie sans parti pris avant de prendre une décision. Aucun jugement hâtif.</p></div><span class="reg-rules__badge">Ticket</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Analyser les preuves</h3><p>Examine l'ensemble des preuves fournies (clips, screenshots, logs) avant toute sanction.</p></div><span class="reg-rules__badge">Preuves</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Rester neutre</h3><p>Reste neutre du début à la fin du traitement. Ne prends pas parti, même en cas de conflit personnel.</p></div><span class="reg-rules__badge">Neutre</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">04</span><div class="reg-rules__body"><h3>Justifier la fermeture</h3><p>Ne ferme jamais un ticket sans justification claire et documentée de la décision prise.</p></div><span class="reg-rules__badge">Clôture</span></li>
                </ol>
              </section>

              <!-- REPORTS -->
              <section class="reg-section" id="reports" data-theme="blue">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Modération</span>
                      <span class="reg-section__id-page">4 / 9</span>
                    </div>
                    <span class="reg-section__tag">Reports</span>
                    <h2 class="reg-section__title">Signalements joueurs</h2>
                    <p class="reg-section__intro">Analyse et traitement des signalements effectués par la communauté.</p>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Vérifier les preuves</h3><p>Vérifie l'ensemble des preuves disponibles avant toute action. Un report sans preuve ne suffit pas.</p></div><span class="reg-rules__badge">Preuves</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Appliquer le règlement</h3><p>Applique le règlement serveur de façon stricte et cohérente avec les décisions précédentes.</p></div><span class="reg-rules__badge">Règles</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Documenter la décision</h3><p>Documente chaque décision prise : motif, preuves consultées et sanction appliquée le cas échéant.</p></div><span class="reg-rules__badge">Trace</span></li>
                </ol>
              </section>

              <!-- PERMISSIONS -->
              <section class="reg-section" id="permissions" data-theme="blue">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Modération</span>
                      <span class="reg-section__id-page">5 / 9</span>
                    </div>
                    <span class="reg-section__tag">Permissions</span>
                    <h2 class="reg-section__title">Commandes staff</h2>
                    <p class="reg-section__intro">Utilisation correcte et encadrée des commandes et permissions staff.</p>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Interdiction du Give Money</h3><p>Le Give Money est strictement interdit, quelle que soit la situation ou le joueur concerné.</p></div><span class="reg-rules__badge">Interdit</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Aucun TP abusif</h3><p>Aucune téléportation abusive hors contexte autorisée. Chaque TP doit être justifié par le service.</p></div><span class="reg-rules__badge">Interdit</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Usage limité au service</h3><p>Les commandes staff sont réservées aux besoins du service. Usage personnel ou avantage en RP = faute grave.</p></div><span class="reg-rules__badge">Service</span></li>
                </ol>
              </section>

              <!-- CONFIDENTIALITÉ -->
              <section class="reg-section" id="confidentialite" data-theme="gold">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Encadrement</span>
                      <span class="reg-section__id-page">6 / 9</span>
                    </div>
                    <span class="reg-section__tag">Confidentialité</span>
                    <h2 class="reg-section__title">Informations internes</h2>
                    <p class="reg-section__intro">Protection des informations internes et du cadre staff.</p>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Réunions staff privées</h3><p>Les réunions staff restent strictement privées. Aucune diffusion externe au staff autorisée.</p></div><span class="reg-rules__badge">Privé</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Informations confidentielles</h3><p>Dossiers joueurs, décisions internes et discussions staff sont confidentiels.</p></div><span class="reg-rules__badge">Confidentiel</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Divulgation = sanction</h3><p>Toute divulgation d'information interne entraîne une sanction immédiate, voire une exclusion du staff.</p></div><span class="reg-rules__badge">Sanction</span></li>
                </ol>
              </section>

              <!-- ACTIVITÉ STAFF -->
              <section class="reg-section" id="activite" data-theme="green">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Conduite</span>
                      <span class="reg-section__id-page">7 / 9</span>
                    </div>
                    <span class="reg-section__tag">Activité staff</span>
                    <h2 class="reg-section__title">Présence &amp; engagement</h2>
                    <p class="reg-section__intro">Présence et engagement attendus de chaque membre de l'équipe.</p>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Présence régulière</h3><p>Maintiens une présence régulière et active sur le serveur lorsque tu es en service staff.</p></div><span class="reg-rules__badge">Présence</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Prévenir en cas d'absence</h3><p>Préviens impérativement l'équipe en cas d'absence prolongée. L'inactivité non signalée est sanctionnée.</p></div><span class="reg-rules__badge">Absence</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Réunions obligatoires</h3><p>La participation aux réunions staff est obligatoire. Les absences répétées sans justification entraînent des mesures.</p></div><span class="reg-rules__badge">Réunion</span></li>
                </ol>
              </section>

              <!-- SANCTIONS STAFF -->
              <section class="reg-section" id="sanctions-staff" data-theme="gold">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Encadrement</span>
                      <span class="reg-section__id-page">8 / 9</span>
                    </div>
                    <span class="reg-section__tag">Sanctions staff</span>
                    <h2 class="reg-section__title">Système progressif</h2>
                    <p class="reg-section__intro">Système de sanctions progressif appliqué aux membres du staff.</p>
                  </div>
                </header>
                <div class="reg-sanctions">
                  <article class="reg-sanction" data-level="s1"><span class="reg-sanction__id">S1</span><div class="reg-sanction__body"><h3>Rappel à l'ordre verbal</h3><p>Premier écart constaté : rappel oral de la charte staff et des règles en vigueur. Aucune restriction immédiate.</p></div><span class="reg-rules__badge">étape 1</span></article>
                  <article class="reg-sanction" data-level="s2"><span class="reg-sanction__id">S2</span><div class="reg-sanction__body"><h3>Avertissement formel</h3><p>Deuxième écart ou faute modérée : avertissement officiel (Warn) consigné dans le dossier staff.</p></div><span class="reg-rules__badge">étape 2</span></article>
                  <article class="reg-sanction" data-level="s3"><span class="reg-sanction__id">S3</span><div class="reg-sanction__body"><h3>Exclusion définitive</h3><p>Troisième écart ou faute grave : exclusion définitive du staff et révocation de toutes les permissions.</p></div><span class="reg-rules__badge">étape 3</span></article>
                </div>
              </section>

              <!-- FAUTES GRAVES -->
              <section class="reg-section" id="fautes-graves" data-theme="red">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Encadrement</span>
                      <span class="reg-section__id-page">9 / 9</span>
                    </div>
                    <span class="reg-section__tag">Fautes graves</span>
                    <h2 class="reg-section__title">Exclusion immédiate</h2>
                    <p class="reg-section__intro">Infractions entraînant une exclusion immédiate du staff, sans passage par le système progressif.</p>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Abus de pouvoir staff</h3><p>Utilisation des permissions staff à des fins personnelles ou pour avantager un joueur.</p></div><span class="reg-rules__badge">Grave</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Favoritisme</h3><p>Favoritisme avéré envers un joueur dans le traitement d'un report ou d'un ticket.</p></div><span class="reg-rules__badge">Grave</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Corruption avérée</h3><p>Corruption RP ou HRP : avantages en échange de clémence ou de décisions biaisées.</p></div><span class="reg-rules__badge">Grave</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">04</span><div class="reg-rules__body"><h3>Harcèlement ou menaces</h3><p>Harcèlement, intimidation ou menaces envers un joueur ou un membre du staff.</p></div><span class="reg-rules__badge">Grave</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">05</span><div class="reg-rules__body"><h3>Discrimination</h3><p>Discrimination de toute forme envers un joueur ou un collègue staff.</p></div><span class="reg-rules__badge">Grave</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">06</span><div class="reg-rules__body"><h3>Triche ou exploitation de bugs</h3><p>Triche, exploitation volontaire de bugs ou failles du serveur.</p></div><span class="reg-rules__badge">Grave</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">07</span><div class="reg-rules__body"><h3>Sanction sans preuve</h3><p>Appliquer une sanction à un joueur sans preuve suffisante ou sans justification documentée.</p></div><span class="reg-rules__badge">Grave</span></li>
                </ol>
              </section>

              <div class="reglement-pager-wrap">
                <a href="accueil" class="reglement-pager__brand" aria-label="South District RP">
                  <img src="assets/logo.png" alt="South District" width="128" height="128" decoding="async">
                </a>
                <div class="reglement-pager__body">
                  <p class="reglement-pager__status" id="reglement-pager-status" aria-live="polite">Lexique RP · 1 / 9</p>
                  <nav class="reglement-pager" aria-label="Navigation entre les parties du règlement staff">
                    <button type="button" class="reglement-pager__btn" id="reglement-prev" disabled>Partie précédente</button>
                    <button type="button" class="reglement-pager__btn" id="reglement-next">Partie suivante</button>
                  </nav>
                </div>
              </div>

              <div class="reglement-footer sd-page-footer" hidden>
                <div class="btn-row">
                  <a href="postuler" class="btn btn-primary">Devenir staff</a>
                  <a href="accueil" class="btn btn-outline">Accueil</a>
                </div>
                <p class="sd-footer-note">© 2026 South District RP — Tous droits réservés.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
  <?= vite('js/reglement.js') ?>
  <?= vite('js/admin-mode.js') ?>
</body>
</html>




