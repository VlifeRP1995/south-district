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
  <?= vite('css/postuler.css') ?>

  <title>Candidature Staff - South District RP</title>
  <meta property="og:title" content="Candidature Staff - South District RP">
  <meta property="og:description" content="Deposez votre candidature pour rejoindre l'equipe staff de South District RP.">
  <meta name="twitter:title" content="Candidature Staff - South District RP">
  <meta name="description" content="Deposez votre candidature pour rejoindre l'equipe staff de South District RP.">
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
<body class="cp-home cp-hero--postuler cp-nav--logo-only">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page">
      <div class="cp-hero__panel">
        <div class="sd-dossier">

          <div class="sd-dossier__tabs" role="tablist">
            <button type="button" class="sd-dossier__tab sd-dossier__tab--active" data-pane="pane-candidature" role="tab" aria-selected="true">
              <img src="assets/logo.png" alt="" class="sd-dossier__tab-logo" width="18" height="18" decoding="async">
              <span>Candidature</span>
            </button>
            <button type="button" class="sd-dossier__tab" data-pane="pane-suivi" role="tab" aria-selected="false">
              <img src="assets/logo.png" alt="" class="sd-dossier__tab-logo" width="18" height="18" decoding="async">
              <span>Suivi dossier</span>
            </button>
          </div>

          <div class="sd-dossier__body">

            <!-- Onglet candidature -->
            <div id="pane-candidature" class="sd-dossier__pane sd-dossier__pane--active" role="tabpanel">

              <!-- Gate connexion : affiché si non connecté -->
              <div id="candidature-gate" class="sd-candidature-gate" hidden>
                <div class="sd-candidature-gate__card">
                  <div class="sd-candidature-gate__logo">
                    <img src="assets/logo.png" alt="South District" width="72" height="72" decoding="async">
                  </div>
                  <h2 class="sd-candidature-gate__title">Connexion requise</h2>
                  <p class="sd-candidature-gate__desc">
                    Vous devez être connecté à votre compte South District pour déposer une candidature staff.
                  </p>
                  <button type="button" id="candidature-gate-login" class="sd-dossier__btn sd-dossier__btn--primary">
                    Se connecter
                  </button>
                  <p class="sd-candidature-gate__hint">Pas encore de compte ? Créez-en un depuis la page de connexion.</p>
                </div>
              </div>

              <!-- Contenu dossier (masqué si non connecté) -->
              <div id="candidature-content">

                <header class="sd-dossier__header">
                  <div class="sd-dossier__stamp sd-dossier__stamp--logo">
                    <img src="assets/logo.png" alt="South District" decoding="async">
                  </div>
                  <div class="sd-dossier__title-block">
                    <h1>Dossier de candidature</h1>
                    <p id="dossier-recruitment-msg">Staff RP, candidature sur le site, délai 48 à 72 heures, âge minimum 17 ans.</p>
                  </div>
                  <div class="sd-dossier__ref">
                    Réf. SD-STAFF
                    <span id="dossier-date"></span>
                  </div>
                </header>

                <div id="dossier-closed-banner" class="sd-dossier__closed-banner" hidden></div>

                <div class="sd-dossier__stats">
                  <div class="sd-dossier__stat">
                    <div class="sd-dossier__stat-label">Statut</div>
                    <div id="dossier-stat-status" class="sd-dossier__stat-value is-open">Ouvert</div>
                  </div>
                  <div class="sd-dossier__stat">
                    <div class="sd-dossier__stat-label">age min</div>
                    <div class="sd-dossier__stat-value">17 ans</div>
                  </div>
                  <div class="sd-dossier__stat">
                    <div class="sd-dossier__stat-label">Delai</div>
                    <div class="sd-dossier__stat-value">48-72h</div>
                  </div>
                  <div class="sd-dossier__stat">
                    <div class="sd-dossier__stat-label">Entretien</div>
                    <div class="sd-dossier__stat-value">Sur le site</div>
                  </div>
                </div>

                <form id="candidature-form" class="sd-dossier__sections" novalidate>

                  <section class="sd-dossier__section" data-section="Identité">
                    <div class="sd-dossier__grid-2">
                      <div class="sd-dossier__field">
                        <label for="pseudo-rp">Pseudo *</label>
                        <input type="text" id="pseudo-rp" name="pseudo_rp" placeholder="Ton pseudo RP / Discord" required maxlength="64" autocomplete="nickname">
                      </div>
                      <div class="sd-dossier__field">
                        <label for="pseudo">ID Discord *</label>
                        <input type="text" id="pseudo" name="pseudo" placeholder="ex: 123456789012345678" required autocomplete="off" inputmode="numeric" pattern="[0-9]{17,20}">
                      </div>
                    </div>
                    <div class="sd-dossier__grid-2">
                      <div class="sd-dossier__field">
                        <label for="age">âge *</label>
                        <input type="number" id="age" name="age" min="17" max="99" required>
                      </div>
                      <div class="sd-dossier__field sd-dossier__field--spacer" aria-hidden="true"></div>
                    </div>
                  </section>

                  <section class="sd-dossier__section" data-section="Poste">
                    <div class="sd-dossier__grid-2">
                      <div class="sd-dossier__field">
                        <label for="poste">Poste souhaité *</label>
                        <select id="poste" name="poste" required>
                          <option value="">Sélectionner</option>
                        </select>
                      </div>
                      <div class="sd-dossier__field">
                        <label for="disponibilite">Dispo hebdo (h) *</label>
                        <input type="number" id="disponibilite" name="disponibilite" min="10" max="80" required>
                      </div>
                    </div>
                  </section>

                  <div id="dossier-dynamic-questions" class="sd-dossier__dynamic-questions" aria-live="polite"></div>

                  <section class="sd-dossier__section" data-section="Signature">
                    <p class="sd-dossier__suivi-intro">Lisez le <a href="reglement-staff" class="sd-mark-link">règlement staff</a> avant de signer et envoyer votre dossier.</p>
                    <div class="sd-dossier__signature">
                      <span class="sd-dossier__signature-label">Signature du candidat *</span>
                      <div id="signature-box" class="sd-dossier__signature-box">
                        <canvas id="candidature-signature" aria-label="Zone de signature"></canvas>
                        <span class="sd-dossier__signature-hint">Signez ici</span>
                      </div>
                      <div class="sd-dossier__signature-actions">
                        <button type="button" id="signature-clear" class="sd-dossier__btn sd-dossier__btn--ghost">Effacer</button>
                        <button type="button" id="signature-save-profile" class="sd-dossier__btn sd-dossier__btn--outline">Enregistrer cette signature comme ma signature</button>
                      </div>
                    </div>
                  </section>

                  <section class="sd-dossier__section" data-section="Validation">
                    <label class="sd-dossier__checkbox">
                      <input type="checkbox" required>
                      <span>J'accepte le <a href="reglement-staff" class="sd-mark-link">règlement staff</a> et certifie l'exactitude des informations fournies.</span>
                    </label>
                    <div class="sd-dossier__submit-row">
                      <button type="submit" class="sd-dossier__btn sd-dossier__btn--primary">Envoyer le dossier</button>
                    </div>
                    <div id="candidature-result" class="sd-dossier__result" role="status"></div>
                  </section>

                </form>

                <p class="sd-dossier__footer">© 2026 South District RP. Dossier confidentiel</p>

              </div><!-- /candidature-content -->

            </div>

            <!-- Onglet suivi -->
            <div id="pane-suivi" class="sd-dossier__pane" role="tabpanel" hidden>

              <div id="suivi-gate" class="sd-suivi-gate" hidden>
                <div class="sd-suivi-gate__card">
                  <img src="assets/logo.png" alt="" width="64" height="64" decoding="async">
                  <h2>Suivi de dossier</h2>
                  <p>Connecte-toi pour consulter l'état de ta candidature staff liée à ton compte.</p>
                  <button type="button" id="suivi-open-login" class="sd-dossier__btn sd-dossier__btn--primary">Se connecter</button>
                </div>
              </div>

              <div id="suivi-loading" class="sd-suivi-loading" role="status">
                <div class="sd-suivi-loading__spinner" aria-hidden="true"></div>
                <p>Chargement de ton dossier…</p>
              </div>

              <div id="suivi-content" class="sd-suivi-content" hidden>
                <header class="sd-dossier__header">
                  <div class="sd-dossier__stamp sd-dossier__stamp--logo">
                    <img src="assets/logo.png" alt="South District" decoding="async">
                  </div>
                  <div class="sd-dossier__title-block">
                    <h1>Mon dossier staff</h1>
                    <p>Suivi en temps réel, lié à ton compte South District</p>
                  </div>
                </header>

                <article id="suivi-card" class="sd-suivi-card">
                  <div class="sd-suivi-card__head">
                    <img src="assets/logo.png" alt="" class="sd-suivi-card__logo" decoding="async">
                    <div>
                      <span class="sd-suivi-card__kicker">Candidature staff</span>
                      <h2 id="suivi-status-label">En attente</h2>
                    </div>
                    <span id="suivi-status-badge" class="sd-suivi-card__badge is-pending">En attente</span>
                  </div>
                  <dl class="sd-suivi-card__meta">
                    <div><dt>Poste visé</dt><dd id="suivi-poste">-</dd></div>
                    <div><dt>Pseudo</dt><dd id="suivi-pseudo-rp">-</dd></div>
                    <div><dt>ID Discord</dt><dd id="suivi-pseudo">-</dd></div>
                    <div><dt>Dépôt</dt><dd id="suivi-date">-</dd></div>
                  </dl>
                  <p id="suivi-note" class="sd-suivi-card__note"></p>
                </article>

                <div id="suivi-empty" class="sd-suivi-empty" hidden>
                  <img src="assets/logo.png" alt="" width="72" height="72" decoding="async">
                  <h2>Aucune candidature active</h2>
                  <p>Tu n'as pas encore déposé de dossier staff. Remplis l'onglet Candidature pour postuler.</p>
                  <button type="button" class="sd-dossier__btn sd-dossier__btn--primary" data-pane-jump="pane-candidature">Déposer ma candidature</button>
                </div>
              </div>

              <p class="sd-dossier__footer">© 2026 South District RP</p>
            </div>

          </div>
        </div>
      </div>
    </main>

  </div>

  <script>
    document.getElementById('dossier-date').textContent = new Date().toLocaleDateString('fr-FR');
  </script>
  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
  <?= vite('js/postuler.js') ?>

  <!-- Intercepte la redirection post-login pour rester sur /postuler -->
  <script>
  (function () {
    // On surcharge location.href uniquement pour les redirections vers /accueil
    // déclenchées par account-ui.js après un login/register réussi sur cette page
    var _realHref = Object.getOwnPropertyDescriptor(window.location.__proto__ || Location.prototype, 'href');
    if (!_realHref) return;
    try {
      Object.defineProperty(window.location, 'href', {
        set: function (url) {
          // Si la redirection vise /accueil ET qu'on est sur /postuler, on ne redirige pas
          if (
            (url === '/accueil' || url === '/accueil/' || url.endsWith('/accueil')) &&
            window.location.pathname.indexOf('postuler') !== -1
          ) {
            // Laisser le gate JS détecter la connexion via sdOnAuthChange
            return;
          }
          // Pour toutes les autres redirections, comportement normal
          _realHref.set.call(window.location, url);
        },
        get: function () {
          return _realHref.get.call(window.location);
        },
        configurable: true
      });
    } catch (e) {}
  })();
  </script>
  <script>
  (function () {
    var gate    = document.getElementById('candidature-gate');
    var content = document.getElementById('candidature-content');
    var canvas  = document.getElementById('candidature-signature');
    var box     = document.getElementById('signature-box');
    var clearBtn = document.getElementById('signature-clear');
    var saveBtn  = document.getElementById('signature-save-profile');

    var isDrawing = false;
    var hasDrawn = false;
    var lastX = 0;
    var lastY = 0;

    function getCoords(e) {
      if (!canvas) return { x: 0, y: 0 };
      var rect = canvas.getBoundingClientRect();
      var clientX = e.clientX;
      var clientY = e.clientY;
      if (e.touches && e.touches.length > 0) {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
      }
      return {
        x: clientX - rect.left,
        y: clientY - rect.top
      };
    }

    function syncCanvasSize(preserveContent) {
      if (!canvas) return;
      var rect = canvas.getBoundingClientRect();
      var dpr  = window.devicePixelRatio || 1;
      var w    = Math.max(Math.floor(rect.width), 1);
      var h    = Math.max(Math.floor(rect.height), 1);

      if (w <= 1 || h <= 1) return;

      var targetW = Math.floor(w * dpr);
      var targetH = Math.floor(h * dpr);

      if (canvas.width === targetW && canvas.height === targetH) {
        return;
      }

      var tempImg = null;
      if (preserveContent && hasDrawn && canvas.width > 2 && canvas.height > 2) {
        try {
          tempImg = canvas.toDataURL();
        } catch (err) {}
      }

      canvas.width  = targetW;
      canvas.height = targetH;

      var ctx = canvas.getContext('2d');
      if (ctx) {
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
        ctx.strokeStyle = '#e8e8e8';
        ctx.lineWidth   = 2.2;

        if (tempImg) {
          var img = new Image();
          img.onload = function () {
            ctx.drawImage(img, 0, 0, w, h);
          };
          img.src = tempImg;
        }
      }

      window.dispatchEvent(new Event('resize'));
    }

    if (canvas) {
      function startDraw(e) {
        syncCanvasSize(true);
        isDrawing = true;
        hasDrawn  = true;
        if (box) box.classList.add('has-stroke');

        if (canvas.setPointerCapture && e.pointerId != null) {
          try { canvas.setPointerCapture(e.pointerId); } catch (err) {}
        }

        var pt = getCoords(e);
        lastX = pt.x;
        lastY = pt.y;

        var ctx = canvas.getContext('2d');
        if (ctx) {
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(lastX, lastY + 0.1);
          ctx.stroke();
        }
        if (e.cancelable && e.type.indexOf('touch') === 0) {
          e.preventDefault();
        }
      }

      function moveDraw(e) {
        if (!isDrawing) return;
        var pt = getCoords(e);
        var ctx = canvas.getContext('2d');
        if (ctx) {
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(pt.x, pt.y);
          ctx.stroke();
        }
        lastX = pt.x;
        lastY = pt.y;
        if (e.cancelable) {
          e.preventDefault();
        }
      }

      function endDraw(e) {
        if (!isDrawing) return;
        isDrawing = false;
        if (canvas.releasePointerCapture && e.pointerId != null) {
          try { canvas.releasePointerCapture(e.pointerId); } catch (err) {}
        }
      }

      if (window.PointerEvent) {
        canvas.addEventListener('pointerdown', startDraw);
        canvas.addEventListener('pointermove', moveDraw);
        canvas.addEventListener('pointerup', endDraw);
        canvas.addEventListener('pointercancel', endDraw);
        canvas.addEventListener('pointerleave', endDraw);
      } else {
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', moveDraw);
        canvas.addEventListener('mouseup', endDraw);
        canvas.addEventListener('mouseleave', endDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', moveDraw, { passive: false });
        canvas.addEventListener('touchend', endDraw);
      }

      canvas.addEventListener('mouseenter', function () { syncCanvasSize(true); }, { passive: true });
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        if (ctx) {
          var rect = canvas.getBoundingClientRect();
          ctx.clearRect(0, 0, rect.width, rect.height);
        }
        hasDrawn = false;
        if (box) box.classList.remove('has-stroke');
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', async function () {
        if (!canvas || !hasDrawn) {
          alert('Veuillez signer avant d\'enregistrer.');
          return;
        }
        var auth = window.SDAuth;
        if (!auth || !auth.user) {
          if (window.SDAccountUI) window.SDAccountUI.openModal('login');
          return;
        }
        try {
          var dataUrl = canvas.toDataURL('image/png');
          await auth.sdUpdateProfile({ signature: dataUrl });
          var origText = saveBtn.textContent;
          saveBtn.textContent = '✓ Signature enregistrée sur ton profil !';
          setTimeout(function () { saveBtn.textContent = origText; }, 2500);
        } catch (err) {
          alert(err.message || 'Erreur lors de la sauvegarde de la signature.');
        }
      });
    }

    function loadSavedSignature(sigUrl) {
      if (!sigUrl || !canvas) return;
      var img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = function () {
        syncCanvasSize(false);
        var rect = canvas.getBoundingClientRect();
        var ctx  = canvas.getContext('2d');
        if (ctx && rect.width > 10) {
          ctx.clearRect(0, 0, rect.width, rect.height);
          ctx.drawImage(img, 0, 0, rect.width, rect.height);
          hasDrawn = true;
          if (box) box.classList.add('has-stroke');
        }
      };
      img.src = sigUrl;
    }

    function showGate() {
      if (!gate || !content) return;
      gate.classList.add('is-visible');
      content.classList.add('is-hidden');
    }

    function showContent(user) {
      if (!gate || !content) return;
      gate.classList.remove('is-visible');
      content.classList.remove('is-hidden');

      requestAnimationFrame(function () {
        syncCanvasSize(true);
        if (user && user.signature) {
          loadSavedSignature(user.signature);
        }
      });
      setTimeout(function () { syncCanvasSize(true); }, 100);
      setTimeout(function () { syncCanvasSize(true); }, 350);
    }

    var btn = document.getElementById('candidature-gate-login');
    if (btn) {
      btn.addEventListener('click', function () {
        if (window.SDAccountUI && typeof window.SDAccountUI.openModal === 'function') {
          window.SDAccountUI.openModal('login');
        }
      });
    }

    if (box && window.ResizeObserver) {
      var ro = new ResizeObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
          if (entries[i].contentRect && entries[i].contentRect.width > 20) {
            syncCanvasSize(true);
          }
        }
      });
      ro.observe(box);
    }

    document.addEventListener('click', function (e) {
      var tab = e.target && e.target.closest && e.target.closest('.sd-dossier__tab');
      if (tab) {
        setTimeout(function () { syncCanvasSize(true); }, 80);
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      showGate();

      var auth = window.SDAuth;
      if (!auth) { return; }

      if (typeof auth.sdOnAuthChange === 'function') {
        auth.sdOnAuthChange(function (user) {
          if (user) { showContent(user); } else { showGate(); }
        });
      }

      if (typeof auth.sdFetchMe === 'function') {
        auth.sdFetchMe()
          .then(function (result) {
            if (result && result.user) {
              showContent(result.user);
            } else {
              showGate();
            }
          })
          .catch(function () { showGate(); });
      } else {
        setTimeout(function () {
          if (auth.user) { showContent(auth.user); } else { showGate(); }
        }, 800);
      }
    });
  })();
  </script>
</body>
</html>
