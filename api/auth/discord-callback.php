<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

function render_discord_error(string $message): never
{
    $appUrl = rtrim(sd_config()['app']['url'], '/');
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur d'authentification | South District</title>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($appUrl); ?>/css/fonts.css">
        <style>
            body {
                background: #0b0c10;
                color: #ffffff;
                font-family: 'Inter', system-ui, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .error-card {
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: var(--radius-md);
                padding: 2.5rem;
                text-align: center;
                max-width: 450px;
                width: 90%;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            }
            h1 {
                color: #ff7b7b;
                font-size: 1.5rem;
                margin-top: 0;
            }
            p {
                color: rgba(255, 255, 255, 0.7);
                font-size: 0.95rem;
                line-height: 1.6;
                margin-bottom: 2rem;
            }
            .btn {
                display: inline-block;
                background: #1ee6a0;
                color: #071612;
                text-decoration: none;
                padding: 0.8rem 2rem;
                border-radius: var(--radius-md);
                font-weight: 700;
                font-size: 0.95rem;
                transition: background 0.2s;
            }
            .btn:hover {
                background: #17b880;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h1>Connexion Discord échouée</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="<?php echo htmlspecialchars($appUrl); ?>/accueil" class="btn">Retour à l'accueil</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$discord = sd_config()['discord'] ?? null;
if (!$discord || empty($discord['client_id']) || empty($discord['client_secret'])) {
    render_discord_error("La connexion Discord n'est pas configurée dans config.php.");
}

$cookieState = $_COOKIE['sd_discord_state'] ?? '';
$paramState = $_GET['state'] ?? '';

setcookie('sd_discord_state', '', time() - 3600, '/');

if ($cookieState === '' || $paramState === '' || $cookieState !== $paramState) {
    render_discord_error("Session d'authentification expirée ou invalide. Veuillez réessayer.");
}

$code = $_GET['code'] ?? '';
if ($code === '') {
    render_discord_error("Code d'autorisation Discord manquant.");
}

$appUrl = rtrim(sd_config()['app']['url'], '/');
$redirectUri = $discord['redirect_uri'] ?? ($appUrl . '/api/auth/discord-callback.php');

$tokenUrl = 'https://discord.com/api/oauth2/token';
$tokenFields = [
    'client_id'     => $discord['client_id'],
    'client_secret' => $discord['client_secret'],
    'grant_type'    => 'authorization_code',
    'code'          => $code,
    'redirect_uri'  => $redirectUri,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    render_discord_error("Impossible d'échanger le code d'authentification avec Discord.");
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? '';
$refreshToken = $tokenData['refresh_token'] ?? null;
$expiresIn = (int) ($tokenData['expires_in'] ?? 604800);

if ($accessToken === '') {
    render_discord_error("Jeton d'accès Discord introuvable.");
}

$userUrl = 'https://discord.com/api/users/@me';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
]);

$userResponse = curl_exec($ch);
$userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userHttpCode !== 200 || !$userResponse) {
    render_discord_error("Impossible de récupérer vos informations depuis Discord.");
}

$discordUser = json_decode($userResponse, true);
$discordId = $discordUser['id'] ?? '';
if ($discordId === '') {
    render_discord_error("Identifiant Discord non renvoyé par l'API.");
}

$avatarUrl = null;
if (!empty($discordUser['avatar'])) {
    $ext = str_starts_with($discordUser['avatar'], 'a_') ? 'gif' : 'png';
    $avatarUrl = sprintf(
        'https://cdn.discordapp.com/avatars/%s/%s.%s',
        $discordId,
        $discordUser['avatar'],
        $ext
    );
} else {
    $discriminator = (int) ($discordUser['discriminator'] ?? 0);
    if ($discriminator === 0) {
        $index = ((int) $discordId >> 22) % 6;
    } else {
        $index = $discriminator % 5;
    }
    $avatarUrl = sprintf('https://cdn.discordapp.com/embed/avatars/%d.png', $index);
}

$pdo = \SouthDistrict\API\Database::getConnection();

$stmt = $pdo->prepare('SELECT * FROM users WHERE discord_id = :discord_id LIMIT 1');
$stmt->execute(['discord_id' => $discordId]);
$user = $stmt->fetch();

if ($user) {
    // Si l'utilisateur est banni
    if (!empty($user['is_banned'])) {
        $reason = trim((string) ($user['ban_reason'] ?? ''));
        render_discord_error($reason !== '' ? "Compte banni : $reason" : "Ce compte est banni.");
    }

    // Mise à jour des tokens et de l'avatar
    $upd = $pdo->prepare(
        'UPDATE users 
         SET discord_access_token = :at,
             discord_refresh_token = COALESCE(:rt, discord_refresh_token),
             discord_token_expires_at = DATE_ADD(NOW(), INTERVAL :exp SECOND),
             avatar = CASE WHEN avatar LIKE \'https://cdn.discordapp.com/%\' OR avatar IS NULL THEN :avatar ELSE avatar END,
             updated_at = NOW() 
         WHERE id = :id'
    );
    $upd->execute([
        'at'     => $accessToken,
        'rt'     => $refreshToken,
        'exp'    => $expiresIn,
        'avatar' => $avatarUrl,
        'id'     => (int) $user['id'],
    ]);

    // Synchronisation automatique des rôles Discord (avec force = true au login)
    sd_sync_discord_user_roles((int) $user['id'], $accessToken, true);

    sd_create_session((int) $user['id']);
    header('Location: ' . $appUrl . '/accueil');
    exit;
}

$email = $discordUser['email'] ?? null;
$verified = !empty($discordUser['verified']);

if ($email !== null && $verified) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if (!empty($existing['is_banned'])) {
            $reason = trim((string) ($existing['ban_reason'] ?? ''));
            render_discord_error($reason !== '' ? "Compte banni : $reason" : "Ce compte est banni.");
        }

        // Si ce compte est déjà lié à un AUTRE compte Discord
        if (!empty($existing['discord_id'])) {
            render_discord_error("Cette adresse e-mail est déjà associée à un autre compte Discord.");
        }

        // Liaison et sauvegarde des tokens
        $upd = $pdo->prepare(
            'UPDATE users 
             SET discord_id = :discord_id, 
                 discord_access_token = :at,
                 discord_refresh_token = :rt,
                 discord_token_expires_at = DATE_ADD(NOW(), INTERVAL :exp SECOND),
                 avatar = COALESCE(avatar, :avatar), 
                 updated_at = NOW() 
             WHERE id = :id'
        );
        $upd->execute([
            'discord_id' => $discordId,
            'at'         => $accessToken,
            'rt'         => $refreshToken,
            'exp'        => $expiresIn,
            'avatar'     => $avatarUrl,
            'id'         => (int) $existing['id'],
        ]);

        // Synchronisation automatique des rôles Discord
        sd_sync_discord_user_roles((int) $existing['id'], $accessToken, true);

        sd_create_session((int) $existing['id']);
        header('Location: ' . $appUrl . '/accueil');
        exit;
    }
}

// C. Création d'un nouveau compte citoyen
$login = 'discord_' . $discordId;
$pseudo = $discordUser['global_name'] ?? $discordUser['username'] ?? 'Citoyen';
$password = bin2hex(random_bytes(16));
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (login, password_hash, pseudo, email, site_role, staff_role, discord_id, discord_access_token, discord_refresh_token, discord_token_expires_at, avatar)
     VALUES (:login, :password_hash, :pseudo, :email, \'citoyen\', NULL, :discord_id, :at, :rt, DATE_ADD(NOW(), INTERVAL :exp SECOND), :avatar)'
);
$stmt->execute([
    'login'         => $login,
    'password_hash' => $hash,
    'pseudo'        => $pseudo,
    'email'         => $verified ? $email : null,
    'discord_id'    => $discordId,
    'at'            => $accessToken,
    'rt'            => $refreshToken,
    'exp'           => $expiresIn,
    'avatar'        => $avatarUrl,
]);

$userId = (int) $pdo->lastInsertId();

// Synchronisation automatique des rôles Discord
sd_sync_discord_user_roles($userId, $accessToken, true);

sd_create_session($userId);

header('Location: ' . $appUrl . '/accueil');
exit;

