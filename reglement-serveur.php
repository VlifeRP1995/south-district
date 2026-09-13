<?php require_once __DIR__ . '/includes/vite.php'; ?>
<!DOCTYPE html>

<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
  <?= vite('css/reglement.css') ?>
  <?= vite('css/admin-mode.css') ?>

  <title>Règlement du Serveur - South District RP</title>
  <meta property="og:title" content="Règlement du Serveur - South District RP">
  <meta property="og:description" content="Lisez attentivement le règlement strict RP de South District. Toutes les règles HRP et In-Game indispensables pour bien jouer.">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Règlement du Serveur - South District RP">
  <meta name="twitter:description" content="Lisez attentivement le règlement strict RP de South District. Toutes les règles HRP et In-Game indispensables pour bien jouer.">
  <meta name="description" content="Lisez attentivement le règlement strict RP de South District. Toutes les règles HRP et In-Game indispensables pour bien jouer.">
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
<body class="cp-home" data-admin-page="reglement-serveur" data-admin-permission="edit_reglement_serveur">

  <div class="cp-shell">

    <?php require_once 'includes/header.php'; ?>

    <main class="cp-hero cp-hero--page cp-hero--reglement">
      <div class="cp-hero__panel reglement-layout">

        <aside class="reglement-sidebar" aria-label="Navigation du règlement">
          <div class="reglement-sidebar__head">
            <p class="reglement-sidebar__title">Sommaire</p>
            <button type="button" class="reglement-resume">Reprendre la lecture</button>
          </div>
          <nav class="reglement-sidebar__nav">
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Général</p>
              <a href="#lexique" class="reglement-nav__item" data-section="lexique" style="--reg-accent:#f5c842"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Lexique RP</span></a>
              <a href="#reglement-global" class="reglement-nav__item" data-section="reglement-global" style="--reg-accent:#f5c842"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Règlement global</span></a>
            </div>
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Légal</p>
              <a href="#cote-civil" class="reglement-nav__item" data-section="cote-civil" style="--reg-accent:#1ee6a0"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Côté civil</span></a>
              <a href="#business" class="reglement-nav__item" data-section="business" style="--reg-accent:#1ee6a0"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Business légal</span></a>
              <a href="#service-public" class="reglement-nav__item" data-section="service-public" style="--reg-accent:#1ee6a0"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Service public</span></a>
            </div>
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Organisations</p>
              <a href="#tiers" class="reglement-nav__item" data-section="tiers" style="--reg-accent:#ef4444"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Structures criminelles</span></a>
              <a href="#street-rp" class="reglement-nav__item" data-section="street-rp" style="--reg-accent:#ef4444"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Street RP</span></a>
              <a href="#encadrement" class="reglement-nav__item" data-section="encadrement" style="--reg-accent:#ef4444"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Encadrement</span></a>
            </div>
            <div class="reglement-nav__group">
              <p class="reglement-nav__group-label">Annexes</p>
              <a href="#vehicules" class="reglement-nav__item" data-section="vehicules" style="--reg-accent:#f472b6"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Véhicules illégaux</span></a>
              <a href="#sanctions" class="reglement-nav__item" data-section="sanctions" style="--reg-accent:#f59e0b"><span class="reglement-nav__radio" aria-hidden="true"></span><span class="reglement-nav__label">Sanctions</span></a>
            </div>
          </nav>
        </aside>

        <div class="reglement-body">
          <div class="cp-page__scroll" id="reglement-scroll" data-reglement-scope="serveur">
            <div class="cp-page__content">

              <!-- LEXIQUE -->
              <section class="reg-section is-current" id="lexique" data-theme="yellow">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Général</span>
                      <span class="reg-section__id-page">1 / 10</span>
                    </div>
                    <span class="reg-section__tag">Lexique · Termes du serveur</span>
                    <h2 class="reg-section__title">Règles du serveur</h2>
                  </div>
                </header>
                <div class="reg-lexique">
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Roleplay (RP)</span><p class="reg-lexique__def">Jouer son personnage de façon réaliste et cohérente en toute situation.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Metagaming</span><p class="reg-lexique__def">Utiliser des infos OOC (Discord, stream, vocal) pour agir en jeu. Strictement interdit.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Powergaming</span><p class="reg-lexique__def">Forcer des actions irréalistes sur un autre joueur sans lui laisser de chance de réagir.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Fear RP</span><p class="reg-lexique__def">Craindre pour sa vie de façon réaliste quand une arme est braquée sur soi.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">NLR (New Life Rule)</span><p class="reg-lexique__def">Après un décès, ton perso oublie tout ce qui a conduit à sa mort.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">RDM (Random Deathmatch)</span><p class="reg-lexique__def">Tuer un joueur sans aucune raison RP. Interdit et sanctionné.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">VDM (Vehicle Deathmatch)</span><p class="reg-lexique__def">Écraser ou percuter volontairement un joueur avec un véhicule.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Fail RP</span><p class="reg-lexique__def">Toute action qui sort du cadre réaliste du roleplay. Exemple : sauter d'un toit sans raison.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Bait RP</span><p class="reg-lexique__def">Provoquer volontairement un autre joueur ou la police pour déclencher un conflit.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Frisk</span><p class="reg-lexique__def">Fouille d'un personnage. Doit être joué de façon réaliste par les deux parties.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">OOC (Out Of Character)</span><p class="reg-lexique__def">Parler en tant que joueur, hors de son personnage. À éviter en jeu.</p></div>
                  <div class="reg-lexique__cell"><span class="reg-lexique__term">Scuff</span><p class="reg-lexique__def">Bug ou glitch technique du serveur. À signaler au staff via ticket Discord.</p></div>
                </div>
              </section>

              <!-- RÈGLEMENT GLOBAL -->
              <section class="reg-section" id="reglement-global" data-theme="yellow">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Général</span>
                      <span class="reg-section__id-page">2 / 10</span>
                    </div>
                    <span class="reg-section__tag">Règlement global</span>
                    <h2 class="reg-section__title">Règles du serveur</h2>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Respect absolu</h3><p>Respect envers les joueurs, le staff et les admins. Tout comportement toxique, harcèlement ou discrimination est interdit en jeu comme sur Discord.</p></div><span class="reg-rules__badge">Essentiel</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Roleplay en permanence</h3><p>Reste dans ton personnage (IC). Le HRP est réservé aux channels dédiés. Chaque action doit avoir une justification RP.</p></div><span class="reg-rules__badge">RP</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>No metagaming</h3><p>Interdiction d'utiliser des informations hors jeu (Discord, streams, vocaux) pour influencer tes actions en jeu.</p></div><span class="reg-rules__badge">Interdit</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">04</span><div class="reg-rules__body"><h3>No powergaming</h3><p>Ne force pas d'actions impossibles ou irréalistes sur les autres joueurs.<span class="reg-rules__example">Exemple interdit : « /me désarme instantanément le joueur sans qu'il puisse réagir. »</span></p></div><span class="reg-rules__badge">Interdit</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">05</span><div class="reg-rules__body"><h3>No RDM / No VDM</h3><p>Random Deathmatch et Vehicle Deathmatch sont strictement interdits. Toute agression doit être justifiée en roleplay.</p></div><span class="reg-rules__badge">Interdit</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">06</span><div class="reg-rules__body"><h3>Autorité du staff</h3><p>Les décisions du staff sont finales. En cas de désaccord, ouvre un ticket Discord. Ne contredit pas un staff en public.</p></div><span class="reg-rules__badge">Staff</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">07</span><div class="reg-rules__body"><h3>Bugs &amp; exploits</h3><p>Signale tout bug au staff. L'exploitation volontaire d'un glitch est passible d'un bannissement permanent.</p></div><span class="reg-rules__badge">Sécurité</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">08</span><div class="reg-rules__body"><h3>Création de personnage</h3><p>Nom réaliste, background cohérent, motivations crédibles. Un personnage bien construit enrichit l'expérience de tous.</p></div><span class="reg-rules__badge">Perso</span></li>
                </ol>
              </section>

              <!-- CÔTÉ CIVIL -->
              <section class="reg-section" id="cote-civil" data-theme="green">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Légal</span>
                      <span class="reg-section__id-page">3 / 10</span>
                    </div>
                    <span class="reg-section__tag">Règlement légal · Côté civil</span>
                    <h2 class="reg-section__title">Vivre sa vie légale</h2>
                    <p class="reg-section__intro">La vie légale n'est pas passive. Construire son business dans la rue, créer des liens, respecter la hiérarchie : tout compte dans l'immersion du serveur.</p>
                  </div>
                </header>
                <div class="reg-grid">
                  <div class="reg-grid__cell"><span class="reg-grid__num">01</span><h3>Construire sa réputation légalement</h3><p>La réputation se bâtit par les actions, les deals et les partenariats. Chaque interaction compte.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">02</span><h3>Cohérence du personnage légal</h3><p>Ton personnage doit rester cohérent. Pas médecin le jour et trafiquant la nuit sans transition RP.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">03</span><h3>Valeur du travail légal</h3><p>Les jobs légaux servent l'immersion, pas le farming. Chaque shift doit être joué activement.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">04</span><h3>Respect de la hiérarchie légale</h3><p>Respecte la chaîne de commande dans ton entreprise ou ta faction légale.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">05</span><h3>Pas de passage direct légal ↔ illégal</h3><p>Passer du légal à l'illégal demande une transition RP progressive et crédible.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">06</span><h3>Le légal influence le criminel</h3><p>Les businessmen légaux peuvent être menacés ou utilisés par le crime pour enrichir le RP.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">07</span><h3>Interaction avec la police</h3><p>Coopération réaliste obligatoire. Fuir ou agresser sans raison crédible = Fail RP.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">08</span><h3>Arnaques &amp; commerce RP</h3><p>Les arnaques sont autorisées si elles s'inscrivent dans une logique RP construite, pas répétitive.</p></div>
                </div>
              </section>

              <!-- BUSINESS -->
              <section class="reg-section" id="business" data-theme="green">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Légal</span>
                      <span class="reg-section__id-page">4 / 10</span>
                    </div>
                    <span class="reg-section__tag">Règlement légal · Business</span>
                    <h2 class="reg-section__title">Gestion des entreprises</h2>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Activité minimale obligatoire</h3><p>Le patron doit maintenir une présence et une activité RP régulière dans son business.</p></div><span class="reg-rules__badge">Patron</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Recrutement RP obligatoire</h3><p>Chaque embauche doit passer par une scène RP : entretien, période d'essai, etc.</p></div><span class="reg-rules__badge">RH</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Gestion des employés</h3><p>Le patron est responsable des actions de ses employés dans le cadre du business.</p></div><span class="reg-rules__badge">RH</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">04</span><div class="reg-rules__body"><h3>Utilisation des scripts légaux</h3><p>Garage, restaurant, etc. : utilisés dans leur contexte RP. Le farming = Fail RP.</p></div><span class="reg-rules__badge">Script</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">05</span><div class="reg-rules__body"><h3>Tarifs &amp; économie RP</h3><p>Les prix doivent rester cohérents avec l'économie du serveur.</p></div><span class="reg-rules__badge">Éco</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">06</span><div class="reg-rules__body"><h3>Blanchiment &amp; double activité</h3><p>Blanchir de l'argent sale via un business légal est autorisé si le RP est cohérent.</p></div><span class="reg-rules__badge">Finance</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">07</span><div class="reg-rules__body"><h3>Ouverture &amp; fermeture du business</h3><p>Annoncer ouverture et fermeture de manière réaliste en jeu.</p></div><span class="reg-rules__badge">Script</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">08</span><div class="reg-rules__body"><h3>Racket &amp; protection</h3><p>Les patrons peuvent être rackettés et doivent réagir de façon réaliste.</p></div><span class="reg-rules__badge">Racket</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">09</span><div class="reg-rules__body"><h3>Concurrence &amp; conflits business</h3><p>Les conflits entre entreprises se règlent en RP : négociation, pression, etc.</p></div><span class="reg-rules__badge">Business</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">10</span><div class="reg-rules__body"><h3>Perte du business</h3><p>Un patron peut perdre son business : dettes RP, racket non géré, décision staff.</p></div><span class="reg-rules__badge">Sanction</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">11</span><div class="reg-rules__body"><h3>Direction de l'entreprise</h3><p>Un seul patron et un seul co-patron par entreprise sont autorisés.</p></div><span class="reg-rules__badge">Direction</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">12</span><div class="reg-rules__body"><h3>Présence Discord inter-patrons</h3><p>Tous les chefs d'entreprise et co-patrons doivent obligatoirement être présents sur le Discord des entreprises (salon inter-patrons).</p></div><span class="reg-rules__badge">Discord</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">13</span><div class="reg-rules__body"><h3>Respect de la grille des taxes</h3><p>Chaque entreprise a l'obligation de respecter la grille des taxes mise en place et expliquée lors de la reprise de l'activité.</p></div><span class="reg-rules__badge">Économie</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">14</span><div class="reg-rules__body"><h3>Libre-échange &amp; usage exclusif interdit</h3><p>Il est interdit de produire un quelconque produit de votre métier dans le seul but de l'utiliser pour votre groupe. Afin de favoriser le libre-échange, vous devez créer une réelle économie et du marketing autour de vos produits et services.</p></div><span class="reg-rules__badge">Commerce</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">15</span><div class="reg-rules__body"><h3>Paiement des salaires &amp; primes</h3><p>Les patrons et co-patrons doivent obligatoirement rémunérer (salaires et primes) leurs employé(e)s chaque dimanche soir au plus tard.</p></div><span class="reg-rules__badge">Salaires</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">16</span><div class="reg-rules__body"><h3>Inactivité &amp; abandon d'entreprise</h3><p>Les patrons et co-patrons qui abandonnent leur entreprise ou sont inactifs pendant 2 semaines sans informer le staff ou leur référent seront wipe sans préavis et blacklistés.</p></div><span class="reg-rules__badge">Wipe</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">17</span><div class="reg-rules__body"><h3>Délai de reprise d'entreprise (48h)</h3><p>Si un patron quitte l'entreprise, un délai de 48 heures est défini afin de recevoir le ticket du co-patron ou d'un(e) employé(e) souhaitant reprendre l'entreprise. Au-delà de ce délai imparti, l'entreprise sera disponible publiquement à la reprise sur le Discord Légal.</p></div><span class="reg-rules__badge">Reprise</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">18</span><div class="reg-rules__body"><h3>Conditions d'éligibilité (Sanctions)</h3><p>Pour qu'un joueur puisse devenir patron ou co-patron d'une entreprise, il ne doit avoir reçu aucune sanction au cours des 20 jours précédant le dépôt de son ticket.</p></div><span class="reg-rules__badge">Éligibilité</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">19</span><div class="reg-rules__body"><h3>Cooldown de reprise (1 mois)</h3><p>Un patron et co-patron ayant quitté volontairement une entreprise ne peuvent pas reprendre une entreprise avant 1 mois.</p></div><span class="reg-rules__badge">Cooldown</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">20</span><div class="reg-rules__body"><h3>Démission du chef d'entreprise</h3><p>Si un chef d'entreprise souhaite quitter son poste, il doit en informer le référent légal au plus vite.</p></div><span class="reg-rules__badge">Départ</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">21</span><div class="reg-rules__body"><h3>Fermeture administrative par le Gouvernement</h3><p>Une entreprise peut être fermée temporairement ou définitivement dans certains cas. En effet, le Gouvernement peut prendre cette décision suite à une enquête fiscale, refus de payer les impôts, ou si le patron a des problèmes avec la justice (jugement, blanchiment, enquête en cours).</p></div><span class="reg-rules__badge">Justice</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">22</span><div class="reg-rules__body"><h3>Interdiction d'arnaque</h3><p>Il est totalement interdit de procéder à une arnaque lors de la vente ou de l'acquisition d'un bien.</p></div><span class="reg-rules__badge">Interdit</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">23</span><div class="reg-rules__body"><h3>Difficultés financières &amp; demande de prêt</h3><p>Pour chaque entreprise se trouvant dans une difficulté financière, il est possible de demander un prêt à l'équipe légale avec une raison roleplay et le rembourser plus tard sous peine de wipe de l'entreprise.</p></div><span class="reg-rules__badge">Finance</span></li>
                </ol>
              </section>

              <!-- SERVICE PUBLIC -->
              <section class="reg-section" id="service-public" data-theme="green">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Légal</span>
                      <span class="reg-section__id-page">5 / 10</span>
                    </div>
                    <span class="reg-section__tag">Règlement légal · Service public &amp; FDO</span>
                    <h2 class="reg-section__title">Service public &amp; Forces de l'ordre</h2>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>PIT pour conduite non conforme</h3><p>Les forces de l'ordre ont l'autorisation de PIT si le véhicule poursuivi possède une conduite qui ne respecte pas le règlement.</p></div><span class="reg-rules__badge">LSPD</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Matériel de travail hors service</h3><p>Il est interdit d'utiliser son matériel de travail (armes, outils, etc.) hors service sans raison roleplay.</p></div><span class="reg-rules__badge">Service</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Identification des travailleurs</h3><p>Un travailleur peut être identifié par sa tenue, par son véhicule, ou tout autre moyen permettant de faire une distinction visuelle y compris l'emplacement de son lieu de travail.</p></div><span class="reg-rules__badge">Identification</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">04</span><div class="reg-rules__body"><h3>Vol de véhicule de service &amp; coffre</h3><p>Il est interdit de voler un véhicule de service du LSMC ou du LSPD (sauf scène le nécessitant), ainsi que le contenu de son coffre (sauf si cohérence RP).</p></div><span class="reg-rules__badge">Interdit</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">05</span><div class="reg-rules__body"><h3>Présence d'un procureur</h3><p>Lorsqu'un délit mineur ou un crime est commis, les forces de l'ordre sont dans l'obligation d'appeler un procureur.</p></div><span class="reg-rules__badge">Justice</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">06</span><div class="reg-rules__body"><h3>Roleplay « RIPOU »</h3><p>Le roleplay « RIPOU » est autorisé sous dossier après validation de l'équipe légale. De plus, il doit y avoir une cohérence avec le background du personnage.</p></div><span class="reg-rules__badge">Dossier</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">07</span><div class="reg-rules__body"><h3>Fear des forces de l'ordre</h3><p>Dans tous les cas, les forces de l'ordre sont à craindre. Ils disposent de lourds moyens pour protéger les citoyens et faire respecter les lois. Leurs décisions doivent être respectées, qu'importe le background de votre personnage.</p></div><span class="reg-rules__badge">Fear RP</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">08</span><div class="reg-rules__body"><h3>Usage des armes à feu</h3><p>Les citoyens, tout comme les forces de l'ordre, ne doivent jamais ouvrir le feu sans un minimum de raison RP.</p></div><span class="reg-rules__badge">Armes</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">09</span><div class="reg-rules__body"><h3>Devoir d'exemplarité &amp; professionnalisme</h3><p>En tant qu'agent de police, vous ne devez pas abuser de vos fonctions, ni insulter les citoyens. Tout comme les chefs d'entreprise, vous avez un rôle clé sur le serveur : vous devez donc garder votre sang-froid ainsi que votre professionnalisme. La politesse est primordiale, l'objectif est de s'amuser au travers des scènes RP.</p></div><span class="reg-rules__badge">Conduite</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">10</span><div class="reg-rules__body"><h3>Tirs sur les pneus</h3><p>Les tirs sur les pneus sont interdits pour le LSPD, cependant en cas de no fear d'un joueur (ou autre abus) vous en avez le droit. Veuillez garder en guise de preuve un REC, car il pourra vous être demandé.</p></div><span class="reg-rules__badge">LSPD</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">11</span><div class="reg-rules__body"><h3>Déclenchement des defcons</h3><p>Les defcons ne peuvent pas être décidés seulement par une entreprise mais par le conseil d'état (les hauts gradés des entreprises publiques).</p></div><span class="reg-rules__badge">Defcon</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">12</span><div class="reg-rules__body"><h3>Perquisitions</h3><p>Les perquisitions se font sous dossier auprès du procureur, sauf exception (accord du staff).</p></div><span class="reg-rules__badge">Justice</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">13</span><div class="reg-rules__body"><h3>Bracelet électronique</h3><p>Le bracelet électronique ne peut être mis aux joueurs que dans certaines situations :<br>- Procureur ou juge non disponible<br>- Sous demande du staff<br>- Sous demande du command staff</p></div><span class="reg-rules__badge">LSPD</span></li>
                </ol>
              </section>

              <!-- TIERS -->
              <section class="reg-section" id="tiers" data-theme="red">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Organisations</span>
                      <span class="reg-section__id-page">6 / 10</span>
                    </div>
                    <span class="reg-section__tag">Organisations criminelles</span>
                    <h2 class="reg-section__title">Structures criminelles</h2>
                    <p class="reg-section__intro">Chaque organisation criminelle s'inscrit dans un modèle défini : Clique, Indépendant ou Famille, avec ses effectifs, son mode d'action et ses règles de fonctionnement.</p>
                  </div>
                </header>
                <div class="reg-tiers">
                  <article class="reg-tier" data-tier="1">
                    <span class="reg-tier__bg-num" aria-hidden="true">01</span>
                    <div class="reg-tier__head">
                      <h3 class="reg-tier__name">Clique</h3>
                      <p class="reg-tier__sub">Petite structure de rue</p>
                    </div>
                    <div class="reg-tier__desc">
                      <p>Une clique est un sous-groupe appartenant à un set. Un set peut regrouper plusieurs cliques, chacune ayant son propre nom, son territoire, son identité ou ses affiliations, tout en restant rattachée au même set.</p>
                      <p>Les cliques sont généralement composées de 4 à 5 membres. Il s'agit le plus souvent d'un groupe de proches issus du même quartier, formant un cercle restreint et soudé.</p>
                    </div>
                    <ul class="reg-tier__list">
                      <li>Effectif recommandé : 4 à 5 membres</li>
                      <li>Agit à petite échelle : présence locale, contrôle de son territoire et activités limitées</li>
                    </ul>
                  </article>
                  <article class="reg-tier" data-tier="2">
                    <span class="reg-tier__bg-num" aria-hidden="true">02</span>
                    <div class="reg-tier__head">
                      <h3 class="reg-tier__name">Indépendant</h3>
                      <p class="reg-tier__sub">Structure autonome</p>
                    </div>
                    <div class="reg-tier__desc">
                      <p>Un indépendant est un individu ou un petit groupe qui agit sans être rattaché à un set, une famille ou une organisation officielle. Il fonctionne de manière autonome, en développant ses propres activités, relations et objectifs.</p>
                    </div>
                    <ul class="reg-tier__list">
                      <li>Organisation ou individu autonome</li>
                      <li>Aucun rattachement officiel à une structure existante</li>
                      <li>Fonctionne selon ses propres règles et intérêts</li>
                      <li>Peut développer son propre réseau et ses propres activités</li>
                      <li>Les alliances ou collaborations restent ponctuelles et non officielles</li>
                    </ul>
                  </article>
                  <article class="reg-tier" data-tier="3">
                    <span class="reg-tier__bg-num" aria-hidden="true">03</span>
                    <div class="reg-tier__head">
                      <h3 class="reg-tier__name">Famille</h3>
                      <p class="reg-tier__sub">Organisation criminelle structurée</p>
                    </div>
                    <div class="reg-tier__desc">
                      <p>Une famille est une organisation criminelle hiérarchisée fondée sur la loyauté, le respect, le secret et les intérêts financiers. Contrairement aux gangs de rue, elle privilégie la discrétion, l'influence et les affaires plutôt que les affrontements directs.</p>
                    </div>
                    <ul class="reg-tier__list">
                      <li>Organisation hiérarchisée</li>
                      <li>Contrôle un territoire, des activités ou des intérêts économiques</li>
                      <li>Fonctionne sur la loyauté, le respect et l'omerta</li>
                      <li>Activités principalement orientées vers le profit et l'influence</li>
                      <li>Les conflits sont privilégiés par la négociation, la stratégie et l'intermédiaire plutôt que par la violence ouverte</li>
                    </ul>
                  </article>
                </div>
              </section>

              <!-- STREET RP -->
              <section class="reg-section" id="street-rp" data-theme="red">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Organisations</span>
                      <span class="reg-section__id-page">7 / 10</span>
                    </div>
                    <span class="reg-section__tag">Mentalité full US</span>
                    <h2 class="reg-section__title">Street RP avant tout</h2>
                    <p class="reg-section__intro">L'mentalité est au cœur du serveur. Ce n'est pas un serveur de gunfight. Chaque sortie d'arme doit être justifiée. La pression, la parole, la réputation : voilà vos vraies armes.</p>
                  </div>
                </header>
                <div class="reg-grid">
                  <div class="reg-grid__cell"><span class="reg-grid__num">01</span><h3>Street RP avant gun RP</h3><p>Une arme se sort uniquement quand la situation l'exige. Priorité à la parole et à l'intimidation.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">02</span><h3>Fear RP permanent</h3><p>Obligatoire en permanence. Sous la menace d'une arme, comportement cohérent et réaliste.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">03</span><h3>Conflits progressifs</h3><p>Les tensions se construisent sur la durée. Aucune attaque directe sans historique RP.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">04</span><h3>Défaite et conséquences RP</h3><p>Perte de territoire, réputation, membres. Les conséquences sont réelles et durables.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">05</span><h3>Respect entre groupes</h3><p>Respect obligatoire même en conflit ouvert. Pas d'irrespect OOC.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">06</span><h3>Réputation comme monnaie</h3><p>Votre nom se construit dans la rue. L'influence pèse plus que les kills.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">07</span><h3>Cohérence RP constante</h3><p>Tenue, véhicule, langage, comportement : tout doit être cohérent.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">08</span><h3>Metagaming interdit</h3><p>Infos OOC (Discord, streams) pour influencer le IC = interdit.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">09</span><h3>No revenge kill</h3><p>Après une mort RP, ton perso oublie les circonstances. Pas de vengeance sur ta propre mort.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">10</span><h3>Scènes jouées jusqu'au bout</h3><p>Toute scène engagée doit être jouée intégralement. Pas de déco pour fuir.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">11</span><h3>Valeur de la vie</h3><p>Ton personnage tient à sa vie. Il négocie, recule, cherche à survivre.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">12</span><h3>Gestion du territoire</h3><p>Un territoire se tient et se vit. Présence régulière, activité visible.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">13</span><h3>Pas de powergaming</h3><p>Interdit d'imposer une situation sans laisser l'autre réagir.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">14</span><h3>Communication IC obligatoire</h3><p>Avant tout acte fort, mise en scène et communication IC requises.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">15</span><h3>Construction du personnage</h3><p>Identité, passé, motivations. Plus il est construit, plus le RP est riche.</p></div>
                  <div class="reg-grid__cell"><span class="reg-grid__num">16</span><h3>Attitude hors scène de tir</h3><p>Entre les tensions, ton perso a une vie : il parle, traîne, crée des liens.</p></div>
                </div>
              </section>

              <!-- ENCADREMENT -->
              <section class="reg-section" id="encadrement" data-theme="red">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Organisations</span>
                      <span class="reg-section__id-page">8 / 10</span>
                    </div>
                    <span class="reg-section__tag">Règles générales</span>
                    <h2 class="reg-section__title">Encadrement &amp; limites</h2>
                  </div>
                </header>
                <ol class="reg-rules">
                  <li class="reg-rules__item"><span class="reg-rules__num">01</span><div class="reg-rules__body"><h3>Armement</h3><p>Armes cohérentes avec le tier. En grosse scène : 3 armes lourdes max au total. Pas d'accumulation abusive.</p></div><span class="reg-rules__badge">Armes</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">02</span><div class="reg-rules__body"><h3>Drive-by</h3><p>Autorisé uniquement à faible vitesse et de manière cohérente. Abus ou haute vitesse = sanction.</p></div><span class="reg-rules__badge">Véhicule</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">03</span><div class="reg-rules__body"><h3>Alliances &amp; missionnage</h3><p>Aucune alliance officielle. Missionnage temporaire toléré si justifié RP.</p></div><span class="reg-rules__badge">Alliance</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">04</span><div class="reg-rules__body"><h3>Free loot interdit</h3><p>Fouille interdite sans scène cohérente. Vider un véhicule nécessite une justification RP.</p></div><span class="reg-rules__badge">Loot</span></li>
                  <li class="reg-rules__item"><span class="reg-rules__num">05</span><div class="reg-rules__body"><h3>Cooldowns braquages</h3><p>Supérettes : 1h. Fleeca : 3h. Pacific Standard : ticket staff obligatoire avant tentative.</p></div><span class="reg-rules__badge">Cooldown</span></li>
                </ol>
              </section>

              <!-- VÉHICULES -->
              <section class="reg-section reg-section--vehicules" id="vehicules" data-theme="pink">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Annexes</span>
                      <span class="reg-section__id-page">9 / 10</span>
                    </div>
                    <span class="reg-section__tag">Véhicules · Côté illégal</span>
                    <h2 class="reg-section__title">Autorisés &amp; interdits</h2>
                    <p class="reg-section__intro">Liste des véhicules permis et des usages interdits pour le roleplay illégal : organisations, street RP et activités criminelles. Ne s'applique pas au côté civil ou aux services légaux.</p>
                  </div>
                </header>

                <div class="reg-vehicles-panel">
                  <section class="reg-vehicles-panel__block" aria-labelledby="vehicules-autorises">
                    <h3 class="reg-vehicles-panel__label" id="vehicules-autorises">
                      <svg class="reg-vehicles-panel__icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                      Autorisés · côté illégal
                    </h3>
                    <ul class="reg-vehicles-list">
                      <li>Buffalo</li><li>Oracle</li><li>Primo</li><li>Washington</li><li>Tailgater</li>
                      <li>Fugitive</li><li>Sultan</li><li>Baller</li><li>Granger</li><li>Cavalcade</li>
                      <li>Landstalker</li><li>Burrito</li><li>Minivan</li><li>Speedo</li><li>Sabre</li>
                      <li>Chino</li><li>Buccaneer</li><li>Manana</li><li>Emperor</li><li>Stanier</li>
                      <li>Véhicule import autorisé</li>
                    </ul>
                  </section>

                  <section class="reg-vehicles-panel__block" aria-labelledby="vehicules-interdits">
                    <h3 class="reg-vehicles-panel__label" id="vehicules-interdits">
                      <svg class="reg-vehicles-panel__icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                      Interdictions · côté illégal
                    </h3>
                    <ul class="reg-vehicles-forbidden">
                      <li>VDM : véhicule utilisé comme arme</li>
                      <li>Car ram abusif</li>
                      <li>Conduite arcade / irréaliste</li>
                      <li>Off-road en montagne ou falaise</li>
                      <li>Pas deux fois le même véhicule en convoi</li>
                    </ul>
                  </section>
                </div>
              </section>

              <!-- SANCTIONS -->
              <section class="reg-section" id="sanctions" data-theme="gold">
                <header class="reg-section__id">
                  <div class="reg-section__id-side">
                    <img src="assets/logo.png" alt="" decoding="async">
                  </div>
                  <div class="reg-section__id-main">
                    <div class="reg-section__id-meta">
                      <span class="reg-section__id-doc">Annexes</span>
                      <span class="reg-section__id-page">10 / 10</span>
                    </div>
                    <span class="reg-section__tag">Sanctions</span>
                    <h2 class="reg-section__title">Conséquences &amp; avertissements</h2>
                  </div>
                </header>
                <div class="reg-sanctions">
                  <article class="reg-sanction" data-level="a1"><span class="reg-sanction__id">A1</span><div class="reg-sanction__body"><h3>Remise à l'ordre</h3><p>Rappel officiel enregistré au groupe. Surveillance renforcée 7 jours. Aucune restriction sur les activités en cours.</p></div><span class="reg-rules__badge">Averto 1</span></article>
                  <article class="reg-sanction" data-level="a2"><span class="reg-sanction__id">A2</span><div class="reg-sanction__body"><h3>Restriction temporaire</h3><p>Suspension possible des braquages et scènes majeures. Retrait de privilèges d'organisation pour une durée définie.</p></div><span class="reg-rules__badge">Averto 2</span></article>
                  <article class="reg-sanction" data-level="a3"><span class="reg-sanction__id">A3</span><div class="reg-sanction__body"><h3>Suppression définitive</h3><p>Dissolution complète de l'organisation. Reset de tous les acquis. Blacklist temporaire, recréation interdite.</p></div><span class="reg-rules__badge">Averto 3</span></article>
                </div>
              </section>

              <div class="reglement-pager-wrap">
                <a href="accueil" class="reglement-pager__brand" aria-label="South District RP">
                  <img src="assets/logo.png" alt="South District">
                </a>
                <div class="reglement-pager__body">
                  <p class="reglement-pager__status" id="reglement-pager-status" aria-live="polite">Lexique RP · 1 / 10</p>
                  <nav class="reglement-pager" aria-label="Navigation entre les parties du règlement">
                    <button type="button" class="reglement-pager__btn" id="reglement-prev" disabled>Partie précédente</button>
                    <button type="button" class="reglement-pager__btn" id="reglement-next">Partie suivante</button>
                  </nav>
                </div>
              </div>

              <div class="reglement-footer sd-page-footer" hidden>
                <div class="btn-row">
                  <a href="postuler" class="btn btn-primary">Devenir staff</a>
                  <a href="accueil" class="btn btn-outline">Accueil</a>
                </div>
                <p class="sd-footer-note">© 2026 South District RP — Tous droits réservés.</p>
              </div>
            </div>
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
  <?= vite('js/reglement.js') ?>
  <?= vite('js/admin-mode.js') ?>
</body>
</html>

