<?php
/**
 * South District RP - Carte entreprises (pings Los Santos)
 * GET  -> { success, pings[], spawn, updated_at }
 * POST { action: add|update|delete|toggle|save_spawn, ... } (permission manage_map)
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET', 'POST', 'OPTIONS');

function ce_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads/entreprise';
}

function ce_save_image(string $dataUrl, string $fnameBase): string
{
    if (!preg_match('#^data:image/([a-zA-Z0-9.+-]+);base64,#', $dataUrl, $m)) {
        return '';
    }
    $ext = strtolower($m[1]);
    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }
    if (!preg_match('/^(jpg|png|gif|webp)$/', $ext)) {
        $ext = 'jpg';
    }
    $bin = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
    if ($bin === false || strlen($bin) === 0 || strlen($bin) > 6000000) {
        return '';
    }
    $dir = ce_upload_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $fname = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fnameBase) . '.' . $ext;
    if (@file_put_contents($dir . '/' . $fname, $bin) === false) {
        return '';
    }
    return 'uploads/entreprise/' . $fname;
}

function ce_unlink_file(string $rel): void
{
    if ($rel === '' || preg_match('#^data:#', $rel)) {
        return;
    }
    $path = dirname(__DIR__) . '/' . ltrim($rel, '/');
    if (is_file($path)) {
        @unlink($path);
    }
}

/** @return array<string, mixed> */
function ce_row_to_ping(array $row): array
{
    return [
        'id'        => $row['id'],
        'lat'       => (float) $row['lat'],
        'lng'       => (float) $row['lng'],
        'title'     => $row['title'],
        'taken'     => !empty($row['taken']),
        'discord'   => $row['discord'],
        'recherche' => $row['recherche'] ?? '',
        'img'       => $row['img'],
        'logo'      => $row['logo'],
        'ic'        => 'building',
    ];
}

