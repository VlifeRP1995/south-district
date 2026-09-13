<?php
namespace SouthDistrict\API;

class Router
{
    public static function securityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    public static function cors(): void
    {
        self::securityHeaders();

        if (empty($_COOKIE['XSRF-TOKEN'])) {
            $app = sd_config()['app'];
            $token = bin2hex(random_bytes(32));
            $_COOKIE['XSRF-TOKEN'] = $token;
            setcookie('XSRF-TOKEN', $token, [
                'expires'  => time() + (int) ($app['session_lifetime'] ?? 604800),
                'path'     => '/',
                'secure'   => (bool) ($app['cookie_secure'] ?? true),
                'httponly' => false,
                'samesite' => $app['cookie_samesite'] ?? 'Lax',
            ]);
        }

        $config = sd_config()['cors'];
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origin !== '' && in_array($origin, $config['allowed_origins'], true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        }

        if (!empty($config['allow_credentials'])) {
            header('Access-Control-Allow-Credentials: true');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN');
        header('Access-Control-Max-Age: 86400');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function jsonSuccess(array $data = [], int $status = 200): void
    {
        self::jsonResponse(['success' => true] + $data, $status);
    }

    public static function jsonError(string $message, int $status = 400, array $extra = []): void
    {
        self::jsonResponse(['success' => false, 'error' => $message] + $extra, $status);
    }

    private static function validateCsrfToken(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        if (
            strpos($uri, '/auth/login.php') !== false ||
            strpos($uri, '/auth/register.php') !== false ||
            strpos($script, 'login.php') !== false ||
            strpos($script, 'register.php') !== false
        ) {
            return;
        }

        $headerToken = $_SERVER['HTTP_X_XSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $cookieToken = $_COOKIE['XSRF-TOKEN'] ?? '';

        if (empty($headerToken) || empty($cookieToken) || !hash_equals($cookieToken, $headerToken)) {
            self::jsonError('Jeton CSRF invalide ou manquant.', 403);
        }
    }

    public static function requireMethod(string ...$methods): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            self::validateCsrfToken();
        }

        if (!in_array($method, $methods, true)) {
            self::jsonError('Méthode non autorisée.', 405);
        }
    }

    public static function jsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            self::jsonError('Corps JSON invalide.', 400);
        }

        return $data;
    }

    public static function clientIp(): string
    {
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded !== '') {
            $parts = explode(',', $forwarded);
            $ip = trim($parts[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '';
    }
}




