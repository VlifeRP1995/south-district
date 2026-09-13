<?php
/**
 * South District RP - Bootstrap API
 * PDO, CORS, sessions, helpers JSON, middleware d'authentification.
 */
declare(strict_types=1);

const SD_SITE_ROLES  = ['citoyen', 'admin'];
const SD_STAFF_ROLES = [
    'staff', 'moderateur', 'administrateur', 'gerant', 'gerant_staff',
    'manager', 'developer', 'co_ceo', 'ceo', 'createur',
];
const SD_PERMISSION_KEYS = [
    'edit_reglement_serveur',
    'edit_reglement_staff',
    'publish_patchnotes',
    'manage_patchnotes',
    'manage_gerant_staff',
    'manage_map',
    'view_logs',
    'manage_permissions',
];
const SD_CANDIDATURE_STATUSES = ['en_attente', 'en_examen', 'entretien', 'accepte', 'refuse'];
/** @deprecated Utiliser sd_get_recruitment_open_roles() */
const SD_CANDIDATURE_POSTES   = ['moderateur', 'support', 'recruteur', 'responsable'];

/** @var array<string, mixed>|null */
$sdConfig = null;

/** @var PDO|null */
$sdPdo = null;

function sd_config(): array
{
    global $sdConfig;

    if ($sdConfig === null) {
        $path = dirname(__DIR__) . '/config.php';
        if (!is_file($path)) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Configuration manquante (config.php).']);
            exit;
        }
        $sdConfig = require $path;
    }

    return $sdConfig;
}

function sd_session_cookie_name(): string
{
    return sd_config()['app']['session_cookie'];
}

function sd_set_session_cookie(string $token, int $expiresAt): void
{
    $app = sd_config()['app'];

    setcookie(sd_session_cookie_name(), $token, [
        'expires'  => $expiresAt,
        'path'     => '/',
        'secure'   => (bool) $app['cookie_secure'],
        'httponly' => true,
        'samesite' => $app['cookie_samesite'],
    ]);

    $csrfToken = bin2hex(random_bytes(32));
    setcookie('XSRF-TOKEN', $csrfToken, [
        'expires'  => $expiresAt,
        'path'     => '/',
        'secure'   => (bool) $app['cookie_secure'],
        'httponly' => false,
        'samesite' => $app['cookie_samesite'],
    ]);
}

function sd_clear_session_cookie(): void
{
    $app = sd_config()['app'];

    setcookie(sd_session_cookie_name(), '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => (bool) $app['cookie_secure'],
        'httponly' => true,
        'samesite' => $app['cookie_samesite'],
    ]);

    $csrfToken = bin2hex(random_bytes(32));
    $_COOKIE['XSRF-TOKEN'] = $csrfToken;
    setcookie('XSRF-TOKEN', $csrfToken, [
        'expires'  => time() + (int) ($app['session_lifetime'] ?? 604800),
        'path'     => '/',
        'secure'   => (bool) ($app['cookie_secure'] ?? true),
        'httponly' => false,
        'samesite' => $app['cookie_samesite'] ?? 'Lax',
    ]);
}

function sd_create_session(int $userId): string
{
    $pdo = \SouthDistrict\API\Database::getConnection();
    $app = sd_config()['app'];
    $token = bin2hex(random_bytes(32));
    $expiresAt = time() + (int) $app['session_lifetime'];

    $stmt = $pdo->prepare(
        'INSERT INTO sessions (id, user_id, expires_at, ip_address, user_agent)
         VALUES (:id, :user_id, FROM_UNIXTIME(:expires_at), :ip, :ua)'
    );
    $stmt->execute([
        'id'         => hash('sha256', $token),
        'user_id'    => $userId,
        'expires_at' => $expiresAt,
        'ip'         => substr(\SouthDistrict\API\Router::clientIp(), 0, 45),
        'ua'         => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
    ]);

    sd_set_session_cookie($token, $expiresAt);

    return $token;
}

function sd_destroy_session(?string $token = null): void
{
    $token ??= $_COOKIE[sd_session_cookie_name()] ?? null;

    if ($token !== null && $token !== '') {
        $pdo = \SouthDistrict\API\Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM sessions WHERE id = :id');
        $stmt->execute(['id' => hash('sha256', $token)]);
    }

    sd_clear_session_cookie();
}

function sd_purge_expired_sessions(): void
{
    \SouthDistrict\API\Database::getConnection()->exec('DELETE FROM sessions WHERE expires_at < NOW()');
}

/** @return array<string, mixed>|null */
function sd_current_user(): ?array
{
    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }
    $cached = true;

    $token = $_COOKIE[sd_session_cookie_name()] ?? '';
    if ($token === '') {
        return null;
    }

    if (empty($_COOKIE['XSRF-TOKEN'])) {
        $app = sd_config()['app'];
        $csrfToken = bin2hex(random_bytes(32));
        setcookie('XSRF-TOKEN', $csrfToken, [
            'expires'  => time() + (int) $app['session_lifetime'],
            'path'     => '/',
            'secure'   => (bool) $app['cookie_secure'],
            'httponly' => false,
            'samesite' => $app['cookie_samesite'],
        ]);
    }

    sd_purge_expired_sessions();

    $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
        'SELECT u.*
         FROM sessions s
         INNER JOIN users u ON u.id = s.user_id
         WHERE s.id = :id AND s.expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute(['id' => hash('sha256', $token)]);
    $row = $stmt->fetch();

    if (!$row) {
        sd_clear_session_cookie();
        return null;
    }

    if (!empty($row['is_banned'])) {
        sd_destroy_session($token);
        return null;
    }

    $normStaff = sd_normalize_staff_role($row['staff_role'] ?? '');
    if (in_array($normStaff, ['createur', 'ceo', 'co_ceo'], true)) {
        if (($row['site_role'] ?? '') !== 'admin' || ($row['staff_role'] ?? '') !== $normStaff) {
            try {
                $upStmt = \SouthDistrict\API\Database::getConnection()->prepare(
                    'UPDATE users SET staff_role = :staff, site_role = "admin" WHERE id = :id'
                );
                $upStmt->execute(['staff' => $normStaff, 'id' => (int) $row['id']]);
                $row['staff_role'] = $normStaff;
                $row['site_role'] = 'admin';
            } catch (Throwable $e) {
                error_log('[Auth Warning] Failed auto-syncing site_role: ' . $e->getMessage());
            }
        }
    }

    $user = $row;
    return $user;
}

/** @return array<string, mixed> */
function sd_require_auth(): array
{
    $user = sd_current_user();
    if ($user === null) {
        \SouthDistrict\API\Router::jsonError('Authentification requise.', 401);
    }

    return $user;
}

/** @return array<string, mixed> */
function sd_require_site_admin(): array
{
    $user = sd_require_auth();
    if (($user['site_role'] ?? '') !== 'admin') {
        \SouthDistrict\API\Router::jsonError('Accès administrateur requis.', 403);
    }

    return $user;
}

/**
 * Normalise une chaîne de rôle staff (minuscules, sans accents, sans espaces parasites).
 */
function sd_normalize_staff_role(?string $role): string
{
    if ($role === null || $role === '') {
        return '';
    }
    $r = mb_strtolower(trim($role), 'UTF-8');
    $r = str_replace(['é', 'è', 'ê', 'ë'], 'e', $r);
    $r = str_replace(['à', 'â', 'ä'], 'a', $r);
    $r = str_replace(['ô', 'ö'], 'o', $r);
    $r = str_replace(['î', 'ï'], 'i', $r);
    $r = str_replace(['ù', 'û', 'ü'], 'u', $r);
    $r = str_replace(['ç'], 'c', $r);
    return $r;
}

/** @return array<string, mixed> */
function sd_require_staff_roles(array $allowedRoles): array
{
    $user = sd_require_auth();
    $normUserRole = sd_normalize_staff_role($user['staff_role'] ?? null);
    $normAllowed = array_map('sd_normalize_staff_role', $allowedRoles);

    if ($normUserRole === '' || !in_array($normUserRole, $normAllowed, true)) {
        \SouthDistrict\API\Router::jsonError('Permissions staff insuffisantes.', 403);
    }

    return $user;
}

function sd_staff_rank(?string $role): int
{
    if ($role === null || $role === '') {
        return 0;
    }

    $norm = sd_normalize_staff_role($role);

    $discordConfig = sd_get_discord_roles_config();
    foreach ($discordConfig as $dRoleId => $dRole) {
        if (!empty($dRole['auto_assign'])) {
            $tRole = !empty($dRole['target_role']) ? (string) $dRole['target_role'] : ('discord_' . (string) $dRoleId);
            if (sd_normalize_staff_role($tRole) === $norm && isset($dRole['order_rank'])) {
                return (int) $dRole['order_rank'];
            }
        }
    }

    foreach (sd_get_staff_role_definitions() as $def) {
        if (sd_normalize_staff_role($def['key'] ?? '') === $norm) {
            return (int) ($def['rank'] ?? 0);
        }
    }

    $legacy = [
        'staff'          => 10,
        'moderateur'     => 20,
        'administrateur' => 30,
        'gerant'         => 40,
        'gerant_staff'   => 40,
        'manager'        => 50,
        'developer'      => 60,
        'co_ceo'         => 70,
        'ceo'            => 80,
        'createur'       => 90,
    ];

    return $legacy[$norm] ?? 0;
}

/**
 * Nettoie le nom d'un rôle Discord en supprimant les caractères décoratifs (», «, ›, ‹, etc.)
 */
function sd_clean_role_name(?string $name): string
{
    if ($name === null || $name === '') {
        return '';
    }
    $decoded = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $cleaned = preg_replace('/^[\s»›>«‹▶►▸•·|―\-–—]+|[\s»›>«‹▶►▸•·|―\-–—]+$/u', '', $decoded);
    $cleaned = trim($cleaned ?? '');
    return $cleaned !== '' ? $cleaned : trim($name);
}

function sd_staff_role_label(?string $role): string
{
    if ($role === null || $role === '') {
        return 'Citoyen';
    }

    $norm = sd_normalize_staff_role($role);

    foreach (sd_get_staff_role_definitions() as $def) {
        if (sd_normalize_staff_role($def['key'] ?? '') === $norm) {
            return sd_clean_role_name((string) ($def['label'] ?? $role));
        }
    }

    $labels = [
        'createur'       => 'Créateur',
        'ceo'            => 'CEO',
        'co_ceo'         => 'Co-CEO',
        'developer'      => 'Developer',
        'manager'        => 'Manager',
        'gerant'         => 'Gérant Staff',
        'gerant_staff'   => 'Gérant Staff',
        'administrateur' => 'Administrateur',
        'moderateur'     => 'Modérateur',
        'staff'          => 'Staff',
    ];

    return sd_clean_role_name($labels[$norm] ?? ucfirst(str_replace('_', ' ', $role)));
}

/**
 * Couleurs par défaut associées à chaque grade staff du site.
 * @return array<string, string>
 */
function sd_default_staff_role_colors(): array
{
    return [
        'createur'       => '#eeae59', // Or / Jaune Discord officiel
        'ceo'            => '#1ee6a0', // Vert néon officiel South District
        'co_ceo'         => '#2dd4bf', // Turquoise
        'developer'      => '#94a3b8', // Gris acier
        'manager'        => '#ef4444', // Rouge
        'gerant_staff'   => '#a855f7', // Violet
        'gerant'         => '#8b5cf6', // Indigo
        'administrateur' => '#f97316', // Orange
        'moderateur'     => '#3b82f6', // Bleu
        'staff'          => '#10b981', // Émeraude
    ];
}

/**
 * Retourne la couleur hexadécimale associée à un rôle staff (priorité aux couleurs Discord en temps réel).
 */
function sd_staff_role_color(?string $role): string
{
    if ($role === null || $role === '') {
        return '#ffffff61';
    }

    $norm = sd_normalize_staff_role($role);

    // Définitions staff
    foreach (sd_get_staff_role_definitions() as $def) {
        if (sd_normalize_staff_role($def['key'] ?? '') === $norm || ($def['key'] ?? '') === $role) {
            $c = (string) ($def['color'] ?? '');
            if ($norm === 'createur' && (strtolower($c) === '#ffffff' || $c === '')) {
                return '#eeae59';
            }
            if ($c !== '' && $c !== '#000000' && strtolower($c) !== '#99aab5') {
                return $c;
            }
        }
    }

    // Configuration des rôles Discord
    $discordConfig = sd_get_discord_roles_config();
    foreach ($discordConfig as $dRoleId => $dRole) {
        $tRole = !empty($dRole['target_role']) ? $dRole['target_role'] : ('discord_' . (string) $dRoleId);
        if (sd_normalize_staff_role($tRole) === $norm || (string) $dRoleId === $role) {
            $c = (string) ($dRole['color'] ?? '');
            if ($norm === 'createur' && (strtolower($c) === '#ffffff' || $c === '')) {
                return '#eeae59';
            }
            if ($c !== '' && $c !== '#000000' && strtolower($c) !== '#99aab5') {
                return $c;
            }
        }
    }

    // Table staff_role_colors
    $colors = sd_get_staff_role_colors(false);
    if (!empty($colors[$norm])) {
        if ($norm === 'createur' && strtolower($colors[$norm]) === '#ffffff') {
            return '#eeae59';
        }
        return $colors[$norm];
    }
    if (!empty($colors[$role])) {
        if ($norm === 'createur' && strtolower($colors[$role]) === '#ffffff') {
            return '#eeae59';
        }
        return $colors[$role];
    }

    $defaults = sd_default_staff_role_colors();
    return $defaults[$norm] ?? '#1ee6a0';
}

/**
 * Récupère la table des couleurs des grades staff enregistrée en base (site_settings.staff_role_colors).
 * @return array<string, string>
 */