/** @return array{0: array<int, array<string, mixed>>, 1: ?int} */
function ce_fetch_all(PDO $pdo): array
{
    $rows = $pdo->query('
        SELECT id, lat, lng, title, taken, discord, recherche, img, logo, updated_at
        FROM sd_entreprise_pings
        ORDER BY title ASC
    ')->fetchAll();

    $pings = [];
    $updatedAt = null;
    foreach ($rows as $row) {
        $pings[] = ce_row_to_ping($row);
        $ts = strtotime($row['updated_at'] ?? '');
        if ($ts && ($updatedAt === null || $ts > $updatedAt)) {
            $updatedAt = $ts;
        }
    }

    return [$pings, $updatedAt ? $updatedAt * 1000 : null];
}

/** @return array{lat: float, lng: float, zoom: float}|null */
function ce_load_spawn(PDO $pdo): ?array
{
    $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');
    $stmt->execute(['k' => 'map_spawn']);
    $raw = $stmt->fetchColumn();
    if (!is_string($raw) || trim($raw) === '' || trim($raw) === 'null') {
        return null;
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return null;
    }
    if (!isset($data['lat'], $data['lng'], $data['zoom'])) {
        return null;
    }
    return [
        'lat'  => (float) $data['lat'],
        'lng'  => (float) $data['lng'],
        'zoom' => (float) $data['zoom'],
    ];
}

function ce_save_spawn(PDO $pdo, float $lat, float $lng, float $zoom, int $userId): void
{
    $payload = json_encode([
        'lat'  => round($lat, 3),
        'lng'  => round($lng, 3),
        'zoom' => round($zoom, 2),
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare('
        INSERT INTO site_settings (setting_key, setting_value, updated_by)
        VALUES (:k, :v, :u)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)
    ');
    $stmt->execute(['k' => 'map_spawn', 'v' => $payload, 'u' => $userId]);
}

/** @return array<int, array<string, mixed>> */
function ce_load_lines(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');
    $stmt->execute(['k' => 'map_custom_lines']);
    $raw = $stmt->fetchColumn();
    if (!is_string($raw) || trim($raw) === '' || trim($raw) === 'null') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function ce_save_lines(PDO $pdo, array $lines, int $userId): void
{
    $payload = json_encode($lines, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare('
        INSERT INTO site_settings (setting_key, setting_value, updated_by)
        VALUES (:k, :v, :u)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)
    ');
    $stmt->execute(['k' => 'map_custom_lines', 'v' => $payload, 'u' => $userId]);
}

function ce_require_map_permission(): array
{
    $user = sd_require_auth();
    if (!sd_user_has_permission($user, 'manage_map')) {
        \SouthDistrict\API\Router::jsonError('Permission carte entreprises requise.', 403);
    }
    return $user;
}

try {
    $pdo = \SouthDistrict\API\Database::getConnection();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        [$pings, $updatedAt] = ce_fetch_all($pdo);
        \SouthDistrict\API\Router::jsonSuccess([
            'pings'      => $pings,
            'spawn'      => ce_load_spawn($pdo),
            'lines'      => ce_load_lines($pdo),
            'updated_at' => $updatedAt,
        ]);
    }

    $input = \SouthDistrict\API\Router::jsonInput();
    $action = isset($input['action']) ? (string) $input['action'] : '';
    $user = ce_require_map_permission();

    if ($action === 'save_lines') {
        $lines = isset($input['lines']) && is_array($input['lines']) ? $input['lines'] : [];
        ce_save_lines($pdo, $lines, (int) $user['id']);
        \SouthDistrict\API\Router::jsonSuccess([
            'lines'      => ce_load_lines($pdo),
            'updated_at' => time() * 1000,
        ]);
    }

    if ($action === 'save_spawn') {
        if (!isset($input['lat'], $input['lng'], $input['zoom'])) {
            \SouthDistrict\API\Router::jsonError('Coordonnées invalides.');
        }
        ce_save_spawn($pdo, (float) $input['lat'], (float) $input['lng'], (float) $input['zoom'], (int) $user['id']);
        \SouthDistrict\API\Router::jsonSuccess([
            'spawn'      => ce_load_spawn($pdo),
            'updated_at' => time() * 1000,
        ]);
    }

    if ($action === 'add') {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            \SouthDistrict\API\Router::jsonError('Indique le nom de l\'entreprise.');
        }
        $id = uniqid('p', true);
        $img = '';
        $logo = '';
        if (!empty($input['img']) && preg_match('#^data:image/#', (string) $input['img'])) {
            $img = ce_save_image((string) $input['img'], $id . '_banner');
            if ($img === '') {
                \SouthDistrict\API\Router::jsonError('Impossible d\'enregistrer la bannière.', 500);
            }
        }
        if (!empty($input['logo']) && preg_match('#^data:image/#', (string) $input['logo'])) {
            $logo = ce_save_image((string) $input['logo'], $id . '_logo');
            if ($logo === '') {
                \SouthDistrict\API\Router::jsonError('Impossible d\'enregistrer le logo.', 500);
            }
        }
        if ($img !== '' && $logo === '') {
            \SouthDistrict\API\Router::jsonError('Un logo est obligatoire lorsqu\'une bannière est ajoutée.');
        }

        $stmt = $pdo->prepare('
            INSERT INTO sd_entreprise_pings (id, lat, lng, title, taken, discord, recherche, img, logo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $id,
            isset($input['lat']) ? (float) $input['lat'] : 0,
            isset($input['lng']) ? (float) $input['lng'] : 0,
            mb_substr($title, 0, 200),
            !empty($input['taken']) ? 1 : 0,
            mb_substr(trim((string) ($input['discord'] ?? '')), 0, 500),
            trim((string) ($input['recherche'] ?? '')),
            $img,
            $logo,
        ]);

        $stmt = $pdo->prepare('SELECT * FROM sd_entreprise_pings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        \SouthDistrict\API\Router::jsonSuccess(['ping' => ce_row_to_ping($row), 'updated_at' => time() * 1000]);
    }

    if ($action === 'update' || $action === 'toggle') {
        $id = trim((string) ($input['id'] ?? ''));
        if ($id === '') {
            \SouthDistrict\API\Router::jsonError('Ping introuvable.');
        }
        $stmt = $pdo->prepare('SELECT * FROM sd_entreprise_pings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            \SouthDistrict\API\Router::jsonError('Ping introuvable.', 404);
        }

        if ($action === 'toggle') {
            $taken = empty($row['taken']) ? 1 : 0;
            $pdo->prepare('UPDATE sd_entreprise_pings SET taken = ? WHERE id = ?')->execute([$taken, $id]);
        } else {
            $fields = [];
            $params = [];
            if (array_key_exists('title', $input)) {
                $fields[] = 'title = ?';
                $params[] = mb_substr(trim((string) $input['title']), 0, 200);
            }
            if (array_key_exists('taken', $input)) {
                $fields[] = 'taken = ?';
                $params[] = !empty($input['taken']) ? 1 : 0;
            }
            if (array_key_exists('discord', $input)) {
                $fields[] = 'discord = ?';
                $params[] = mb_substr(trim((string) $input['discord']), 0, 500);
            }
            if (array_key_exists('recherche', $input)) {
                $fields[] = 'recherche = ?';
                $params[] = trim((string) $input['recherche']);
            }
            if (array_key_exists('lat', $input)) {
                $fields[] = 'lat = ?';
                $params[] = (float) $input['lat'];
            }
            if (array_key_exists('lng', $input)) {
                $fields[] = 'lng = ?';
                $params[] = (float) $input['lng'];
            }
            if (array_key_exists('img', $input)) {
                $imgVal = (string) $input['img'];
                if (preg_match('#^data:image/#', $imgVal)) {
                    $saved = ce_save_image($imgVal, $id . '_banner_' . time());
                    if ($saved !== '') {
                        ce_unlink_file($row['img']);
                        $fields[] = 'img = ?';
                        $params[] = $saved;
                        $row['img'] = $saved;
                    }
                } elseif ($imgVal === '') {
                    ce_unlink_file($row['img']);
                    $fields[] = 'img = ?';
                    $params[] = '';
                }
            }
            if (array_key_exists('logo', $input)) {
                $logoVal = (string) $input['logo'];
                if (preg_match('#^data:image/#', $logoVal)) {
                    $saved = ce_save_image($logoVal, $id . '_logo_' . time());
                    if ($saved !== '') {
                        ce_unlink_file($row['logo']);
                        $fields[] = 'logo = ?';
                        $params[] = $saved;
                        $row['logo'] = $saved;
                    }
                } elseif ($logoVal === '') {
                    ce_unlink_file($row['logo']);
                    $fields[] = 'logo = ?';
                    $params[] = '';
                }
            }
            if ($fields) {
                $params[] = $id;
                $pdo->prepare('UPDATE sd_entreprise_pings SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
            }
        }

        $stmt = $pdo->prepare('SELECT * FROM sd_entreprise_pings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $updated = $stmt->fetch();
        if (!empty($updated['img']) && empty($updated['logo'])) {
            \SouthDistrict\API\Router::jsonError('Un logo est obligatoire lorsqu\'une bannière est présente.');
        }
        \SouthDistrict\API\Router::jsonSuccess(['ping' => ce_row_to_ping($updated), 'updated_at' => time() * 1000]);
    }

    if ($action === 'delete') {
        $id = trim((string) ($input['id'] ?? ''));
        if ($id === '') {
            \SouthDistrict\API\Router::jsonError('Ping introuvable.');
        }
        $stmt = $pdo->prepare('SELECT img, logo FROM sd_entreprise_pings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            \SouthDistrict\API\Router::jsonError('Ping introuvable.', 404);
        }
        ce_unlink_file($row['img']);
        ce_unlink_file($row['logo']);
        $pdo->prepare('DELETE FROM sd_entreprise_pings WHERE id = ?')->execute([$id]);
        \SouthDistrict\API\Router::jsonSuccess(['updated_at' => time() * 1000]);
    }

    \SouthDistrict\API\Router::jsonError('Action inconnue.');
} catch (Throwable $e) {
    \SouthDistrict\API\Router::jsonError('Erreur serveur.', 500);
}

