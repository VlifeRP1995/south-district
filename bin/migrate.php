<?php
/**
 * South District RP - CLI Database Migration Tool
 *
 * Usage:
 *   php bin/migrate.php
 *
 * Purpose:
 *   Applies structural DDL updates, ensures required tables, columns,
 *   and indexes exist, without any runtime impact on HTTP requests.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
    http_response_code(403);
    fwrite(STDERR, "Erreur : Ce script ne peut être exécuté qu'en ligne de commande (CLI).\n");
    exit(1);
}

$startTime = microtime(true);

echo "South District RP - Database Migration (CLI)\n\n";

$rootDir = dirname(__DIR__);
$configPath = $rootDir . '/api/config.php';

if (!is_file($configPath)) {
    fwrite(STDERR, "[FAIL] Fichier de configuration introuvable : {$configPath}\n");
    exit(1);
}

$config = require $configPath;
$dbConfig = $config['db'] ?? null;

if (!is_array($dbConfig)) {
    fwrite(STDERR, "[FAIL] Configuration de base de données manquante dans config.php.\n");
    exit(1);
}

$dbName    = $dbConfig['name'] ?? 'southdistrict';
$dbUser    = $dbConfig['user'] ?? 'southdistrict';
$dbPass    = $dbConfig['pass'] ?? '';
$dbHost    = $dbConfig['host'] ?? '127.0.0.1';
$dbPort    = (int) ($dbConfig['port'] ?? 3306);
$dbCharset = $dbConfig['charset'] ?? 'utf8mb4';

echo sprintf("[INFO] Connexion au serveur MySQL (%s:%d, base: %s)...\n", $dbHost, $dbPort, $dbName);

/** @var PDO|null $pdo */
$pdo = null;

try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $dbHost, $dbPort, $dbName, $dbCharset);
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Tentative de fallback sur socket unix localhost
    try {
        $dsnFallback = sprintf('mysql:host=localhost;dbname=%s;charset=%s', $dbName, $dbCharset);
        $pdo = new PDO($dsnFallback, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e2) {
        fwrite(STDERR, sprintf("[FAIL] Échec de connexion : %s (fallback: %s)\n", $e->getMessage(), $e2->getMessage()));
        exit(1);
    }
}

echo "[OK] Connecté avec succès.\n\n";

