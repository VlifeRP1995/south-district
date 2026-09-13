<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



\SouthDistrict\API\Router::requireMethod('GET');



$user = sd_require_auth();

if (!sd_user_has_permission($user, 'manage_gerant_staff')) {

    \SouthDistrict\API\Router::jsonError('Permission insuffisante.', 403);

}



$pdo = \SouthDistrict\API\Database::getConnection();



$status   = trim((string) ($_GET['status'] ?? ''));

$search   = trim((string) ($_GET['search'] ?? ''));

$archived = trim((string) ($_GET['archived'] ?? '0'));

$limit    = min(100, max(1, (int) ($_GET['limit'] ?? 50)));

$offset   = max(0, (int) ($_GET['offset'] ?? 0));



$archivedFlag = in_array(strtolower($archived), ['1', 'true', 'yes'], true) ? 1 : 0;

if ($archivedFlag) {
    $sql = sd_candidature_select_sql() . " WHERE (c.is_archived = 1 OR c.status IN ('accepte', 'refuse'))";
    $params = [];
} else {
    $sql = sd_candidature_select_sql() . " WHERE (c.is_archived = 0 OR c.is_archived IS NULL) AND c.status NOT IN ('accepte', 'refuse')";
    $params = [];
}

if ($status !== '') {
    if (!in_array($status, SD_CANDIDATURE_STATUSES, true)) {
        \SouthDistrict\API\Router::jsonError('Statut invalide.');
    }
    $sql .= ' AND c.status = :status';
    $params['status'] = $status;
}

if ($search !== '') {
    $sql .= ' AND (
        c.code LIKE :s1 OR c.pseudo_discord LIKE :s2 OR c.pseudo_rp LIKE :s3 OR c.motivation LIKE :s4
        OR c.experience LIKE :s5 OR c.scenario LIKE :s6 OR c.form_responses LIKE :s7
        OR u.pseudo LIKE :s8 OR u.login LIKE :s9 OR u.email LIKE :s10
    )';
    for ($i = 1; $i <= 10; $i++) {
        $params['s' . $i] = '%' . $search . '%';
    }
}

$sql .= ' ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset';

try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $candidatures = array_map(
        static fn(array $row): array => sd_public_candidature($row, true, 'admin'),
        $stmt->fetchAll()
    );

    $countSql = $archivedFlag
        ? "SELECT COUNT(*) FROM candidatures c LEFT JOIN users u ON u.id = c.user_id WHERE (c.is_archived = 1 OR c.status IN ('accepte', 'refuse'))"
        : "SELECT COUNT(*) FROM candidatures c LEFT JOIN users u ON u.id = c.user_id WHERE (c.is_archived = 0 OR c.is_archived IS NULL) AND c.status NOT IN ('accepte', 'refuse')";
    $countParams = [];

    if ($status !== '') {
        $countSql .= ' AND c.status = :status';
        $countParams['status'] = $status;
    }

    if ($search !== '') {
        $countSql .= ' AND (
            c.code LIKE :cs1 OR c.pseudo_discord LIKE :cs2 OR c.pseudo_rp LIKE :cs3 OR c.motivation LIKE :cs4
            OR c.experience LIKE :cs5 OR c.scenario LIKE :cs6 OR c.form_responses LIKE :cs7
            OR u.pseudo LIKE :cs8 OR u.login LIKE :cs9 OR u.email LIKE :cs10
        )';
        for ($i = 1; $i <= 10; $i++) {
            $countParams['cs' . $i] = '%' . $search . '%';
        }
    }

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $total = (int) $countStmt->fetchColumn();

    \SouthDistrict\API\Router::jsonSuccess([
        'candidatures' => $candidatures,
        'total'        => $total,
        'limit'        => $limit,
        'offset'       => $offset,
        'archived'     => (bool) $archivedFlag,
    ]);
} catch (\Throwable $e) {
    \SouthDistrict\API\Router::jsonError('Erreur lors du chargement des candidatures: ' . $e->getMessage(), 500);
}