function sd_get_staff_role_colors(bool $refreshFromDiscordIfStale = true): array
{
    static $cache = null;
    if ($cache !== null && !$refreshFromDiscordIfStale) {
        return $cache;
    }

    // Auto-actualisation automatique sans devoir aller dans l'espace admin :
    // Si la dernière synchronisation date de plus de 45 secondes, on interroge Discord automatiquement.
    if ($refreshFromDiscordIfStale) {
        try {
            $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
                'SELECT UNIX_TIMESTAMP(updated_at) AS last_up FROM site_settings WHERE setting_key = :k LIMIT 1'
            );
            $stmt->execute(['k' => 'staff_role_colors']);
            $lastUp = (int) ($stmt->fetchColumn() ?: 0);
            if ($lastUp === 0 || (time() - $lastUp) > 45) {
                sd_update_staff_role_colors_from_discord();
            }
        } catch (\Throwable) {
        }
    }

    $defaults = sd_default_staff_role_colors();
    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
            'SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1'
        );
        $stmt->execute(['k' => 'staff_role_colors']);
        $raw = $stmt->fetchColumn();
        if ($raw && is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $cache = array_merge($defaults, $decoded);
                return $cache;
            }
        }
    } catch (\Throwable) {
    }

    $cache = $defaults;
    return $cache;
}

/**
 * Enregistre les couleurs des grades staff dans site_settings.
 * @param array<string, string> $colors
 */
function sd_save_staff_role_colors(array $colors, ?int $userId = null): bool
{
    try {
        $val = json_encode($colors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
            'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
             ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
        );
        return $stmt->execute([
            'k'  => 'staff_role_colors',
            'v'  => $val,
            'v2' => $val,
            'u'  => $userId,
            'u2' => $userId,
        ]);
    } catch (\Throwable) {
        return false;
    }
}

/**
 * Met à jour automatiquement les couleurs des grades staff à partir des rôles Discord du serveur.
 * @return array<string, string> Couleurs mises à jour
 */
function sd_update_staff_role_colors_from_discord(): array
{
    $discordRoles = sd_fetch_discord_guild_roles_detailed();
    if (empty($discordRoles)) {
        return sd_get_staff_role_colors(false);
    }

    $savedConfig = sd_get_discord_roles_config();
    $definedRoles = sd_default_staff_role_definitions();
    $currentColors = sd_get_staff_role_colors(false);
    $updated = false;
    $configUpdated = false;

    foreach ($discordRoles as $r) {
        $rId = (string) ($r['id'] ?? '');
        $color = (string) ($r['color'] ?? '');
        if ($color === '' || $color === '#000000' || strtolower($color) === '#99aab5') {
            continue;
        }

        $targetRole = null;
        if (isset($savedConfig[$rId]) && !empty($savedConfig[$rId]['auto_assign'])) {
            $targetRole = !empty($savedConfig[$rId]['target_role']) ? (string) $savedConfig[$rId]['target_role'] : ('discord_' . $rId);
            if (($savedConfig[$rId]['color'] ?? '') !== $color) {
                $savedConfig[$rId]['color'] = $color;
                $configUpdated = true;
            }
        } else {
            $targetRole = sd_detect_staff_role_from_name($r['name'] ?? '', $definedRoles);
        }

        if ($targetRole !== null && $targetRole !== '') {
            $cleanColor = strtolower($color);
            if ($targetRole === 'createur' && $cleanColor === '#ffffff') {
                $cleanColor = '#eeae59';
            }
            if (!isset($currentColors[$targetRole]) || strtolower($currentColors[$targetRole]) !== $cleanColor) {
                $currentColors[$targetRole] = $cleanColor;
                $updated = true;
            }
        }
    }

    if (!empty($configUpdated)) {
        sd_save_discord_roles_config($savedConfig);
    }

    if ($updated) {
        sd_save_staff_role_colors($currentColors);
    } else {
        try {
            \SouthDistrict\API\Database::getConnection()->prepare(
                'UPDATE site_settings SET updated_at = NOW() WHERE setting_key = "staff_role_colors"'
            )->execute();
        } catch (\Throwable) {
        }
    }

    return $currentColors;
}

/** @return list<array{key:string,label:string,rank:int,locked:bool,color:string}> */
function sd_default_staff_role_definitions(): array
{
    $colors = sd_default_staff_role_colors();
    return [
        ['key' => 'createur', 'label' => 'Créateur', 'rank' => 90, 'locked' => true, 'color' => $colors['createur']],
        ['key' => 'ceo', 'label' => 'CEO', 'rank' => 80, 'locked' => true, 'color' => $colors['ceo']],
        ['key' => 'co_ceo', 'label' => 'Co-CEO', 'rank' => 70, 'locked' => true, 'color' => $colors['co_ceo']],
        ['key' => 'developer', 'label' => 'Developer', 'rank' => 60, 'locked' => true, 'color' => $colors['developer']],
        ['key' => 'manager', 'label' => 'Manager', 'rank' => 50, 'locked' => true, 'color' => $colors['manager']],
        ['key' => 'gerant_staff', 'label' => 'Gérant Staff', 'rank' => 40, 'locked' => true, 'color' => $colors['gerant_staff']],
        ['key' => 'administrateur', 'label' => 'Administrateur', 'rank' => 30, 'locked' => true, 'color' => $colors['administrateur']],
        ['key' => 'moderateur', 'label' => 'Modérateur', 'rank' => 20, 'locked' => true, 'color' => $colors['moderateur']],
        ['key' => 'staff', 'label' => 'Staff', 'rank' => 10, 'locked' => true, 'color' => $colors['staff']],
    ];
}

/** @return list<array{key:string,label:string,rank:int,locked:bool,color:string}> */
function sd_get_staff_role_definitions(bool $refreshCache = false): array
{
    static $cache = null;
    if ($cache !== null && !$refreshCache) {
        return $cache;
    }

    $defaults = sd_default_staff_role_definitions();
    $defaultByKey = [];
    foreach ($defaults as $def) {
        $defaultByKey[$def['key']] = $def;
    }

    $roleColors = sd_get_staff_role_colors();

    // Rôles Discord configurés avec attribution staff active
    try {
        $discordConfig = sd_get_discord_roles_config();
        $activeDiscordRoles = [];
        foreach ($discordConfig as $dId => $dConf) {
            if (!empty($dConf['auto_assign'])) {
                $activeDiscordRoles[] = [
                    'id'         => (string) $dId,
                    'name'       => trim((string) ($dConf['name'] ?? '')),
                    'color'      => trim((string) ($dConf['color'] ?? '')),
                    'order_rank' => isset($dConf['order_rank']) ? (int) $dConf['order_rank'] : 0,
                    'target_role'=> trim((string) ($dConf['target_role'] ?? '')),
                ];
            }
        }

        if (!empty($activeDiscordRoles)) {
            usort($activeDiscordRoles, static function (array $a, array $b): int {
                $rankA = (int) ($a['order_rank'] ?? 0);
                $rankB = (int) ($b['order_rank'] ?? 0);
                if ($rankA !== $rankB) {
                    return $rankB <=> $rankA;
                }
                return strcmp($a['name'], $b['name']);
            });

            $discordDefs = [];
            $seenKeys = [];
            foreach ($activeDiscordRoles as $idx => $r) {
                $key = $r['target_role'] !== '' ? strtolower(trim($r['target_role'])) : ('discord_' . $r['id']);
                $key = preg_replace('/[^a-z0-9_]/', '', $key) ?? '';
                if ($key === '') {
                    $key = 'discord_' . $r['id'];
                }
                if (isset($seenKeys[$key])) {
                    $key = 'discord_' . $r['id'];
                }
                $seenKeys[$key] = true;

                $label = $r['name'] !== '' ? $r['name'] : ($defaultByKey[$key]['label'] ?? $key);
                $color = (!empty($r['color']) && $r['color'] !== '#000000') ? $r['color'] : ($roleColors[$key] ?? '#1ee6a0');
                $rank = $r['order_rank'] > 0 ? $r['order_rank'] : max(1, 100 - ($idx * 5));
                $locked = in_array($key, ['createur', 'ceo', 'co_ceo', 'developer'], true);

                $discordDefs[] = [
                    'key'    => $key,
                    'label'  => mb_substr($label, 0, 64),
                    'rank'   => $rank,
                    'locked' => $locked,
                    'color'  => $color,
                ];
            }

            foreach ($defaults as $def) {
                if ($def['locked'] && !isset($seenKeys[$def['key']])) {
                    $def['color'] = $roleColors[$def['key']] ?? $def['color'];
                    $discordDefs[] = $def;
                    $seenKeys[$def['key']] = true;
                }
            }

            $cache = $discordDefs;
            return $cache;
        }
    } catch (\Throwable) {
        // Fallback
    }

    $result = [];
    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => 'staff_role_definitions']);
        $row = $stmt->fetch();
        if ($row) {
            $decoded = json_decode($row['setting_value'], true);
            if (is_array($decoded) && $decoded !== []) {
                $seenKeys = [];
                foreach ($decoded as $idx => $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $key = strtolower(trim((string) ($item['key'] ?? '')));
                    $key = preg_replace('/[^a-z0-9_]/', '', $key) ?? '';
                    if ($key === '' || strlen($key) > 48 || isset($seenKeys[$key])) {
                        continue;
                    }
                    $label = trim((string) ($item['label'] ?? ''));
                    if ($label === '' && isset($defaultByKey[$key])) {
                        $label = $defaultByKey[$key]['label'];
                    }
                    if ($label === '') {
                        continue;
                    }
                    $rank = isset($item['rank']) ? (int) $item['rank'] : max(1, 100 - ($idx * 2));
                    $locked = !empty($item['locked']) || !empty($defaultByKey[$key]['locked']);
                    $color = $item['color'] ?? ($roleColors[$key] ?? ($defaultByKey[$key]['color'] ?? '#1ee6a0'));
                    $result[] = [
                        'key'    => $key,
                        'label'  => mb_substr($label, 0, 64),
                        'rank'   => $rank,
                        'locked' => $locked,
                        'color'  => $color,
                    ];
                    $seenKeys[$key] = true;
                }

                foreach ($defaults as $def) {
                    if (!isset($seenKeys[$def['key']])) {
                        $def['color'] = $roleColors[$def['key']] ?? $def['color'];
                        $result[] = $def;
                        $seenKeys[$def['key']] = true;
                    }
                }

                $cache = $result;
                return $cache;
            }
        }
    } catch (Throwable) {
        /* defaults */
    }

    $cache = $defaults;
    return $cache;
}

/** @return list<string> */
function sd_staff_role_keys(): array
{
    return array_map(static fn(array $def): string => $def['key'], sd_get_staff_role_definitions());
}

/** @param list<array<string, mixed>> $roles */
function sd_normalize_staff_role_definitions(array $roles): array
{
    $defaults = sd_default_staff_role_definitions();
    $defaultByKey = [];
    foreach ($defaults as $def) {
        $defaultByKey[$def['key']] = $def;
    }

    $normalized = [];
    $seenKeys = [];

    foreach ($roles as $idx => $item) {
        if (!is_array($item)) {
            continue;
        }
        $key = strtolower(trim((string) ($item['key'] ?? '')));
        $key = preg_replace('/[^a-z0-9_]/', '', $key) ?? '';
        if ($key === '' || strlen($key) > 48 || isset($seenKeys[$key])) {
            continue;
        }
        if (in_array($key, SD_SITE_ROLES, true)) {
            continue;
        }
        $label = trim((string) ($item['label'] ?? ''));
        if ($label === '' && isset($defaultByKey[$key])) {
            $label = $defaultByKey[$key]['label'];
        }
        if ($label === '') {
            continue;
        }

        $rank = max(1, 100 - ($idx * 2));
        $locked = !empty($defaultByKey[$key]['locked']);
        $roleColors = sd_get_staff_role_colors();
        $color = $item['color'] ?? ($roleColors[$key] ?? ($defaultByKey[$key]['color'] ?? '#1ee6a0'));

        $normalized[] = [
            'key'    => $key,
            'label'  => mb_substr($label, 0, 64),
            'rank'   => $rank,
            'locked' => $locked,
            'color'  => $color,
        ];
        $seenKeys[$key] = true;
    }

    foreach ($defaults as $def) {
        if (!isset($seenKeys[$def['key']])) {
            $normalized[] = $def;
            $seenKeys[$def['key']] = true;
        }
    }

    return $normalized;
}

function sd_is_master_ceo(array $user): bool
{
    return !empty($user['is_master_ceo']);
}

function sd_is_protected_account(array $target, array $actor): bool
{
    if ((int) ($actor['id'] ?? 0) === (int) ($target['id'] ?? 0)) {
        return false;
    }

    $actorRole = strtolower(trim((string) ($actor['staff_role'] ?? '')));
    $actorRole = str_replace(['é', 'è', 'ê', 'ë'], 'e', $actorRole);
    $targetRole = strtolower(trim((string) ($target['staff_role'] ?? '')));
    $targetRole = str_replace(['é', 'è', 'ê', 'ë'], 'e', $targetRole);

    if ($actorRole === 'createur') {
        return $targetRole === 'createur';
    }

    if (!empty($target['is_master_ceo'])) {
        return true;
    }

    return in_array($targetRole, ['createur', 'ceo'], true);
}

function sd_can_manage_role_definitions(array $user): bool
{
    if (sd_is_master_ceo($user) || ($user['site_role'] ?? '') === 'admin') {
        return true;
    }
    $role = strtolower(trim((string) ($user['staff_role'] ?? '')));
    $role = str_replace(['é', 'è', 'ê', 'ë'], 'e', $role);
    if (in_array($role, ['createur', 'ceo', 'co_ceo', 'developer', 'developpeur', 'equipe_technique', 'equipe technique'], true)) {
        return true;
    }
    return sd_user_has_permission($user, 'manage_permissions');
}

