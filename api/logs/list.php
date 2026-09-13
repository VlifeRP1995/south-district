<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



\SouthDistrict\API\Router::requireMethod('GET');



$user = sd_require_auth();

if (!sd_can_manage_role_definitions($user)) {
    \SouthDistrict\API\Router::jsonError('Accès réservé aux CEO et Co-CEO.', 403);
}

$limit = min(100, max(1, (int) ($_GET['limit'] ?? 50)));



$logs = [];

try {

    $check = \SouthDistrict\API\Database::getConnection()->query("SHOW TABLES LIKE 'site_logs'");

    if (!$check || !$check->fetch()) {

        \SouthDistrict\API\Router::jsonSuccess([

            'logs'                => [],

            'migration_required'  => true,

            'message'             => 'Table site_logs absente. Importez sql/migrate_v3.sql dans phpMyAdmin.',

        ]);

    }



    $stmt = \SouthDistrict\API\Database::getConnection()->prepare(

        'SELECT id, event_type, page_key, summary, detail, actor_pseudo, created_at

         FROM site_logs ORDER BY created_at DESC LIMIT :lim'

    );

    $stmt->bindValue('lim', $limit, PDO::PARAM_INT);

    $stmt->execute();

    $logs = $stmt->fetchAll();

} catch (Throwable $e) {

    \SouthDistrict\API\Router::jsonSuccess([

        'logs'               => [],

        'migration_required' => true,

        'message'            => 'Impossible de lire les logs : ' . $e->getMessage(),

    ]);

}



\SouthDistrict\API\Router::jsonSuccess(['logs' => $logs, 'migration_required' => false]);



