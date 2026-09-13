<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (!in_array($method, ['POST', 'GET'], true)) {
    \SouthDistrict\API\Router::jsonError('Méthode non autorisée.', 405);
}

$config = sd_config()['discord'] ?? [];
$expectedApiKey = (string) ($config['bot_api_key'] ?? '');

$isAuthorized = false;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$botHeader = $_SERVER['HTTP_X_BOT_KEY'] ?? '';
$providedKey = '';

if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
    $providedKey = $matches[1];
} elseif ($botHeader !== '') {
    $providedKey = $botHeader;
} elseif (isset($_GET['api_key'])) {
    $providedKey = (string) $_GET['api_key'];
}

if ($expectedApiKey !== '' && $providedKey !== '' && hash_equals($expectedApiKey, $providedKey)) {
    $isAuthorized = true;
} else {
    // Si pas de clé bot, on vérifie si un administrateur connecté lance la synchronisation
    $currentUser = sd_current_user();
    if ($currentUser && sd_can_access_admin($currentUser)) {
        $isAuthorized = true;
    }
}

if (!$isAuthorized) {
    \SouthDistrict\API\Router::jsonError('Accès non autorisé. Clé API bot ou session administrateur requise.', 401);
}

$rawInput = file_get_contents('php://input');
$body = $rawInput ? json_decode($rawInput, true) : [];

$membersData = [];
$isFullSync = true;

if (is_array($body) && isset($body['members']) && is_array($body['members'])) {
    // Mode PUSH : Le bot Node.js envoie la liste complète des membres
    $membersData = $body['members'];
    $isFullSync = !empty($body['full_sync']);
} elseif (is_array($body) && isset($body['member']) && is_array($body['member'])) {
    // Mode Événement : Le bot envoie la mise à jour d'un seul membre
    $membersData = [$body['member']];
    $isFullSync = false;
} else {
    // Mode PULL : Interrogation directe de l'API REST de Discord via DISCORD_BOT_TOKEN
    $botToken = $config['bot_token'] ?? '';
    if ($botToken === '' || $botToken === 'votre_token_copié_ici') {
        \SouthDistrict\API\Router::jsonError("Aucune donnée reçue et aucun jeton DISCORD_BOT_TOKEN valide n'est configuré dans .env pour l'interrogation automatique.", 400);
    }

    $discordMembers = sd_fetch_discord_guild_members($botToken);
    if ($discordMembers === null) {
        \SouthDistrict\API\Router::jsonError("Impossible de récupérer les membres depuis l'API Discord. Vérifiez votre DISCORD_BOT_TOKEN et l'option 'Server Members Intent'.", 502);
    }

    foreach ($discordMembers as $dm) {
        $userObj = $dm['user'] ?? [];
        $dId = (string) ($userObj['id'] ?? '');
        if ($dId === '' || !empty($userObj['bot'])) {
            continue;
        }

        $avatarHash = $userObj['avatar'] ?? null;
        $avatarUrl = null;
        if (!empty($avatarHash)) {
            $ext = str_starts_with((string) $avatarHash, 'a_') ? 'gif' : 'png';
            $avatarUrl = sprintf('https://cdn.discordapp.com/avatars/%s/%s.%s', $dId, $avatarHash, $ext);
        } else {
            $discrim = (int) ($userObj['discriminator'] ?? 0);
            $idx = $discrim === 0 ? (((int) $dId >> 22) % 6) : ($discrim % 5);
            $avatarUrl = sprintf('https://cdn.discordapp.com/embed/avatars/%d.png', $idx);
        }

        $displayName = $dm['nick'] ?? $userObj['global_name'] ?? $userObj['username'] ?? 'Membre';

        $membersData[] = [
            'discord_id'   => $dId,
            'username'     => $userObj['username'] ?? '',
            'display_name' => $displayName,
            'avatar_url'   => $avatarUrl,
            'roles'        => is_array($dm['roles'] ?? null) ? $dm['roles'] : [],
        ];
    }
    $isFullSync = true;
}

try {
    $syncResult = sd_sync_team_from_discord_members($membersData, $isFullSync);
    sd_update_staff_role_colors_from_discord();

    // Nettoyer systématiquement tout compte bot ou résidu de test et anciens membres
    $botClientId = (string) ($config['client_id'] ?? '1546266986127036419');
    $pdo = \SouthDistrict\API\Database::getConnection();
    $pdo->prepare("DELETE FROM users WHERE discord_id = :bid OR login = :blg OR pseudo LIKE '%South District Bot%'")
        ->execute(['bid' => $botClientId, 'blg' => 'discord_' . $botClientId]);
    \SouthDistrict\API\Router::jsonSuccess([
        'message'     => 'Synchronisation Discord terminée avec succès.',
        'full_sync'   => $isFullSync,
        'stats'       => $syncResult,
    ]);
} catch (\Throwable $e) {
    \SouthDistrict\API\Router::jsonError('Erreur synchronisation : ' . $e->getMessage(), 500);
}
