<?php

namespace App\WebSocket\User;

use App\Database\User\UserStatusUpdater;

class UserSessionManager {
    protected $userSessions = [];
    private $statusUpdater;

    public function __construct(UserStatusUpdater $statusUpdater) {
        $this->statusUpdater = $statusUpdater;
    }

    public function setUserSession($userId, $conn) {
        $this->userSessions[$userId] = $conn;
        $this->statusUpdater->updateUserStatus($userId, 'online');
    }

    public function removeUserSession($userId) {
        unset($this->userSessions[$userId]);
        $this->statusUpdater->updateUserStatus($userId, 'offline');
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