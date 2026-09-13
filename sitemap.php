<?php
header('Content-Type: application/xml; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$baseUrl = 'https://south-district.fr';
$pages = array(
    array('loc' => '/',                  'lastmod' => '2026-08-15', 'changefreq' => 'daily',   'priority' => '1.0'),
    array('loc' => '/accueil',           'lastmod' => '2026-08-15', 'changefreq' => 'daily',   'priority' => '0.9'),
    array('loc' => '/serveur',           'lastmod' => '2026-09-01', 'changefreq' => 'hourly',  'priority' => '0.9'),
    array('loc' => '/postuler',          'lastmod' => '2026-07-20', 'changefreq' => 'monthly', 'priority' => '0.8'),
    array('loc' => '/patch-notes',       'lastmod' => '2026-09-01', 'changefreq' => 'weekly',  'priority' => '0.8'),
    array('loc' => '/reglement-serveur', 'lastmod' => '2026-07-01', 'changefreq' => 'monthly', 'priority' => '0.7'),
    array('loc' => '/lore',              'lastmod' => '2026-06-10', 'changefreq' => 'monthly', 'priority' => '0.7'),
    array('loc' => '/equipe',            'lastmod' => '2026-08-01', 'changefreq' => 'monthly', 'priority' => '0.7'),
    array('loc' => '/carte-entreprise',  'lastmod' => '2026-08-20', 'changefreq' => 'weekly',  'priority' => '0.7'),
    array('loc' => '/partenaires',       'lastmod' => '2026-07-15', 'changefreq' => 'monthly', 'priority' => '0.6'),
    array('loc' => '/reglement-staff',   'lastmod' => '2026-07-01', 'changefreq' => 'monthly', 'priority' => '0.5'),
);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . $page['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>" . $page['lastmod'] . "</lastmod>\n";
    echo "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
    echo "    <priority>" . $page['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";