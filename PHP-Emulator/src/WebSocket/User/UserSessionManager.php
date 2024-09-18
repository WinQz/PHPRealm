<?php

namespace App\WebSocket\User;

use App\Database\User\UserStatusUpdater;

class UserSessionManager {
    protected $userSessions = [];
    private $statusUpdater;

    public function __construct(UserStatusUpdater $statusUpdater) {
        $this->statusUpdater = $statusUpdater;
        $this->log('UserSessionManager Initialized.');
    }

    public function setUserSession($userId, $conn) {
        $this->userSessions[$userId] = $conn;
        $this->statusUpdater->updateUserStatus($userId, 'online');
        $this->log("User session set: UserID {$userId}, ConnID {$conn->resourceId}");
    }

    public function removeUserSession($userId) {
        if (isset($this->userSessions[$userId])) {
            $conn = $this->userSessions[$userId];
            $this->log("Removing user session: UserID {$userId}, ConnID {$conn->resourceId}");
            unset($this->userSessions[$userId]);
            $this->statusUpdater->updateUserStatus($userId, 'offline');
        } else {
            $this->log("No session to remove for UserID {$userId}");
        }
    }

    public function getUserSession($userId) {
        $session = $this->userSessions[$userId] ?? null;
        if ($session) {
            $this->log("Retrieved user session: UserID {$userId}, ConnID {$session->resourceId}");
        } else {
            $this->log("No session found for UserID {$userId}");
        }
        return $session;
    }

    public function getAllSessions() {
        return $this->userSessions;
    }

    public function disconnectPreviousSession($userId, $newConn) {
        if (isset($this->userSessions[$userId]) && $this->userSessions[$userId] !== $newConn) {
            $oldSession = $this->userSessions[$userId];
            $this->log("Disconnecting previous session: UserID {$userId}, OldConnID {$oldSession->resourceId}, NewConnID {$newConn->resourceId}");
            $oldSession->close();
            $this->removeUserSession($userId);
        } else {
            $this->log("No duplicate session to disconnect for UserID {$userId}");
        }
    }

    private function log(string $message) {
        echo $message . "\n";
    }
}