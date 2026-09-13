<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



\SouthDistrict\API\Router::requireMethod('PATCH', 'PUT', 'POST');



$reviewer = sd_require_auth();

if (!sd_user_has_permission($reviewer, 'manage_gerant_staff')) {

    \SouthDistrict\API\Router::jsonError('Permission insuffisante.', 403);

}



$input = \SouthDistrict\API\Router::jsonInput();



$id                 = (int) ($input['id'] ?? $input['candidature_id'] ?? 0);

$code               = strtoupper(trim((string) ($input['code'] ?? '')));

$status             = trim((string) ($input['status'] ?? ''));

$reviewNote         = array_key_exists('review_note', $input) ? trim((string) $input['review_note']) : null;
if ($reviewNote !== null) {
    $reviewNote = strip_tags($reviewNote);
    $reviewNote = preg_replace('/Dernière action par\s*:.*?(\([^\)]+\)|$)/ui', '', $reviewNote);
    $reviewNote = trim($reviewNote);
}

$convocationMessage = array_key_exists('convocation_message', $input)

    ? trim((string) $input['convocation_message'])

    : null;

$archiveOnly        = !empty($input['archive']);



$pdo = \SouthDistrict\API\Database::getConnection();



if ($id > 0) {

    $stmt = $pdo->prepare(sd_candidature_select_sql() . ' WHERE c.id = :id LIMIT 1');

    $stmt->execute(['id' => $id]);

} elseif ($code !== '') {

    $stmt = $pdo->prepare(sd_candidature_select_sql() . ' WHERE c.code = :code LIMIT 1');

    $stmt->execute(['code' => $code]);

} else {

    \SouthDistrict\API\Router::jsonError('id ou code requis.');

}



$row = $stmt->fetch();

if (!$row) {

    \SouthDistrict\API\Router::jsonError('Candidature introuvable.', 404);

}



sd_backup_candidature($row, $archiveOnly ? 'before_archive' : 'before_review', $reviewer);



if ($archiveOnly) {

    $update = $pdo->prepare(

        'UPDATE candidatures SET is_archived = 1, archived_at = NOW(), reviewer_id = :reviewer_id, reviewed_at = COALESCE(reviewed_at, NOW()), updated_at = NOW() WHERE id = :id'

    );

    $update->execute([
        'id'          => (int) $row['id'],
        'reviewer_id' => (int) $reviewer['id'],
    ]);



    sd_log_site_event(

        $reviewer,

        'candidature_review',

        sprintf('%s a archivé la candidature %s', $reviewer['pseudo'], $row['pseudo_discord']),

        null,

        'Dossier déplacé dans les archives · sauvegarde créée'

    );



    sd_notify_candidature_review_webhook([
        'action'      => 'archive',
        'reviewer'    => $reviewer,
        'candidature' => $row,
        'old_status'  => (string) ($row['status'] ?? ''),
        'new_status'  => 'archive',
        'review_note' => $reviewNote,
    ]);



    $stmt = $pdo->prepare(sd_candidature_select_sql() . ' WHERE c.id = :id LIMIT 1');

    $stmt->execute(['id' => (int) $row['id']]);

    \SouthDistrict\API\Router::jsonSuccess(['candidature' => sd_public_candidature($stmt->fetch(), true, 'admin')]);

}



if ($status === '' || !in_array($status, SD_CANDIDATURE_STATUSES, true)) {

    \SouthDistrict\API\Router::jsonError('Statut invalide.');

}



$autoArchive = in_array($status, ['accepte', 'refuse'], true);

$archiveSql = $autoArchive ? ', is_archived = 1, archived_at = NOW()' : '';



$params = [

    'status'              => $status,

    'reviewer_id'         => (int) $reviewer['id'],

    'review_note'         => $reviewNote !== null && $reviewNote !== '' ? $reviewNote : null,

    'convocation_message' => $convocationMessage !== null && $convocationMessage !== '' ? $convocationMessage : null,

    'id'                  => (int) $row['id'],

];



$update = $pdo->prepare(

    "UPDATE candidatures

     SET status = :status,

         reviewer_id = :reviewer_id,

         review_note = :review_note,

         convocation_message = :convocation_message,

         reviewed_at = NOW(),

         updated_at = NOW()

         {$archiveSql}

     WHERE id = :id"

);

$update->execute($params);



$statusLabel = sd_candidature_status_label($status);

