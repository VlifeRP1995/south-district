<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('PATCH', 'PUT');

$user = sd_require_auth();
if (!sd_can_manage_role_definitions($user)) {
    \SouthDistrict\API\Router::jsonError('Seuls le CEO et le Co-CEO peuvent gérer les rôles staff.', 403);
}

$input = \SouthDistrict\API\Router::jsonInput();
$rawRoles = $input['roles'] ?? null;
if (!is_array($rawRoles)) {
    \SouthDistrict\API\Router::jsonError('roles requis (tableau).');
}

$roles = sd_normalize_staff_role_definitions($rawRoles);
$required = ['ceo', 'co_ceo'];
foreach ($required as $key) {
    $found = false;
    foreach ($roles as $role) {
        if (($role['key'] ?? '') === $key) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        \SouthDistrict\API\Router::jsonError('Les rôles CEO et Co-CEO sont obligatoires.');
    }
}

$incomingKeys = array_map(static fn(array $r): string => $r['key'], $roles);
$previousKeys = sd_staff_role_keys();
$removed = array_diff($previousKeys, $incomingKeys);

if ($removed !== []) {
    $pdo = \SouthDistrict\API\Database::getConnection();
    foreach ($removed as $key) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE staff_role = :role');
        $stmt->execute(['role' => $key]);
        if ((int) $stmt->fetchColumn() > 0) {
            \SouthDistrict\API\Router::jsonError(sprintf('Impossible de supprimer le rôle « %s » : des membres l\'utilisent encore.', sd_staff_role_label($key)));
        }
    }
}

$val = json_encode($roles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$stmt = \SouthDistrict\API\Database::getConnection()->prepare(
    'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
     ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
);
$stmt->execute([
    'k'  => 'staff_role_definitions',
    'v'  => $val,
    'v2' => $val,
    'u'  => $user['id'],
    'u2' => $user['id'],
]);

sd_log_site_event(
    $user,
    'role_definitions_update',
    sprintf('%s a mis à jour la hiérarchie des rôles staff', $user['pseudo']),
    null,
    count($roles) . ' rôles définis'
);

\SouthDistrict\API\Router::jsonSuccess(['roles' => sd_get_staff_role_definitions(true)]);

