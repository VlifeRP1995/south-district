<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('POST');

sd_require_auth();
sd_destroy_session();

\SouthDistrict\API\Router::jsonSuccess(['message' => 'Déconnexion réussie.']);

