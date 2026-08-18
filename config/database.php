<?php
/**
 * PDO connection singleton. Always use prepared statements — never
 * interpolate user input directly into SQL anywhere in this project.
 */

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('[DB CONNECTION ERROR] ' . $e->getMessage());
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
                }
                die('Sorry, something went wrong. Please try again shortly.');
            }
        }

        return self::$instance;
    }
}
