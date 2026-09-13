<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $user = sd_require_auth();
    if (!sd_can_access_admin($user) || !sd_can_manage_role_definitions($user)) {
        \SouthDistrict\API\Router::jsonError('Permission "Gestion Permissions" requise.', 403);
    }

    $discordConfig = sd_config()['discord'] ?? [];
    $guildId = (string) ($discordConfig['guild_id'] ?? '');
    $botConfigured = !empty($discordConfig['bot_token']) && $discordConfig['bot_token'] !== 'votre_token_copié_ici';

    // Récupérer tous les rôles du serveur Discord en direct
    $discordRoles = sd_fetch_discord_guild_roles_detailed();

    // Récupérer la configuration sauvegardée
    $savedConfig = sd_get_discord_roles_config();

    // Mise à jour automatique des couleurs depuis Discord si disponible
    sd_update_staff_role_colors_from_discord();

    // Grades staff disponibles sur le site
    $staffRoleDefs = sd_get_staff_role_definitions(true);
    $validStaffKeys = array_map(static fn(array $r): string => $r['key'], $staffRoleDefs);

    // Fusionner les rôles avec la configuration enregistrée ou pré-détectée
    $enhancedRoles = [];
    foreach ($discordRoles as $r) {
        $id = $r['id'];
        $name = sd_clean_role_name((string) $r['name']);

        $autoAssign = false;
        $targetRole = '';
        $orderRank = (int) ($r['position'] ?? 0);

        if (isset($savedConfig[$id])) {
            $autoAssign = !empty($savedConfig[$id]['auto_assign']);
            $targetRole = (string) ($savedConfig[$id]['target_role'] ?? '');
            if (isset($savedConfig[$id]['order_rank'])) {
                $orderRank = (int) $savedConfig[$id]['order_rank'];
            }
        } else {
            // Suggestion par défaut : d'abord role_mapping dans config.php, sinon détection de nom
            $roleMapping = $discordConfig['role_mapping'] ?? [];
            if (isset($roleMapping[$id])) {
                $targetRole = (string) $roleMapping[$id];
                $autoAssign = true;
            } else {
                $detected = sd_detect_staff_role_from_name($name, $staffRoleDefs);
                $autoAssign = ($detected !== null);
                $targetRole = $detected ?? '';
            }
        }



        $enhancedRoles[] = [
            'id'          => $id,
            'name'        => $name,
            'color'       => $r['color'],
            'position'    => $r['position'],
            'order_rank'  => $orderRank,
            'managed'     => $r['managed'],
            'is_everyone' => $r['is_everyone'],
            'auto_assign' => $autoAssign,
            'target_role' => $targetRole,
        ];
    }

    // Si le bot n'est pas encore actif, afficher les rôles connus depuis role_mapping et BDD
    if (empty($enhancedRoles)) {
        $roleMapping = $discordConfig['role_mapping'] ?? [];
        $knownRoleIds = array_unique(array_merge(array_keys($savedConfig), array_keys($roleMapping)));
        foreach ($knownRoleIds as $id) {
            $target = $savedConfig[$id]['target_role'] ?? ($roleMapping[$id] ?? '');
            $name = sd_clean_role_name($savedConfig[$id]['name'] ?? ($target ? sd_staff_role_label($target) : "Rôle Discord ({$id})"));
            $enhancedRoles[] = [
                'id'          => (string) $id,
                'name'        => $name,
                'color'       => $savedConfig[$id]['color'] ?? '#5865F2',
                'position'    => 0,
                'managed'     => false,
                'is_everyone' => false,
                'auto_assign' => !empty($target),
                'target_role' => $target,
            ];
        }
    }

    // Trier les rôles : les rôles avec auto_assign et order_rank personnalisé en premier, selon order_rank décroissant
    usort($enhancedRoles, static function (array $a, array $b): int {
        $aActive = !empty($a['auto_assign']) ? 1 : 0;
        $bActive = !empty($b['auto_assign']) ? 1 : 0;
        if ($aActive !== $bActive) {
            return $bActive <=> $aActive;
        }
        $rankA = (int) ($a['order_rank'] ?? 0);
        $rankB = (int) ($b['order_rank'] ?? 0);
        if ($rankA !== $rankB) {
            return $rankB <=> $rankA;
        }
        return ((int) ($b['position'] ?? 0)) <=> ((int) ($a['position'] ?? 0));
    });

    $showSecondaryRoles = true;
    try {
        $pdo = \SouthDistrict\API\Database::getConnection();
        $stmtSec = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'team_show_secondary_roles' LIMIT 1");
        $stmtSec->execute();
        $rawSec = $stmtSec->fetchColumn();
        if ($rawSec !== false && $rawSec !== null) {
            $showSecondaryRoles = (bool) json_decode((string) $rawSec, true);
        }
    } catch (\Throwable) {
        $showSecondaryRoles = true;
    }

    \SouthDistrict\API\Router::jsonSuccess([
        'guild_id'              => $guildId,
        'bot_configured'        => $botConfigured,
        'discord_roles'         => $enhancedRoles,
        'staff_roles'           => $staffRoleDefs,
        'can_manage'            => sd_can_manage_role_definitions($user),
        'total_roles'           => count($enhancedRoles),
        'show_secondary_roles'  => $showSecondaryRoles,
    ]);
}

