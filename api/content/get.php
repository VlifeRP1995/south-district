<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



\SouthDistrict\API\Router::requireMethod('GET');



$pageKey = trim((string) ($_GET['page'] ?? ''));

if ($pageKey === '') {

    \SouthDistrict\API\Router::jsonError('Paramètre page requis.');

}



$content = null;

$stored = false;

$updatedAt = null;



try {

    $stmt = \SouthDistrict\API\Database::getConnection()->prepare(

        'SELECT content, updated_at FROM page_content WHERE page_key = :k LIMIT 1'

    );

    $stmt->execute(['k' => $pageKey]);

    $row = $stmt->fetch();

    if ($row) {

        $decoded = json_decode($row['content'], true);

        $content = is_array($decoded) ? $decoded : null;

        $stored = true;

        $updatedAt = $row['updated_at'] ?? null;

    }

} catch (Throwable) {

    $content = null;

    $stored = false;

}



\SouthDistrict\API\Router::jsonSuccess([

    'page'       => $pageKey,

    'content'    => $content,

    'stored'     => $stored,

    'updated_at' => $updatedAt,

]);


