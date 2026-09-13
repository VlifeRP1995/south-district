<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $user = sd_require_auth();
    if (!sd_can_access_admin($user) || !sd_can_manage_role_definitions($user)) {
        \SouthDistrict\API\Router::jsonError('Permission "Gestion Permissions" requise.', 403);
    }

    $staffRoles = sd_get_staff_role_definitions();
    $permDefs = sd_permission_definitions();
    $rolePerms = sd_get_role_permissions_config();
    $canManage = sd_can_manage_role_definitions($user);

    \SouthDistrict\API\Router::jsonSuccess([
        'staff_roles'            => $staffRoles,
        'permission_definitions' => $permDefs,
        'role_permissions'       => $rolePerms,
        'can_manage'             => $canManage,
    ]);
}

if ($method === 'POST') {
    $user = sd_require_auth();
    if (!sd_can_manage_role_definitions($user)) {
        \SouthDistrict\API\Router::jsonError('Permission "Gestion Permissions" requise.', 403);
    }

    $input = \SouthDistrict\API\Router::jsonInput();
    $rawPerms = $input['permissions'] ?? null;

    if (!is_array($rawPerms)) {
        \SouthDistrict\API\Router::jsonError('Paramètre "permissions" invalide (objet attendu).');
    }

    $staffRoles = sd_get_staff_role_definitions();
    $validRoleKeys = array_map(static fn(array $r): string => $r['key'], $staffRoles);

    $cleanConfig = [];
    foreach ($validRoleKeys as $roleKey) {
        $cleanConfig[$roleKey] = [];
        $roleInput = is_array($rawPerms[$roleKey] ?? null) ? $rawPerms[$roleKey] : [];

        // Rôles directeurs et développeur toujours protégés avec toutes les permissions actives
        $normKey = strtolower(trim($roleKey));
        $normKey = str_replace(['é', 'è', 'ê', 'ë'], 'e', $normKey);
        $isProtected = in_array($normKey, ['createur', 'ceo', 'co_ceo', 'developer'], true);

        foreach (SD_PERMISSION_KEYS as $permKey) {
            if ($isProtected) {
                $cleanConfig[$roleKey][$permKey] = true;
            } else {
                $cleanConfig[$roleKey][$permKey] = !empty($roleInput[$permKey]);
            }
        }
    }

    sd_save_role_permissions_config($cleanConfig, (int) $user['id']);

    sd_log_site_event(
        $user,
        'role_permissions_update',
        sprintf('%s a mis à jour les permissions des rôles staff', $user['pseudo']),
        'admin',
        count($cleanConfig) . ' rôles configurés'
    );

    \SouthDistrict\API\Router::jsonSuccess([
        'message'          => 'Permissions des rôles enregistrées avec succès.',
        'role_permissions' => sd_get_role_permissions_config(),
    ]);
}

\SouthDistrict\API\Router::jsonError('Méthode non autorisée.', 405);