/** @param array<string, mixed> $custom */
function sd_migrate_permissions_legacy(array $custom): array
{
    if (!empty($custom['edit_reglement'])) {
        $custom['edit_reglement_serveur'] = $custom['edit_reglement_serveur'] ?? $custom['edit_reglement'];
        $custom['edit_reglement_staff'] = $custom['edit_reglement_staff'] ?? $custom['edit_reglement'];
    }
    if (!empty($custom['edit_patchnotes'])) {
        $custom['publish_patchnotes'] = $custom['publish_patchnotes'] ?? $custom['edit_patchnotes'];
        $custom['manage_patchnotes'] = $custom['manage_patchnotes'] ?? $custom['edit_patchnotes'];
    }
    if (!empty($custom['manage_recruitment'])) {
        $custom['manage_gerant_staff'] = $custom['manage_gerant_staff'] ?? $custom['manage_recruitment'];
    }
    if (!empty($custom['manage_staff'])) {
        $custom['manage_gerant_staff'] = true;
    }
    if (!empty($custom['manage_roles'])) {
        $custom['manage_permissions'] = $custom['manage_permissions'] ?? $custom['manage_roles'];
    }

    return $custom;
}

/**
 * Définitions et descriptions détaillées des permissions du site.
 * @return array<string, array{label:string, description:string, category:string, icon:string}>
 */
function sd_permission_definitions(): array
{
    return [
        'edit_reglement_serveur' => [
            'label'       => 'Règlement Serveur',
            'description' => 'Modifier et publier le règlement officiel du serveur.',
            'category'    => 'Contenu',
            'icon'        => '📜',
        ],
        'edit_reglement_staff'   => [
            'label'       => 'Règlement Staff',
            'description' => 'Modifier et publier le règlement interne de l\'équipe staff.',
            'category'    => 'Contenu',
            'icon'        => '📋',
        ],
        'publish_patchnotes'     => [
            'label'       => 'Publier Patch-Notes',
            'description' => 'Créer et publier de nouveaux patch-notes de mises à jour.',
            'category'    => 'Mises à jour',
            'icon'        => '🚀',
        ],
        'manage_patchnotes'      => [
            'label'       => 'Gérer Patch-Notes',
            'description' => 'Modifier, réordonner et supprimer les patch-notes existants.',
            'category'    => 'Mises à jour',
            'icon'        => '📝',
        ],
        'manage_gerant_staff'    => [
            'label'       => 'Recrutement & Staff',
            'description' => 'Consulter et traiter les candidatures, gérer les absences staff et paramètres recrutement.',
            'category'    => 'Gestion Staff',
            'icon'        => '👥',
        ],
        'manage_map'             => [
            'label'       => 'Carte Entreprises',
            'description' => 'Placer des pings, modifier les informations des entreprises et définir le spawn global.',
            'category'    => 'Serveur',
            'icon'        => '🗺️',
        ],
        'view_logs'              => [
            'label'       => 'Logs & Audit',
            'description' => 'Consulter l\'historique d\'activité et les journaux de sécurité du site.',
            'category'    => 'Administration',
            'icon'        => '🔍',
        ],
        'manage_permissions'     => [
            'label'       => 'Gestion Permissions',
            'description' => 'Configurer la hiérarchie des rôles staff, les permissions et les accès des membres.',
            'category'    => 'Administration',
            'icon'        => '⚙️',
        ],
    ];
}

/**
 * Permissions par défaut associées à chaque rôle staff du site.
 * @return array<string, array<string, bool>>
 */
function sd_default_role_permissions(): array
{
    $allTrue = [];
    foreach (SD_PERMISSION_KEYS as $k) {
        $allTrue[$k] = true;
    }

    $allFalse = [];
    foreach (SD_PERMISSION_KEYS as $k) {
        $allFalse[$k] = false;
    }

    return [
        'createur'       => $allTrue,
        'ceo'            => $allTrue,
        'co_ceo'         => $allTrue,
        'developer'      => array_merge($allFalse, [
            'edit_reglement_serveur' => true,
            'publish_patchnotes'     => true,
            'manage_patchnotes'      => true,
            'manage_map'             => true,
            'view_logs'              => true,
            'manage_permissions'     => true,
        ]),
        'manager'        => array_merge($allFalse, [
            'edit_reglement_staff' => true,
            'manage_gerant_staff'  => true,
            'manage_map'           => true,
            'view_logs'            => true,
        ]),
        'gerant'         => array_merge($allFalse, [
            'manage_gerant_staff' => true,
            'view_logs'           => true,
        ]),
        'gerant_staff'   => array_merge($allFalse, [
            'edit_reglement_staff' => true,
            'manage_gerant_staff'  => true,
            'view_logs'            => true,
        ]),
        'administrateur' => array_merge($allFalse, [
            'edit_reglement_serveur' => true,
            'publish_patchnotes'     => true,
            'manage_gerant_staff'    => true,
            'view_logs'              => true,
        ]),
        'moderateur'     => array_merge($allFalse, [
            'manage_gerant_staff' => true,
        ]),
        'staff'          => array_merge($allFalse, [
            'manage_gerant_staff' => true,
        ]),
    ];
}

/**
 * Récupère la matrice des permissions par rôle stockée en BDD (site_settings.role_permissions).
 * @return array<string, array<string, bool>>
 */
function sd_get_role_permissions_config(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $defaults = sd_default_role_permissions();
    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
            'SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1'
        );
        $stmt->execute(['k' => 'role_permissions']);
        $raw = $stmt->fetchColumn();
        if ($raw && is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                // Compléter avec les rôles par défaut manquants
                foreach ($defaults as $roleKey => $defaultPerms) {
                    if (!isset($decoded[$roleKey]) || !is_array($decoded[$roleKey])) {
                        $decoded[$roleKey] = $defaultPerms;
                    } else {
                        foreach ($defaultPerms as $permKey => $defVal) {
                            if (!isset($decoded[$roleKey][$permKey])) {
                                $decoded[$roleKey][$permKey] = $defVal;
                            }
                        }
                    }
                }
                $cache = $decoded;
                return $cache;
            }
        }
    } catch (\Throwable) {
        // En cas d'erreur SQL, utiliser les valeurs par défaut
    }

    $cache = $defaults;
    return $cache;
}

/**
 * Enregistre la matrice des permissions par rôle dans site_settings.
 */
function sd_save_role_permissions_config(array $config, ?int $userId = null): bool
{
    // Sécurité : Les rôles directeurs et développeur conservent obligatoirement toutes les permissions
    foreach (['createur', 'ceo', 'co_ceo', 'developer'] as $protectedKey) {
        if (!isset($config[$protectedKey]) || !is_array($config[$protectedKey])) {
            $config[$protectedKey] = [];
        }
        foreach (SD_PERMISSION_KEYS as $pk) {
            $config[$protectedKey][$pk] = true;
        }
    }

    $val = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    return $stmt->execute([
        'k'  => 'role_permissions',
        'v'  => $val,
        'v2' => $val,
        'u'  => $userId,
        'u2' => $userId,
    ]);
}

/** @return array<string, bool> */
function sd_user_permissions(array $user): array
{
    $perms = [];
    foreach (SD_PERMISSION_KEYS as $key) {
        $perms[$key] = false;
    }

    if (!empty($user['is_banned'])) {
        return $perms;
    }

    $userRoles = [];
    if (!empty($user['staff_role'])) {
        $userRoles[] = (string) $user['staff_role'];
    }
    if (!empty($user['secondary_roles'])) {
        $secondaries = is_string($user['secondary_roles'])
            ? json_decode($user['secondary_roles'], true)
            : $user['secondary_roles'];
        if (is_array($secondaries)) {
            foreach ($secondaries as $sr) {
                if (is_string($sr) && $sr !== '') {
                    $userRoles[] = $sr;
                }
            }
        }
    }
    $userRoles = array_values(array_unique($userRoles));

    // Application des permissions configurées pour chaque rôle
    $rolePermsConfig = sd_get_role_permissions_config();
    foreach ($userRoles as $rKey) {
        $normRKey = strtolower(trim((string) $rKey));
        $normRKey = str_replace(['é', 'è', 'ê', 'ë'], 'e', $normRKey);
        if (in_array($normRKey, ['createur', 'ceo', 'co_ceo', 'developer', 'developpeur', 'equipe_technique', 'equipe technique'], true)) {
            foreach (SD_PERMISSION_KEYS as $k) {
                $perms[$k] = true;
            }
            break;
        }

        if (isset($rolePermsConfig[$rKey]) && is_array($rolePermsConfig[$rKey])) {
            foreach (SD_PERMISSION_KEYS as $k) {
                if (!empty($rolePermsConfig[$rKey][$k])) {
                    $perms[$k] = true;
                }
            }
        }
    }

    if (($user['site_role'] ?? '') === 'admin' && empty($userRoles)) {
        foreach (SD_PERMISSION_KEYS as $k) {
            $perms[$k] = true;
        }
    }

    // Surcharges individuelles au niveau du compte
    if (!empty($user['permissions'])) {
        $custom = is_string($user['permissions'])
            ? json_decode($user['permissions'], true)
            : $user['permissions'];
        if (is_array($custom)) {
            $custom = sd_migrate_permissions_legacy($custom);
            foreach (SD_PERMISSION_KEYS as $key) {
                if (array_key_exists($key, $custom)) {
                    $perms[$key] = !empty($custom[$key]);
                }
            }
        }
    }

    // Droits complets pour les administrateurs et développeurs
    $normRole = strtolower(trim((string) ($user['staff_role'] ?? '')));
    $normRole = str_replace(['é', 'è', 'ê', 'ë'], 'e', $normRole);
    if (sd_is_master_ceo($user) || ($user['site_role'] ?? '') === 'admin' || in_array($normRole, ['createur', 'ceo', 'co_ceo', 'developer', 'developpeur', 'equipe_technique', 'equipe technique'], true)) {
        foreach (SD_PERMISSION_KEYS as $k) {
            $perms[$k] = true;
        }
    }

    return $perms;
}

function sd_user_has_permission(array $user, string $permission): bool
{
    $perms = sd_user_permissions($user);
    return !empty($perms[$permission]);
}

function sd_can_access_admin(array $user): bool
{
    if (!empty($user['is_banned'])) {
        return false;
    }
    return ($user['site_role'] ?? '') === 'admin'
        || sd_staff_rank($user['staff_role'] ?? null) > 0
        || sd_is_master_ceo($user);
}

function sd_is_staff_visible(array $user): bool
{
    if (array_key_exists('is_staff_visible', $user)) {
        return !empty($user['is_staff_visible']);
    }
    return sd_staff_rank($user['staff_role'] ?? null) > 0;
}

/** @param array<string, mixed> $user */
function sd_public_user(array $user): array
{
    $secondaryRoles = [];
    if (!empty($user['secondary_roles'])) {
        $decoded = is_string($user['secondary_roles'])
            ? json_decode($user['secondary_roles'], true)
            : $user['secondary_roles'];
        if (is_array($decoded)) {
            $secondaryRoles = array_values(array_filter($decoded, 'is_string'));
        }
    }

    $secondaryLabels = array_map('sd_staff_role_label', $secondaryRoles);

    $normStaff = sd_normalize_staff_role($user['staff_role'] ?? '');
    $publicStaffRole = in_array($normStaff, ['createur', 'ceo', 'co_ceo'], true) ? $normStaff : ($user['staff_role'] ?? null);
    $publicSiteRole = $user['site_role'] ?? 'member';
    if (in_array($normStaff, ['createur', 'ceo', 'co_ceo', 'developer', 'developpeur', 'equipe_technique', 'equipe technique'], true) || sd_is_master_ceo($user)) {
        $publicSiteRole = 'admin';
    }

    return [
        'id'                   => (int) $user['id'],
        'login'                => $user['login'],
        'pseudo'               => $user['pseudo'],
        'email'                => $user['email'],
        'site_role'            => $publicSiteRole,
        'staff_role'           => $publicStaffRole,
        'staff_label'          => sd_staff_role_label($user['staff_role'] ?? null),
        'staff_title'          => $user['staff_title'] ?? null,
        'secondary_roles'      => $secondaryRoles,
        'secondary_labels'     => $secondaryLabels,
        'is_staff_visible'     => !empty($user['is_staff_visible']),
        'avatar'               => sd_avatar_public_url($user),
        'has_avatar'           => !empty($user['avatar_mime']) || !empty($user['avatar']),
        'signature'            => sd_public_asset_path($user['signature'] ?? null) ?? ($user['signature'] ?? null),
        'has_signature'        => !empty($user['signature']),
        'permissions'          => sd_user_permissions($user),
        'is_master_ceo'        => sd_is_master_ceo($user),
        'is_banned'            => !empty($user['is_banned']),
        'banned_at'            => $user['banned_at'] ?? null,
        'ban_reason'           => $user['ban_reason'] ?? null,
        'has_discord'          => !empty($user['discord_id']),
        'discord_id'           => $user['discord_id'] ?? null,
        'discord_last_sync_at' => $user['discord_last_sync_at'] ?? null,
        'created_at'           => $user['created_at'],
        'updated_at'           => $user['updated_at'],
    ];
}

/**
 * Rafraîchit le jeton d'accès Discord OAuth2 à l'aide du refresh_token.
 */
