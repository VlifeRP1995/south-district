<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('PATCH', 'PUT');

$user = sd_require_auth();
$input = \SouthDistrict\API\Router::jsonInput();
$pageKey = trim((string) ($input['page'] ?? ''));
$content = $input['content'] ?? null;

if ($pageKey === '' || !is_array($content)) {
    \SouthDistrict\API\Router::jsonError('page et content (objet) requis.');
}

$permMap = [
    'reglement-serveur' => 'edit_reglement_serveur',
    'reglement-staff'   => 'edit_reglement_staff',
    'patch-notes'       => 'publish_patchnotes',
];

$perm = $permMap[$pageKey] ?? null;
if ($perm === null || !sd_user_has_permission($user, $perm)) {
    \SouthDistrict\API\Router::jsonError('Permission insuffisante pour modifier cette page.', 403);
}

$json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$stmt = \SouthDistrict\API\Database::getConnection()->prepare(
    'INSERT INTO page_content (page_key, content, updated_by) VALUES (:k, :c, :u)
     ON DUPLICATE KEY UPDATE content = :c2, updated_by = :u2, updated_at = NOW()'
);
$stmt->execute(['k' => $pageKey, 'c' => $json, 'c2' => $json, 'u' => $user['id'], 'u2' => $user['id']]);

$sectionCount = 0;
if (isset($content['sections']) && is_array($content['sections'])) {
    $sectionCount = count($content['sections']);
} elseif (isset($content['patches']) && is_array($content['patches'])) {
    $sectionCount = count($content['patches']);
}

sd_log_site_event(
    $user,
    'content_update',
    sprintf('%s a publié des modifications sur %s', $user['pseudo'], sd_page_label($pageKey)),
    $pageKey,
    $sectionCount > 0
        ? sprintf('%d élément(s) enregistré(s) · visible pour tous les visiteurs', $sectionCount)
        : 'Contenu mis à jour · visible pour tous les visiteurs'
);

\SouthDistrict\API\Router::jsonSuccess(['page' => $pageKey, 'saved' => true]);

