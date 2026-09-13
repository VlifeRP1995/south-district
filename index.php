<?php
require_once __DIR__ . '/includes/vite.php';
?>
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
  
  


  <link rel="icon" href="assets/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32.png">
  <link rel="icon" type="image/png" sizes="48x48" href="assets/favicon.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png">
  <?= vite('js/site-guard.js') ?>
  <?= vite('css/home.css') ?>
  <?= vite('css/account.css') ?>
  <noscript><?= vite('css/account.css') ?></noscript>

  <title>South District RP | Serveur GTA V Roleplay FiveM FR</title>
  <meta property="og:title" content="South District RP | Serveur GTA V Roleplay FiveM FR">
  <meta property="og:description" content="Rejoignez South District RP, un serveur GTA V Roleplay sur FiveM. Économie réaliste, jobs inédits, gangs et LSPD. Serveur 100% FR Strict RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="South District RP | Serveur GTA V Roleplay FiveM FR">
  <meta name="twitter:description" content="Rejoignez South District RP, un serveur GTA V Roleplay sur FiveM. Économie réaliste, jobs inédits, gangs et LSPD. Serveur 100% FR Strict RP.">
  <meta name="description" content="Rejoignez South District RP, un serveur GTA V Roleplay sur FiveM. Économie réaliste, jobs inédits, gangs et LSPD. Serveur 100% FR Strict RP.">
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
<body class="cp-home">


  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero">

      <div class="cp-hero__panel">

        <div class="cp-mosaic" id="cp-mosaic">
          <div class="cp-mosaic__photo" aria-hidden="true">
            <video class="cp-mosaic__video cp-mosaic__video--primary" autoplay loop muted playsinline preload="auto">
              <source src="assets/hero-bg.mp4" type="video/mp4">
              Votre navigateur ne supporte pas la lecture de cette vidéo.
            </video>
            <video class="cp-mosaic__video cp-mosaic__video--secondary" autoplay loop muted playsinline preload="auto" style="opacity: 0;">
              <source src="assets/hero-bg.mp4" type="video/mp4">
              Votre navigateur ne supporte pas la lecture de cette vidéo.
            </video>
          </div>
          <a href="reglement-serveur" class="cp-mosaic__tile">
            <span class="cp-mosaic__label">
              <span class="cp-mosaic__pill">Règlement Serveur</span>
            </span>
          </a>
          <a href="reglement-staff" class="cp-mosaic__tile">
            <span class="cp-mosaic__label">
              <span class="cp-mosaic__pill">Règlement Staff</span>
            </span>
          </a>
          <a href="postuler" class="cp-mosaic__tile">
            <span class="cp-mosaic__label">
              <span class="cp-mosaic__pill">Postuler</span>
            </span>
          </a>
          <a href="lore" class="cp-mosaic__tile cp-mosaic__tile--wide">
            <span class="cp-mosaic__label">
              <span class="cp-mosaic__pill">Lore</span>
            </span>
          </a>
        </div>

        <section class="cp-recruit" aria-label="Recrutement">
          <div class="cp-recruit__inner">
            <div class="cp-recruit__main">
              <h2 class="cp-recruit__title">Recrutement</h2>
              <div class="cp-recruit__info" id="cp-recruit-info">
                <button type="button" class="cp-recruit__trigger" aria-expanded="false" aria-controls="cp-recruit-preview">
                  Infos
                </button>
              </div>
            </div>

            <div class="cp-staff-team" id="cp-staff-team">
              <button type="button" class="cp-recruit__trigger" aria-expanded="false" aria-controls="cp-staff-team-preview">
                Staff
              </button>
            </div>
            <span id="cp-recruit-state" class="cp-recruit__state cp-recruit__state--open" role="status" aria-label="Candidatures staff ouvertes">Ouvert</span>
          </div>
        </section>

      </div>
    </main>

  </div>

  <div class="cp-hover-preview cp-recruit__preview" id="cp-recruit-preview" role="tooltip" aria-hidden="true">
    <p class="cp-recruit__preview-title">Recrutement</p>
    <div class="cp-recruit__preview-frame">
      <ul class="cp-recruit__preview-grid" id="cp-recruit-preview-list">
        <li>Staff RP</li>
        <li>Discord</li>
        <li>48 à 72h</li>
        <li>17 ans min.</li>
      </ul>
    </div>
  </div>

  <div class="cp-hover-preview cp-staff-team__preview" id="cp-staff-team-preview" role="tooltip" aria-hidden="true">
    <p class="cp-recruit__preview-title">Gérants staff</p>
    <div class="cp-recruit__preview-frame">
      <div class="cp-staff-team__preview-row">
        <div class="cp-staff-team__preview-member">
          <img src="assets/staff/ritano-louvier.png" alt="" width="56" height="56" decoding="async">
          <span class="cp-staff-team__preview-name">GS I Ritano Louvier</span>
        </div>
        <span class="cp-staff-team__preview-sep" aria-hidden="true"></span>
        <div class="cp-staff-team__preview-member">
          <img src="assets/staff/ivar.png" alt="" width="56" height="56" decoding="async">
          <span class="cp-staff-team__preview-name">GS I Ivar</span>
         </div>
        <span class="cp-staff-team__preview-sep" aria-hidden="true"></span>
        <div class="cp-staff-team__preview-member">
          <img src="assets/staff/Lkg.png" alt="" width="56" height="56" decoding="async">
          <span class="cp-staff-team__preview-name">GS I Lkg.4k00</span>
        </div>
      </div>
    </div>
    <p class="cp-recruit__preview-desc">
      Équipe de gestion du recrutement staff. Contacte-les sur Discord pour toute question liée aux candidatures.
    </p>
  </div>

  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
  <?= vite('js/home.js') ?>
</body>
</html>