if ($method === 'POST') {
    $user = sd_require_auth();
    if (!sd_can_manage_role_definitions($user)) {
        \SouthDistrict\API\Router::jsonError('Permission "Gestion Permissions" requise.', 403);
    }

    $input = \SouthDistrict\API\Router::jsonInput();
    $rawMapping = $input['mapping'] ?? null;

    if (!is_array($rawMapping)) {
        \SouthDistrict\API\Router::jsonError('Paramètre "mapping" invalide (objet attendu).');
    }

    $staffRoleDefs = sd_get_staff_role_definitions();
    $validStaffKeys = array_map(static fn(array $r): string => $r['key'], $staffRoleDefs);

    $cleanConfig = [];
    $activeCount = 0;
    $newStaffColors = [];

    foreach ($rawMapping as $roleId => $conf) {
        $rId = trim((string) $roleId);
        if ($rId === '' || !is_array($conf)) {
            continue;
        }

        $autoAssign = !empty($conf['auto_assign']);
        $targetRole = trim((string) ($conf['target_role'] ?? ''));
        $roleName = sd_clean_role_name(trim((string) ($conf['name'] ?? '')));
        $roleColor = trim((string) ($conf['color'] ?? ''));
        if ($roleColor !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $roleColor)) {
            $roleColor = '';
        }

        // En intégration directe Discord (Option 2), le rôle Discord est directement le rôle staff
        if ($autoAssign && $targetRole === '') {
            $detected = sd_detect_staff_role_from_name($roleName, $staffRoleDefs);
            $targetRole = $detected ?? ('discord_' . $rId);
        }

        if ($autoAssign) {
            $activeCount++;
            if ($targetRole !== '' && $roleColor !== '' && strtolower($roleColor) !== '#000000') {
                $newStaffColors[$targetRole] = strtolower($roleColor);
            }
        }

        $cleanConfig[$rId] = [
            'name'        => $roleName,
            'color'       => $roleColor,
            'auto_assign' => $autoAssign,
            'target_role' => $targetRole,
            'order_rank'  => isset($conf['order_rank']) ? (int) $conf['order_rank'] : 0,
        ];
    }

    // Sauvegarder dans site_settings
    sd_save_discord_roles_config($cleanConfig, (int) $user['id']);

    if (array_key_exists('show_secondary_roles', $input)) {
        $pdo = \SouthDistrict\API\Database::getConnection();
        $showSecVal = json_encode((bool) $input['show_secondary_roles']);
        $stmtUpdSec = $pdo->prepare(
            'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
             ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
        );
        $stmtUpdSec->execute([
            'k'  => 'team_show_secondary_roles',
            'v'  => $showSecVal,
            'v2' => $showSecVal,
            'u'  => (int) $user['id'],
            'u2' => (int) $user['id'],
        ]);
    }

    if (!empty($newStaffColors)) {
        $existingColors = sd_get_staff_role_colors();
        $mergedColors = array_merge($existingColors, $newStaffColors);
        sd_save_staff_role_colors($mergedColors, (int) $user['id']);
    }

    sd_log_site_event(
        $user,
        'discord_roles_config_update',
        sprintf('%s a mis à jour la configuration des rôles Discord', $user['pseudo']),
        'admin',
        count($cleanConfig) . " rôles configurés ({$activeCount} avec attribution auto active)"
    );

    $syncResult = null;
    if (!empty($input['sync_now'])) {
        $syncResult = sd_sync_all_guild_members_now();
    } else {
        sd_update_staff_role_colors_from_discord();
    }

    \SouthDistrict\API\Router::jsonSuccess([
        'message'      => 'Configuration des rôles Discord enregistrée avec succès.',
        'roles_count'  => count($cleanConfig),
        'active_count' => $activeCount,
        'sync_result'  => $syncResult,
    ]);
}

\SouthDistrict\API\Router::jsonError('Méthode non autorisée.', 405);