function sd_refresh_discord_token(int $userId, string $refreshToken): ?string
{
    $discord = sd_config()['discord'] ?? null;
    if (!$discord || empty($discord['client_id']) || empty($discord['client_secret'])) {
        return null;
    }

    $tokenUrl = 'https://discord.com/api/oauth2/token';
    $tokenFields = [
        'client_id'     => $discord['client_id'],
        'client_secret' => $discord['client_secret'],
        'grant_type'    => 'refresh_token',
        'refresh_token' => $refreshToken,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return null;
    }

    $tokenData = json_decode($response, true);
    $newAccessToken = $tokenData['access_token'] ?? null;
    $newRefreshToken = $tokenData['refresh_token'] ?? $refreshToken;
    $expiresIn = (int) ($tokenData['expires_in'] ?? 604800);

    if (!$newAccessToken) {
        return null;
    }

    $pdo = \SouthDistrict\API\Database::getConnection();
    $stmt = $pdo->prepare(
        'UPDATE users 
         SET discord_access_token = :at,
             discord_refresh_token = :rt,
             discord_token_expires_at = DATE_ADD(NOW(), INTERVAL :exp SECOND),
             updated_at = NOW()
         WHERE id = :id'
    );
    $stmt->execute([
        'at'  => $newAccessToken,
        'rt'  => $newRefreshToken,
        'exp' => $expiresIn,
        'id'  => $userId,
    ]);

    return $newAccessToken;
}

/**
 * Synchronise les rôles du serveur Discord de l'utilisateur avec son grade staff (principal + secondaires) sur le site web.
 * Utilise le token bot (Bot <token>) pour appeler GET /guilds/{guild}/members/{discord_id}.
 * Ne dépend plus du token OAuth utilisateur ni du refresh token.
 *
 * @param int         $userId      ID de l'utilisateur
 * @param string|null $accessToken Ignoré — conservé pour compatibilité des appelants existants
 * @param bool        $force       Forcer la synchronisation même si effectuée récemment (< 15 min)
 * @return array{staff_role:?string,secondary_roles:list<string>,changed:bool}|null
 */
function sd_sync_discord_user_roles(int $userId, ?string $accessToken = null, bool $force = false): ?array
{
    $discordConfig = sd_config()['discord'] ?? [];
    $guildId  = (string) ($discordConfig['guild_id']  ?? '1505972908042747924');
    $botToken = (string) ($discordConfig['bot_token'] ?? '');
    $roleMapping = $discordConfig['role_mapping'] ?? [];

    if ($guildId === '' || $botToken === '' || $botToken === 'votre_token_copié_ici') {
        return null;
    }

    $pdo  = \SouthDistrict\API\Database::getConnection();
    $stmt = $pdo->prepare(
        'SELECT id, pseudo, login, staff_role, secondary_roles, site_role, is_master_ceo, discord_id,
                discord_last_sync_at
         FROM users
         WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user || empty($user['discord_id'])) {
        return null;
    }

    // Cooldown de 15 minutes pour éviter le spam d'API si non forcé
    if (!$force && !empty($user['discord_last_sync_at'])) {
        $lastSyncTime = strtotime($user['discord_last_sync_at']);
        if ($lastSyncTime && (time() - $lastSyncTime) < 900) {
            return [
                'staff_role'      => $user['staff_role'],
                'secondary_roles' => !empty($user['secondary_roles']) ? (json_decode($user['secondary_roles'], true) ?: []) : [],
                'changed'         => false,
            ];
        }
    }

    $discordId = (string) $user['discord_id'];

    // Appel bot : GET /guilds/{guild_id}/members/{discord_id}
    $url = 'https://discord.com/api/v10/guilds/' . urlencode($guildId) . '/members/' . urlencode($discordId);
    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bot ' . $botToken,
        'User-Agent: SouthDistrictBot (https://south-district.fr, 1.0)',
    ]);
    $res          = curl_exec($ch);
    $lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $userDiscordRoles = [];
    $memberFound      = false;

    if ($lastHttpCode === 200 && $res) {
        $data = json_decode($res, true);
        if (is_array($data['roles'] ?? null)) {
            $userDiscordRoles = $data['roles'];
            $memberFound      = true;
        }
    }

    // Fail-safe : erreur serveur Discord (5xx) ou réseau → ne rien modifier
    if ($lastHttpCode >= 500 || $lastHttpCode === 0) {
        return null;
    }

    $oldStaffRole = $user['staff_role'] ?? null;
    $oldSecondaryRoles = !empty($user['secondary_roles']) ? (json_decode($user['secondary_roles'], true) ?: []) : [];
    $normOldStaff = sd_normalize_staff_role($oldStaffRole);

    // Si le membre n'a pas été trouvé (ex: 404 sur tous les serveurs), ne jamais toucher aux directeurs ni aux développeurs
    if (!$memberFound) {
        if (in_array($normOldStaff, ['createur', 'ceo', 'co_ceo', 'developer', 'developpeur', 'equipe_technique', 'equipe technique'], true) || sd_is_master_ceo($user)) {
            return [
                'staff_role'      => $normOldStaff,
                'secondary_roles' => $oldSecondaryRoles,
                'changed'         => false,
            ];
        }
        // Pour les autres membres, si Discord est inaccessible ou code non-200, ne rien modifier
        if ($lastHttpCode !== 404) {
            return null;
        }
    }

    // Convertir les rôles Discord en chaînes
    $userRolesStr = array_map(static fn($r): string => (string) $r, $userDiscordRoles);

    // Collecter TOUS les grades du site associés aux rôles Discord de l'utilisateur
    $matchedSiteRoleKeys = [];
    $discordRolesConfig = sd_get_discord_roles_config();

    foreach ($userRolesStr as $dRoleId) {
        $assigned = false;
        // 1. Priorité : configuration enregistrée dans site_settings (discord_roles_config)
        if (isset($discordRolesConfig[$dRoleId])) {
            $conf = $discordRolesConfig[$dRoleId];
            if (!empty($conf['auto_assign'])) {
                $matchedSiteRoleKeys[] = !empty($conf['target_role']) ? (string) $conf['target_role'] : ('discord_' . $dRoleId);
                $assigned = true;
            }
        }

        // 2. Fallback direct sur role_mapping de config.php (OAUTH SANS BOT / configuration statique)
        if (!$assigned && isset($roleMapping[$dRoleId])) {
            $matchedSiteRoleKeys[] = (string) $roleMapping[$dRoleId];
            $assigned = true;
        }
    }

    // 3. Détection automatique par nom SEULEMENT pour les rôles non configurés (si bot configuré)
    $botToken = $discordConfig['bot_token'] ?? '';
    if (!empty($botToken) && $botToken !== 'votre_token_copié_ici') {
        $unconfiguredRoles = array_filter(
            $userRolesStr,
            static fn(string $id): bool => !isset($discordRolesConfig[$id]) && !isset($roleMapping[$id])
        );

        if (!empty($unconfiguredRoles)) {
            $guildRoles = sd_fetch_discord_guild_roles();
            if (!empty($guildRoles)) {
                $definedRoles = sd_get_staff_role_definitions();
                foreach ($unconfiguredRoles as $dRoleId) {
                    if (isset($guildRoles[$dRoleId])) {
                        $detected = sd_detect_staff_role_from_name($guildRoles[$dRoleId], $definedRoles);
                        if ($detected !== null && !in_array($detected, $matchedSiteRoleKeys, true)) {
                            $matchedSiteRoleKeys[] = $detected;
                        }
                    }
                }
            }
        }
    }

    $matchedSiteRoleKeys = array_values(array_unique($matchedSiteRoleKeys));

    $primaryStaffRole = null;
    $secondaryStaffRoles = [];

    if ($matchedSiteRoleKeys !== []) {
        $definedRoles = sd_get_staff_role_definitions();
        // Trier les rôles correspondants selon la hiérarchie du site
        $orderedMatches = [];
        foreach ($definedRoles as $def) {
            if (in_array($def['key'], $matchedSiteRoleKeys, true)) {
                $orderedMatches[] = $def['key'];
            }
        }
        // Pour les rôles non listés dans definitions
        foreach ($matchedSiteRoleKeys as $k) {
            if (!in_array($k, $orderedMatches, true)) {
                $orderedMatches[] = $k;
            }
        }

        if ($orderedMatches !== []) {
            // Le premier rôle ayant le rang le plus élevé est le rôle principal
            $primaryStaffRole = $orderedMatches[0];
            // Tous les rôles suivants sont les rôles secondaires (double-rôle / multi-rôles)
            $secondaryStaffRoles = array_values(array_slice($orderedMatches, 1));
        }
    } else {
        // Préservation des rôles de direction et techniques
        if (in_array($normOldStaff, ['createur', 'ceo', 'co_ceo', 'developer', 'developpeur', 'equipe_technique', 'equipe technique'], true) || sd_is_master_ceo($user)) {
            $primaryStaffRole = $normOldStaff;
            $secondaryStaffRoles = $oldSecondaryRoles;
        } elseif (!$memberFound) {
            $primaryStaffRole = $oldStaffRole;
            $secondaryStaffRoles = $oldSecondaryRoles;
        }
    }

    $secondaryJson = json_encode($secondaryStaffRoles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $adminSiteRoles = ['createur', 'ceo', 'co_ceo', 'developer'];
    // Les directeurs et Master CEO conservent toujours leur site_role 'admin'
    $newSiteRole = (sd_is_master_ceo($user) || in_array($primaryStaffRole, $adminSiteRoles, true) || ($user['site_role'] ?? '') === 'admin') ? 'admin' : 'citoyen';
    $isStaffVisible = $primaryStaffRole !== null ? 1 : 0;

    $upd = $pdo->prepare(
        'UPDATE users 
         SET staff_role = :staff_role, 
             secondary_roles = :secondary_roles,
             site_role = :site_role, 
             is_staff_visible = :is_staff_visible, 
             discord_last_sync_at = NOW(),
             updated_at = NOW() 
         WHERE id = :id'
    );
    $upd->execute([
        'staff_role'       => $primaryStaffRole,
        'secondary_roles'  => $secondaryJson,
        'site_role'        => $newSiteRole,
        'is_staff_visible' => $isStaffVisible,
        'id'               => $userId,
    ]);

    $changed = ($oldStaffRole !== $primaryStaffRole) || ($oldSecondaryRoles !== $secondaryStaffRoles);

    if ($changed) {
        $primaryLabel = $primaryStaffRole ? sd_staff_role_label($primaryStaffRole) : 'Aucun (Citoyen)';
        $secondaryLabelsStr = $secondaryStaffRoles !== []
            ? ' + ' . implode(', ', array_map('sd_staff_role_label', $secondaryStaffRoles))
            : '';

        $details = !$memberFound
            ? "Départ du serveur Discord détecté · Retrait de tous les accès staff"
            : "Rôle principal : '{$primaryLabel}'{$secondaryLabelsStr}";

        sd_log_site_event(
            $user,
            'discord_role_sync',
            "Synchronisation Discord : '{$primaryLabel}'{$secondaryLabelsStr}",
            'auth',
            "Utilisateur : {$user['pseudo']} (@{$user['login']}) — {$details}"
        );
    }

    return [
        'staff_role'      => $primaryStaffRole,
        'secondary_roles' => $secondaryStaffRoles,
        'changed'         => $changed,
    ];
}

function sd_validate_login(string $login): ?string
{
    $login = trim($login);
    if ($login === '' || strlen($login) < 3 || strlen($login) > 64) {
        return 'Identifiant invalide (3–64 caractères).';
    }
    if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $login)) {
        return 'Identifiant : lettres, chiffres, _, . et - uniquement.';
    }

    return null;
}

function sd_validate_password(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Mot de passe : minimum 8 caractères.';
    }

    return null;
}

function sd_recruitment_open(): bool
{
    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => 'recruitment_open']);
        $row = $stmt->fetch();
        if (!$row) {
            return true;
        }
        $val = json_decode($row['setting_value'], true);

        return $val !== false;
    } catch (Throwable) {
        return true;
    }
}

/** @return list<string> */
function sd_recruitment_excluded_role_keys(): array
{
    return ['createur', 'ceo', 'co_ceo'];
}

/** @return list<string> */
function sd_default_recruitment_open_roles(): array
{
    return array_column(sd_default_recruitment_postes(), 'key');
}

/** @return list<array{key:string,label:string}> */
function sd_default_recruitment_postes(): array
{
    return [
        ['key' => 'moderateur', 'label' => 'Modérateur'],
        ['key' => 'staff', 'label' => 'Staff'],
    ];
}

function sd_slugify_recruitment_key(string $label): string
{
    $s = strtolower(trim($label));
    if (function_exists('transliterator_transliterate')) {
        $s = transliterator_transliterate('Any-Latin; Latin-ASCII', $s) ?: $s;
    } else {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if (is_string($ascii) && $ascii !== '') {
            $s = $ascii;
        }
    }
    $s = preg_replace('/[^a-z0-9_]+/', '_', $s) ?? '';
    $s = trim($s, '_');
    if ($s === '' || in_array($s, sd_recruitment_excluded_role_keys(), true)) {
        $s = 'poste_' . bin2hex(random_bytes(4));
    }

    return substr($s, 0, 48);
}

/** @param list<mixed> $items @return list<array{key:string,label:string}> */
function sd_normalize_recruitment_postes(array $items): array
{
    $excluded = array_flip(sd_recruitment_excluded_role_keys());
    $staffLabels = [];
    foreach (sd_get_staff_role_definitions() as $def) {
        $staffLabels[$def['key']] = $def['label'];
    }

    $out = [];
    $seen = [];

    foreach ($items as $item) {
        if (is_string($item)) {
            $key = strtolower(trim($item));
            $key = preg_replace('/[^a-z0-9_]/', '', $key) ?? '';
            $label = $staffLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
        } elseif (is_array($item)) {
            $key = strtolower(trim((string) ($item['key'] ?? '')));
            $key = preg_replace('/[^a-z0-9_]/', '', $key) ?? '';
            $label = trim((string) ($item['label'] ?? ''));
            if ($key === '' && $label !== '') {
                $key = sd_slugify_recruitment_key($label);
            }
            if ($label === '' && $key !== '' && isset($staffLabels[$key])) {
                $label = $staffLabels[$key];
            }
            if ($label === '' && $key !== '') {
                $label = ucfirst(str_replace('_', ' ', $key));
            }
        } else {
            continue;
        }

        if ($key === '' || $label === '' || isset($excluded[$key])) {
            continue;
        }
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = [
            'key'   => substr($key, 0, 48),
            'label' => mb_substr($label, 0, 64),
        ];
    }

    return $out;
}