// Tables de base
echo "[MIGRATION] 1/4 - Vérification des tables de base (sessions, settings, content, logs, partenaires, absences, pings)...\n";
$pdo->exec("
    CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(64) NOT NULL PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        expires_at DATETIME NOT NULL,
        ip_address VARCHAR(45) NULL DEFAULT NULL,
        user_agent TEXT NULL DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_sessions_user_id (user_id),
        INDEX idx_sessions_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
        setting_value LONGTEXT NULL,
        updated_by INT UNSIGNED NULL DEFAULT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS page_content (
        page_key VARCHAR(64) NOT NULL PRIMARY KEY,
        content LONGTEXT NULL,
        updated_by INT UNSIGNED NULL DEFAULT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS site_logs (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        event_type VARCHAR(64) NOT NULL,
        page_key VARCHAR(64) NULL DEFAULT NULL,
        summary VARCHAR(255) NOT NULL,
        detail TEXT NULL,
        actor_id INT UNSIGNED NULL DEFAULT NULL,
        actor_pseudo VARCHAR(64) NULL DEFAULT NULL,
        meta LONGTEXT NULL DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_site_logs_event (event_type),
        INDEX idx_site_logs_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS sd_partenaires (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(128) NOT NULL,
        description TEXT NULL DEFAULT NULL,
        category VARCHAR(64) NOT NULL DEFAULT 'Partenaire Officiel',
        logo VARCHAR(512) NULL DEFAULT NULL,
        banner VARCHAR(512) NULL DEFAULT NULL,
        link VARCHAR(512) NULL DEFAULT NULL,
        website_link VARCHAR(512) NULL DEFAULT NULL,
        rank_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_sd_partenaires_order (rank_order, name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS sd_staff_absences (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        pseudo VARCHAR(64) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        justification TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_sd_absences_start (start_date),
        INDEX idx_sd_absences_end (end_date),
        INDEX idx_sd_absences_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS sd_entreprise_pings (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        company_id VARCHAR(64) NOT NULL,
        company_name VARCHAR(128) NOT NULL,
        pos_x FLOAT NOT NULL,
        pos_y FLOAT NOT NULL,
        pos_z FLOAT NOT NULL DEFAULT 0,
        created_by INT UNSIGNED NULL DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_entreprise_pings_cid (company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "[OK] Tables du cœur opérationnelles.\n\n";

/**
 * 3. Colonnes modération / bannissement dans `users`
 */
echo "[MIGRATION] 2/4 - Vérification des colonnes de modération dans `users`...\n";
$userCols = array_flip($pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN));

if (!isset($userCols['is_banned'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0 AFTER is_master_ceo');
    echo "  + Colonne `is_banned` ajoutée\n";
}
if (!isset($userCols['banned_at'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN banned_at DATETIME NULL DEFAULT NULL AFTER is_banned');
    echo "  + Colonne `banned_at` ajoutée\n";
}
if (!isset($userCols['ban_reason'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN ban_reason VARCHAR(255) NULL DEFAULT NULL AFTER banned_at');
    echo "  + Colonne `ban_reason` ajoutée\n";
}

$userIndexes = [];
foreach ($pdo->query('SHOW INDEX FROM users')->fetchAll() as $idx) {
    $userIndexes[$idx['Key_name']] = true;
}
if (!isset($userIndexes['idx_users_is_banned'])) {
    $pdo->exec('ALTER TABLE users ADD INDEX idx_users_is_banned (is_banned)');
    echo "  + Index `idx_users_is_banned` ajouté\n";
}
echo "[OK] Colonnes de modération à jour.\n\n";

// Colonnes OAuth Discord dans users
echo "[MIGRATION] 3/4 - Vérification des colonnes OAuth Discord dans `users`...\n";
if (!isset($userCols['discord_id'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN discord_id VARCHAR(64) NULL DEFAULT NULL AFTER is_master_ceo');
    echo "  + Colonne `discord_id` ajoutée\n";
}
if (!isset($userCols['discord_access_token'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN discord_access_token VARCHAR(255) NULL DEFAULT NULL AFTER discord_id');
    echo "  + Colonne `discord_access_token` ajoutée\n";
}
if (!isset($userCols['discord_refresh_token'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN discord_refresh_token VARCHAR(255) NULL DEFAULT NULL AFTER discord_access_token');
    echo "  + Colonne `discord_refresh_token` ajoutée\n";
}
if (!isset($userCols['discord_token_expires_at'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN discord_token_expires_at DATETIME NULL DEFAULT NULL AFTER discord_refresh_token');
    echo "  + Colonne `discord_token_expires_at` ajoutée\n";
}
if (!isset($userCols['discord_last_sync_at'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN discord_last_sync_at DATETIME NULL DEFAULT NULL AFTER discord_token_expires_at');
    echo "  + Colonne `discord_last_sync_at` ajoutée\n";
}
if (!isset($userCols['secondary_roles'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN secondary_roles TEXT NULL DEFAULT NULL AFTER staff_title');
    echo "  + Colonne `secondary_roles` ajoutée\n";
}
if (!isset($userCols['avatar_blob'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN avatar_blob LONGBLOB NULL DEFAULT NULL');
    echo "  + Colonne `avatar_blob` ajoutée\n";
}
if (!isset($userCols['avatar_mime'])) {
    $pdo->exec('ALTER TABLE users ADD COLUMN avatar_mime VARCHAR(64) NULL DEFAULT NULL');
    echo "  + Colonne `avatar_mime` ajoutée\n";
}

if (!isset($userIndexes['uq_users_discord_id'])) {
    $pdo->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_discord_id (discord_id)');
    echo "  + Index UNIQUE `uq_users_discord_id` ajouté\n";
}
echo "[OK] Colonnes OAuth Discord à jour.\n\n";

// Table et colonnes candidatures
echo "[MIGRATION] 4/4 - Vérification de la table et des colonnes `candidatures`...\n";
$pdo->exec("
    CREATE TABLE IF NOT EXISTS candidatures (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NULL DEFAULT NULL,
        code VARCHAR(32) NOT NULL UNIQUE,
        pseudo_discord VARCHAR(128) NOT NULL,
        pseudo_rp VARCHAR(128) NULL DEFAULT NULL,
        age INT UNSIGNED NOT NULL DEFAULT 0,
        poste VARCHAR(128) NOT NULL DEFAULT '',
        disponibilite_hebdo INT UNSIGNED NOT NULL DEFAULT 0,
        experience TEXT NOT NULL,
        motivation TEXT NOT NULL,
        scenario TEXT NOT NULL,
        form_responses LONGTEXT NULL DEFAULT NULL,
        signature VARCHAR(255) NULL DEFAULT NULL,
        status ENUM('en_attente', 'en_examen', 'entretien', 'accepte', 'refuse') NOT NULL DEFAULT 'en_attente',
        review_note TEXT NULL DEFAULT NULL,
        convocation_message TEXT NULL DEFAULT NULL,
        reviewer_id INT UNSIGNED NULL DEFAULT NULL,
        reviewed_at DATETIME NULL DEFAULT NULL,
        is_archived TINYINT(1) NOT NULL DEFAULT 0,
        archived_at DATETIME NULL DEFAULT NULL,
        viewed_at DATETIME NULL DEFAULT NULL,
        submitted_ip VARCHAR(45) NULL DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_cand_status (status),
        INDEX idx_cand_archived (is_archived),
        INDEX idx_cand_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS candidature_backups (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        candidature_id INT UNSIGNED NOT NULL,
        backup_type VARCHAR(64) NOT NULL,
        payload LONGTEXT NOT NULL,
        actor_id INT UNSIGNED NULL DEFAULT NULL,
        actor_pseudo VARCHAR(128) NULL DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_cb_cand_id (candidature_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$candCols = array_flip($pdo->query('SHOW COLUMNS FROM candidatures')->fetchAll(PDO::FETCH_COLUMN));

$candColumnsToAdd = [
    'is_archived'         => 'ALTER TABLE candidatures ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0',
    'archived_at'         => 'ALTER TABLE candidatures ADD COLUMN archived_at DATETIME NULL DEFAULT NULL',
    'viewed_at'           => 'ALTER TABLE candidatures ADD COLUMN viewed_at DATETIME NULL DEFAULT NULL',
    'form_responses'      => 'ALTER TABLE candidatures ADD COLUMN form_responses LONGTEXT NULL DEFAULT NULL',
    'signature'           => 'ALTER TABLE candidatures ADD COLUMN signature VARCHAR(255) NULL DEFAULT NULL',
    'review_note'         => 'ALTER TABLE candidatures ADD COLUMN review_note TEXT NULL DEFAULT NULL',
    'convocation_message' => 'ALTER TABLE candidatures ADD COLUMN convocation_message TEXT NULL DEFAULT NULL',
    'reviewer_id'         => 'ALTER TABLE candidatures ADD COLUMN reviewer_id INT UNSIGNED NULL DEFAULT NULL',
    'reviewed_at'         => 'ALTER TABLE candidatures ADD COLUMN reviewed_at DATETIME NULL DEFAULT NULL',
];

foreach ($candColumnsToAdd as $colName => $alterSql) {
    if (!isset($candCols[$colName])) {
        $pdo->exec($alterSql);
        echo "  + Colonne candidatures.`{$colName}` ajoutée\n";
    }
}
echo "[OK] Candidatures et sauvegardes à jour.\n\n";

$elapsed = round((microtime(true) - $startTime) * 1000, 2);

echo "\n[SUCCESS] Migration terminée en {$elapsed} ms.\n";
exit(0);
