<?php
namespace SouthDistrict\API;

use PDO;
use PDOException;
use Throwable;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $configPath = dirname(__DIR__) . '/config.php';
            
            if (!is_file($configPath)) {
                Router::jsonError("Fichier de configuration de la base de données introuvable.", 500);
            }
            
            $config = require $configPath;
            
            if (!isset($config['db'])) {
                Router::jsonError("Configuration de base de données manquante.", 500);
            }
            
            $db = $config['db'];
            $name = $db['name'] ?? 'southdistrict';
            $user = $db['user'] ?? 'southdistrict';
            $pass = $db['pass'] ?? '';
            $charset = $db['charset'] ?? 'utf8mb4';

            $err1 = '';
            try {
                $dsn1 = sprintf('mysql:host=127.0.0.1;port=3306;dbname=%s;charset=%s', $name, $charset);
                self::$pdo = new PDO($dsn1, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                return self::$pdo;
            } catch (PDOException $e1) {
                $err1 = $e1->getMessage();
            }

            try {
                $dsn2 = sprintf('mysql:host=localhost;dbname=%s;charset=%s', $name, $charset);
                self::$pdo = new PDO($dsn2, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                return self::$pdo;
            } catch (PDOException $e2) {
                error_log(sprintf('[Database Error] DSN1: %s | DSN2: %s', $err1, $e2->getMessage()));
                Router::jsonError("Erreur de connexion à la base de données.", 500);
            }
        }

        return self::$pdo;
    }
}
