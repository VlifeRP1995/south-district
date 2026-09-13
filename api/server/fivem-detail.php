<?php
/**
 * South District - FiveM API Wrapper
 */

header('Content-Type: application/json; charset=utf-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['https://www.south-district.fr', 'https://south-district.fr', 'http://localhost'];
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

define('SD_SERVER_CODE', 'leepglo');
define('SD_API_URL', 'https://frontend.cfx-services.net/api/servers/single/leepglo');
define('SD_CACHE_FILE', __DIR__ . '/cache_fivem.json');
define('SD_CACHE_TTL', 60);

function sd_read_cache() {
    if (!file_exists(SD_CACHE_FILE)) return null;
    $content = file_get_contents(SD_CACHE_FILE);
    if ($content === false || $content === '') return null;
    return array('content' => $content, 'age' => time() - (int)filemtime(SD_CACHE_FILE));
}

function sd_write_cache($data) {
    $tmp = SD_CACHE_FILE . '.tmp.' . getmypid();
    if (file_put_contents($tmp, $data, LOCK_EX) === false) return false;
    return rename($tmp, SD_CACHE_FILE);
}

function sd_fetch_api() {
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => SD_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'SouthDistrictApp/2.0',
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => array('Accept: application/json'),
    ));
    $body  = curl_exec($ch);
    $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return array('body' => is_string($body) ? $body : '', 'code' => $code, 'error' => $error);
}

$cache = sd_read_cache();

// Cache frais
if ($cache !== null && $cache['age'] < SD_CACHE_TTL) {
    header('X-Cache: HIT');
    echo $cache['content'];
    exit;
}

// Refresh
$result = sd_fetch_api();

if ($result['code'] === 200 && $result['body'] !== '') {
    $decoded = json_decode($result['body']);
    if ($decoded !== null) {
        sd_write_cache($result['body']);
        header('X-Cache: MISS');
        echo $result['body'];
        exit;
    }
}

// Stale cache
if ($cache !== null) {
    header('X-Cache: STALE');
    echo $cache['content'];
    exit;
}

// Aucun cache + API morte
http_response_code(503);
echo json_encode(array('error' => 'Service temporairement indisponible.', 'code' => $result['code']), JSON_UNESCAPED_UNICODE);