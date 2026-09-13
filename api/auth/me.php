<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET');

$user = sd_require_auth();

// Synchronisation automatique silencieuse en tâche de fond (si cooldown de 15 min écoulé)
if (!empty($user['discord_id'])) {
    try {
        $syncRes = sd_sync_discord_user_roles((int) $user['id'], null, false);
        if ($syncRes && !empty($syncRes['changed'])) {
            // Recharger l'utilisateur avec ses nouveaux rôles
            $stmtUser = \SouthDistrict\API\Database::getConnection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $stmtUser->execute(['id' => (int) $user['id']]);
            $reloaded = $stmtUser->fetch();
            if ($reloaded) {
                $user = $reloaded;
            }
        }
    } catch (Throwable) {
        // En cas d'erreur de sync, ne pas bloquer l'accès me.php
    }
}

$payload = ['user' => sd_public_user($user)];

try {
    $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
        'SELECT * FROM candidatures WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1'
    );
    $stmt->execute(['user_id' => (int) $user['id']]);
    $candidature = $stmt->fetch();
    if ($candidature) {
        $payload['candidature'] = sd_public_candidature($candidature, true, 'owner');
    }
} catch (Throwable) {
    // Colonne user_id absente si migration non appliquée
}

\SouthDistrict\API\Router::jsonSuccess($payload);


