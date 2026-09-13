<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET');

$user = sd_require_auth();
if (
    !sd_can_manage_role_definitions($user)
    && !sd_user_has_permission($user, 'manage_permissions')
    && !sd_user_has_permission($user, 'manage_gerant_staff')
) {
    \SouthDistrict\API\Router::jsonError('Permission insuffisante.', 403);
}

$pdo = \SouthDistrict\API\Database::getConnection();

$search = trim((string) ($_GET['search'] ?? ''));
$limit  = min(200, max(1, (int) ($_GET['limit'] ?? 100)));
$offset = max(0, (int) ($_GET['offset'] ?? 0));

$sql = 'SELECT * FROM users';
$params = [];

if ($search !== '') {
    $sql .= ' WHERE login LIKE :s1 OR pseudo LIKE :s2 OR email LIKE :s3';
    $params['s1'] = '%' . $search . '%';
    $params['s2'] = '%' . $search . '%';
    $params['s3'] = '%' . $search . '%';
}

$sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$users = array_map(static fn(array $row): array => sd_public_user($row), $stmt->fetchAll());

$countSql = 'SELECT COUNT(*) FROM users';
$countParams = [];
if ($search !== '') {
    $countSql .= ' WHERE login LIKE :s1 OR pseudo LIKE :s2 OR email LIKE :s3';
    $countParams['s1'] = '%' . $search . '%';
    $countParams['s2'] = '%' . $search . '%';
    $countParams['s3'] = '%' . $search . '%';
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);

\SouthDistrict\API\Router::jsonSuccess([
    'users'  => $users,
    'total'  => (int) $countStmt->fetchColumn(),
    'limit'  => $limit,
    'offset' => $offset,
]);

