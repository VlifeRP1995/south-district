<?php
/**
 * South District RP — Liste publique des partenaires
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET');

$pdo = \SouthDistrict\API\Database::getConnection();

try {
    $stmt = $pdo->query(
        'SELECT id, name, description, category, logo, banner, link, website_link, rank_order, created_at 
         FROM sd_partenaires 
         ORDER BY rank_order ASC, name ASC'
    );
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    // Si la table n'existe pas encore ou erreur BDD
    $rows = [];
}

$partenaires = array_map(static function (array $r): array {
    return [
        'id'           => (int) $r['id'],
        'name'         => $r['name'],
        'description'  => $r['description'] ?? '',
        'category'     => $r['category'] ?? 'Partenaire Officiel',
        'logo'         => $r['logo'] ?? null,
        'banner'       => $r['banner'] ?? null,
        'link'         => $r['link'] ?? null,
        'website_link' => $r['website_link'] ?? null,
        'rank_order'   => (int) ($r['rank_order'] ?? 0),
        'created_at'   => $r['created_at'] ?? null,
    ];
}, $rows);

\SouthDistrict\API\Router::jsonSuccess(['partenaires' => $partenaires]);

