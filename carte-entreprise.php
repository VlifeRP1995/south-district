<?php require_once __DIR__ . '/includes/vite.php'; ?>
<!DOCTYPE html>

<html lang="fr" class="flora-carte-page-html">
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
  <?= vite('css/carte-entreprise.css') ?>

  <title>Carte & Entreprises - South District RP</title>
  <meta property="og:title" content="Carte & Entreprises - South District RP">
  <meta property="og:description" content="Découvrez la carte interactive de Los Santos, les entreprises, factions et gangs disponibles sur South District RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Carte & Entreprises - South District RP">
  <meta name="twitter:description" content="Découvrez la carte interactive de Los Santos, les entreprises, factions et gangs disponibles sur South District RP.">
  <meta name="description" content="Découvrez la carte interactive de Los Santos, les entreprises, factions et gangs disponibles sur South District RP.">
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
<body class="cp-home flora-carte-page-body">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <div class="flora-carte-page-shell">
      <header class="flora-carte-page-bar">
        <a href="accueil" class="flora-carte-page-back" aria-label="Retour à l'accueil">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
          Accueil
        </a>
        <img class="flora-carte-page-bar-icon" src="assets/logo.png" alt="" width="32" height="32">
        <div class="flora-carte-page-bar-copy">
          <span class="flora-carte-page-kicker">Los Santos · South District RP</span>
          <h1 class="flora-carte-page-title">Entreprises disponibles</h1>
        </div>
      </header>
      <div id="sd-carte-page-root" class="flora-carte-page-root" aria-label="Carte interactive des entreprises"></div>
    </div>

  </div>

  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/carte-entreprise.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
</body>
</html>




