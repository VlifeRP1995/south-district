<?php
/**
 * South District API - Bootstrapper
 * Ce fichier sert uniquement de point d'entrée pour charger les dépendances.
 */
declare(strict_types=1);

spl_autoload_register(function (string $class) {
    $prefix = 'SouthDistrict\\API\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/src/LegacyApp.php';

\SouthDistrict\API\Router::securityHeaders();
\SouthDistrict\API\Router::cors();


