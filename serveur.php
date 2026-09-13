<?php
/**
 * South District - Page Serveur FiveM
 * Clone parfait FiveM - Architecture Senior
 */
require_once __DIR__ . '/includes/vite.php';

$ogTitle = 'South District RP';
$ogDesc  = '[FR] South District - Free-Access RP Serieux';
$ogImage = 'https://zupimages.net/up/26/23/l6nx.png';

$cacheFile = __DIR__ . '/api/server/cache_fivem.json';
if (is_file($cacheFile)) {
    $raw = file_get_contents($cacheFile);
    if ($raw !== false) {
        $cached = json_decode($raw, true);
        if (isset($cached['Data'])) {
            $clients = (int)($cached['Data']['clients'] ?? 0);
            $maxCli  = (int)($cached['Data']['sv_maxclients'] ?? 64);
            $ogTitle = "South District RP | {$clients}/{$maxCli} Joueurs";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($ogTitle) ?></title>
  <meta name="theme-color" content="#1ec776">
  <?= vite('css/home.css') ?>
  <style>
    html, body.cp-home { height: 100vh !important; overflow: hidden !important; }
    .cp-shell { height: 100vh !important; overflow: hidden !important; }

    html, body, body * {
      user-select: none !important;
      -webkit-user-select: none !important;
      -moz-user-select: none !important;
      -ms-user-select: none !important;
    }
    
    #fm-outer * { box-sizing: border-box; }
    
    #fm-outer {
      width: 100%;
      max-width: 1600px; width: 96%;
      margin: 10px auto 0 auto;
      padding: 0 16px;
      position: relative;
      z-index: 10;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #fm-container {
      background: #11131c;
      border: 1px solid #161a25;
      border-radius: var(--radius-md);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 30px 60px rgba(0,0,0,0.6);
    }

    #fm-banner-img {
      width: 100%;
      height: auto;
      display: block;
      background: #1a1a1c;
      min-height: 100px; max-height: 180px; object-fit: cover; object-position: center;
    }

    #fm-grid {
      display: grid;
      grid-template-columns: 1fr 380px;
      padding: 24px;
      gap: 16px;
    }

    @media (max-width: 900px) {
      #fm-grid { grid-template-columns: 1fr; padding: 20px; gap: 32px; }
    }

    #fm-main { min-width: 0; }
    
    #fm-header { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 12px; }
    #fm-icon { 
      width: 84px; height: 84px; border-radius: var(--radius-sm); 
      object-fit: cover; flex-shrink: 0; background: #1a1a1c; 
    }
    #fm-title-block { min-width: 0; padding-top: 2px; }
    #fm-title { 
      font-size: 22px; font-weight: 800; color: #fff; 
      margin: 0 0 8px; line-height: 1.35; word-break: break-word; 
    }
    #fm-subtitle { font-size: 14px; color: #a1a1aa; margin: 0; line-height: 1.5; }

    #fm-meta {
      display: flex; align-items: center; flex-wrap: wrap;
      gap: 10px; font-size: 13px; color: #a1a1aa; margin-bottom: 16px;
    }
    #fm-meta .dot { color: #555; font-size: 12px; }
    #fm-players-val { color: #e4e4e7; }
    #fm-flag { width: 16px; height: 12px; border-radius: var(--radius-sm); object-fit: cover; }

    #fm-dlc { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
    .fm-dlc-pill {
      background: #1c202e;
      border: 1px solid #161a25;
      border-radius: var(--radius-sm); padding: 4px 10px;
      font-size: 11px; color: #a1a1aa;
      display: flex; align-items: center;
    }

    #fm-actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .fm-btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 24px; border-radius: var(--radius-full); font-size: 14px;
      font-weight: 600; cursor: pointer; border: none;
      text-decoration: none; transition: all .2s; line-height: 1;
    }
    .fm-btn:hover { opacity: .9; transform: scale(0.98); }
    .fm-btn-connect { background: #1ec776; color: #000; }
    .fm-btn-copy { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.25); }
    .fm-btn-copy.copied { border-color: #1ec776; color: #1ec776; }

    #fm-sidebar { min-width: 0; display: flex; flex-direction: column; gap: 16px; }
    
    .fm-sec-title {
      font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
      text-transform: uppercase; color: #f4f4f5; margin-bottom: 16px;
      display: flex; align-items: center;
    }
    .fm-sec-title svg { margin-right: 8px; color: #a1a1aa; }
    .fm-line { flex: 1; height: 1px; background: #161a25; margin-left: 12px; }

    .fm-row { display: flex; gap: 8px; font-size: 13px; margin-bottom: 8px; line-height: 1.5; align-items: baseline; }
    .fm-row-k { color: #a1a1aa; white-space: nowrap; flex-shrink: 0; }
    .fm-row-k::after { content: ":"; }
    .fm-row-v { color: #e4e4e7; word-break: break-word; min-width: 0; flex: 1; }
    .fm-row-v a { color: #1ec776; text-decoration: none; }
    .fm-row-v a:hover { text-decoration: underline; }

    #fm-tags-wrap { display: flex; flex-wrap: wrap; gap: 6px; }
    .fm-tag {
      background: #1c202e;
      border: 1px solid #161a25;
      border-radius: var(--radius-sm); padding: 4px 10px;
      font-size: 12px; color: #a1a1aa;
    }

    .fm-res-cnt { color: #e4e4e7; font-weight: 600; font-size: 12px; margin-left: auto; letter-spacing: 0; }
    #fm-res-wrap { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .fm-res-badge {
      background: #1c202e;
      border: 1px solid #161a25;
      border-radius: var(--radius-sm); padding: 4px 8px;
      font-size: 11px; color: #a1a1aa;
      font-family: ui-monospace, "Cascadia Mono", monospace;
    }
    #fm-showall {
      background: transparent; color: #fff;
      border: 1px solid rgba(255,255,255,0.25);
      border-radius: var(--radius-full); padding: 4px 12px;
      font-size: 11px; font-weight: 600; cursor: pointer; transition: 0.2s;
    }
    #fm-showall:hover { border-color: #fff; }

    #fm-drawer-backdrop {
      position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
      background: rgba(9, 10, 9, 0.85); z-index: 9999;
      opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
      backdrop-filter: blur(2px);
    }
    #fm-drawer-backdrop.active { opacity: 1; pointer-events: auto; }
    
    #fm-drawer {
      position: fixed; top: 0; right: -420px; width: 400px; max-width: 100vw; height: 100vh;
      background: #111113; z-index: 10000; box-shadow: -10px 0 40px rgba(0,0,0,0.8);
      transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1); 
      display: flex; flex-direction: column;
      border-left: 1px solid #1c202e;
    }
    #fm-drawer.open { right: 0; }
    
    .fm-drawer-header {
      padding: 24px; border-bottom: 1px solid #1c202e;
      display: flex; align-items: center; justify-content: space-between;
    }
    .fm-drawer-title-flex { display: flex; align-items: center; gap: 10px; }
    .fm-drawer-title { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: 0.1em; margin: 0; }
    .fm-drawer-count { font-size: 13px; font-weight: 600; color: #a1a1aa; }
    
    #fm-drawer-close {
      background: transparent; border: none; color: #a1a1aa;
      cursor: pointer; padding: 4px; border-radius: var(--radius-sm); transition: 0.2s;
    }
    #fm-drawer-close:hover { color: #fff; background: rgba(255,255,255,0.1); }

    .fm-drawer-body { padding: 24px; flex: 1; overflow-y: auto; }
    
    #fm-res-filter {
      width: 100%; background: #1a1a1c; border: 1px solid rgba(255,255,255,0.1);
      border-radius: var(--radius-md); padding: 10px 14px; color: #fff; font-size: 13px;
      margin-bottom: 20px; outline: none; transition: 0.2s;
    }
    #fm-res-filter:focus { border-color: #1ec776; }

    #fm-drawer-res-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .fm-drawer-res-item {
      background: #1c202e;
      border: 1px solid #161a25;
      border-radius: var(--radius-sm); padding: 5px 10px;
      font-size: 12px; color: #a1a1aa;
      font-family: ui-monospace, "Cascadia Mono", monospace;
    }
  </style>
