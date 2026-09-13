<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('PATCH', 'PUT');

$user = sd_require_auth();
$input = \SouthDistrict\API\Router::jsonInput();

$pseudo          = array_key_exists('pseudo', $input) ? trim((string) $input['pseudo']) : null;
$email           = array_key_exists('email', $input) ? trim((string) $input['email']) : null;
$signature       = array_key_exists('signature', $input) ? (string) $input['signature'] : null;
$newPassword     = array_key_exists('password', $input) ? (string) $input['password'] : null;
$currentPassword = array_key_exists('current_password', $input) ? (string) $input['current_password'] : null;

$fields = [];
$params = ['id' => (int) $user['id']];

if ($pseudo !== null) {
    if ($pseudo === '' || strlen($pseudo) > 64) {
        \SouthDistrict\API\Router::jsonError('Pseudo invalide (max 64 caractères).');
    }
    $fields[] = 'pseudo = :pseudo';
    $params['pseudo'] = $pseudo;
}

if ($email !== null) {
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        \SouthDistrict\API\Router::jsonError('Adresse e-mail invalide.');
    }
    $fields[] = 'email = :email';
    $params['email'] = $email !== '' ? $email : null;
}

if ($signature !== null) {
    if ($signature === '') {
        $fields[] = 'signature = :signature';
        $params['signature'] = null;
    } else {
        $sigDir = sd_config()['uploads']['signature_dir'];
        $saved = sd_save_png_data_url($sigDir, (string) $user['id'], $signature);
        $fields[] = 'signature = :signature';
        $params['signature'] = $saved ?? $signature;
    }
}

if ($newPassword !== null && $newPassword !== '') {
    if ($err = sd_validate_password($newPassword)) {
        \SouthDistrict\API\Router::jsonError($err);
    }
    if ($currentPassword === '' || !password_verify($currentPassword, $user['password_hash'])) {
        \SouthDistrict\API\Router::jsonError('Mot de passe actuel incorrect.', 403);
    }
    $fields[] = 'password_hash = :password_hash';
    $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
}

if ($fields === []) {
    \SouthDistrict\API\Router::jsonError('Aucune donnée à mettre à jour.');
}

$pdo = \SouthDistrict\API\Database::getConnection();
$sql = 'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => (int) $user['id']]);
$updated = $stmt->fetch();

\SouthDistrict\API\Router::jsonSuccess(['user' => sd_public_user($updated)]);

