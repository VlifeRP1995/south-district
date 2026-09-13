<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



\SouthDistrict\API\Router::requireMethod('GET');



try {

    $stmt = \SouthDistrict\API\Database::getConnection()->query(

        'SELECT id, pseudo, avatar, avatar_mime, updated_at, staff_role, staff_title, is_staff_visible, secondary_roles
         FROM users
         WHERE staff_role IS NOT NULL AND is_staff_visible = 1
           AND (discord_id IS NULL OR discord_id != \'1546266986127036419\')
           AND pseudo NOT LIKE \'%South District Bot%\'
           AND pseudo NOT LIKE \'%SouthDistrictSite%\''

    );

} catch (Throwable) {

    $stmt = \SouthDistrict\API\Database::getConnection()->query(

        'SELECT id, pseudo, avatar, avatar_mime, updated_at, staff_role, staff_title, secondary_roles
         FROM users
         WHERE staff_role IS NOT NULL
           AND (discord_id IS NULL OR discord_id != \'1546266986127036419\')
           AND pseudo NOT LIKE \'%South District Bot%\'
           AND pseudo NOT LIKE \'%SouthDistrictSite%\''

    );

}



$showSecondaryRoles = true;
try {
    $stmtSec = \SouthDistrict\API\Database::getConnection()->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'team_show_secondary_roles' LIMIT 1");
    $stmtSec->execute();
    $rawSec = $stmtSec->fetchColumn();
    if ($rawSec !== false && $rawSec !== null) {
        $showSecondaryRoles = (bool) json_decode((string) $rawSec, true);
    }
} catch (Throwable) {
    $showSecondaryRoles = true;
}

$roleColors = sd_get_staff_role_colors();
if (empty($roleColors['createur']) || strtolower($roleColors['createur']) === '#ffffff') {
    $roleColors['createur'] = '#eeae59';
}
if (!empty($roleColors['co_ceo']) && $roleColors['co_ceo'] === '#a80000') {
    $roleColors['co_ceo'] = '#749472';
}
$members = [];

foreach ($stmt->fetchAll() as $row) {

    $secondaryRoles = [];
    if ($showSecondaryRoles && !empty($row['secondary_roles'])) {
        $decoded = is_string($row['secondary_roles']) ? json_decode($row['secondary_roles'], true) : $row['secondary_roles'];
        if (is_array($decoded)) {
            $secondaryRoles = array_values(array_filter($decoded, 'is_string'));
        }
    }
    $secondaryLabels = array_map('sd_staff_role_label', $secondaryRoles);

    $staffRoleKey = (string) ($row['staff_role'] ?? '');
    $staffColor = sd_staff_role_color($staffRoleKey);

    $secondaryColors = [];
    foreach ($secondaryRoles as $sr) {
        $secondaryColors[$sr] = sd_staff_role_color($sr);
    }

    $members[] = [

        'id'               => (int) $row['id'],

        'pseudo'           => $row['pseudo'],

        'avatar'           => sd_avatar_public_url($row),

        'has_avatar'       => !empty($row['avatar_mime']) || !empty($row['avatar']),

        'staff_role'       => $row['staff_role'],

        'staff_label'      => sd_staff_role_label($row['staff_role']),

        'staff_color'      => $staffColor,

        'staff_title'      => $row['staff_title'] ?? null,
        'secondary_roles'  => $secondaryRoles,
        'secondary_labels' => $secondaryLabels,
        'secondary_colors' => $secondaryColors,
        'updated_at'       => $row['updated_at'] ?? null,
        'rank'             => sd_staff_rank($row['staff_role']),

    ];

}



usort($members, static function (array $a, array $b): int {
    $rankCmp = ($b['rank'] <=> $a['rank']);
    if ($rankCmp !== 0) {
        return $rankCmp;
    }

    return strcasecmp($a['pseudo'], $b['pseudo']);
});



\SouthDistrict\API\Router::jsonSuccess([
    'members'     => $members,
    'role_colors' => $roleColors,
]);

