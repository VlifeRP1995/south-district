<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('POST', 'GET');

$user = sd_require_auth();

if (empty($user['discord_id'])) {
    \SouthDistrict\API\Router::jsonError('Aucun compte Discord lié à ce profil. Connectez-vous avec Discord pour lier votre compte.', 400);
}

try {
    $syncRes = sd_sync_discord_user_roles((int) $user['id'], null, true);
} catch (Throwable $e) {
    \SouthDistrict\API\Router::jsonError('Erreur lors de la communication avec Discord : ' . $e->getMessage(), 500);
}

// Rechargement du profil utilisateur à jour
$stmt = \SouthDistrict\API\Database::getConnection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => (int) $user['id']]);
$freshUser = $stmt->fetch();

if (!$freshUser) {
    \SouthDistrict\API\Router::jsonError('Utilisateur introuvable.', 404);
}

$pub = sd_public_user($freshUser);

$primaryLabel = $pub['staff_label'] ?? 'Citoyen';
$secondaryLabels = $pub['secondary_labels'] ?? [];
$rolesSummary = $primaryLabel;
if ($secondaryLabels !== []) {
    $rolesSummary .= ' (+ ' . implode(', ', $secondaryLabels) . ')';
}

$message = !empty($syncRes['changed'])
    ? "Rôles Discord actualisés avec succès : {$rolesSummary}"
    : "Rôles Discord déjà à jour : {$rolesSummary}";

\SouthDistrict\API\Router::jsonSuccess([
    'message'   => $message,
    'changed'   => !empty($syncRes['changed']),
    'user'      => $pub,
]);

