<?php

namespace App\Network;

use Ratchet\ConnectionInterface;
use App\Database\User\Get\GetUserData;
use SplObjectStorage;
use App\WebSocket\User\UserSessionManager;

class MessageDispatcher {
    private $userDataFetcher;
    private $sessionManager;
    private $clients;
    private $messageSender;

    public function __construct(GetUserData $userDataFetcher, UserSessionManager $sessionManager, SplObjectStorage $clients) {
        $this->userDataFetcher = $userDataFetcher;
        $this->sessionManager = $sessionManager;
        $this->clients = $clients;
        $this->messageSender = new MessageSender();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        var_dump($data);

        if (!$data) {
            Logger::log("Invalid JSON message received: {$msg}");
            return;
        }

        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'userJoin':
                    if (isset($data['userId'])) {
                        $this->handleUserData($from, $data['userId']);
                    } else {
                        Logger::log("userJoin message missing userId: {$msg}");
                    }
                    break;
                case 'playerUpdate':
                    $this->handlePlayerUpdate($from, $data['data']);
                    break;
            }
        }
    }

    private function handleUserData(ConnectionInterface $conn, int $id) {
        $userData = $this->userDataFetcher->getUserById($id);

        if (!$userData) {
            Logger::log("User ID {$id} not found.");
            return;
        }

        $this->manageUserSession($conn, $id, $userData);

        $this->sessionManager->setUserData($id, [
            'x' => $userData['x'] ?? 0,
            'y' => $userData['y'] ?? 0
        ]);

        $this->sendUserUpdate($conn);

        $this->messageSender->broadcastMessage($this->clients, [
            'type' => 'userJoined',
            'data' => $this->messageSender->filterSensitiveData($userData)
        ]);
    }

    private function manageUserSession(ConnectionInterface $conn, int $id, array $userData) {
        $existingSession = $this->sessionManager->getUserSession($id);

        if ($existingSession && $existingSession !== $conn) {
            $existingUsername = $existingSession->userData['username'] ?? 'Unknown';
            $this->sessionManager->disconnectPreviousSession($id, $conn);

            Logger::log("User {$existingUsername} had a duplicate session. Previous session has been removed.");

            $this->messageSender->broadcastMessage($this->clients, [
                'type' => 'userDuplicateSession',
                'id' => $id,
                'removedSessionId' => $existingSession->resourceId
            ]);
        }

        $conn->userData = $userData;
        $this->sessionManager->setUserSession($id, $conn);

        Logger::log("User {$userData['username']} has joined the adventure.");
    }

    private function sendUserUpdate(ConnectionInterface $conn) {
        $playersData = [];

        foreach ($this->sessionManager->getAllSessions() as $sessionUserId => $sessionConn) {
            $userDataFiltered = $this->messageSender->filterSensitiveData($sessionConn->userData);
            $playersData[$sessionUserId] = $userDataFiltered;
        }

        $conn->send(json_encode([
            'type' => 'userUpdate',
            'data' => $playersData
        ]));
    }

    private function handlePlayerUpdate(ConnectionInterface $conn, array $playerData) {
        $userId = $playerData['id'] ?? null;

        if (!$userId || !$this->validateSession($conn, $userId)) {
            Logger::log("Invalid session or missing user ID for player update.");
            return;
        }

        if (!isset($playerData['x']) || !isset($playerData['y'])) {
            Logger::log("Missing coordinates in player update.");
            return;
        }

        $this->updatePlayerPosition($userId, [
            'x' => (float)$playerData['x'],
            'y' => (float)$playerData['y']
        ]);

        $this->broadcastPlayerPositions();
    }

    private function validateSession(ConnectionInterface $conn, int $userId): bool {
        $session = $this->sessionManager->getUserSession($userId);
        return $session && $session === $conn;
    }

    private function updatePlayerPosition(int $userId, array $playerData) {
        $this->sessionManager->setUserData($userId, [
            'x' => $playerData['x'],
            'y' => $playerData['y']
        ]);
    }

    private function broadcastPlayerPositions() {
        $playerPositions = [];

        foreach ($this->sessionManager->getAllSessions() as $sessionUserId => $sessionConn) {
            $userData = $this->sessionManager->getUserData($sessionUserId);
            $playerPositions[$sessionUserId] = $userData;
        }

        $this->messageSender->broadcastMessage($this->clients, [
            'type' => 'updatePlayerPosition',
            'data' => $playerPositions
        ]);
    }
}