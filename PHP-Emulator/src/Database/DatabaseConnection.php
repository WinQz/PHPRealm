<?php

namespace App\Database;

use PDO;
use PDOException;

class DatabaseConnection {
    private $pdo;

    public function __construct() {
        $env = $this->loadEnv('.env');

        $server = $env['SERVER'] ?? 'mysql';
        $host = $env['HOST'] ?? 'localhost';
        $dbname = $env['DBNAME'] ?? 'phprealm';
        $username = $env['DB_USER'] ?? 'username';
        $password = $env['DB_PASSWORD'] ?? 'password';

        $dsn = sprintf('%s:host=%s;dbname=%s', $server, $host, $dbname);

        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            exit;
        }
    }

    private function loadEnv($file) {
        $env = [];
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $env[trim($key)] = trim($value);
                }
            }
        }
        return $env;
    }

    public function getConnection() {
        return $this->pdo;
    }
}