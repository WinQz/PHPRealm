<?php

namespace App\WebSocket\User;

use App\Database\User\Set\UserStatusUpdater;

class UserSessionManager {
    protected $userSessions = [];
    protected $userData = [];
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

        if (!isset($this->userData[$userId])) {
            $this->userData[$userId] = [
                'x' => 0,
                'y' => 0,
                'userId' => $userId
            ];
        }

        $conn->userData = $this->userData[$userId];

        $this->statusUpdater->updateUserStatus($userId, 'online');
    }

    public function removeUserSession($userId) {
        if (isset($this->userSessions[$userId])) {
            unset($this->userSessions[$userId]);
            unset($this->userData[$userId]);
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

    public function getUserData($userId) {
        return $this->userData[$userId] ?? null;
    }

    public function setUserData($userId, array $data) {
        if (isset($this->userData[$userId])) {
            $this->userData[$userId] = array_merge($this->userData[$userId], $data);
        }
    }
}