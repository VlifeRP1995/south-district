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
  
  
  <meta name="twitter:image" content="assets/hero-bg.png">

  
    <link rel="icon" href="assets/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32.png">
  <link rel="icon" type="image/png" sizes="48x48" href="assets/favicon.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png">
  <?= vite('js/site-guard.js') ?>
<?= vite('css/home.css') ?>
  <?= vite('css/profil.css') ?>
  <?= vite('css/account.css') ?>

  <title>Mon Profil Joueur - South District RP</title>
  <meta property="og:title" content="Mon Profil Joueur - South District RP">
  <meta property="og:description" content="Gérez votre compte joueur, vos personnages et vos statistiques sur South District RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Mon Profil Joueur - South District RP">
  <meta name="twitter:description" content="Gérez votre compte joueur, vos personnages et vos statistiques sur South District RP.">
  <meta name="description" content="Gérez votre compte joueur, vos personnages et vos statistiques sur South District RP.">
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
<body class="cp-home cp-hero--profil cp-nav--logo-only">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page">
      <div class="cp-hero__panel">
        <div id="profil-gate" class="sd-profil-gate" hidden>
          <div class="sd-profil-gate__card">
            <img src="assets/logo.png" alt="" class="sd-profil-gate__logo" width="56" height="56" decoding="async">
            <h2>Espace profil</h2>
            <p>Connecte-toi pour modifier ton pseudo, ton avatar, ta signature et consulter ta candidature staff.</p>
            <button type="button" id="profil-open-login" class="sd-profil__btn sd-profil__btn--primary">Se connecter</button>
          </div>
        </div>

        <div id="profil-loading" class="sd-profil-loading" role="status">
          <div class="sd-profil-loading__spinner" aria-hidden="true"></div>
          <p>Chargement de ton profil…</p>
        </div>

        <div id="profil-layout" class="sd-profil" hidden>
          <section class="sd-profil__edit">
            <h2>Modifier mon profil</h2>
            <form id="profil-form" class="sd-profil__form">
              <label for="profil-pseudo">Pseudo affiché
                <input type="text" id="profil-pseudo" name="pseudo" autocomplete="nickname" required>
              </label>
              <label class="sd-profil__field">Avatar
                <span class="sd-file-upload">
                  <input type="file" id="profil-avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
                  <button type="button" class="sd-file-upload__btn" id="profil-avatar-trigger">Choisir une image</button>
                  <span class="sd-file-upload__name" id="profil-avatar-name">Aucune image sélectionnée</span>
                </span>
              </label>

              <div class="sd-profil__signature-block">
                <span class="sd-profil__signature-label">Ma signature</span>
                <div id="profil-signature-box" class="sd-profil__signature-box">
                  <canvas id="profil-signature" aria-label="Zone de signature"></canvas>
                  <span class="sd-profil__signature-hint">Signez ici</span>
                </div>
                <div class="sd-profil__signature-actions">
                  <button type="button" id="profil-signature-clear" class="sd-profil__btn sd-profil__btn--ghost">Effacer</button>
                  <button type="button" id="profil-signature-save" class="sd-profil__btn sd-profil__btn--outline">Enregistrer cette signature comme ma signature</button>
                </div>
              </div>

              <div class="sd-profil__actions">
                <button type="submit" class="sd-profil__btn sd-profil__btn--primary">Enregistrer le profil</button>
              </div>
            </form>
            <p id="profil-msg" class="sd-profil__msg" role="status"></p>

            <div class="sd-profil__sync-box" id="profil-discord-sync-box">
              <div class="sd-profil__sync-info">
                <span class="sd-profil__sync-title">Synchronisation Discord</span>
                <span class="sd-profil__sync-desc" id="profil-discord-sync-status">Grade et rôles synchronisés automatiquement.</span>
              </div>
              <button type="button" id="profil-sync-discord-btn" class="sd-profil__btn sd-profil__btn--discord-sync">
                <svg class="sd-sync-icon" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46A7.93 7.93 0 0 0 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74A7.93 7.93 0 0 0 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
                <span>Actualiser mes rôles Discord</span>
              </button>
            </div>
          </section>

          <section class="sd-profil__preview">
            <h2>Aperçu live</h2>
            <div class="sd-profil-card">
              <div class="sd-profil-card__hero">
                <div class="sd-profil-card__avatar-ring" id="profil-preview-avatar-wrap">
                  <img id="profil-preview-avatar" class="sd-profil-card__avatar" src="" alt="" width="112" height="112" decoding="async" hidden>
                  <span class="sd-profil-card__initials" id="profil-preview-initials" aria-hidden="true">?</span>
                </div>
                <div class="sd-profil-card__identity">
                  <p id="profil-preview-pseudo" class="sd-profil-card__pseudo">Pseudo</p>
                  <p id="profil-preview-role" class="sd-profil-card__role">Citoyen</p>
                  <div id="profil-preview-secondary-roles" class="sd-profil-card__secondary-roles"></div>
                </div>
              </div>
              <div class="sd-profil-card__body">
                <div id="profil-preview-signature-wrap" class="sd-profil-card__signature-wrap" hidden>
                  <span class="sd-profil-card__signature-label">Signature</span>
                  <img id="profil-preview-signature" class="sd-profil-card__signature" src="" alt="Signature">
                </div>
                <div id="profil-preview-candidature" class="sd-profil-card__candidature" hidden></div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </main>

  </div>

  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
  <?= vite('js/profil.js') ?>
  <?= vite('js/profil-sync.js') ?>
</body>
</html>




