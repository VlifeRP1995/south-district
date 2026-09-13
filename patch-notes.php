<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/vite.php';

$targetId = trim((string) ($_GET['patch'] ?? $_GET['id'] ?? $_GET['v'] ?? ''));

$patchNotes = [];

if (file_exists(__DIR__ . '/api/bootstrap.php')) {
    try {
        require_once __DIR__ . '/api/bootstrap.php';
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare('SELECT content FROM page_content WHERE page_key = "patch-notes" LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row && !empty($row['content'])) {
            $decoded = json_decode((string)$row['content'], true);
            if (isset($decoded['patches']) && is_array($decoded['patches'])) {
                $patchNotes = $decoded['patches'];
            } elseif (is_array($decoded)) {
                $patchNotes = $decoded;
            }
        }
    } catch (Throwable) {}
}

if (empty($patchNotes) && file_exists(__DIR__ . '/data/patch-notes.json')) {
    $raw = file_get_contents(__DIR__ . '/data/patch-notes.json');
    $decoded = json_decode((string)$raw, true);
    if (isset($decoded['patches']) && is_array($decoded['patches'])) {
        $patchNotes = $decoded['patches'];
    } elseif (is_array($decoded)) {
        $patchNotes = $decoded;
    }
}

$currentPatch = null;
if (!empty($patchNotes)) {
    if ($targetId !== '') {
        foreach ($patchNotes as $p) {
            $pId = (string)($p['id'] ?? '');
            $pLabel = (string)($p['label'] ?? '');
            $pVersion = (string)($p['version'] ?? '');
            if ($pId === $targetId || $pLabel === $targetId || $pVersion === $targetId || strcasecmp($pLabel, $targetId) === 0 || strcasecmp($pId, $targetId) === 0) {
                $currentPatch = $p;
                break;
            }
        }
    }
    if (!$currentPatch) {
        $currentPatch = $patchNotes[0];
    }
}

$siteName = 'South District RP';
$ogTitle = 'Patch-notes | South District RP';
$pageTitle = 'Patch-notes | South District RP';
$ogDesc = "Découvrez les dernières mises à jour, nouveautés et correctifs de South District RP.";

if ($currentPatch) {
    $label = trim((string)($currentPatch['label'] ?? ''));
    $version = trim((string)($currentPatch['version'] ?? ''));
    
    if ($label !== '' && $version !== '' && strpos($version, $label) === false) {
        $displayTitle = "{$version} ({$label})";
    } elseif ($label !== '') {
        $displayTitle = "Patch Note {$label}";
    } else {
        $displayTitle = $version ?: 'Mise à jour';
    }
    
    $ogTitle = "📋 {$displayTitle} — {$siteName}";
    $pageTitle = "{$displayTitle} | Patch-notes | {$siteName}";

    $descParts = [];

    if (!empty($currentPatch['sections']) && is_array($currentPatch['sections'])) {
        foreach ($currentPatch['sections'] as $sec) {
            $rawTitle = trim((string)($sec['title'] ?? ''));
            $secTitle = strtoupper($rawTitle);
            $items = (array)($sec['items'] ?? []);
            if (!empty($items)) {
                $secIcon = '🟢';
                if (stripos($secTitle, 'CORRECT') !== false || stripos($secTitle, 'FIX') !== false) $secIcon = '🔧';
                elseif (stripos($secTitle, 'REFONTE') !== false || stripos($secTitle, 'REWORK') !== false) $secIcon = '🔄';
                elseif (stripos($secTitle, 'OPTIM') !== false) $secIcon = '⚡';
                elseif (stripos($secTitle, 'RETRAIT') !== false) $secIcon = '🗑️';

                $cleanItems = [];
                foreach (array_slice($items, 0, 3) as $it) {
                    $clean = strip_tags(str_replace(['**', '*'], '', (string)$it));
                    $clean = preg_replace('/\s+/', ' ', trim($clean));
                    if ($clean !== '') {
                        if (mb_strlen($clean) > 75) {
                            $clean = mb_substr($clean, 0, 72) . '…';
                        }
                        $cleanItems[] = $clean;
                    }
                }
                
                if (!empty($cleanItems)) {
                    $descParts[] = "{$secIcon} {$secTitle} :\n• " . implode("\n• ", $cleanItems);
                }
            }
        }
    }

    if (!empty($descParts)) {
        $ogDesc = implode("\n\n", $descParts);
    }
}

