<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET');

$user = sd_require_auth();
if (!sd_user_has_permission($user, 'manage_gerant_staff')) {
    \SouthDistrict\API\Router::jsonError('Permission insuffisante.', 403);
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    \SouthDistrict\API\Router::jsonError('Paramètre id requis.');
}

$stmt = \SouthDistrict\API\Database::getConnection()->prepare(sd_candidature_select_sql() . ' WHERE c.id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    \SouthDistrict\API\Router::jsonError('Candidature introuvable.', 404);
}

if (empty($row['viewed_at'])) {
    try {
        \SouthDistrict\API\Database::getConnection()->prepare('UPDATE candidatures SET viewed_at = NOW() WHERE id = :id')->execute(['id' => $id]);
        $row['viewed_at'] = date('Y-m-d H:i:s');
    } catch (Throwable) {
        // colonne absente si migration non appliquée
    }
}

\SouthDistrict\API\Router::jsonSuccess(['candidature' => sd_public_candidature($row, true, 'admin')]);

