<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('POST');

$input = \SouthDistrict\API\Router::jsonInput();

$login    = trim((string) ($input['login'] ?? ''));
$password = (string) ($input['password'] ?? '');
$pseudo   = trim((string) ($input['pseudo'] ?? ''));
$email    = trim((string) ($input['email'] ?? ''));

if ($err = sd_validate_login($login)) {
    \SouthDistrict\API\Router::jsonError($err);
}
if ($err = sd_validate_password($password)) {
    \SouthDistrict\API\Router::jsonError($err);
}
if ($pseudo === '' || strlen($pseudo) > 64) {
    \SouthDistrict\API\Router::jsonError('Pseudo requis (max 64 caractères).');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    \SouthDistrict\API\Router::jsonError('Adresse e-mail invalide.');
}

$pdo = \SouthDistrict\API\Database::getConnection();

$stmt = $pdo->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
$stmt->execute(['login' => $login]);
if ($stmt->fetch()) {
    \SouthDistrict\API\Router::jsonError('Cet identifiant est déjà utilisé.', 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (login, password_hash, pseudo, email, site_role, staff_role)
     VALUES (:login, :password_hash, :pseudo, :email, :site_role, NULL)'
);
$stmt->execute([
    'login'         => $login,
    'password_hash' => $hash,
    'pseudo'        => $pseudo,
    'email'         => $email !== '' ? $email : null,
    'site_role'     => 'citoyen',
]);

$userId = (int) $pdo->lastInsertId();
sd_create_session($userId);

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

\SouthDistrict\API\Router::jsonSuccess(['user' => sd_public_user($user)], 201);

