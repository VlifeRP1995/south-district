<?php
/**
 * South District RP - Absences staff (accueil admin)
 * GET    -> liste
 * POST   -> créer { start_date, end_date, justification }
 * DELETE -> supprimer { id } (auteur ou admin site / CEO / Co-CEO)
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET', 'POST', 'DELETE', 'OPTIONS');

function sd_absences_can_moderate(array $user): bool
{
    return sd_user_has_permission($user, 'manage_gerant_staff');
}

/** @return array<string, mixed> */
function sd_absence_row(array $row): array
{
    return [
        'id'             => (int) $row['id'],
        'user_id'        => (int) $row['user_id'],
        'pseudo'         => $row['pseudo'],
        'start_date'     => $row['start_date'],
        'end_date'       => $row['end_date'],
        'justification'  => $row['justification'],
        'created_at'     => $row['created_at'],
    ];
}

try {
    $user = sd_require_auth();
    if (!sd_can_access_admin($user)) {
        \SouthDistrict\API\Router::jsonError('Accès admin requis.', 403);
    }

    $pdo = \SouthDistrict\API\Database::getConnection();
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        $stmt = $pdo->query('
            SELECT id, user_id, pseudo, start_date, end_date, justification, created_at
            FROM sd_staff_absences
            ORDER BY start_date DESC, created_at DESC
            LIMIT 100
        ');
        $rows = $stmt ? $stmt->fetchAll() : [];
        $absences = array_map('sd_absence_row', $rows);
        \SouthDistrict\API\Router::jsonSuccess(['absences' => $absences]);
    }

    if ($method === 'POST') {
        $input = \SouthDistrict\API\Router::jsonInput();
        $start = trim((string) ($input['start_date'] ?? ''));
        $end = trim((string) ($input['end_date'] ?? ''));
        $justification = trim((string) ($input['justification'] ?? ''));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            \SouthDistrict\API\Router::jsonError('Dates invalides.');
        }
        if ($end < $start) {
            \SouthDistrict\API\Router::jsonError('La date de retour doit être après (ou égale) au départ.');
        }
        if (mb_strlen($justification) < 10) {
            \SouthDistrict\API\Router::jsonError('La justification doit contenir au moins 10 caractères.');
        }
        if (mb_strlen($justification) > 2000) {
            \SouthDistrict\API\Router::jsonError('Justification trop longue (max 2000 caractères).');
        }

        $stmt = $pdo->prepare('
            INSERT INTO sd_staff_absences (user_id, pseudo, start_date, end_date, justification)
            VALUES (:uid, :pseudo, :start, :end, :just)
        ');
        $stmt->execute([
            'uid'    => (int) $user['id'],
            'pseudo' => (string) $user['pseudo'],
            'start'  => $start,
            'end'    => $end,
            'just'   => htmlspecialchars((string) $justification, ENT_QUOTES, 'UTF-8'),
        ]);

        $id = (int) $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM sd_staff_absences WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        sd_log_site_event(
            $user,
            'absence_create',
            sprintf('%s a déclaré une absence', $user['pseudo']),
            null,
            sprintf('%s → %s', $start, $end)
        );

        \SouthDistrict\API\Router::jsonSuccess(['absence' => sd_absence_row($row)]);
    }

    if ($method === 'DELETE') {
        $input = \SouthDistrict\API\Router::jsonInput();
        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);
        if ($id < 1) {
            \SouthDistrict\API\Router::jsonError('Absence introuvable.');
        }

        $stmt = $pdo->prepare('SELECT * FROM sd_staff_absences WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            \SouthDistrict\API\Router::jsonError('Absence introuvable.', 404);
        }

        $isOwner = (int) $row['user_id'] === (int) $user['id'];
        if (!$isOwner && !sd_absences_can_moderate($user)) {
            \SouthDistrict\API\Router::jsonError('Permission insuffisante.', 403);
        }

        $pdo->prepare('DELETE FROM sd_staff_absences WHERE id = :id')->execute(['id' => $id]);

        sd_log_site_event(
            $user,
            'absence_delete',
            sprintf('%s a retiré une absence', $user['pseudo']),
            null,
            sprintf('%s · %s → %s', $row['pseudo'], $row['start_date'], $row['end_date'])
        );

        \SouthDistrict\API\Router::jsonSuccess(['deleted' => true]);
    }

    \SouthDistrict\API\Router::jsonError('Méthode non supportée.', 405);
} catch (Throwable $e) {
    \SouthDistrict\API\Router::jsonError('Erreur serveur.', 500);
}

