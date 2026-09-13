<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('POST');

$input = \SouthDistrict\API\Router::jsonInput();

$login    = trim((string) ($input['login'] ?? ''));
$password = (string) ($input['password'] ?? '');

if ($login === '' || $password === '') {
    \SouthDistrict\API\Router::jsonError('Identifiant et mot de passe requis.');
}

$pdo = \SouthDistrict\API\Database::getConnection();
$stmt = $pdo->prepare('SELECT * FROM users WHERE login = :login LIMIT 1');
$stmt->execute(['login' => $login]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    \SouthDistrict\API\Router::jsonError('Identifiants incorrects.', 401);
}

if (!empty($user['is_banned'])) {
    $reason = trim((string) ($user['ban_reason'] ?? ''));
    \SouthDistrict\API\Router::jsonError(
        $reason !== ''
            ? ('Compte banni : ' . $reason)
            : 'Compte banni. Tu n\'as plus accès au site ni à l\'espace admin.',
        403
    );
}

sd_create_session((int) $user['id']);

$payload = ['user' => sd_public_user($user)];

$stmt = \SouthDistrict\API\Database::getConnection()->prepare(
    'SELECT * FROM candidatures WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1'
);
$stmt->execute(['user_id' => (int) $user['id']]);
$candidature = $stmt->fetch();
if ($candidature) {
    $payload['candidature'] = sd_public_candidature($candidature);
}

\SouthDistrict\API\Router::jsonSuccess($payload);

