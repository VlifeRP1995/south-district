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
  <?= vite('css/equipe.css') ?>

  <title>Notre Équipe - South District RP</title>
  <meta property="og:title" content="Notre Équipe - South District RP">
  <meta property="og:description" content="Découvrez le staff de South District RP : fondateurs, développeurs, administrateurs et modérateurs.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Notre Équipe - South District RP">
  <meta name="twitter:description" content="Découvrez le staff de South District RP : fondateurs, développeurs, administrateurs et modérateurs.">
  <meta name="description" content="Découvrez le staff de South District RP : fondateurs, développeurs, administrateurs et modérateurs.">
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
<body class="cp-home cp-hero--equipe">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page">
      <div class="cp-hero__panel">
        <div class="cp-page__scroll">
          <div class="cp-page__content cp-page__content--equipe">

            <div class="sd-team-intro cp-frame">
              <div class="sd-team-intro__brand">
                <img src="assets/logo.png" alt="" width="128" height="128" decoding="async">
              </div>
              <div class="sd-team-intro__body">
                <span class="section-tag">Communauté</span>
                <h1>Équipe</h1>
                <p class="sd-team-intro__desc">Découvrez les membres qui font vivre South District RP au quotidien : direction, staff et animation.</p>
              </div>
            </div>

            <div class="sd-team-panels">
              <section class="sd-team-panel sd-team-panel--hierarchy" aria-labelledby="sd-team-all-title">
                <header class="sd-team-panel__head">
                  <h2 id="sd-team-all-title">Hiérarchie staff</h2>
                  <p id="sd-team-count">Chargement⬦</p>
                </header>
                <div class="sd-team-panel__body">
                  <div id="sd-team-hierarchy" class="sd-team-hierarchy" aria-live="polite"></div>
                </div>
              </section>
            </div>

            <?php require_once 'includes/footer.php'; ?>
          </div>
        </div>
      </div>
    </main>

  </div>

  <!-- Laisser [] pour utiliser les 30 exemples par défaut dans js/equipe.js -->
  <script type="application/json" id="sd-team-data">[]</script>

  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
  <?= vite('js/equipe.js') ?>
  <?= vite('js/equipe-multiroles.js') ?>
</body>
</html>





