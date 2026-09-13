<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('PATCH', 'PUT');

$actor = sd_require_auth();
if (!sd_user_has_permission($actor, 'manage_permissions')) {
    \SouthDistrict\API\Router::jsonError('Permission insuffisante.', 403);
}

$input = \SouthDistrict\API\Router::jsonInput();
$targetId = (int) ($input['user_id'] ?? 0);
$siteRole = array_key_exists('site_role', $input) ? (string) $input['site_role'] : null;
$staffRole = array_key_exists('staff_role', $input) ? $input['staff_role'] : null;
$isStaffVisible = array_key_exists('is_staff_visible', $input) ? (bool) $input['is_staff_visible'] : null;
$permissions = array_key_exists('permissions', $input) ? $input['permissions'] : null;
$staffTitle = array_key_exists('staff_title', $input) ? trim((string) $input['staff_title']) : null;

if ($targetId <= 0) {
    \SouthDistrict\API\Router::jsonError('user_id requis.');
}

$pdo = \SouthDistrict\API\Database::getConnection();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $targetId]);
$target = $stmt->fetch();

if (!$target) {
    \SouthDistrict\API\Router::jsonError('Utilisateur introuvable.', 404);
}

if (sd_is_protected_account($target, $actor)) {
    \SouthDistrict\API\Router::jsonError('Le compte est protégé et ne peut pas être modifié.', 403);
}

$actorRank = sd_staff_rank($actor['staff_role']);
$targetRank = sd_staff_rank($target['staff_role']);
$actorIsMaster = sd_is_master_ceo($actor);

if ($targetRank >= $actorRank && (int) $actor['id'] !== $targetId) {
    if (!($actorIsMaster && !sd_is_protected_account($target, $actor))) {
        \SouthDistrict\API\Router::jsonError('Vous ne pouvez pas modifier un membre de rang égal ou supérieur.', 403);
    }
}

$fields = [];
$params = ['id' => $targetId];

if ($siteRole !== null) {
    if (!in_array($siteRole, SD_SITE_ROLES, true)) {
        \SouthDistrict\API\Router::jsonError('site_role invalide.');
    }
    if ($siteRole === 'admin' && $actorRank < sd_staff_rank('co_ceo') && !$actorIsMaster) {
        \SouthDistrict\API\Router::jsonError('Seuls Co-CEO, CEO et Créateur peuvent promouvoir un administrateur site.', 403);
    }
    $fields[] = 'site_role = :site_role';
    $params['site_role'] = $siteRole;
}

if (array_key_exists('staff_role', $input)) {
    $allowedRoles = sd_staff_role_keys();
    if ($staffRole !== null && $staffRole !== '' && !in_array($staffRole, $allowedRoles, true)) {
        \SouthDistrict\API\Router::jsonError('staff_role invalide.');
    }
    $newStaff = ($staffRole === null || $staffRole === '') ? null : $staffRole;
    $newRank = sd_staff_rank($newStaff);

    if ($newRank >= $actorRank && (int) $actor['id'] !== $targetId) {
        if (!$actorIsMaster) {
            \SouthDistrict\API\Router::jsonError('Vous ne pouvez pas attribuer un rôle égal ou supérieur au vôtre.', 403);
        }
    }

    $fields[] = 'staff_role = :staff_role';
    $params['staff_role'] = $newStaff;
    if ($isStaffVisible === null) {
        $fields[] = 'is_staff_visible = :is_staff_visible';
        $params['is_staff_visible'] = $newStaff !== null ? 1 : 0;
    }
}

if ($isStaffVisible !== null) {
    $fields[] = 'is_staff_visible = :is_staff_visible';
    $params['is_staff_visible'] = $isStaffVisible ? 1 : 0;
}

if ($staffTitle !== null) {
    $fields[] = 'staff_title = :staff_title';
    $params['staff_title'] = $staffTitle !== '' ? $staffTitle : null;
}

if ($permissions !== null && is_array($permissions)) {
    $clean = [];
    foreach (SD_PERMISSION_KEYS as $key) {
        if (array_key_exists($key, $permissions)) {
            $clean[$key] = (bool) $permissions[$key];
        }
    }
    $fields[] = 'permissions = :permissions';
    $params['permissions'] = json_encode($clean);
}

if ($fields === []) {
    \SouthDistrict\API\Router::jsonError('Aucune donnée à mettre à jour.');
}

$sql = 'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $targetId]);
$updated = $stmt->fetch();

$changes = [];
if ($siteRole !== null && $siteRole !== $target['site_role']) {
    $changes[] = 'site: ' . ($target['site_role'] ?: 'citoyen') . ' → ' . $siteRole;
}
if (array_key_exists('staff_role', $input)) {
    $prevStaff = $target['staff_role'] ?: 'aucun';
    $newStaffLog = ($staffRole === null || $staffRole === '') ? 'aucun' : $staffRole;
    if ($newStaffLog !== $prevStaff) {
        $changes[] = 'staff: ' . $prevStaff . ' → ' . $newStaffLog;
    }
}
if ($isStaffVisible !== null && (bool) $target['is_staff_visible'] !== $isStaffVisible) {
    $changes[] = $isStaffVisible ? 'visible équipe' : 'masqué équipe';
}
if ($permissions !== null && is_array($permissions)) {
    $changes[] = 'permissions mises à jour';
}

sd_log_site_event(
    $actor,
    'role_change',
    sprintf('%s a modifié les droits de %s', $actor['pseudo'], $target['pseudo']),
    null,
    $changes ? implode(' · ', $changes) : 'Droits enregistrés'
);

\SouthDistrict\API\Router::jsonSuccess(['user' => sd_public_user($updated)]);

