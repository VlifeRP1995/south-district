<?php
/**
 * South District RP — Gestion des partenaires (Réservé CEO & Co-CEO)
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$user = sd_require_auth();

// Restriction stricte : CEO et Co-CEO uniquement
if (!sd_can_manage_role_definitions($user) && !sd_is_master_ceo($user)) {
    \SouthDistrict\API\Router::jsonError('Permission insuffisante. Accès strictement réservé à la Direction (CEO / Co-CEO).', 403);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pdo = \SouthDistrict\API\Database::getConnection();

if ($method === 'POST') {
    $input = \SouthDistrict\API\Router::jsonInput();
    $action = trim((string) ($input['action'] ?? $_POST['action'] ?? 'save'));

    if ($action === 'delete') {
        $id = (int) ($input['id'] ?? $_POST['id'] ?? 0);
        if ($id < 1) {
            \SouthDistrict\API\Router::jsonError('ID partenaire invalide.');
        }

        $stmt = $pdo->prepare('SELECT name FROM sd_partenaires WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            \SouthDistrict\API\Router::jsonError('Partenaire introuvable.', 404);
        }

        $pdo->prepare('DELETE FROM sd_partenaires WHERE id = :id')->execute(['id' => $id]);

        sd_log_site_event(
            $user,
            'partenaire_delete',
            sprintf('%s a supprimé le partenaire "%s"', $user['pseudo'], $row['name']),
            null,
            $row['name']
        );

        \SouthDistrict\API\Router::jsonSuccess(['message' => 'Partenaire supprimé avec succès.']);
    }

    $id = (int) ($input['id'] ?? 0);
    $name = trim((string) ($input['name'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $category = trim((string) ($input['category'] ?? 'Partenaire Officiel'));
    $logo = trim((string) ($input['logo'] ?? ''));
    $banner = trim((string) ($input['banner'] ?? ''));
    $link = trim((string) ($input['link'] ?? ''));
    $websiteLink = trim((string) ($input['website_link'] ?? ''));
    $rankOrder = (int) ($input['rank_order'] ?? 0);

    if ($name === '') {
        \SouthDistrict\API\Router::jsonError('Le nom du partenaire est obligatoire.');
    }

    if (mb_strlen($name) > 128) {
        \SouthDistrict\API\Router::jsonError('Le nom du partenaire ne doit pas dépasser 128 caractères.');
    }

    if ($id > 0) {
        // Mise à jour
        $stmt = $pdo->prepare('
            UPDATE sd_partenaires 
            SET name = :name, description = :desc, category = :cat, logo = :logo, banner = :banner, link = :link, website_link = :web_link, rank_order = :rank
            WHERE id = :id
        ');
        $stmt->execute([
            'id'       => $id,
            'name'     => $name,
            'desc'     => $description !== '' ? $description : null,
            'cat'      => $category !== '' ? $category : 'Partenaire Officiel',
            'logo'     => $logo !== '' ? $logo : null,
            'banner'   => $banner !== '' ? $banner : null,
            'link'     => $link !== '' ? $link : null,
            'web_link' => $websiteLink !== '' ? $websiteLink : null,
            'rank'     => $rankOrder,
        ]);

        sd_log_site_event(
            $user,
            'partenaire_update',
            sprintf('%s a modifié le partenaire "%s"', $user['pseudo'], $name),
            null,
            $name
        );

        \SouthDistrict\API\Router::jsonSuccess(['message' => 'Partenaire mis à jour avec succès.', 'id' => $id]);
    } else {
        // Création
        $stmt = $pdo->prepare('
            INSERT INTO sd_partenaires (name, description, category, logo, banner, link, website_link, rank_order)
            VALUES (:name, :desc, :cat, :logo, :banner, :link, :web_link, :rank)
        ');
        $stmt->execute([
            'name'     => $name,
            'desc'     => $description !== '' ? $description : null,
            'cat'      => $category !== '' ? $category : 'Partenaire Officiel',
            'logo'     => $logo !== '' ? $logo : null,
            'banner'   => $banner !== '' ? $banner : null,
            'link'     => $link !== '' ? $link : null,
            'web_link' => $websiteLink !== '' ? $websiteLink : null,
            'rank'     => $rankOrder,
        ]);
        $newId = (int) $pdo->lastInsertId();

        sd_log_site_event(
            $user,
            'partenaire_create',
            sprintf('%s a ajouté le partenaire "%s"', $user['pseudo'], $name),
            null,
            $name
        );

        \SouthDistrict\API\Router::jsonSuccess(['message' => 'Partenaire ajouté avec succès.', 'id' => $newId]);
    }
}

if ($method === 'DELETE') {
    $input = \SouthDistrict\API\Router::jsonInput();
    $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);
    if ($id < 1) {
        \SouthDistrict\API\Router::jsonError('ID partenaire invalide.');
    }

    $stmt = $pdo->prepare('SELECT name FROM sd_partenaires WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        \SouthDistrict\API\Router::jsonError('Partenaire introuvable.', 404);
    }

    $pdo->prepare('DELETE FROM sd_partenaires WHERE id = :id')->execute(['id' => $id]);

    sd_log_site_event(
        $user,
        'partenaire_delete',
        sprintf('%s a supprimé le partenaire "%s"', $user['pseudo'], $row['name']),
        null,
        $row['name']
    );

    \SouthDistrict\API\Router::jsonSuccess(['message' => 'Partenaire supprimé avec succès.']);
}

\SouthDistrict\API\Router::jsonError('Méthode non autorisée.', 405);

