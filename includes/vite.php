<?php
/**
 * South District - Vite Asset Helper
 * Resout les URLs d assets Vite en mode dev et production.
 * Le manifest est mis en cache en memoire (variable statique).
 */

define('VITE_DEV_SERVER', 'http://localhost:5173');
define('IS_VITE_DEVELOPMENT', false);

function vite($entry) {
    if (IS_VITE_DEVELOPMENT) {
        $url = VITE_DEV_SERVER . '/' . ltrim($entry, '/');
        return '<script type="module" src="' . htmlspecialchars($url) . '"></script>';
    }

    static $manifest = null;

    if ($manifest === null) {
        $paths = array(
            __DIR__ . '/../dist/.vite/manifest.json',
            __DIR__ . '/../dist/manifest.json',
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                $raw = file_get_contents($path);
                if ($raw !== false) {
                    $manifest = json_decode($raw, true);
                    if ($manifest === null) $manifest = array();
                    break;
                }
            }
        }
        if ($manifest === null) {
            $manifest = array();
            return '<!-- Vite: manifest.json introuvable. Lancez npm run build -->';
        }
    }

    $entryKey = ltrim($entry, '/');

    if (!isset($manifest[$entryKey])) {
        return '<!-- Vite: asset "' . htmlspecialchars($entryKey) . '" absent du manifest -->';
    }

    $file = $manifest[$entryKey]['file'];
    $fullPath = __DIR__ . '/../dist/' . $file;
    $version = is_file($fullPath) ? (string) filemtime($fullPath) : '1';
    $url  = '/dist/' . $file . '?v=' . $version;

    if (substr($file, -4) === '.css') {
        return '<link rel="stylesheet" href="' . htmlspecialchars($url) . '">';
    }

    return '<script type="module" src="' . htmlspecialchars($url) . '"></script>';
}