</head>
<body class="cp-home">

  <div class="cp-shell">
    <?php require_once 'includes/header.php'; ?>

    <div id="fm-outer">
      <div id="fm-container">
        <img id="fm-banner-img" src="https://zupimages.net/up/26/23/l6nx.png" alt="Banniere par defaut">
        
        <div id="fm-grid">
          
          <main id="fm-main">
            <div id="fm-header">
              <img id="fm-icon" src="/assets/server-logo.jpg" alt="Logo">
              <div id="fm-title-block">
                <h1 id="fm-title">Chargement...</h1>
                <p id="fm-subtitle"></p>
              </div>
            </div>
            
            <div id="fm-meta">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a1a1aa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span class="dot">•</span>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <span id="fm-players-val">0/0</span>
              <span class="dot">•</span>
              <span><span id="fm-peak-val">0</span> peak (24h)</span>
              <span class="dot">•</span>
              <img src="https://flagcdn.com/w20/fr.png" id="fm-flag" alt="France">
            </div>

            <div id="fm-dlc">
              <span class="fm-dlc-pill" id="fm-dlc-build" style="display:none"></span>
              <span class="fm-dlc-pill" id="fm-dlc-map" style="display:none"></span>
              <span class="fm-dlc-pill" id="fm-dlc-game" style="display:none"></span>
            </div>

            <div id="fm-actions">
              <a id="fm-connect-btn" href="#" class="fm-btn fm-btn-connect">Connect</a>
              <button id="fm-copy-btn" class="fm-btn fm-btn-copy">Copy Connect <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
            </div>
          </main>

          <aside id="fm-sidebar">
            <div class="fm-sec">
              <div class="fm-sec-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> 
                DETAILS 
                <div class="fm-line"></div>
              </div>
              <div id="fm-details-list">Chargement...</div>
            </div>

            <div class="fm-sec">
              <div class="fm-sec-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> 
                TAGS 
                <div class="fm-line"></div>
              </div>
              <div id="fm-tags-wrap"></div>
            </div>

            <div class="fm-sec">
              <div class="fm-sec-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 
                RESOURCES 
                <div class="fm-line"></div> 
                <span class="fm-res-cnt" id="fm-res-count">0</span>
              </div>
              <div id="fm-res-wrap">
                <button id="fm-showall">Show all</button>
              </div>
            </div>

            
          </aside>

        </div>
      </div>
    </div>
  </div>

  <div id="fm-drawer-backdrop"></div>
  <div id="fm-drawer">
    <div class="fm-drawer-header">
      <div class="fm-drawer-title-flex">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
        <h2 class="fm-drawer-title">RESOURCES</h2>
        <span class="fm-drawer-count" id="fm-drawer-count">0</span>
      </div>
      <button id="fm-drawer-close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>
    <div class="fm-drawer-body">
      <input type="text" id="fm-res-filter" placeholder="Filter" autocomplete="off">
      <div id="fm-drawer-res-list"></div>
    </div>
  </div>

  <?= vite('js/nav.js') ?>

  <script>
  (function () {
    var API = '/api/server/fivem-detail.php';
    var SKIP_EX = ['tags','activitypubFeed','gamename','locale','premium','peak_players','players_count','upvotePower','iconVersion'];
    var SKIP_PRE = ['sv_','banner_','onesync_','txAdmin','tx_','fivem_'];

    function clean(s) { return s ? String(s).replace(/\^[0-9a-zA-Z]/g, '').trim() : ''; }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
    function q(id) { return document.getElementById(id); }
    function safe(fn) { try { fn(); } catch(e) { console.warn('[SD Render Error]', e); } }

    fetch(API).then(function(r){return r.json();}).then(function(j){
        if(j && j.Data) render(j.Data);
    }).catch(function(e){
        console.error('Erreur API:', e);
    });

    function render(s) {
      var v = s.vars || {}, ep = (s.connectEndPoints && s.connectEndPoints[0]) || 'leepglo';
      
      safe(function(){
        if(v.banner_detail) {
            var ban = q('fm-banner-img');
            if(ban) ban.src = v.banner_detail;
        }
      });
      
      safe(function(){
        q('fm-icon').src = '/assets/server-logo.jpg';
        q('fm-title').textContent = clean(s.hostname) || 'South District RP';
        q('fm-subtitle').textContent = clean(v.sv_projectDesc || 'Serveur GTA V Roleplay');
        q('fm-players-val').textContent = (s.clients||0)+'/'+(s.sv_maxclients||64);
        q('fm-peak-val').textContent = v.peak_players||s.upvotePower||'0';
      });

      safe(function(){
        var b = {'3095':'The Chop Shop','2944':'San Andreas Mercenaries','2802':'Los Santos Drug Wars'};
        if(v.sv_enforceGameBuild && q('fm-dlc-build')) {
            q('fm-dlc-build').style.display='flex';
            q('fm-dlc-build').innerHTML = 'DLC: '+(b[v.sv_enforceGameBuild]||v.sv_enforceGameBuild);
        }
        if(s.mapname && q('fm-dlc-map')) {
            q('fm-dlc-map').style.display='flex';
            q('fm-dlc-map').innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="margin-right:6px"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>' + clean(s.mapname);
        }
        if(s.gametype && q('fm-dlc-game')) {
            q('fm-dlc-game').style.display='flex';
            q('fm-dlc-game').innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="margin-right:6px"><rect x="2" y="6" width="20" height="12" rx="2"></rect><path d="M6 12h4M8 10v4M15 13h.01M18 11h.01"></path></svg>' + clean(s.gametype);
        }
      });

      safe(function(){
        if(q('fm-connect-btn')) q('fm-connect-btn').href = 'fivem://connect/'+ep;
        var btnCopy = q('fm-copy-btn');
        if(btnCopy) {
          btnCopy.onclick = function() {
            navigator.clipboard.writeText('connect leepglo');
            var oTxt = btnCopy.innerHTML;
            btnCopy.classList.add('copied');
            btnCopy.innerHTML = 'Copied! <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            setTimeout(function(){ btnCopy.innerHTML=oTxt; btnCopy.classList.remove('copied'); }, 2000);
          }
        }
      });

      safe(function(){
        var dlist = q('fm-details-list');
        if(!dlist) return;
        dlist.innerHTML = ''; 
        Object.keys(v).forEach(function(k){
            if(k.indexOf('-')!==-1||SKIP_EX.indexOf(k)!==-1) return;
            for(var i=0;i<SKIP_PRE.length;i++) if(k.indexOf(SKIP_PRE[i])===0) return;
            var val = clean(String(v[k])); if(!val) return;
            var vh = (val.indexOf('http')===0||val.indexOf('.gg')!==-1||k.toLowerCase().indexOf('discord')!==-1) ? '<a href="'+esc(val)+'" target="_blank">'+esc(val)+'</a>' : esc(val);
            dlist.innerHTML += '<div class="fm-row"><span class="fm-row-k">'+esc(k)+'</span><span class="fm-row-v">'+vh+'</span></div>';
        });
      });

      safe(function(){
        var twrap = q('fm-tags-wrap');
        if(!twrap || !v.tags) return;
        String(v.tags).split(',').filter(Boolean).forEach(function(t){
            twrap.innerHTML += '<span class="fm-tag">'+clean(t)+'</span>';
        });
      });

      safe(function(){
        var resources = s.resources || [];
        var resCount = resources.length;
        
        q('fm-res-count').textContent = resCount;
        q('fm-drawer-count').textContent = resCount;

        var resWrap = q('fm-res-wrap');
        var btnShowAll = q('fm-showall');
        var drawerList = q('fm-drawer-res-list');

        var limit = Math.min(2, resCount);
        for(var i=0; i<limit; i++) {
          var badge = document.createElement('span');
          badge.className = 'fm-res-badge';
          badge.textContent = clean(resources[i]);
          resWrap.insertBefore(badge, btnShowAll);
        }

        if(resCount <= 2) {
          btnShowAll.style.display = 'none';
        } else {
          resources.forEach(function(r) {
            var badge = document.createElement('span');
            badge.className = 'fm-drawer-res-item';
            badge.textContent = clean(r);
            drawerList.appendChild(badge);
          });
        }

        var backdrop = q('fm-drawer-backdrop');
        var drawer = q('fm-drawer');
        var closeBtn = q('fm-drawer-close');
        
        function openDrawer() { backdrop.classList.add('active'); drawer.classList.add('open'); }
        function closeDrawer() { backdrop.classList.remove('active'); drawer.classList.remove('open'); }

        btnShowAll.onclick = openDrawer;
        closeBtn.onclick = closeDrawer;
        backdrop.onclick = closeDrawer;

        var filterInput = q('fm-res-filter');
        filterInput.addEventListener('input', function(e) {
            var term = e.target.value.toLowerCase();
            var items = drawerList.querySelectorAll('.fm-drawer-res-item');
            items.forEach(function(item) {
                if(item.textContent.toLowerCase().indexOf(term) !== -1) {
                    item.style.display = 'inline-block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
      });
    }
  })();
  </script>
</body>
</html>