$ogDescEscaped = htmlspecialchars($ogDesc, ENT_QUOTES, 'UTF-8');
$ogTitleEscaped = htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8');
$pageTitleEscaped = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'www.south-district.fr';
$ogImageUrl = "{$protocol}://{$host}/assets/hero-bg.png";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1ee6a0">
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png">
  <?= vite('js/site-guard.js') ?>
  <?= vite('css/home.css') ?>
  <?= vite('css/account.css') ?>
  <?= vite('css/pages.css') ?>
  <?= vite('css/patch-notes.css') ?>
  <?= vite('css/admin-mode.css') ?>

  <title>Patch Notes & Mises à jour - South District RP</title>
  <meta property="og:title" content="Patch Notes & Mises à jour - South District RP">
  <meta property="og:description" content="Suivez toutes les nouveautés, correctifs et ajouts de fonctionnalités (Patch Notes) du serveur South District RP.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Patch Notes & Mises à jour - South District RP">
  <meta name="twitter:description" content="Suivez toutes les nouveautés, correctifs et ajouts de fonctionnalités (Patch Notes) du serveur South District RP.">
  <meta name="description" content="Suivez toutes les nouveautés, correctifs et ajouts de fonctionnalités (Patch Notes) du serveur South District RP.">
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
<body class="cp-home" data-admin-page="patch-notes" data-admin-permission-manage="manage_patchnotes" data-admin-permission-publish="publish_patchnotes">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page cp-hero--patch-notes">
      <div class="cp-hero__panel reglement-layout">

        <aside class="reglement-sidebar" aria-label="Historique des patch-notes">
          <div class="reglement-sidebar__head">
            <p class="reglement-sidebar__title">Patch-notes</p>
            <button type="button" class="patch-resume" id="patch-resume">Reprendre la lecture</button>
            <label class="patch-filter-wrap">
              <span class="visually-hidden">Filtrer les patch-notes</span>
              <input type="search" class="patch-filter" id="patch-filter" placeholder="Rechercher par version, date…" autocomplete="off">
            </label>
          </div>
          <nav class="reglement-sidebar__nav" id="patch-sidebar-nav" aria-label="Liste par date"></nav>
        </aside>

        <div class="reglement-body">
          <div class="cp-page__scroll" id="patch-scroll">
            <div class="cp-page__content">
              <div id="patch-content" aria-live="polite"></div>
              <p class="patch-empty" id="patch-empty" hidden>Aucun patch-note ne correspond à votre recherche.</p>

              <div class="reglement-pager-wrap">
                <a href="accueil" class="reglement-pager__brand" aria-label="South District RP">
                  <img src="assets/logo.png" alt="South District">
                </a>
                <div class="reglement-pager__body">
                  <p class="reglement-pager__status" id="patch-pager-status" aria-live="polite">Chargement…</p>
                  <nav class="reglement-pager" aria-label="Navigation entre les patch-notes">
                    <button type="button" class="reglement-pager__btn" id="patch-prev" disabled>Patch précédent</button>
                    <button type="button" class="reglement-pager__btn" id="patch-next" disabled>Patch suivant</button>
                  </nav>
                </div>
              </div>

              <p class="sd-footer-note">© 2026 South District RP — Tous droits réservés.</p>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script type="application/json" id="patch-notes-data">[]</script>
  <?= vite('js/csrf-interceptor.js') ?>
  <?= vite('js/auth-client.js') ?>
  <?= vite('js/account-ui.js') ?>
  <?= vite('js/nav.js') ?>
  <?= vite('js/motion.js') ?>
  <?= vite('js/admin-mode.js') ?>
  <?= vite('js/patch-notes.js') ?>

  <script>
    // Auto-sélection du patch si passé via ?patch= ou ?id=
    (function() {
      try {
        var p = new URLSearchParams(window.location.search);
        var target = p.get('patch') || p.get('id') || p.get('v');
        if (target && !window.location.hash) {
          window.location.hash = '#' + target;
        }
      } catch(e) {}
    })();
  </script>

  <script>
    // Bouton interactif "Partager sur Discord"
    (function() {
      function attachShareButtons() {
        const patchNotes = document.querySelectorAll('.patch-note');
        patchNotes.forEach(patch => {
          if (patch.querySelector('.btn-patch-share')) return;
          
          const meta = patch.querySelector('.patch-note__meta');
          if (!meta) return;
          
          const patchId = patch.id || '';
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn-patch-share';
          btn.title = 'Copier le lien d\'aperçu Discord pour cette mise à jour';
          btn.innerHTML = `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M20.317 4.37a19.79 19.79 0 0 0-4.885-1.515.074.074 0 0 0-.079.037 12.3 12.3 0 0 0-.608 1.25 18.27 18.27 0 0 0-5.487 0 11.64 11.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107 14.322 14.322 0 0 0 1.225 1.993.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg><span>Partager sur Discord</span>`;
          
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const fullUrl = 'https://www.south-district.fr/patch-notes.php?patch=' + encodeURIComponent(patchId);
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
              navigator.clipboard.writeText(fullUrl).then(function() {
                btn.classList.add('is-copied');
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Lien Discord copié !</span>';
                setTimeout(function() {
                  btn.classList.remove('is-copied');
                  btn.innerHTML = `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M20.317 4.37a19.79 19.79 0 0 0-4.885-1.515.074.074 0 0 0-.079.037 12.3 12.3 0 0 0-.608 1.25 18.27 18.27 0 0 0-5.487 0 11.64 11.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107 14.322 14.322 0 0 0 1.225 1.993.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg><span>Partager sur Discord</span>`;
                }, 2500);
              });
            }
          });
          
          meta.appendChild(btn);
        });
      }
      
      setInterval(attachShareButtons, 400);
      document.addEventListener('DOMContentLoaded', attachShareButtons);
    })();
  </script>
</body>
</html>