/** @param list<mixed> $keys */
function sd_normalize_recruitment_open_roles(array $keys): array
{
    return array_column(sd_normalize_recruitment_postes($keys), 'key');
}

/** @return list<array{key:string,label:string}> */
function sd_get_recruitment_postes(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $defaults = sd_default_recruitment_postes();

    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');

        $stmt->execute(['k' => 'recruitment_postes']);
        $row = $stmt->fetch();
        if ($row) {
            $decoded = json_decode($row['setting_value'], true);
            if (is_array($decoded)) {
                $normalized = sd_normalize_recruitment_postes($decoded);
                $cache = $normalized !== [] ? $normalized : $defaults;

                return $cache;
            }
        }

        $stmt->execute(['k' => 'recruitment_open_roles']);
        $row = $stmt->fetch();
        if ($row) {
            $decoded = json_decode($row['setting_value'], true);
            if (is_array($decoded)) {
                $legacy = [];
                foreach ($decoded as $key) {
                    $legacy[] = ['key' => (string) $key, 'label' => ''];
                }
                $normalized = sd_normalize_recruitment_postes($legacy);
                $cache = $normalized !== [] ? $normalized : $defaults;

                return $cache;
            }
        }
    } catch (Throwable) {
        /* defaults */
    }

    $cache = $defaults;

    return $cache;
}

/** @return list<string> */
function sd_get_recruitment_open_roles(): array
{
    return array_column(sd_get_recruitment_postes(), 'key');
}

/** @return list<array{key:string,label:string,rank:int}> */
function sd_get_recruitment_poste_options(): array
{
    $defs = [];
    foreach (sd_get_staff_role_definitions() as $def) {
        $defs[$def['key']] = $def;
    }

    $options = [];
    foreach (sd_get_recruitment_postes() as $poste) {
        $rank = isset($defs[$poste['key']]) ? (int) $defs[$poste['key']]['rank'] : 0;
        $options[] = [
            'key'   => $poste['key'],
            'label' => $poste['label'],
            'rank'  => $rank,
        ];
    }

    usort($options, static fn(array $a, array $b): int => ($b['rank'] <=> $a['rank']) ?: strcmp($a['label'], $b['label']));

    return $options;
}

function sd_recruitment_poste_label(string $key): string
{
    foreach (sd_get_recruitment_postes() as $poste) {
        if ($poste['key'] === $key) {
            return $poste['label'];
        }
    }

    foreach (sd_get_staff_role_definitions() as $def) {
        if ($def['key'] === $key) {
            return $def['label'];
        }
    }

    return $key;
}

/** @return list<array<string, mixed>> */
function sd_default_recruitment_questions(): array
{
    return [
        [
            'id'          => 'experience',
            'type'        => 'textarea',
            'label'       => 'Expérience staff',
            'section'     => 'Parcours',
            'placeholder' => 'Serveurs précédents, rôles occupés, durée…',
            'required'    => true,
            'emoji'       => 'trophy',
            'order'       => 1,
        ],
        [
            'id'          => 'motivation',
            'type'        => 'textarea',
            'label'       => 'Motivation',
            'section'     => 'Motivation',
            'placeholder' => 'Pourquoi South District ? Que pouvez-vous apporter ?',
            'required'    => true,
            'emoji'       => 'idea',
            'order'       => 2,
        ],
        [
            'id'          => 'scenario',
            'type'        => 'textarea',
            'label'       => 'Scénario RP',
            'section'     => 'Scénario RP',
            'placeholder' => 'Un joueur vous report pour RDM. Comment gérez-vous ?',
            'required'    => true,
            'emoji'       => 'gamepad',
            'order'       => 3,
        ],
    ];
}

/** @return list<array<string, mixed>> */
function sd_get_recruitment_questions(?PDO $pdo = null): array
{
    $defaults = sd_default_recruitment_questions();

    try {
        $pdo ??= \SouthDistrict\API\Database::getConnection();
        $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => 'recruitment_questions']);
        $row = $stmt->fetch();
        if (!$row) {
            return $defaults;
        }
        $decoded = json_decode($row['setting_value'], true);
        if (!is_array($decoded) || $decoded === []) {
            return $defaults;
        }

        return sd_normalize_recruitment_questions($decoded, $defaults);
    } catch (Throwable) {
        return $defaults;
    }
}

/**
 * @param list<mixed> $questions
 * @param list<array<string, mixed>>|null $fallback
 * @return list<array<string, mixed>>
 */
function sd_normalize_recruitment_questions(array $questions, ?array $fallback = null): array
{
    $allowedTypes = ['textarea', 'text', 'number'];
    $normalized = [];
    $usedIds = [];

    foreach ($questions as $idx => $q) {
        if (!is_array($q)) {
            continue;
        }

        $id = strtolower(trim((string) ($q['id'] ?? '')));
        $id = preg_replace('/[^a-z0-9_-]/', '', $id) ?? '';
        if ($id === '' || strlen($id) > 48) {
            $id = 'q_' . substr(md5(json_encode($q) . (string) $idx), 0, 10);
        }
        if (isset($usedIds[$id])) {
            $id .= '_' . count($usedIds);
        }
        $usedIds[$id] = true;

        $type = (string) ($q['type'] ?? 'textarea');
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'textarea';
        }

        $label = trim((string) ($q['label'] ?? ''));
        if ($label === '') {
            continue;
        }

        $normalized[] = [
            'id'          => $id,
            'type'        => $type,
            'label'       => mb_substr($label, 0, 120),
            'section'     => mb_substr(trim((string) ($q['section'] ?? 'Questions')), 0, 64) ?: 'Questions',
            'placeholder' => mb_substr(trim((string) ($q['placeholder'] ?? '')), 0, 255),
            'required'    => !array_key_exists('required', $q) || (bool) $q['required'],
            'emoji'       => mb_substr(trim((string) ($q['emoji'] ?? 'clipboard')), 0, 32) ?: 'clipboard',
            'order'       => (int) ($q['order'] ?? ($idx + 1)),
        ];
    }

    if ($normalized === [] && $fallback !== null) {
        return $fallback;
    }

    usort($normalized, static fn(array $a, array $b): int => ($a['order'] <=> $b['order']));

    return array_slice($normalized, 0, 25);
}

/** @param list<array<string, mixed>> $questions */
function sd_validate_candidature_responses(array $questions, array $rawResponses): array
{
    $formResponses = [];
    $legacy = ['experience' => '', 'motivation' => '', 'scenario' => ''];

    foreach ($questions as $q) {
        $id = (string) ($q['id'] ?? '');
        $value = isset($rawResponses[$id]) ? trim((string) $rawResponses[$id]) : '';

        if (!empty($q['required']) && $value === '') {
            \SouthDistrict\API\Router::jsonError(sprintf('Le champ « %s » est requis.', $q['label'] ?? $id));
        }

        if (($q['type'] ?? '') === 'number' && $value !== '' && !is_numeric($value)) {
            \SouthDistrict\API\Router::jsonError(sprintf('« %s » doit être un nombre.', $q['label'] ?? $id));
        }

        if (mb_strlen($value) > 10000) {
            \SouthDistrict\API\Router::jsonError(sprintf('« %s » est trop long (max 10 000 caractères).', $q['label'] ?? $id));
        }

        $formResponses[] = [
            'id'      => $id,
            'label'   => $q['label'] ?? $id,
            'section' => $q['section'] ?? 'Questions',
            'type'    => $q['type'] ?? 'textarea',
            'emoji'   => $q['emoji'] ?? '',
            'value'   => $value,
        ];

        if (array_key_exists($id, $legacy)) {
            $legacy[$id] = $value;
        }
    }

    foreach ($legacy as $key => $val) {
        if ($val === '') {
            $legacy[$key] = '-';
        }
    }

    return [
        'responses' => $formResponses,
        'legacy'    => $legacy,
    ];
}

/** @return array<string, mixed> */
function sd_load_site_settings(): array
{
    $settings = [
        'recruitment_open'        => true,
        'recruitment_message'     => 'Staff RP · Discord · 48 à 72h · 17 ans min.',
        'recruitment_questions'   => sd_default_recruitment_questions(),
        'recruitment_open_roles'  => sd_default_recruitment_open_roles(),
        'recruitment_postes'      => sd_default_recruitment_postes(),
        'candidature_webhook_url' => '',
    ];

    try {
        $keys = ['recruitment_open', 'recruitment_message', 'recruitment_questions', 'recruitment_postes', 'recruitment_open_roles', 'candidature_webhook_url'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
            "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ($placeholders)"
        );
        $stmt->execute($keys);

        foreach ($stmt->fetchAll() as $row) {
            $val = json_decode($row['setting_value'], true);
            if ($row['setting_key'] === 'recruitment_questions') {
                $settings['recruitment_questions'] = is_array($val)
                    ? sd_normalize_recruitment_questions($val, sd_default_recruitment_questions())
                    : sd_default_recruitment_questions();
                continue;
            }
            if ($row['setting_key'] === 'recruitment_postes') {
                $settings['recruitment_postes'] = is_array($val)
                    ? sd_normalize_recruitment_postes($val)
                    : sd_default_recruitment_postes();
                if ($settings['recruitment_postes'] === []) {
                    $settings['recruitment_postes'] = sd_default_recruitment_postes();
                }
                continue;
            }
            if ($row['setting_key'] === 'recruitment_open_roles') {
                $settings['recruitment_open_roles'] = is_array($val)
                    ? sd_normalize_recruitment_open_roles($val)
                    : sd_default_recruitment_open_roles();
                continue;
            }
            $settings[$row['setting_key']] = $val;
        }
    } catch (Throwable) {
        /* valeurs par défaut */
    }

    if (is_string($settings['recruitment_message'] ?? null)) {
        $settings['recruitment_message'] = preg_replace(
            '/\b15\s*ans\b/iu',
            '17 ans',
            $settings['recruitment_message']
        ) ?? $settings['recruitment_message'];
    }

    $settings['recruitment_postes'] = sd_get_recruitment_poste_options();
    $settings['recruitment_open_roles'] = sd_get_recruitment_open_roles();

    return $settings;
}

/** Enregistre une image data-URL PNG sur disque (persistant hors déploiement). */
function sd_save_png_data_url(string $targetDir, string $basename, string $dataUrl): ?string
{
    if ($dataUrl === '') {
        return null;
    }
    if (!str_starts_with($dataUrl, 'data:image/png;base64,')) {
        return str_starts_with($dataUrl, 'uploads/') ? $dataUrl : null;
    }

    $raw = base64_decode(substr($dataUrl, 22), true);
    if ($raw === false || $raw === '') {
        \SouthDistrict\API\Router::jsonError('Signature invalide.', 400);
    }

    $config = sd_config()['uploads'];
    $maxBytes = (int) ($config['signature_max_bytes'] ?? 512000);
    if (strlen($raw) > $maxBytes) {
        \SouthDistrict\API\Router::jsonError('Signature trop volumineuse.', 413);
    }

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        \SouthDistrict\API\Router::jsonError('Impossible de créer le dossier d\'upload.', 500);
    }

    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $basename) . '.png';
    $destPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (file_put_contents($destPath, $raw) === false) {
        \SouthDistrict\API\Router::jsonError('Échec de l\'enregistrement de la signature.', 500);
    }

    $root = realpath(__DIR__ . '/../..');
    $rel = str_replace('\\', '/', str_replace($root . DIRECTORY_SEPARATOR, '', $destPath));

    return $rel;
}

function sd_upload_abs_path(?string $relPath): ?string
{
    if ($relPath === null || $relPath === '') {
        return null;
    }
    $rel = ltrim(str_replace('\\', '/', $relPath), '/');
    if (!preg_match('#^uploads/[a-z0-9/_-]+\.(jpe?g|png|webp|gif)$#i', $rel)) {
        return null;
    }
    $root = realpath(__DIR__ . '/../..');
    if ($root === false) {
        return null;
    }

    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
}

/** @param array<string, mixed> $user */
function sd_avatar_public_url(array $user): ?string
{
    $id = isset($user['id']) ? (int) $user['id'] : 0;
    if (!empty($user['avatar_mime']) && $id > 0) {
        $v = !empty($user['updated_at']) ? strtotime((string) $user['updated_at']) : time();

        return '/api/avatars/serve.php?id=' . $id . '&v=' . max(1, (int) $v);
    }

    $path = $user['avatar'] ?? null;
    if ($path === null || $path === '') {
        return null;
    }
    if (str_starts_with($path, '/api/avatars/')) {
        return $path;
    }
    if (str_starts_with($path, 'http') || str_starts_with($path, 'data:')) {
        return $path;
    }

    $abs = sd_upload_abs_path($path);
    if ($abs && is_file($abs)) {
        $public = sd_public_asset_path($path);

        return ($public ?? '') . '?v=' . filemtime($abs);
    }

    return null;
}

function sd_public_asset_path(?string $path): ?string
{
    if ($path === null || $path === '') {
        return null;
    }
    if (str_starts_with($path, 'http') || str_starts_with($path, 'data:')) {
        return $path;
    }

    return '/' . ltrim(str_replace('\\', '/', $path), '/');
}

