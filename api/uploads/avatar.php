<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



\SouthDistrict\API\Router::requireMethod('POST');



$user   = sd_require_auth();

$config = sd_config()['uploads'];



if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {

    \SouthDistrict\API\Router::jsonError('Fichier avatar requis (champ avatar).');

}



$file = $_FILES['avatar'];



if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {

    \SouthDistrict\API\Router::jsonError('Erreur lors de l\'upload du fichier.', 400);

}



if (($file['size'] ?? 0) > $config['avatar_max_bytes']) {

    \SouthDistrict\API\Router::jsonError('Fichier trop volumineux (max 2 Mo).', 413);

}



$finfo = new finfo(FILEINFO_MIME_TYPE);

$mime  = $finfo->file($file['tmp_name']);



if ($mime === false || !in_array($mime, $config['avatar_allowed_mimes'], true)) {

    \SouthDistrict\API\Router::jsonError('Type de fichier non autorisé (JPEG, PNG, WebP uniquement).', 415);

}



$imageInfo = @getimagesize($file['tmp_name']);

if ($imageInfo === false) {

    \SouthDistrict\API\Router::jsonError('Le fichier n\'est pas une image valide.', 415);

}



$bytes = @file_get_contents($file['tmp_name']);

if ($bytes === false || $bytes === '') {

    \SouthDistrict\API\Router::jsonError('Lecture du fichier impossible.', 400);

}



$userId = (int) $user['id'];

$pdo    = \SouthDistrict\API\Database::getConnection();

$avatarDir = $config['avatar_dir'];



foreach (glob($avatarDir . DIRECTORY_SEPARATOR . $userId . '.*') ?: [] as $oldFile) {

    if (is_file($oldFile)) {

        @unlink($oldFile);

    }

}



$storedInDb = false;



try {

    $stmt = $pdo->prepare(

        'UPDATE users SET avatar_blob = :blob, avatar_mime = :mime, avatar = NULL, updated_at = NOW() WHERE id = :id'

    );

    $stmt->bindValue('blob', $bytes, PDO::PARAM_LOB);

    $stmt->bindValue('mime', $mime);

    $stmt->bindValue('id', $userId, PDO::PARAM_INT);

    $stmt->execute();

    $storedInDb = true;

} catch (Throwable) {

    /* migration v5 non appliquée : repli fichier disque */

    if (!is_dir($avatarDir) && !mkdir($avatarDir, 0755, true)) {

        \SouthDistrict\API\Router::jsonError('Impossible de créer le dossier d\'upload.', 500);

    }



    $extensions = [

        'image/jpeg' => 'jpg',

        'image/png'  => 'png',

        'image/webp' => 'webp',

    ];

    $ext      = $extensions[$mime] ?? 'jpg';

    $destPath = $avatarDir . DIRECTORY_SEPARATOR . $userId . '.' . $ext;



    if (!move_uploaded_file($file['tmp_name'], $destPath)) {

        \SouthDistrict\API\Router::jsonError('Échec de l\'enregistrement du fichier.', 500);

    }



    $publicPath = 'uploads/avatars/' . $userId . '.' . $ext;

    $stmt = $pdo->prepare('UPDATE users SET avatar = :avatar, updated_at = NOW() WHERE id = :id');

    $stmt->execute([

        'avatar' => $publicPath,

        'id'     => $userId,

    ]);

}



$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');

$stmt->execute(['id' => $userId]);

$updated = $stmt->fetch();



\SouthDistrict\API\Router::jsonSuccess([

    'avatar'      => sd_avatar_public_url($updated),

    'has_avatar'  => true,

    'stored_in_db' => $storedInDb,

    'user'        => sd_public_user($updated),

]);


