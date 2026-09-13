<?php
require_once __DIR__ . '/includes/vite.php';
header('Content-Type: application/manifest+json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');
?>
{
  "id": "/",
  "name": "South District RP",
  "short_name": "South District",
  "description": "Portail communautaire officiel de South District Roleplay.",
  "start_url": "/accueil",
  "scope": "/",
  "display": "standalone",
  "orientation": "any",
  "background_color": "#0a0a0a",
  "theme_color": "#1ee6a0",
  "icons": [
    {
      "src": "assets/favicon-32.png",
      "sizes": "32x32",
      "type": "image/png"
    },
    {
      "src": "assets/favicon.png",
      "sizes": "48x48",
      "type": "image/png"
    },
    {
      "src": "assets/apple-touch-icon.png",
      "sizes": "180x180",
      "type": "image/png"
    },
    {
      "src": "assets/icon-192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "assets/icon-512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "assets/icon-512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable"
    }
  ]
}
