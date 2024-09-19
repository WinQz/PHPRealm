<?php

namespace App\Database\User\Set;

class UserStatusUpdater {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function updateUserStatus($userId, $status) {
        $stmt = $this->pdo->prepare("UPDATE users SET status = :status, last_login = NOW() WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $userId]);
    }
}