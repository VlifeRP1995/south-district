<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('POST', 'PATCH', 'DELETE');

$actor = sd_require_auth();
if (!sd_can_manage_role_definitions($actor)) {
    \SouthDistrict\API\Router::jsonError('Accès réservé aux CEO et Co-CEO.', 403);
}

$input = \SouthDistrict\API\Router::jsonInput();
$action = strtolower(trim((string) ($input['action'] ?? $_GET['action'] ?? '')));
if ($action === '' && strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'DELETE') {
    $action = 'delete';
}

$targetId = (int) ($input['user_id'] ?? $_GET['user_id'] ?? 0);
$reason = trim((string) ($input['reason'] ?? ''));
if (strlen($reason) > 255) {
    $reason = substr($reason, 0, 255);
}

if ($targetId <= 0) {
    \SouthDistrict\API\Router::jsonError('user_id requis.');
}

if (!in_array($action, ['ban', 'unban', 'delete'], true)) {
    \SouthDistrict\API\Router::jsonError('Action invalide (ban, unban, delete).');
}

$pdo = \SouthDistrict\API\Database::getConnection();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $targetId]);
$target = $stmt->fetch();

if (!$target) {
    \SouthDistrict\API\Router::jsonError('Utilisateur introuvable.', 404);
}

if ((int) $actor['id'] === $targetId) {
    \SouthDistrict\API\Router::jsonError('Tu ne peux pas appliquer cette action sur ton propre compte.', 403);
}

if (sd_is_protected_account($target, $actor)) {
    \SouthDistrict\API\Router::jsonError('Ce compte est protégé.', 403);
}

$actorRank = sd_staff_rank($actor['staff_role'] ?? null);
$targetRank = sd_staff_rank($target['staff_role'] ?? null);
$actorIsMaster = sd_is_master_ceo($actor);

if ($targetRank >= $actorRank && !($actorIsMaster && !sd_is_protected_account($target, $actor))) {
    \SouthDistrict\API\Router::jsonError('Tu ne peux pas gérer un compte de rang égal ou supérieur.', 403);
}

if ($action === 'ban') {
    if (!empty($target['is_banned'])) {
        \SouthDistrict\API\Router::jsonError('Ce compte est déjà banni.');
    }

    $stmt = $pdo->prepare(
        'UPDATE users
         SET is_banned = 1,
             banned_at = NOW(),
             ban_reason = :reason,
             is_staff_visible = 0,
             updated_at = NOW()
         WHERE id = :id'
    );
    $stmt->execute([
        'reason' => $reason !== '' ? $reason : null,
        'id'     => $targetId,
    ]);

    $pdo->prepare('DELETE FROM sessions WHERE user_id = :id')->execute(['id' => $targetId]);

    sd_log_site_event(
        $actor,
        'account_ban',
        sprintf('%s a banni le compte %s', $actor['pseudo'], $target['pseudo']),
        null,
        $reason !== '' ? ('Motif : ' . $reason) : 'Accès site et admin coupé'
    );

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $targetId]);
    \SouthDistrict\API\Router::jsonSuccess(['user' => sd_public_user($stmt->fetch())]);
}

if ($action === 'unban') {
    if (empty($target['is_banned'])) {
        \SouthDistrict\API\Router::jsonError('Ce compte n\'est pas banni.');
    }

    $stmt = $pdo->prepare(
        'UPDATE users
         SET is_banned = 0,
             banned_at = NULL,
             ban_reason = NULL,
             updated_at = NOW()
         WHERE id = :id'
    );
    $stmt->execute(['id' => $targetId]);

    sd_log_site_event(
        $actor,
        'account_unban',
        sprintf('%s a débanni le compte %s', $actor['pseudo'], $target['pseudo']),
        null,
        'Accès rétabli'
    );

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $targetId]);
    \SouthDistrict\API\Router::jsonSuccess(['user' => sd_public_user($stmt->fetch())]);
}

/* delete */
$pdo->prepare('DELETE FROM sessions WHERE user_id = :id')->execute(['id' => $targetId]);
try {
    $pdo->prepare('DELETE FROM sd_staff_absences WHERE user_id = :id')->execute(['id' => $targetId]);
} catch (\Throwable $e) {
    error_log('[Account Delete] Failed cleaning staff absences: ' . $e->getMessage());
}
try {
    $pdo->prepare('UPDATE candidatures SET user_id = NULL WHERE user_id = :id')->execute(['id' => $targetId]);
} catch (\Throwable $e) {
    error_log('[Account Delete] Failed unlinking candidatures: ' . $e->getMessage());
}

$stmt = $pdo->prepare('DELETE FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $targetId]);

sd_log_site_event(
    $actor,
    'account_delete',
    sprintf('%s a supprimé définitivement le compte %s (@%s)', $actor['pseudo'], $target['pseudo'], $target['login']),
    null,
    'Suppression définitive'
);

\SouthDistrict\API\Router::jsonSuccess(['deleted' => true, 'user_id' => $targetId]);

