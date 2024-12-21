<?php

namespace App\Database;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class DatabaseConnection {
    private $pdo;
    private $logger;

    public function __construct(array $config, LoggerInterface $logger) {
        $this->logger = $logger;

        $server = $config['server'];
        $host = $config['host'];
        $dbname = $config['dbname'];
        $username = $config['username'];
        $password = $config['password'];

        $dsn = sprintf('%s:host=%s;dbname=%s;charset=utf8mb4', $server, $host, $dbname);

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $this->logger->info('Database connection established.');
        } catch (PDOException $e) {
            $this->logger->error('Connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed');
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}