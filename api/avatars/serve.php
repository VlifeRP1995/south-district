<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

\SouthDistrict\API\Router::requireMethod('GET');

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'id requis.']);
    exit;
}

$pdo = \SouthDistrict\API\Database::getConnection();

try {
    $stmt = $pdo->prepare(
        'SELECT id, avatar, avatar_blob, avatar_mime, updated_at FROM users WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Lecture avatar impossible.']);
    exit;
}

if (!$row) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable.']);
    exit;
}

$mime = $row['avatar_mime'] ?? null;
$blob = $row['avatar_blob'] ?? null;

if (!empty($mime) && $blob !== null && $blob !== '') {
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=31536000, immutable');
    echo $blob;
    exit;
}

$abs = sd_upload_abs_path($row['avatar'] ?? null);
if ($abs && is_file($abs)) {
    $bytes = @file_get_contents($abs);
    if ($bytes !== false && $bytes !== '') {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->buffer($bytes) ?: 'image/jpeg';

        try {
            $upd = $pdo->prepare(
                'UPDATE users SET avatar_blob = :blob, avatar_mime = :mime, avatar = NULL, updated_at = NOW() WHERE id = :id'
            );
            $upd->bindValue('blob', $bytes, PDO::PARAM_LOB);
            $upd->bindValue('mime', $detected);
            $upd->bindValue('id', $id, PDO::PARAM_INT);
            $upd->execute();
            @unlink($abs);
        } catch (Throwable) {
            /* colonnes v5 absentes : sert le fichier disque */
        }

        header('Content-Type: ' . $detected);
        header('Cache-Control: public, max-age=86400');
        echo $bytes;
        exit;
    }
}

http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => false, 'error' => 'Avatar introuvable.']);

