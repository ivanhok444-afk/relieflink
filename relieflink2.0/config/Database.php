<?php
/**
 * Database Configuration
 * Relief Goods Distribution Management System
 * Production deployments should provide AIVAN_DB_HOST, AIVAN_DB_NAME,
 * AIVAN_DB_USER, and AIVAN_DB_PASS through the process environment.
 */

class Database {
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            require_once __DIR__ . '/config.php';
            $this->conn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('The database is temporarily unavailable.');
        }

        return $this->conn;
    }
}
?>