function sd_log_site_event(
    array $actor,
    string $eventType,
    string $summary,
    ?string $pageKey = null,
    ?string $detail = null,
    ?array $meta = null
): void {
    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
            'INSERT INTO site_logs (event_type, page_key, summary, detail, actor_id, actor_pseudo, meta)
             VALUES (:type, :page, :summary, :detail, :aid, :pseudo, :meta)'
        );
        $stmt->execute([
            'type'    => $eventType,
            'page'    => $pageKey,
            'summary' => $summary,
            'detail'  => $detail,
            'aid'     => (int) $actor['id'],
            'pseudo'  => $actor['pseudo'] ?? null,
            'meta'    => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable) {
        /* table absente si migration non appliquée */
    }
}

function sd_page_label(string $pageKey): string
{
    $labels = [
        'reglement-serveur' => 'Règlement Serveur',
        'reglement-staff'     => 'Règlement Staff',
        'patch-notes'         => 'Patch-notes',
    ];

    return $labels[$pageKey] ?? $pageKey;
}

function sd_generate_candidature_code(): string
{
    $pdo = \SouthDistrict\API\Database::getConnection();
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    for ($attempt = 0; $attempt < 20; $attempt++) {
        $suffix = '';
        for ($i = 0; $i < 6; $i++) {
            $suffix .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $code = 'SD-' . $suffix;

        $stmt = $pdo->prepare('SELECT id FROM candidatures WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        if (!$stmt->fetch()) {
            return $code;
        }
    }

    \SouthDistrict\API\Router::jsonError('Impossible de générer un code de candidature.', 500);
}

function sd_candidature_status_label(string $status): string
{
    $labels = [
        'en_attente' => 'En attente',
        'en_examen'  => 'En cours d\'examen',
        'entretien'  => 'Entretien planifié',
        'accepte'    => 'Accepté',
        'refuse'     => 'Refusé',
    ];

    return $labels[$status] ?? $status;
}

function sd_candidature_select_sql(): string
{
    return 'SELECT c.*,
                   u.pseudo AS user_pseudo,
                   u.login AS user_login,
                   u.email AS user_email,
                   u.avatar AS user_avatar,
                   u.avatar_mime AS user_avatar_mime,
                   u.updated_at AS user_updated_at,
                   u.created_at AS user_created_at,
                   rev.pseudo AS reviewer_pseudo,
                   rev.staff_role AS reviewer_staff_role
            FROM candidatures c
            LEFT JOIN users u ON u.id = c.user_id
            LEFT JOIN users rev ON rev.id = c.reviewer_id';
}

/** @param array<string, mixed> $row */
function sd_public_candidature(array $row, bool $full = false, string $scope = 'public'): array
{
    $status = (string) ($row['status'] ?? '');
    $public = [
        'status' => $status,
        'label'  => sd_candidature_status_label($status),
    ];

    if ($scope === 'admin') {
        $public['code'] = (string) ($row['code'] ?? '');
    }

    if (!$full) {
        return $public;
    }

    $payload = [
        'id'                  => (int) ($row['id'] ?? 0),
        'pseudo_discord'      => (string) ($row['pseudo_discord'] ?? ''),
        'pseudo_rp'           => $row['pseudo_rp'] ?? null,
        'age'                 => (int) ($row['age'] ?? 0),
        'poste'               => (string) ($row['poste'] ?? ''),
        'disponibilite_hebdo' => (int) ($row['disponibilite_hebdo'] ?? 0),
        'experience'          => (string) ($row['experience'] ?? ''),
        'motivation'          => (string) ($row['motivation'] ?? ''),
        'scenario'            => (string) ($row['scenario'] ?? ''),
        'form_responses'      => !empty($row['form_responses'])
            ? (is_string($row['form_responses'])
                ? json_decode($row['form_responses'], true)
                : $row['form_responses'])
            : null,
        'created_at'          => $row['created_at'] ?? null,
        'updated_at'          => $row['updated_at'] ?? null,
        'has_signature'       => !empty($row['signature']),
        'signature'           => !empty($row['signature'])
            ? (sd_public_asset_path($row['signature']) ?? $row['signature'])
            : null,
    ];

    if ($scope === 'admin') {
        $cleanReviewNote = $row['review_note'] ?? null;
        if (is_string($cleanReviewNote) && $cleanReviewNote !== '') {
            $cleanReviewNote = trim(preg_replace('/Dernière action par\s*:.*?(\([^\)]+\)|$)/ui', '', strip_tags($cleanReviewNote)));
        }
        $payload += [
            'review_note'         => $cleanReviewNote,
            'convocation_message' => $row['convocation_message'] ?? null,
            'reviewer_id'         => isset($row['reviewer_id']) && $row['reviewer_id'] !== null ? (int) $row['reviewer_id'] : null,
            'reviewer_pseudo'     => $row['reviewer_pseudo'] ?? null,
            'reviewer_staff_role' => $row['reviewer_staff_role'] ?? null,
            'reviewed_at'         => $row['reviewed_at'] ?? null,
            'is_archived'         => !empty($row['is_archived']),
            'archived_at'         => $row['archived_at'] ?? null,
            'viewed_at'           => $row['viewed_at'] ?? null,
            'user_id'             => isset($row['user_id']) && $row['user_id'] !== null ? (int) $row['user_id'] : null,
            'user_pseudo'         => $row['user_pseudo'] ?? null,
            'user_login'          => $row['user_login'] ?? null,
            'user_email'          => $row['user_email'] ?? null,
            'user_avatar'         => sd_avatar_public_url([
                'id'          => isset($row['user_id']) ? (int) $row['user_id'] : 0,
                'avatar'      => $row['user_avatar'] ?? null,
                'avatar_mime' => $row['user_avatar_mime'] ?? null,
                'updated_at'  => $row['user_updated_at'] ?? null,
            ]),
            'user_created_at'     => $row['user_created_at'] ?? null,
        ];
    } elseif ($scope === 'owner') {
        if (in_array($status, ['accepte', 'entretien'], true) && !empty($row['convocation_message'])) {
            $payload['convocation_message'] = $row['convocation_message'];
        }
    }

    return $public + $payload;
}

/** @param array<string, mixed> $row */
function sd_backup_candidature(array $row, string $type, ?array $actor = null): void
{
    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
            'INSERT INTO candidature_backups (candidature_id, backup_type, payload, actor_id, actor_pseudo)
             VALUES (:cid, :type, :payload, :aid, :pseudo)'
        );
        $stmt->execute([
            'cid'     => (int) $row['id'],
            'type'    => $type,
            'payload' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'aid'     => $actor !== null ? (int) $actor['id'] : null,
            'pseudo'  => $actor['pseudo'] ?? null,
        ]);
    } catch (Throwable) {
        // Ne bloque pas la review si la table backup n'existe pas encore
    }
}

/**
 * Récupère l'URL du Webhook Discord pour les candidatures.
 * Priorité : 1) BDD (site_settings) -> 2) config.php -> 3) .env
 */
function sd_get_candidature_webhook_url(): ?string
{
    // 1. Depuis la base de données (site_settings)
    try {
        $pdo = \SouthDistrict\API\Database::getConnection();
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'candidature_webhook_url' LIMIT 1");
        $stmt->execute();
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $val = json_decode((string) $raw, true);
            $url = is_string($val) ? trim($val) : trim((string) $raw);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }
    } catch (Throwable) {
        // Fallback
    }

    // 2. Depuis le fichier .env (analyse directe des lignes, robuste aux caractères spéciaux)
    $envFile = __DIR__ . '/../.env';
    if (is_file($envFile)) {
        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'DISCORD_CANDIDATURE_WEBHOOK=')) {
                $val = trim(substr($line, strlen('DISCORD_CANDIDATURE_WEBHOOK=')));
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }
                if (!empty($val) && filter_var($val, FILTER_VALIDATE_URL)) {
                    return $val;
                }
            }
        }
    }

    // 3. Depuis config.php
    $cfgUrl = trim((string) (sd_config()['discord']['candidature_webhook'] ?? ''));
    if (!empty($cfgUrl) && filter_var($cfgUrl, FILTER_VALIDATE_URL)) {
        return $cfgUrl;
    }

    // 4. Depuis les variables d'environnement système
    $envDirect = trim((string) (getenv('DISCORD_CANDIDATURE_WEBHOOK') ?: ''));
    if (!empty($envDirect) && filter_var($envDirect, FILTER_VALIDATE_URL)) {
        return $envDirect;
    }

    // 5. Fallback par défaut direct
    return '';
}

/**
 * Envoie un payload à un Webhook Discord de manière robuste et non-bloquante.
 *
 * @param string|null $url
 * @param array<string, mixed> $payload
 * @return bool
 */
function sd_send_discord_webhook(?string $url, array $payload): bool
{
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    $logFile = $logDir . '/webhook.log';

    // 1. Envoi via cURL avec désactivation de la vérification SSL locale (compatibilité Windows/XAMPP)
    if (function_exists('curl_init')) {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $json,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'User-Agent: SouthDistrict-Webhook/1.0',
                ],
            ]);

            $res = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                @file_put_contents($logFile, sprintf("[%s] [SUCCESS] cURL HTTP %d\n", date('Y-m-d H:i:s'), $httpCode), FILE_APPEND);
                return true;
            }

            @file_put_contents($logFile, sprintf("[%s] [ERROR] cURL HTTP %d : %s (err: %s)\n", date('Y-m-d H:i:s'), $httpCode, (string) $res, $curlErr), FILE_APPEND);
        } catch (Throwable $e) {
            @file_put_contents($logFile, sprintf("[%s] [EXCEPTION cURL] %s\n", date('Y-m-d H:i:s'), $e->getMessage()), FILE_APPEND);
        }
    }

    // 2. Fallback via file_get_contents (Stream Context)
    try {
        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nUser-Agent: SouthDistrict-Webhook/1.0\r\n",
                'content'       => $json,
                'timeout'       => 8,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $res = @file_get_contents($url, false, $context);
        if ($res !== false) {
            @file_put_contents($logFile, sprintf("[%s] [SUCCESS] stream context fallback\n", date('Y-m-d H:i:s')), FILE_APPEND);
            return true;
        }
    } catch (Throwable) {
        // Échec silencieux
    }

    return false;
}

/**
 * Envoie une notification Discord Webhook riche et détaillée lors d'une action
 * sur une candidature (Boutons : Examen, Entretien, Accepter, Refuser, Archiver).
 *
 * @param array<string, mixed> $params
 * @return bool
 */
function sd_notify_candidature_review_webhook(array $params): bool
{
    try {
        $webhookUrl = sd_get_candidature_webhook_url();
        if (!$webhookUrl) {
            return false;
        }

        $action             = (string) ($params['action'] ?? 'status_update');
        $reviewer           = $params['reviewer'] ?? [];
        $candidature        = $params['candidature'] ?? [];
        $newStatus          = (string) ($params['new_status'] ?? $action);
        $oldStatus          = (string) ($params['old_status'] ?? '');
        $reviewNote         = !empty($params['review_note']) ? trim((string) $params['review_note']) : null;
        $convocationMessage = !empty($params['convocation_message']) ? trim((string) $params['convocation_message']) : null;
        $roleGrantedMsg     = !empty($params['role_granted_msg']) ? trim((string) $params['role_granted_msg']) : null;

        $reviewerPseudo = !empty($reviewer['pseudo']) ? (string) $reviewer['pseudo'] : 'Staff';
        $reviewerRoleRaw = (string) ($reviewer['staff_role'] ?? '');
        $reviewerRole   = sd_staff_role_label($reviewerRoleRaw) ?: ($reviewerRoleRaw ?: 'Staff');

        $pseudoRp       = !empty($candidature['pseudo_rp']) ? (string) $candidature['pseudo_rp'] : 'Non renseigné';
        $pseudoDiscord  = (string) ($candidature['pseudo_discord'] ?? 'Inconnu');
        $code           = (string) ($candidature['code'] ?? 'N/A');
        $posteKey       = (string) ($candidature['poste'] ?? '');
        $posteLabel     = sd_staff_role_label($posteKey) ?: ($posteKey ?: 'Non spécifié');

        $discordMention = preg_match('/^[0-9]{17,20}$/', $pseudoDiscord)
            ? "<@{$pseudoDiscord}> (`{$pseudoDiscord}`)"
            : "`{$pseudoDiscord}`";

        // Configuration visuelle et sémantique selon l'action déclenchée par le bouton
        switch ($action) {
            case 'en_examen':
                $title       = '⏳ Candidature passée en cours d\'examen';
                $actionLabel = 'Mise en examen';
                $color       = 0xF59E0B; // Orange ambre
                $statusBadge = '⏳ En cours d\'examen';
                $description = "**{$reviewerPseudo}** a passé le dossier en **examen**.";
                break;

            case 'entretien':
                $title       = '🎙️ Candidat convoqué pour un entretien';
                $actionLabel = 'Convocation en entretien';
                $color       = 0x3B82F6; // Bleu FiveM / Discord
                $statusBadge = '🎙️ Entretien planifié';
                $description = "**{$reviewerPseudo}** a convoqué le candidat pour un **entretien oral**.";
                break;

            case 'accepte':
                $title       = '✅ Candidature validée et acceptée';
                $actionLabel = 'Candidature Acceptée';
                $color       = 0x1EE6A0; // Vert Émeraude South District
                $statusBadge = '✅ Accepté';
                $description = "**{$reviewerPseudo}** a **accepté** la candidature de **{$pseudoRp}** !";
                break;

            case 'refuse':
                $title       = '❌ Candidature refusée';
                $actionLabel = 'Candidature Refusée';
                $color       = 0xEF4444; // Rouge
                $statusBadge = '❌ Refusé';
                $description = "**{$reviewerPseudo}** a **refusé** la candidature.";
                break;

            case 'archive':
                $title       = '📦 Candidature déplacée dans les archives';
                $actionLabel = 'Archivage du dossier';
                $color       = 0x64748B; // Gris ardoise
                $statusBadge = '📦 Archivé';
                $description = "**{$reviewerPseudo}** a archivé le dossier de candidature.";
                break;

            default:
                $title       = '📋 Mise à jour d\'une candidature';
                $actionLabel = 'Mise à jour statut';
                $color       = 0x1EE6A0;
                $statusBadge = sd_candidature_status_label($newStatus);
                $description = "**{$reviewerPseudo}** a modifié le statut du dossier.";
                break;
        }

        $fields = [
            [
                'name'   => '👤 Action réalisée par',
                'value'  => "**{$reviewerPseudo}**\n*Grade : {$reviewerRole}*",
                'inline' => true,
            ],
            [
                'name'   => '🎯 Poste candidaté',
                'value'  => "**{$posteLabel}**",
                'inline' => true,
            ],
            [
                'name'   => '📊 Nouveau statut',
                'value'  => "**{$statusBadge}**",
                'inline' => true,
            ],
            [
                'name'   => '📋 Candidat (RP & Discord)',
                'value'  => "**{$pseudoRp}**\nDiscord : {$discordMention}",
                'inline' => true,
            ],
            [
                'name'   => '🆔 Référence Dossier',
                'value'  => "`{$code}`",
                'inline' => true,
            ],
        ];

        if ($oldStatus !== '' && $oldStatus !== $newStatus) {
            $fields[] = [
                'name'   => '🔄 Transition d\'état',
                'value'  => '`' . sd_candidature_status_label($oldStatus) . '` ➔ `' . $statusBadge . '`',
                'inline' => true,
            ];
        }

        if (!empty($reviewNote)) {
            $fields[] = [
                'name'   => '📝 Note / Motif du staff',
                'value'  => '>>> ' . mb_substr($reviewNote, 0, 1000),
                'inline' => false,
            ];
        }

        if (!empty($convocationMessage)) {
            $fields[] = [
                'name'   => '📅 Message de convocation',
                'value'  => '>>> ' . mb_substr($convocationMessage, 0, 1000),
                'inline' => false,
            ];
        }

        if (!empty($roleGrantedMsg)) {
            $fields[] = [
                'name'   => '🎖️ Promotion Staff automatique',
                'value'  => mb_substr(ltrim($roleGrantedMsg, ' ·'), 0, 500),
                'inline' => false,
            ];
        }

        $embed = [
            'title'       => $title,
            'description' => $description,
            'color'       => $color,
            'fields'      => $fields,
            'footer'      => [
                'text'     => 'South District RP • Système de Recrutement Staff',
                'icon_url' => 'https://www.south-district.fr/assets/favicon.png',
            ],
            'timestamp'   => date('c'),
        ];

        $payload = [
            'username'   => 'South District — Recrutement',
            'avatar_url' => 'https://www.south-district.fr/assets/favicon.png',
            'embeds'     => [$embed],
        ];

        return sd_send_discord_webhook($webhookUrl, $payload);
    } catch (Throwable) {
        return false;
    }
}

