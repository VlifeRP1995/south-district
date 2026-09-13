<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET');

$user = sd_require_auth();
if (!sd_can_access_admin($user)) {
    \SouthDistrict\API\Router::jsonError('Accès admin requis.', 403);
}

\SouthDistrict\API\Router::jsonSuccess([
    'roles'       => sd_get_staff_role_definitions(),
    'can_manage'  => sd_can_manage_role_definitions($user),
]);

