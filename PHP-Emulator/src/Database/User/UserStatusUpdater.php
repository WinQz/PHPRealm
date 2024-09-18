<?php

namespace App\Database\User;

class UserStatusUpdater {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function updateUserStatus($userId, $status) {
        $stmt = $this->pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $userId]);
    }
}