<?php

namespace App\WebSocket\User;

class UserSessionManager {
    protected $userSessions = [];

    public function setUserSession($userId, $conn) {
        $this->userSessions[$userId] = $conn;
    }

    public function removeUserSession($userId) {
        unset($this->userSessions[$userId]);
    }

    public function getUserSession($userId) {
        return $this->userSessions[$userId] ?? null;
    }

    public function getAllSessions() {
        return $this->userSessions;
    }

    public function disconnectPreviousSession($userId, $newConn) {
        if (isset($this->userSessions[$userId]) && $this->userSessions[$userId] !== $newConn) {
            $this->userSessions[$userId]->close();
            $this->removeUserSession($userId);
        }
    }
}