// Attribution automatique du grade au candidat si la candidature est acceptée
$roleGrantedMsg = '';
if ($status === 'accepte') {
    $candidateUserId = isset($row['user_id']) ? (int) $row['user_id'] : 0;
    if ($candidateUserId <= 0 && !empty($row['pseudo_discord'])) {
        // Tentative de recherche par pseudo_discord si non lié
        $findU = $pdo->prepare('SELECT id FROM users WHERE pseudo = :p OR login = :l LIMIT 1');
        $findU->execute(['p' => $row['pseudo_discord'], 'l' => $row['pseudo_discord']]);
        $foundUid = $findU->fetchColumn();
        if ($foundUid) {
            $candidateUserId = (int) $foundUid;
        }
    }

    if ($candidateUserId > 0) {
        $stmtCandUser = $pdo->prepare('SELECT id, pseudo, staff_role, secondary_roles, site_role FROM users WHERE id = :id LIMIT 1');
        $stmtCandUser->execute(['id' => $candidateUserId]);
        $candUser = $stmtCandUser->fetch();

        if ($candUser) {
            $targetRoleKey = strtolower(trim((string) $row['poste']));
            $currentRank = sd_staff_rank($candUser['staff_role'] ?? null);
            $newRank = sd_staff_rank($targetRoleKey);

            $adminSiteRoles = ['createur', 'ceo', 'co_ceo', 'developer'];
            $newSiteRole = in_array($targetRoleKey, $adminSiteRoles, true) ? 'admin' : ($candUser['site_role'] ?? 'citoyen');

            // Si le candidat n'a pas de grade ou si le nouveau grade est supérieur
            if ($currentRank === 0 || $newRank > $currentRank) {
                // L'ancien rôle devient rôle secondaire si ce n'était pas vide
                $secRoles = !empty($candUser['secondary_roles']) ? (json_decode($candUser['secondary_roles'], true) ?: []) : [];
                if (!empty($candUser['staff_role']) && !in_array($candUser['staff_role'], $secRoles, true)) {
                    $secRoles[] = $candUser['staff_role'];
                }
                // Retirer le nouveau rôle des secondaires s'il y était
                $secRoles = array_values(array_diff($secRoles, [$targetRoleKey]));

                $updUser = $pdo->prepare(
                    'UPDATE users 
                     SET staff_role = :role, 
                         secondary_roles = :sec, 
                         site_role = :site_role, 
                         is_staff_visible = 1, 
                         updated_at = NOW() 
                     WHERE id = :id'
                );
                $updUser->execute([
                    'role'      => $targetRoleKey,
                    'sec'       => json_encode($secRoles, JSON_UNESCAPED_UNICODE),
                    'site_role' => $newSiteRole,
                    'id'        => $candidateUserId,
                ]);

                $roleGrantedMsg = sprintf(" · Grade '%s' attribué automatiquement au compte de %s", sd_staff_role_label($targetRoleKey), $candUser['pseudo']);
            } else {
                // Ajouter comme rôle secondaire (double rôle) si le candidat a déjà un grade supérieur
                $secRoles = !empty($candUser['secondary_roles']) ? (json_decode($candUser['secondary_roles'], true) ?: []) : [];
                if (!in_array($targetRoleKey, $secRoles, true) && $targetRoleKey !== $candUser['staff_role']) {
                    $secRoles[] = $targetRoleKey;
                    $updUser = $pdo->prepare('UPDATE users SET secondary_roles = :sec, updated_at = NOW() WHERE id = :id');
                    $updUser->execute([
                        'sec' => json_encode($secRoles, JSON_UNESCAPED_UNICODE),
                        'id'  => $candidateUserId,
                    ]);
                    $roleGrantedMsg = sprintf(" · Rôle secondaire '%s' ajouté automatiquement au compte de %s", sd_staff_role_label($targetRoleKey), $candUser['pseudo']);
                }
            }
        }
    }
}

sd_log_site_event(
    $reviewer,
    'candidature_review',
    sprintf('%s a mis à jour %s → %s', $reviewer['pseudo'], $row['pseudo_discord'], $statusLabel),
    null,
    $autoArchive
        ? ('Décision enregistrée · dossier archivé automatiquement · backup créé' . $roleGrantedMsg)
        : ('Statut mis à jour · backup créé' . $roleGrantedMsg)
);

sd_notify_candidature_review_webhook([
    'action'              => $status,
    'reviewer'            => $reviewer,
    'candidature'         => $row,
    'old_status'          => (string) ($row['status'] ?? ''),
    'new_status'          => $status,
    'review_note'         => $reviewNote,
    'convocation_message' => $convocationMessage,
    'auto_archived'       => $autoArchive,
    'role_granted_msg'    => $roleGrantedMsg,
]);

$stmt = $pdo->prepare(sd_candidature_select_sql() . ' WHERE c.id = :id LIMIT 1');

$stmt->execute(['id' => (int) $row['id']]);

$updated = $stmt->fetch();



\SouthDistrict\API\Router::jsonSuccess(['candidature' => sd_public_candidature($updated, true, 'admin')]);


