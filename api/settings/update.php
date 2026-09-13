<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('PATCH', 'PUT');

$user = sd_require_auth();
if (!sd_user_has_permission($user, 'manage_gerant_staff')) {
    \SouthDistrict\API\Router::jsonError('Permission insuffisante.', 403);
}

$input = \SouthDistrict\API\Router::jsonInput();
$pdo = \SouthDistrict\API\Database::getConnection();

if (array_key_exists('recruitment_open', $input)) {
    $val = json_encode((bool) $input['recruitment_open']);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    $stmt->execute(['k' => 'recruitment_open', 'v' => $val, 'v2' => $val, 'u' => $user['id'], 'u2' => $user['id']]);
}

if (array_key_exists('recruitment_message', $input)) {
    $msg = trim((string) $input['recruitment_message']);
    $val = json_encode($msg);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    $stmt->execute(['k' => 'recruitment_message', 'v' => $val, 'v2' => $val, 'u' => $user['id'], 'u2' => $user['id']]);
}

if (array_key_exists('recruitment_questions', $input)) {
    $raw = $input['recruitment_questions'];
    if (!is_array($raw)) {
        \SouthDistrict\API\Router::jsonError('recruitment_questions doit être un tableau.');
    }
    $questions = sd_normalize_recruitment_questions($raw);
    if ($questions === []) {
        \SouthDistrict\API\Router::jsonError('Au moins une question est requise.');
    }
    $val = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    $stmt->execute(['k' => 'recruitment_questions', 'v' => $val, 'v2' => $val, 'u' => $user['id'], 'u2' => $user['id']]);
}

if (array_key_exists('recruitment_postes', $input)) {
    $raw = $input['recruitment_postes'];
    if (!is_array($raw)) {
        \SouthDistrict\API\Router::jsonError('recruitment_postes doit être un tableau.');
    }
    $postes = sd_normalize_recruitment_postes($raw);
    if ($postes === []) {
        \SouthDistrict\API\Router::jsonError('Ajoutez au moins un poste ouvert à la candidature.');
    }
    $val = json_encode($postes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    $stmt->execute(['k' => 'recruitment_postes', 'v' => $val, 'v2' => $val, 'u' => $user['id'], 'u2' => $user['id']]);

    $rolesVal = json_encode(array_column($postes, 'key'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt->execute(['k' => 'recruitment_open_roles', 'v' => $rolesVal, 'v2' => $rolesVal, 'u' => $user['id'], 'u2' => $user['id']]);
} elseif (array_key_exists('recruitment_open_roles', $input)) {
    $raw = $input['recruitment_open_roles'];
    if (!is_array($raw)) {
        \SouthDistrict\API\Router::jsonError('recruitment_open_roles doit être un tableau.');
    }
    $legacy = [];
    foreach ($raw as $key) {
        $legacy[] = ['key' => (string) $key, 'label' => ''];
    }
    $postes = sd_normalize_recruitment_postes($legacy);
    if ($postes === []) {
        \SouthDistrict\API\Router::jsonError('Sélectionnez au moins un poste ouvert à la candidature.');
    }
    $val = json_encode($postes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    $stmt->execute(['k' => 'recruitment_postes', 'v' => $val, 'v2' => $val, 'u' => $user['id'], 'u2' => $user['id']]);

    $rolesVal = json_encode(array_column($postes, 'key'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt->execute(['k' => 'recruitment_open_roles', 'v' => $rolesVal, 'v2' => $rolesVal, 'u' => $user['id'], 'u2' => $user['id']]);
}

$openLabel = array_key_exists('recruitment_open', $input)
    ? ((bool) $input['recruitment_open'] ? 'ouvert' : 'fermé')
    : null;
$detailParts = [];
if ($openLabel !== null) {
    $detailParts[] = 'Recrutement ' . $openLabel;
}
if (array_key_exists('recruitment_message', $input)) {
    $detailParts[] = 'Message mis à jour';
}
if (array_key_exists('recruitment_questions', $input)) {
    $detailParts[] = 'Questions du formulaire mises à jour';
}
if (array_key_exists('recruitment_postes', $input) || array_key_exists('recruitment_open_roles', $input)) {
    $detailParts[] = 'Postes ouverts mis à jour';
}

if (array_key_exists('candidature_webhook_url', $input)) {
    $wh = trim((string) $input['candidature_webhook_url']);
    $val = json_encode($wh, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2, updated_at = NOW()'
    );
    $stmt->execute(['k' => 'candidature_webhook_url', 'v' => $val, 'v2' => $val, 'u' => $user['id'], 'u2' => $user['id']]);
    $detailParts[] = 'Webhook Discord candidatures mis à jour';
}

sd_log_site_event(
    $user,
    'settings_update',
    sprintf('%s a modifié les paramètres recrutement', $user['pseudo']),
    null,
    $detailParts ? implode(' · ', $detailParts) : 'Paramètres enregistrés'
);

\SouthDistrict\API\Router::jsonSuccess(['settings' => sd_load_site_settings()]);

