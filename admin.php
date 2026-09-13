<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/includes/vite.php';

$currentUser = sd_current_user();
$canAccessAdmin = $currentUser !== null && sd_can_access_admin($currentUser);
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
  
  <link rel="icon" href="assets/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32.png">
  <link rel="icon" type="image/png" sizes="48x48" href="assets/favicon.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png">
  <?= vite('js/site-guard.js') ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <?= vite('css/home.css') ?>
  <?= vite('css/reglement.css') ?>
  <?= vite('css/admin.css') ?>
  <?= vite('css/carte-entreprise.css') ?>
  <?= vite('css/account.css') ?>
  <noscript><?= vite('css/account.css') ?></noscript>


  <title>Administration - South District RP</title>
  <meta property="og:title" content="Administration - South District RP">
  <meta property="og:description" content="Espace administration sécurisé du serveur South District RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Administration - South District RP">
  <meta name="twitter:description" content="Espace administration sécurisé du serveur South District RP.">
  <meta name="description" content="Espace administration sécurisé du serveur South District RP.">
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
<body class="cp-home cp-hero--admin cp-nav--logo-only">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page cp-hero--reglement cp-hero--admin">
      <div id="sd-admin-gate" class="sd-admin-gate cp-hero__panel" <?= $canAccessAdmin ? 'style="display: none !important;" hidden' : '' ?>>
        <p>Accès réservé aux membres staff et administrateurs.</p>
        <button type="button" id="sd-admin-open-login" class="sd-profil__btn sd-profil__btn--primary">Se connecter</button>
      </div>

      <?php if ($canAccessAdmin): ?>
      <script>
        window.__SD_BOOTSTRAP_USER__ = <?= json_encode(sd_public_user($currentUser), JSON_UNESCAPED_UNICODE) ?>;
      </script>
      <?php endif; ?>

      <div id="sd-admin-layout" class="cp-hero__panel reglement-layout <?= $canAccessAdmin ? 'sd-force-visible' : '' ?>" <?= !$canAccessAdmin ? 'style="display: none !important;" hidden' : '' ?>>
        <aside class="reglement-sidebar sd-admin-sidebar" aria-label="Administration">
          <div class="reglement-sidebar__head">
            <p class="reglement-sidebar__title">Sommaire</p>
          </div>
          <nav class="reglement-sidebar__nav" id="sd-admin-nav"></nav>
        </aside>
        <div class="reglement-body sd-admin-body">
          <div id="sd-admin-main" class="sd-admin__main"></div>
        </div>
      </div>
    </main>

    <script>
      // Nettoyage automatique des anciens caches Service Worker sur l'administration
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
          for (var reg of registrations) { reg.update(); }
        });
        if ('caches' in window) {
          caches.keys().then(function(names) {
            for (var name of names) {
              if (name !== 'south-district-v5') { caches.delete(name); }
            }
          });
        }
      }
    </script>

  </div>

  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/carte-entreprise.js') ?>
  <?= vite('js/admin-dashboard.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
</body>
</html>
