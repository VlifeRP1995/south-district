<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$discord = sd_config()['discord'] ?? null;
if (!$discord || empty($discord['client_id'])) {
    http_response_code(500);
    echo "Erreur : La connexion Discord n'est pas configurée.";
    exit;
}

$appUrl = rtrim(sd_config()['app']['url'], '/');
$redirectUri = $discord['redirect_uri'] ?? ($appUrl . '/api/auth/discord-callback.php');

$state = bin2hex(random_bytes(16));
$secure = (bool) (sd_config()['app']['cookie_secure'] ?? true);
$samesite = sd_config()['app']['cookie_samesite'] ?? 'Lax';
setcookie('sd_discord_state', $state, [
    'expires'  => time() + 600,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => $samesite,
]);

$params = [
    'client_id'     => $discord['client_id'],
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'identify guilds.members.read',
    'state'         => $state,
];

$url = 'https://discord.com/api/oauth2/authorize?' . http_build_query($params);
header('Location: ' . $url);
exit;

