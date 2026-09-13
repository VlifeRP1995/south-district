<?php
/**
 * South District RP - Configuration (template)
 * Copiez ce fichier vers config.php et renseignez vos identifiants MySQL / domaine.
 */
declare(strict_types=1);

$env = [];
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';') continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
            $v = substr($v, 1, -1);
        }
        $env[$k] = $v;
    }
}

return [
    'db' => [
        'host'    => $env['DB_HOST'] ?? '127.0.0.1',
        'name'    => $env['DB_NAME'] ?? 'southdistrict',
        'user'    => $env['DB_USER'] ?? 'southdistrict',
        'pass'    => $env['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'             => 'South District RP',
        'url'              => $env['APP_URL'] ?? (isset($_SERVER['HTTP_HOST']) ? ('https://' . $_SERVER['HTTP_HOST']) : 'https://www.south-district.fr'),
        'session_cookie'   => 'sd_session',
        'session_lifetime' => 604800, // 7 jours
        'cookie_secure'    => true,   // false en local HTTP
        'cookie_samesite'  => 'Lax',
    ],
    'cors' => [
        'allowed_origins'   => [
            'https://www.south-district.fr',
            'https://south-district.fr',
            'https://south-district.duckdns.org',
            'http://localhost',
        ],
        'allow_credentials' => true,
    ],
    'uploads' => [
        'avatar_dir'         => __DIR__ . '/../uploads/avatars',
        'avatar_max_bytes'   => 2 * 1024 * 1024,
        'avatar_allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
        'signature_dir'        => __DIR__ . '/../uploads/signatures',
        'candidature_sig_dir'  => __DIR__ . '/../uploads/candidatures',
    ],
    'discord' => [
        'client_id'     => $env['DISCORD_CLIENT_ID'] ?? '',
        'client_secret' => $env['DISCORD_SECRET'] ?? '',
        'guild_id'      => $env['DISCORD_GUILD_ID'] ?? '',
        'bot_token'     => $env['DISCORD_BOT_TOKEN'] ?? '',
        'bot_api_key'   => $env['BOT_API_KEY'] ?? '',
        'candidature_webhook' => $env['DISCORD_CANDIDATURE_WEBHOOK'] ?? '',
        'redirect_uri'  => null,
        // Correspondance des rôles Discord
        'role_mapping'  => [
            '1533513699636150398' => 'createur',
            '1534681534261493940' => 'ceo',
            '1534667067901345833' => 'co_ceo',
            '1505972908042747927' => 'developer',
            '1505983824251584542' => 'manager',
            '1505972908168843308' => 'gerant_staff',
            '1505074460035805476' => 'administrateur',
            '1505972908042747933' => 'moderateur',
            '1505972908168843306' => 'responsable_cm',          
            '1505972908042747929' => 'cm',         
            '1505992154202243175' => 'staff',
        ],
    ],
];