/**
 * Envoie une notification Discord Webhook lors de la soumission d'une nouvelle candidature.
 *
 * @param array<string, mixed> $candidature
 * @return bool
 */
function sd_notify_candidature_submission_webhook(array $candidature): bool
{
    try {
        $webhookUrl = sd_get_candidature_webhook_url();
        if (!$webhookUrl) {
            return false;
        }

        $pseudoRp       = !empty($candidature['pseudo_rp']) ? (string) $candidature['pseudo_rp'] : 'Non renseigné';
        $pseudoDiscord  = (string) ($candidature['pseudo_discord'] ?? 'Inconnu');
        $code           = (string) ($candidature['code'] ?? 'N/A');
        $posteKey       = (string) ($candidature['poste'] ?? '');
        $posteLabel     = sd_staff_role_label($posteKey) ?: ($posteKey ?: 'Non spécifié');
        $age            = (int) ($candidature['age'] ?? 0);
        $dispo          = (int) ($candidature['disponibilite_hebdo'] ?? 0);

        $discordMention = preg_match('/^[0-9]{17,20}$/', $pseudoDiscord)
            ? "<@{$pseudoDiscord}> (`{$pseudoDiscord}`)"
            : "`{$pseudoDiscord}`";

        $embed = [
            'title'       => '📝 Nouvelle candidature reçue',
            'description' => "Un joueur vient de déposer sa candidature pour intégrer l'équipe de **South District RP**.",
            'color'       => 0x1EE6A0,
            'fields'      => [
                [
                    'name'   => '📋 Candidat (RP & Discord)',
                    'value'  => "**{$pseudoRp}**\nDiscord : {$discordMention}",
                    'inline' => true,
                ],
                [
                    'name'   => '🎯 Poste convoité',
                    'value'  => "**{$posteLabel}**",
                    'inline' => true,
                ],
                [
                    'name'   => '🆔 Référence Dossier',
                    'value'  => "`{$code}`",
                    'inline' => true,
                ],
                [
                    'name'   => '🎂 Âge',
                    'value'  => "{$age} ans",
                    'inline' => true,
                ],
                [
                    'name'   => '⏱️ Disponibilité',
                    'value'  => "{$dispo}h / semaine",
                    'inline' => true,
                ],
                [
                    'name'   => '📊 Statut initial',
                    'value'  => '`En attente d\'examen`',
                    'inline' => true,
                ],
            ],
            'footer'      => [
                'text'     => 'South District RP • Système de Recrutement Staff',
                'icon_url' => 'https://www.south-district.fr/assets/favicon.png',
            ],
            'timestamp'   => date('c'),
        ];

        $payload = [
            'username'   => 'South District — Recrutement',
            'avatar_url' => 'https://www.south-district.fr/assets/favicon.png',
            'embeds'     => [$embed],
        ];

        return sd_send_discord_webhook($webhookUrl, $payload);
    } catch (Throwable) {
        return false;
    }
}



/**
 * Interroge directement l'API REST de Discord pour récupérer la liste des membres du serveur.
 * Nécessite un bot token avec l'intent Server Members activé.
 *
 * @return list<array<string, mixed>>|null
 */
function sd_fetch_discord_guild_members(?string $botToken = null, ?string $guildId = null): ?array
{
    $config = sd_config()['discord'] ?? [];
    $token = $botToken ?: ($config['bot_token'] ?? '');
    $guild = $guildId ?: ($config['guild_id'] ?? '');

    if ($token === '' || $guild === '' || $token === 'votre_token_copié_ici') {
        return null;
    }

    $allMembers = [];
    $after = null;
    $limit = 1000;

    for ($page = 0; $page < 10; $page++) {
        $url = "https://discord.com/api/v10/guilds/" . urlencode($guild) . "/members?limit=" . $limit;
        if ($after !== null) {
            $url .= "&after=" . urlencode($after);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bot ' . $token,
            'User-Agent: SouthDistrictBot (https://south-district.fr, 1.0)',
        ]);

        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$res) {
            if ($page === 0) {
                return null;
            }
            break;
        }

        $batch = json_decode($res, true);
        if (!is_array($batch) || empty($batch)) {
            break;
        }

        foreach ($batch as $m) {
            $allMembers[] = $m;
        }

        if (count($batch) < $limit) {
            break;
        }

        $lastItem = end($batch);
        $after = $lastItem['user']['id'] ?? null;
        if (!$after) {
            break;
        }
    }

    return $allMembers;
}

/**
 * Récupère la configuration de mapping et attribution automatique des rôles Discord stockée en BDD.
 * @return array<string, array{name:string, auto_assign:bool, target_role:string}>
 */
function sd_get_discord_roles_config(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    try {
        $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
            'SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1'
        );
        $stmt->execute(['k' => 'discord_roles_config']);
        $raw = $stmt->fetchColumn();
        if ($raw && is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $cache = $decoded;
                return $cache;
            }
        }
    } catch (\Throwable) {
        // En cas d'erreur SQL ou table non initialisée
    }

    $cache = [];
    return $cache;
}

/**
 * Enregistre la configuration de mapping des rôles Discord dans site_settings.
 */
function sd_save_discord_roles_config(array $config, ?int $userId = null): bool
{
    $val = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = \SouthDistrict\API\Database::getConnection()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    return $stmt->execute([
        'k'  => 'discord_roles_config',
        'v'  => $val,
        'v2' => $val,
        'u'  => $userId,
        'u2' => $userId,
    ]);
}

/**
 * Récupère la liste complète et détaillée de TOUS les rôles Discord du serveur (avec couleur, position, statut géré).
 * Triée de la plus haute position hiérarchique à la plus basse.
 *
 * @return list<array{id:string, name:string, color:string, position:int, managed:bool, is_everyone:bool}>
 */
function sd_fetch_discord_guild_roles_detailed(?string $botToken = null, ?string $guildId = null): array
{
    $config = sd_config()['discord'] ?? [];
    $token = $botToken ?: ($config['bot_token'] ?? '');
    $guild = $guildId ?: ($config['guild_id'] ?? '');

    if ($token === '' || $guild === '' || $token === 'votre_token_copié_ici') {
        return [];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/v10/guilds/' . urlencode($guild) . '/roles');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bot ' . $token,
        'User-Agent: SouthDistrictBot (https://south-district.fr, 1.0)',
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$res) {
        return [];
    }

    $rawRoles = json_decode($res, true);
    if (!is_array($rawRoles)) {
        return [];
    }

    $roles = [];
    foreach ($rawRoles as $r) {
        if (!isset($r['id'], $r['name'])) {
            continue;
        }
        $id = (string) $r['id'];
        $name = sd_clean_role_name((string) $r['name']);
        $colorInt = (int) ($r['color'] ?? 0);

        // Support complet des rôles dégradés Discord (Enhanced Role Colors)
        $primaryInt = isset($r['colors']['primary_color']) && $r['colors']['primary_color'] !== null ? (int) $r['colors']['primary_color'] : null;
        $secondaryInt = isset($r['colors']['secondary_color']) && $r['colors']['secondary_color'] !== null ? (int) $r['colors']['secondary_color'] : null;

        $primaryHex = ($primaryInt !== null && $primaryInt > 0) ? sprintf('#%06x', $primaryInt) : null;
        $secondaryHex = ($secondaryInt !== null && $secondaryInt > 0) ? sprintf('#%06x', $secondaryInt) : null;

        if ($secondaryHex !== null && ($primaryHex === null || strtolower($primaryHex) === '#ffffff')) {
            $colorHex = $secondaryHex;
        } elseif ($primaryHex !== null && strtolower($primaryHex) !== '#ffffff' && strtolower($primaryHex) !== '#000000') {
            $colorHex = $primaryHex;
        } elseif ($colorInt > 0) {
            $rawHex = sprintf('#%06x', $colorInt);
            $colorHex = (strtolower($rawHex) === '#ffffff' && $secondaryHex !== null) ? $secondaryHex : $rawHex;
        } else {
            $colorHex = '#99aab5';
        }

        // Cas particulier rôle Créateur Discord avec dégradé
        if ((str_contains(mb_strtolower($name), 'créat') || str_contains(mb_strtolower($name), 'creat')) && strtolower($colorHex) === '#ffffff') {
            $colorHex = $secondaryHex ?: '#eeae59';
        }

        $position = (int) ($r['position'] ?? 0);
        $managed = !empty($r['managed']);
        $isEveryone = ($id === $guild);

        $roles[] = [
            'id'              => $id,
            'name'            => $name,
            'color'           => $colorHex,
            'secondary_color' => $secondaryHex,
            'position'        => $position,
            'managed'         => $managed,
            'is_everyone'     => $isEveryone,
        ];
    }

    usort($roles, static fn(array $a, array $b): int => $b['position'] <=> $a['position']);

    return $roles;
}

/**
 * Déclenche une synchronisation immédiate de tous les membres du serveur Discord via l'API REST bot.
 */
