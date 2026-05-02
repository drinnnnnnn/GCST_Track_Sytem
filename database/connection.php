<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            self::$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (self::$connection->connect_error) {
                die('Database connection failed: ' . self::$connection->connect_error);
            }
            self::$connection->set_charset(DB_CHARSET);
        }
        return self::$connection;
    }

    public static function getPdo() {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        try {
            return new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('PDO connection failed: ' . $e->getMessage());
        }
    }
}

$conn = Database::getConnection();
