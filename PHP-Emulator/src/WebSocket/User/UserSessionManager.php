<?php

namespace App\WebSocket\User;

use App\Database\User\Set\UserStatusUpdater;

class UserSessionManager {
    protected $userSessions = [];
    private $statusUpdater;

    public function __construct(UserStatusUpdater $statusUpdater) {
        $this->statusUpdater = $statusUpdater;
        echo "User Session Manager Initialized.\n";
    }

    public function setUserSession($userId, $conn) {
        
        $existingSession = $this->getUserSession($userId);
        if ($existingSession && $existingSession !== $conn) {
            $this->disconnectPreviousSession($userId, $conn);
        }
        
        $this->userSessions[$userId] = $conn;
        $this->statusUpdater->updateUserStatus($userId, 'online');
    }

    public function removeUserSession($userId) {
        if (isset($this->userSessions[$userId])) {
            unset($this->userSessions[$userId]);
            $this->statusUpdater->updateUserStatus($userId, 'offline');
        }
    }

    public function getUserSession($userId) {
        return $this->userSessions[$userId] ?? null;
    }

    public function getAllSessions() {
        return $this->userSessions;
    }

    public function disconnectPreviousSession($userId, $newConn) {
        if (isset($this->userSessions[$userId]) && $this->userSessions[$userId] !== $newConn) {
            $oldSession = $this->userSessions[$userId];
            $oldSession->close();
            $this->removeUserSession($userId);
        }
    }
}