function sd_sync_all_guild_members_now(): array
{
    $config = sd_config()['discord'] ?? [];
    $token = (string) ($config['bot_token'] ?? '');
    $guild = (string) ($config['guild_id'] ?? '');

    if ($token === '' || $guild === '' || $token === 'votre_token_copié_ici') {
        return ['success' => false, 'error' => 'Bot Discord non configuré'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/v10/guilds/' . urlencode($guild) . '/members?limit=1000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bot ' . $token,
        'User-Agent: SouthDistrictBot (https://south-district.fr, 1.0)',
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$res) {
        return ['success' => false, 'error' => "Erreur Discord HTTP {$code}"];
    }

    $members = json_decode($res, true);
    if (!is_array($members)) {
        return ['success' => false, 'error' => 'Format Discord invalide'];
    }

    $formattedList = [];
    foreach ($members as $m) {
        $u = $m['user'] ?? null;
        if (!$u || !empty($u['bot'])) {
            continue;
        }

        $userId = (string) ($u['id'] ?? '');
        $username = (string) ($u['username'] ?? '');
        $displayName = (string) ($m['nick'] ?? $u['global_name'] ?? $username);
        $avatarHash = $u['avatar'] ?? null;
        $avatarUrl = $avatarHash ? "https://cdn.discordapp.com/avatars/{$userId}/{$avatarHash}.png?size=256" : null;

        $formattedList[] = [
            'discord_id'   => $userId,
            'username'     => $username,
            'display_name' => $displayName,
            'avatar_url'   => $avatarUrl,
            'roles'        => (array) ($m['roles'] ?? []),
        ];
    }

    sd_update_staff_role_colors_from_discord();
    $syncResult = sd_sync_team_from_discord_members($formattedList, true);
    return [
        'success'      => true,
        'total_parsed' => count($formattedList),
        'created'      => $syncResult['created'] ?? 0,
        'updated'      => $syncResult['updated'] ?? 0,
        'demoted'      => $syncResult['demoted'] ?? 0,
        'staff_count'  => $syncResult['staff_count'] ?? 0,
    ];
}

/**
 * Récupère tous les rôles du serveur Discord avec le token de bot configuré.
 *
 * @return array<string, string> Map [role_id => role_name]
 */
function sd_fetch_discord_guild_roles(?string $botToken = null, ?string $guildId = null): array
{
    $config = sd_config()['discord'] ?? [];
    $token = $botToken ?: ($config['bot_token'] ?? '');
    $guild = $guildId ?: ($config['guild_id'] ?? '');

    if ($token === '' || $guild === '' || $token === 'votre_token_copié_ici') {
        return [];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/v10/guilds/' . urlencode($guild) . '/roles');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bot ' . $token,
        'User-Agent: SouthDistrictBot (https://south-district.fr, 1.0)',
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $rolesMap = [];
    if ($res) {
        $roles = json_decode($res, true);
        if (is_array($roles)) {
            foreach ($roles as $r) {
                if (isset($r['id'], $r['name'])) {
                    $rolesMap[(string) $r['id']] = sd_clean_role_name((string) $r['name']);
                }
            }
        }
    }
    return $rolesMap;
}

/**
 * Détecte automatiquement la clé de rôle du site à partir du nom d'un rôle Discord.
 */
function sd_detect_staff_role_from_name(string $name, array $definedRoles = []): ?string
{
    $clean = mb_strtolower(trim($name), 'UTF-8');
    $clean = strtr(
        $clean,
        ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','à'=>'a','â'=>'a','ä'=>'a','î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c']
    );
    $clean = preg_replace('/[^\w\s-]/u', ' ', $clean) ?? $clean;
    $clean = preg_replace('/\s+/', ' ', $clean) ?? $clean;
    $clean = trim($clean);

    if ($clean === '') {
        return null;
    }

    // 1. Mots-clés prioritaires GTA RP / Discord
    if (preg_match('/\b(co[\s_-]?ceo)\b/', $clean)) {
        return 'co_ceo';
    }
    if (preg_match('/\b(createur|creat(rice)?)\b/', $clean)) {
        return 'createur';
    }
    if (preg_match('/\b(fondat(eur|rice)|owner|ceo|directeur|direction)\b/', $clean)) {
        return 'ceo';
    }
    if (preg_match('/\b(dev(eloppeur)?|developer)\b/', $clean)) {
        return 'developer';
    }
    if (preg_match('/\b(gerant[\s_-]?staff|responsable[\s_-]?staff)\b/', $clean)) {
        return 'gerant_staff';
    }
    if (preg_match('/\b(manager)\b/', $clean)) {
        return 'manager';
    }
    if (preg_match('/\b(responsable[\s_-]?cm|lead[\s_-]?cm)\b/', $clean)) {
        return 'responsable_cm';
    }
    if (preg_match('/\b(community[\s_-]?manager|cm)\b/', $clean)) {
        return 'cm';
    }
    if (preg_match('/\b(administrat(eur|rice)|admin)\b/', $clean)) {
        return 'administrateur';
    }
    if (preg_match('/\b(moderat(eur|rice)|modo|helpeur|support)\b/', $clean)) {
        return 'moderateur';
    }
    if (preg_match('/\b(staff)\b/', $clean)) {
        return 'staff';
    }

    // 2. Correspondance avec les libellés définis dans le système
    foreach ($definedRoles as $def) {
        $key = (string) ($def['key'] ?? '');
        $label = mb_strtolower((string) ($def['label'] ?? ''), 'UTF-8');
        if ($key === '' || $label === '') continue;

        if (str_contains($clean, $key) || str_contains($clean, $label)) {
            return $key;
        }
    }

    return null;
}

/**
 * Synchronise les membres Discord (reçus du bot ou de l'API REST) avec la table users.
 *
 * @param list<array{discord_id:string,username:string,display_name?:?string,avatar_url?:?string,roles:list<mixed>}> $membersData
 * @param bool $removeMissingFromStaff
 * @return array{synced_count:int,staff_count:int,created:int,updated:int,demoted:int}
 */
function sd_sync_team_from_discord_members(array $membersData, bool $removeMissingFromStaff = true): array
{
    $discordConfig = sd_config()['discord'] ?? [];
    $roleMapping = $discordConfig['role_mapping'] ?? [];

    $definedRoles = sd_get_staff_role_definitions();
    $adminSiteRoles = ['createur', 'ceo', 'co_ceo', 'developer'];

    $pdo = \SouthDistrict\API\Database::getConnection();

    $created = 0;
    $updated = 0;
    $demoted = 0;
    $staffDiscordIds = [];

    $botClientId = (string) ($discordConfig['client_id'] ?? '1546266986127036419');
    try {
        $pdo->prepare("DELETE FROM users WHERE discord_id = :bid OR login = :blg OR pseudo LIKE '%South District Bot%'")
            ->execute(['bid' => $botClientId, 'blg' => 'discord_' . $botClientId]);
    } catch (\Throwable $e) {
        error_log('[Discord Sync] Failed cleaning bot accounts: ' . $e->getMessage());
    }

    foreach ($membersData as $m) {
        $discordId = (string) ($m['discord_id'] ?? '');
        if ($discordId === '' || $discordId === $botClientId || !empty($m['bot']) || stripos((string)($m['username'] ?? ''), 'bot') !== false) {
            continue;
        }

        $rawRoles = is_array($m['roles'] ?? null) ? $m['roles'] : [];

        $matchedSiteRoleKeys = [];
        $discordRolesConfig = sd_get_discord_roles_config();
        $roleColors = sd_get_staff_role_colors();
        $colorsUpdated = false;
        $configRolesUpdated = false;

        foreach ($rawRoles as $r) {
            $rId = is_array($r) ? (string) ($r['id'] ?? '') : (string) $r;
            $rName = is_array($r) ? (string) ($r['name'] ?? '') : '';
            $rColor = is_array($r) ? (string) ($r['color'] ?? '') : '';

            $targetRole = null;
            // Rôles personnalisés dans site_settings (discord_roles_config)
            if ($rId !== '' && isset($discordRolesConfig[$rId])) {
                $conf = $discordRolesConfig[$rId];
                if (!empty($conf['auto_assign'])) {
                    $targetRole = !empty($conf['target_role']) ? (string) $conf['target_role'] : ('discord_' . $rId);
                    $matchedSiteRoleKeys[] = $targetRole;
                }
            } elseif ($rId !== '' && isset($roleMapping[$rId])) {
                $targetRole = (string) $roleMapping[$rId];
                $matchedSiteRoleKeys[] = $targetRole;
            } elseif ($rName !== '') {
                $detected = sd_detect_staff_role_from_name($rName, $definedRoles);
                if ($detected !== null) {
                    $targetRole = $detected;
                    $matchedSiteRoleKeys[] = $targetRole;
                }
            }

            // Synchronisation de la couleur Discord si le rôle en a une
            if ($rColor !== '' && $rColor !== '#000000' && strtolower($rColor) !== '#99aab5') {
                $effectiveColor = $rColor;
                if ($targetRole === 'createur' && strtolower($effectiveColor) === '#ffffff') {
                    $effectiveColor = '#eeae59';
                }
                if ($targetRole) {
                    if (!isset($roleColors[$targetRole]) || $roleColors[$targetRole] !== $effectiveColor) {
                        $roleColors[$targetRole] = $effectiveColor;
                        $colorsUpdated = true;
                    }
                }
                if ($rId !== '' && isset($discordRolesConfig[$rId])) {
                    if (($discordRolesConfig[$rId]['color'] ?? '') !== $effectiveColor) {
                        $discordRolesConfig[$rId]['color'] = $effectiveColor;
                        $configRolesUpdated = true;
                    }
                }
            }
        }
        if ($colorsUpdated) {
            sd_save_staff_role_colors($roleColors);
        }
        if ($configRolesUpdated) {
            sd_save_discord_roles_config($discordRolesConfig);
        }
        $matchedSiteRoleKeys = array_values(array_unique($matchedSiteRoleKeys));

        $stmt = $pdo->prepare('SELECT * FROM users WHERE discord_id = :d LIMIT 1');
        $stmt->execute(['d' => $discordId]);
        $existing = $stmt->fetch();

        // Si le membre n'a aucun rôle staff
        if (empty($matchedSiteRoleKeys)) {
            if ($existing && !empty($existing['staff_role'])) {
                if (sd_is_master_ceo($existing)) {
                    continue;
                }
                $demSiteRole = 'citoyen';
                $dem = $pdo->prepare('UPDATE users SET staff_role = NULL, secondary_roles = "[]", site_role = :sr, is_staff_visible = 0, discord_last_sync_at = NOW(), updated_at = NOW() WHERE id = :id');
                $dem->execute(['sr' => $demSiteRole, 'id' => (int) $existing['id']]);
                $demoted++;
            }
            continue;
        }

        // Détermination du rôle principal et des rôles secondaires selon la hiérarchie
        $orderedMatches = [];
        foreach ($definedRoles as $def) {
            if (in_array($def['key'], $matchedSiteRoleKeys, true)) {
                $orderedMatches[] = $def['key'];
            }
        }
        foreach ($matchedSiteRoleKeys as $k) {
            if (!in_array($k, $orderedMatches, true)) {
                $orderedMatches[] = $k;
            }
        }

        $primaryStaffRole = $orderedMatches[0] ?? null;
        $secondaryStaffRoles = array_values(array_slice($orderedMatches, 1));
        $secondaryJson = json_encode($secondaryStaffRoles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $pseudo = trim((string) ($m['display_name'] ?? $m['username'] ?? 'Membre'));
        $avatarUrl = !empty($m['avatar_url']) ? (string) $m['avatar_url'] : null;
        $newSiteRole = (($existing && sd_is_master_ceo($existing)) || in_array($primaryStaffRole, $adminSiteRoles, true)) ? 'admin' : 'citoyen';

        $staffDiscordIds[] = $discordId;

        if ($existing) {
            $fieldsToUpdate = [
                'staff_role = :staff_role',
                'secondary_roles = :secondary_roles',
                'site_role = :site_role',
                'is_staff_visible = 1',
                'discord_last_sync_at = NOW()',
                'updated_at = NOW()',
            ];
            $params = [
                'staff_role'      => $primaryStaffRole,
                'secondary_roles' => $secondaryJson,
                'site_role'       => $newSiteRole,
                'id'              => (int) $existing['id'],
            ];

            if ($pseudo !== '' && (empty($existing['pseudo']) || str_starts_with((string) $existing['pseudo'], 'discord_'))) {
                $fieldsToUpdate[] = 'pseudo = :pseudo';
                $params['pseudo'] = $pseudo;
            }

            if ($avatarUrl !== null && (empty($existing['avatar']) || str_starts_with((string) $existing['avatar'], 'https://cdn.discordapp.com/'))) {
                $fieldsToUpdate[] = 'avatar = :avatar';
                $params['avatar'] = $avatarUrl;
            }

            $sql = 'UPDATE users SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = :id';
            $upd = $pdo->prepare($sql);
            $upd->execute($params);
            $updated++;
        } else {
            // Création automatique du compte pour ce membre du staff
            $login = 'discord_' . $discordId;
            $fakePass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $ins = $pdo->prepare(
                'INSERT INTO users (login, password_hash, pseudo, site_role, staff_role, secondary_roles, is_staff_visible, discord_id, avatar, created_at, updated_at, discord_last_sync_at)
                 VALUES (:login, :pass, :pseudo, :site_role, :staff_role, :secondary_roles, 1, :discord_id, :avatar, NOW(), NOW(), NOW())'
            );
            $ins->execute([
                'login'           => $login,
                'pass'            => $fakePass,
                'pseudo'          => $pseudo,
                'site_role'       => $newSiteRole,
                'staff_role'      => $primaryStaffRole,
                'secondary_roles' => $secondaryJson,
                'discord_id'      => $discordId,
                'avatar'          => $avatarUrl,
            ]);
            $created++;
        }
    }

    // Retirer de l'affichage staff les anciens qui ne sont plus dans la liste staff Discord lors d'un full sync
    if ($removeMissingFromStaff) {
        if (!empty($staffDiscordIds)) {
            $placeholders = implode(',', array_fill(0, count($staffDiscordIds), '?'));
            $cleanStmt = $pdo->prepare(
                "UPDATE users 
                 SET staff_role = NULL, secondary_roles = '[]', site_role = IF(is_master_ceo = 1, 'admin', 'citoyen'), is_staff_visible = 0, updated_at = NOW() 
                 WHERE is_staff_visible = 1 
                   AND discord_id IS NOT NULL
                   AND is_master_ceo = 0
                   AND id != 97
                   AND discord_id NOT IN ($placeholders)"
            );
            $cleanStmt->execute($staffDiscordIds);
            $demoted += $cleanStmt->rowCount();
        } else {
            $cleanStmt = $pdo->prepare(
                "UPDATE users 
                 SET staff_role = NULL, secondary_roles = '[]', site_role = IF(is_master_ceo = 1, 'admin', 'citoyen'), is_staff_visible = 0, updated_at = NOW() 
                 WHERE is_staff_visible = 1 
                   AND discord_id IS NOT NULL
                   AND is_master_ceo = 0
                   AND id != 97"
            );
            $cleanStmt->execute();
            $demoted += $cleanStmt->rowCount();
        }
    }

    return [
        'synced_count' => count($membersData),
        'staff_count'  => count($staffDiscordIds),
        'created'      => $created,
        'updated'      => $updated,
        'demoted'      => $demoted,
    ];
}
