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
  <?= vite('css/partenaires.css') ?>
  <style>
    @media (min-width: 993px) {
      html, body.cp-hero--partenaires {
        min-height: 100vh !important;
        height: auto !important;
      }
      .cp-hero--partenaires .cp-shell {
        min-height: calc(100vh - var(--sd-pad, 0.75rem) * 2) !important;
        display: flex !important;
        flex-direction: column !important;
      }
      .cp-hero--partenaires .cp-hero {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
      }
      .cp-hero--partenaires .cp-hero__panel {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        margin: clamp(.35rem, .75vh, .55rem) .4rem .4rem !important;
        border-radius: var(--radius-md) !important;
        overflow: hidden !important;
      }
      .cp-hero--partenaires .cp-page__scroll {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin !important;
        scrollbar-color: rgba(30, 230, 160, .35) transparent !important;
        display: flex !important;
        flex-direction: column !important;
      }
      .cp-hero--partenaires .cp-page__content--partenaires {
        flex: 1 1 auto !important;
        min-height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        padding: clamp(.3rem, .55vh, .45rem) clamp(.85rem, 1.6vw, 1.5rem) clamp(.3rem, .5vh, .4rem) !important;
        gap: clamp(.3rem, .55vh, .45rem) !important;
        box-sizing: border-box !important;
      }

      /* Banniere intro compacte */
      .sd-partenaires-intro {
        flex: 0 0 auto !important;
        margin-bottom: 0 !important;
        padding: clamp(.3rem, .55vh, .45rem) clamp(.85rem, 1.4vw, 1.25rem) !important;
        display: grid !important;
        grid-template-columns: auto 1fr !important;
        align-items: center !important;
        gap: clamp(.65rem, 1.1vw, 1.1rem) !important;
      }
      .sd-partenaires-intro__brand {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 clamp(.75rem, 1.2vw, 1.15rem) 0 0 !important;
        border-right: 1px dashed rgba(255, 255, 255, .14) !important;
        border-bottom: none !important;
        flex-shrink: 0 !important;
      }
      .sd-partenaires-intro__brand img {
        height: clamp(1.85rem, 3.2vh, 2.4rem) !important;
        width: auto !important;
        object-fit: contain !important;
        opacity: .95 !important;
      }
      .sd-partenaires-intro__body {
        min-width: 0 !important;
      }
      .sd-partenaires-intro__body .section-tag {
        display: inline-block !important;
        margin-bottom: .12rem !important;
        font-size: .64rem !important;
        letter-spacing: .12em !important;
        color: var(--sd-neon, #1ee6a0) !important;
        text-transform: uppercase !important;
      }
      .sd-partenaires-intro__body h1 {
        font-size: clamp(1.1rem, 1.8vh, 1.4rem) !important;
        margin-bottom: .12rem !important;
        line-height: 1.05 !important;
        letter-spacing: .04em !important;
      }
      .sd-partenaires-intro__desc {
        font-size: clamp(.7rem, 1vh, .78rem) !important;
        line-height: 1.3 !important;
        margin: 0 !important;
        color: var(--sd-grey, #9999a3) !important;
        max-width: 52rem !important;
      }

      /* Panneau carrousel */
      .sd-partenaires-panel {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        border-radius: var(--radius-md);
        margin-bottom: 0 !important;
        background: rgba(10, 13, 16, .6) !important;
      }
      .sd-partenaires-panel__head {
        flex: 0 0 auto !important;
        padding: clamp(.25rem, .45vh, .4rem) clamp(.85rem, 1.4vw, 1.25rem) !important;
        gap: .65rem !important;
        background: rgba(10, 13, 16, .75) !important;
      }
      .sd-partenaires-count {
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: .45rem !important;
        padding: .35rem .75rem !important;
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, .04) !important;
        border: 1px solid rgba(255, 255, 255, .09) !important;
        font-family: Inter, system-ui, sans-serif !important;
        font-size: .76rem !important;
        font-weight: 700 !important;
        color: var(--sd-grey, #9ca3af) !important;
        box-shadow: none !important;
        letter-spacing: .06em !important;
        text-transform: uppercase !important;
      }
      .sd-partenaires-count:before {
        display: none !important;
      }
      .sd-partenaires-filter {
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, .04) !important;
        border: 1px solid rgba(255, 255, 255, .08) !important;
      }
      .sd-partenaires-filter.active {
        background: rgba(30, 230, 160, .14) !important;
        border-color: rgba(30, 230, 160, .5) !important;
        box-shadow: none !important;
        color: var(--sd-neon, #1ee6a0) !important;
      }
      .sd-partenaires-panel__body {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        padding: clamp(.15rem, .35vh, .3rem) clamp(.75rem, 1.2vw, 1.25rem) !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        overflow: visible !important;
      }

      /* Stage et track 3D agrandis */
      .sd-pro-3d-stage {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        height: auto !important;
        max-height: 100% !important;
        width: 100% !important;
        padding: clamp(.2rem, .5vh, .4rem) 0 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative !important;
        overflow: visible !important;
      }
      .sd-pro-3d-track {
        position: relative !important;
        width: 100% !important;
        height: clamp(420px, 56vh, 500px) !important;
        min-height: 380px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        perspective: 1600px !important;
      }

      /* Cartes 3D nettement plus grandes */
      .sd-pro-card {
        position: absolute !important;
        width: clamp(480px, 32vw, 520px) !important;
        height: clamp(400px, 50vh, 470px) !important;
        border-radius: var(--radius-md);
      }
      .sd-pro-card__banner {
        height: clamp(95px, 13vh, 140px) !important;
      }
      .sd-pro-card__header {
        margin-top: -32px !important;
        padding: 0 1.4rem !important;
        gap: 14px !important;
      }
      .sd-pro-card__logo {
        width: clamp(56px, 6.8vh, 68px) !important;
        height: clamp(56px, 6.8vh, 68px) !important;
        border-radius: var(--radius-md);
      }
      .sd-pro-card__title {
        font-size: clamp(1.15rem, 1.75vh, 1.35rem) !important;
        font-weight: 800 !important;
      }
      .sd-pro-card__body {
        padding: clamp(.45rem, .8vh, .7rem) 1.4rem clamp(.55rem, 1vh, .9rem) !important;
        flex: 1 1 auto !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
      }
      .sd-pro-card__badge-row {
        margin-bottom: .4rem !important;
      }
      .sd-pro-card__badge {
        font-size: .7rem !important;
        padding: 4px 9px !important;
        border-radius: var(--radius-md);
      }
      .sd-pro-card__desc {
        margin: .25rem 0 .5rem !important;
        font-size: clamp(.83rem, 1.15vh, .89rem) !important;
        line-height: 1.5 !important;
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        max-height: clamp(150px, 22vh, 190px) !important;
      }
      .sd-pro-card__actions {
        margin-top: auto !important;
        padding-top: .4rem !important;
        gap: .6rem !important;
      }
      .sd-pro-btn {
        padding: clamp(7px, 1vh, 10px) 16px !important;
        font-size: .76rem !important;
        font-weight: 700 !important;
        border-radius: var(--radius-md);
      }

      /* Transitions carrousel 3D */
      .sd-pro-card.is-active {
        transform: translate(0) scale(1.02) !important;
        z-index: 10 !important;
        opacity: 1 !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, .75), 0 0 0 1px rgba(30, 230, 160, .15) !important;
      }
      .sd-pro-card.is-prev {
        transform: translateX(clamp(-480px, -32vw, -400px)) translateZ(-190px) rotateY(38deg) scale(.87) !important;
        z-index: 5 !important;
        opacity: .5 !important;
        filter: blur(1px) !important;
      }
      .sd-pro-card.is-next {
        transform: translateX(clamp(400px, 32vw, 480px)) translateZ(-190px) rotateY(-38deg) scale(.87) !important;
        z-index: 5 !important;
        opacity: .5 !important;
        filter: blur(1px) !important;
      }
      .sd-pro-card.is-far-left {
        transform: translateX(clamp(-820px, -55vw, -680px)) translateZ(-380px) rotateY(46deg) scale(.7) !important;
      }
      .sd-pro-card.is-far-right {
        transform: translateX(clamp(680px, 55vw, 820px)) translateZ(-380px) rotateY(-46deg) scale(.7) !important;
      }

      /* Barre de progression */
      .sd-pro-progress-wrap {
        width: 100% !important;
        height: 3px !important;
        margin-top: clamp(.15rem, .3vh, .25rem) !important;
        flex-shrink: 0 !important;
      }

      /* Pied de page visible (Bouton vert + mention copyright) */
      .sd-page-footer {
        flex: 0 0 auto !important;
        margin-top: clamp(.25rem, .5vh, .4rem) !important;
        padding-top: clamp(.25rem, .45vh, .35rem) !important;
        border-top: 1px solid rgba(255, 255, 255, .08) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: clamp(.25rem, .4vh, .35rem) !important;
      }
      .sd-page-footer .btn-row {
        display: flex !important;
        justify-content: center !important;
        margin: 0 !important;
      }
      .sd-page-footer .btn.btn-primary {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: clamp(.42rem, .8vh, .58rem) clamp(1.1rem, 1.6vw, 1.6rem) !important;
        font-size: clamp(.74rem, 1.15vh, .82rem) !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: .06em !important;
        border-radius: var(--radius-md);
        background: var(--sd-neon, #1ee6a0) !important;
        border: 1px solid rgba(30, 230, 160, .45) !important;
        color: #071612 !important;
        text-decoration: none !important;
        cursor: pointer !important;
        box-shadow: 0 4px 14px rgba(30, 230, 160, .35) !important;
        transition: all .2s ease !important;
      }
      .sd-page-footer .btn.btn-primary:hover {
        background: var(--sd-neon-hover, #64f0b4) !important;
        border-color: #1ee6a0 !important;
        box-shadow: 0 4px 16px rgba(30, 230, 160, .4) !important;
        transform: translateY(-1px) !important;
      }
      .sd-footer-note {
        font-size: clamp(.68rem, 1vh, .75rem) !important;
        color: #ffffff6b !important;
        margin: 0 !important;
        padding: 0 !important;
        text-align: center !important;
        line-height: 1.2 !important;
      }
    }

    @media (min-width: 993px) and (max-width: 1250px) {
      .sd-pro-card.is-prev { transform: translateX(-330px) translateZ(-160px) rotateY(38deg) scale(.85) !important; }
      .sd-pro-card.is-next { transform: translateX(330px) translateZ(-160px) rotateY(-38deg) scale(.85) !important; }
      .sd-pro-card.is-far-left { transform: translateX(-560px) translateZ(-300px) rotateY(46deg) scale(.68) !important; }
      .sd-pro-card.is-far-right { transform: translateX(560px) translateZ(-300px) rotateY(-46deg) scale(.68) !important; }
    }
  </style>

  <title>Partenaires Officiels - South District RP</title>
  <meta property="og:title" content="Partenaires Officiels - South District RP">
  <meta property="og:description" content="Voici les partenaires, streamers et créateurs de contenu qui font vivre la communauté de South District RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Partenaires Officiels - South District RP">
  <meta name="twitter:description" content="Voici les partenaires, streamers et créateurs de contenu qui font vivre la communauté de South District RP.">
  <meta name="description" content="Voici les partenaires, streamers et créateurs de contenu qui font vivre la communauté de South District RP.">
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
<body class="cp-home cp-hero--partenaires">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page">
      <div class="cp-hero__panel">
        <div class="cp-page__scroll">
          <div class="cp-page__content cp-page__content--partenaires">

            <!-- Hero Intro -->
            <div class="sd-partenaires-intro cp-frame">
              <div class="sd-partenaires-intro__brand">
                <img src="assets/logo.png" alt="South District" decoding="async">
              </div>
              <div class="sd-partenaires-intro__body">
                <span class="section-tag">Communauté & Alliances</span>
                <h1>Nos Partenaires</h1>
                <p class="sd-partenaires-intro__desc">
                  Découvrez les communautés, serveurs, créateurs de contenu et partenaires officiels qui soutiennent le projet South District RP.
                </p>
              </div>
            </div>

            <!-- Main Panel -->
            <div class="sd-partenaires-panel">
              <header class="sd-partenaires-panel__head">
                <div class="sd-partenaires-filters" id="sd-partenaires-filters">
                  <button type="button" class="sd-partenaires-filter active" data-category="all">Tous les partenaires</button>
                </div>
                <p id="sd-partenaires-count" class="sd-partenaires-count">Chargement…</p>
              </header>

              <div class="sd-partenaires-panel__body">
                <div class="sd-pro-3d-stage" id="proStage">
                  <div class="sd-pro-3d-track" id="sd-partenaires-grid" aria-live="polite"></div>
                </div>
                <div class="sd-pro-progress-wrap">
                  <div class="sd-pro-progress-line" id="proProgress"></div>
                </div>
              </div>
            </div>

            <!-- Pied de page Partenaires -->
            <footer class="sd-page-footer">
              <div class="btn-row">
                <a href="https://discord.gg/south-district" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Devenir Partenaire</a>
              </div>
              <p class="sd-footer-note">© 2026 South District RP — Tous droits réservés.</p>
            </footer>

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
  <?= vite('js/partenaires.js') ?>
</body>
</html>
