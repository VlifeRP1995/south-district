<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



\SouthDistrict\API\Router::requireMethod('GET');



\SouthDistrict\API\Router::jsonSuccess(['settings' => sd_load_site_settings()]);


