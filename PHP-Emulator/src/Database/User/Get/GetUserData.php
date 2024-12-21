<?php

namespace App\Database\User\Get;

class GetUserData {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserById($userId) {

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        
        $stmt->execute([':id' => $userId]);
        
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user){ 
            return;
        }        
        return $user;
    }
}