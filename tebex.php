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
  <?= vite('css/tebex.css') ?>
  <?= vite('css/account.css') ?>

  <title>Boutique Tebex - South District RP</title>
  <meta property="og:title" content="Boutique Tebex - South District RP">
  <meta property="og:description" content="Soutenez le serveur sur notre boutique Tebex officielle et obtenez des avantages exclusifs sur South District RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Boutique Tebex - South District RP">
  <meta name="twitter:description" content="Soutenez le serveur sur notre boutique Tebex officielle et obtenez des avantages exclusifs sur South District RP.">
  <meta name="description" content="Soutenez le serveur sur notre boutique Tebex officielle et obtenez des avantages exclusifs sur South District RP.">
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
<body class="cp-home cp-hero--tebex cp-hero--page">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page">
      <div class="cp-hero__panel">
        <section class="sd-tebex" aria-labelledby="sd-tebex-title">
          <div class="sd-tebex__visual">
            <img src="assets/hero-bg.png" alt="" decoding="async">
          </div>
          <div class="sd-tebex__content">
            <div class="sd-tebex__intro">
              <span class="section-tag">Boutique officielle</span>
              <h1 id="sd-tebex-title">Soutiens South District</h1>
              <p class="sd-tebex__lead">Packs VIP, cosmétiques et avantages en jeu. Paiement sécurisé, livraison automatique.</p>
            </div>

            <section class="sd-tebex__panel" aria-label="Catégories boutique">
              <header class="sd-tebex__panel-head">
                <h2 class="sd-tebex__panel-title">Boutique Tebex</h2>
                <a
                  href="https://south-district.tebex.io"
                  class="sd-tebex__panel-go"
                  target="_blank"
                  rel="noopener"
                  aria-label="Ouvrir la boutique Tebex"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M7 17L17 7"/>
                    <path d="M7 7h10v10"/>
                  </svg>
                </a>
              </header>

              <ul class="sd-tebex__grid">
                <li>
                  <a href="https://south-district.tebex.io" class="sd-tebex__card" target="_blank" rel="noopener">
                    <span class="sd-tebex__card-icon" aria-hidden="true">
                      <img src="assets/icons/tebex/vip-star.png" alt="" width="28" height="28" decoding="async">
                    </span>
                    <span class="sd-tebex__card-body">
                      <span class="sd-tebex__card-title">Packs VIP</span>
                      <span class="sd-tebex__card-desc">Slots prioritaires, bonus et avantages exclusifs.</span>
                      <span class="sd-tebex__card-link">Voir les offres</span>
                    </span>
                  </a>
                </li>
                <li>
                  <a href="https://south-district.tebex.io" class="sd-tebex__card" target="_blank" rel="noopener">
                    <span class="sd-tebex__card-icon" aria-hidden="true">
                      <img src="assets/icons/tebex/cosmetics.png" alt="" width="28" height="28" decoding="async">
                    </span>
                    <span class="sd-tebex__card-body">
                      <span class="sd-tebex__card-title">Cosmétiques</span>
                      <span class="sd-tebex__card-desc">Skins, véhicules et personnalisations uniques.</span>
                      <span class="sd-tebex__card-link">Parcourir</span>
                    </span>
                  </a>
                </li>
                <li>
                  <a href="https://south-district.tebex.io" class="sd-tebex__card" target="_blank" rel="noopener">
                    <span class="sd-tebex__card-icon" aria-hidden="true">
                      <img src="assets/icons/tebex/boost.png" alt="" width="28" height="28" decoding="async">
                    </span>
                    <span class="sd-tebex__card-body">
                      <span class="sd-tebex__card-title">Avantages en jeu</span>
                      <span class="sd-tebex__card-desc">Boosts, items et récompenses livrés automatiquement.</span>
                      <span class="sd-tebex__card-link">Acheter</span>
                    </span>
                  </a>
                </li>
                <li>
                  <a href="https://south-district.tebex.io" class="sd-tebex__card" target="_blank" rel="noopener">
                    <span class="sd-tebex__card-icon" aria-hidden="true">
                      <img src="assets/icons/tebex/shop.png" alt="" width="28" height="28" decoding="async">
                    </span>
                    <span class="sd-tebex__card-body">
                      <span class="sd-tebex__card-title">Boutique Tebex</span>
                      <span class="sd-tebex__card-desc">south-district.tebex.io, paiement sécurisé.</span>
                      <span class="sd-tebex__card-link">Ouvrir le site</span>
                    </span>
                  </a>
                </li>
              </ul>
            </section>
          </div>
        </section>
      </div>
    </main>

  </div>

  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
</body>
</html>




