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
  <?= vite('css/lore.css') ?>

  <title>Lore et Histoire - South District RP</title>
  <meta property="og:title" content="Lore et Histoire - South District RP">
  <meta property="og:description" content="Plongez dans l'histoire passionnante de South District RP. Découvrez le background officiel de notre serveur GTA V Roleplay.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Lore et Histoire - South District RP">
  <meta name="twitter:description" content="Plongez dans l'histoire passionnante de South District RP. Découvrez le background officiel de notre serveur GTA V Roleplay.">
  <meta name="description" content="Plongez dans l'histoire passionnante de South District RP. Découvrez le background officiel de notre serveur GTA V Roleplay.">
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

    <main class="cp-hero cp-hero--page cp-hero--lore">
      <div class="cp-hero__panel">
        <section class="sd-lore" aria-label="Lore · Bande-annonce">
          <div class="sd-cinema" id="sd-cinema">
            <div class="sd-cinema__viewport" id="sd-cinema-viewport">
              <video
                class="sd-cinema__video"
                id="sd-cinema-video"
                preload="metadata"
                playsinline
                tabindex="0"
                poster="assets/logo.png"
                aria-label="Bande-annonce South District RP"
              >
                <source src="assets/lore/trailer-h264.mp4" type="video/mp4">
                <source src="assets/lore/trailer.mp4" type="video/mp4">
              </video>
              <p class="sd-cinema__unavailable" id="sd-cinema-unavailable" hidden>
                Bande-annonce indisponible. Vérifiez que <code>assets/lore/trailer-h264.mp4</code> est bien sur le serveur.
              </p>

              <div class="sd-cinema__shade" aria-hidden="true"></div>

              <button type="button" class="sd-cinema__launch" id="sd-cinema-launch" aria-label="Lancer la bande-annonce">
                <img class="sd-cinema__launch-logo" src="assets/logo.png" alt="South District RP" decoding="async">
              </button>

              <div class="sd-cinema__hud-top" id="sd-cinema-deck">
                <div class="sd-cinema__deck-brand">
                  <img class="sd-cinema__deck-logo" src="assets/logo.png" alt="" decoding="async">
                  <span class="sd-cinema__deck-sep" aria-hidden="true"></span>
                  <button type="button" class="sd-cinema__btn sd-cinema__btn--play" id="sd-cinema-toggle" aria-label="Lecture">
                    <svg class="sd-cinema__icon-play" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    <svg class="sd-cinema__icon-pause" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                  </button>
                  <div class="sd-cinema__time">
                    <span id="sd-cinema-current">0:00</span>
                    <span class="sd-cinema__time-sep">/</span>
                    <span id="sd-cinema-duration">0:00</span>
                  </div>
                </div>
                <div class="sd-cinema__hud-top-right">
                  <span class="sd-cinema__deck-meta">Lore · Trailer</span>
                  <div class="sd-cinema__volume" id="sd-cinema-volume-wrap">
                    <button type="button" class="sd-cinema__btn sd-cinema__btn--ghost sd-cinema__btn--vol" id="sd-cinema-mute" aria-label="Couper le son">
                      <svg class="sd-cinema__icon-vol" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/></svg>
                      <svg class="sd-cinema__icon-muted" viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3 3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4 9.91 6.09 12 8.18V4z"/></svg>
                    </button>
                    <input type="range" class="sd-cinema__vol-slider" id="sd-cinema-volume" min="0" max="1" step="0.05" value="0.85" aria-label="Volume">
                    <span class="sd-cinema__vol-label" id="sd-cinema-vol-label" aria-hidden="true">85%</span>
                  </div>
                </div>
              </div>

              <div class="sd-cinema__hud-bottom">
                <div class="sd-cinema__progress" id="sd-cinema-progress" role="slider" aria-label="Progression" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
                  <div class="sd-cinema__progress-track">
                    <div class="sd-cinema__progress-buffer" id="sd-cinema-buffer"></div>
                    <div class="sd-cinema__progress-fill" id="sd-cinema-fill"></div>
                    <div class="sd-cinema__progress-head" id="sd-cinema-head"></div>
                  </div>
                </div>
              </div>
            </div>
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
  <?= vite('js/lore.js') ?>
</body>
</html>




