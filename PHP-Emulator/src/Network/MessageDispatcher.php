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
    private $lastBroadcastTime = 0;
    private $broadcastInterval = 50; // Reduce the interval to send updates more frequently

    public function __construct(GetUserData $userDataFetcher, UserSessionManager $sessionManager, SplObjectStorage $clients) {
        $this->userDataFetcher = $userDataFetcher;
        $this->sessionManager = $sessionManager;
        $this->clients = $clients;
        $this->messageSender = new MessageSender();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!$data) {
            Logger::log("Invalid JSON message received: {$msg}");
            return;
        }

        if (isset($data['type'])) {
            $this->handleMessageType($from, $data);
        }
    }

    private function handleMessageType(ConnectionInterface $from, array $data) {
        switch ($data['type']) {
            case 'userJoin':
                $this->handleUserJoin($from, $data);
                break;
            case 'playerUpdate':
                $this->handlePlayerUpdate($from, $data['data']);
                break;
            default:
                Logger::log("Unknown message type: {$data['type']}");
                break;
        }
    }

    private function handleUserJoin(ConnectionInterface $conn, array $data) {
        if (!isset($data['userId'])) {
            Logger::log("userJoin message missing userId: " . json_encode($data));
            return;
        }

        $userId = $data['userId'];
        $userData = $this->userDataFetcher->getUserById($userId);

        if (!$userData) {
            Logger::log("User ID {$userId} not found.");
            return;
        }

        $this->manageUserSession($conn, $userId, $userData);

        $this->sessionManager->setUserData($userId, [
            'x' => $userData['x'] ?? 0,
            'y' => $userData['y'] ?? 0
        ]);

        $this->sendUserUpdate($conn);

        $this->messageSender->broadcastMessage($this->clients, [
            'type' => 'userJoined',
            'data' => $this->messageSender->filterSensitiveData($userData)
        ]);
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
        $now = microtime(true) * 1000; // Current time in milliseconds
        if ($now - $this->lastBroadcastTime >= $this->broadcastInterval) {
            $playerPositions = [];

            foreach ($this->sessionManager->getAllSessions() as $sessionUserId => $sessionConn) {
                $userData = $this->sessionManager->getUserData($sessionUserId);
                $playerPositions[$sessionUserId] = $userData;
            }

            $this->messageSender->broadcastPlayerPositions($this->clients, $playerPositions);

            $this->lastBroadcastTime = $now;
        }
    }

    public function handleDisconnection(ConnectionInterface $conn) {
        foreach ($this->sessionManager->getAllSessions() as $id => $client) {
            if ($client === $conn) {
                $userData = $this->sessionManager->getUserData($id);
                $username = $userData['username'] ?? 'Unknown';
                $this->sessionManager->removeUserSession($id);
                Logger::log("{$username} has left the adventure");

                $this->messageSender->broadcastMessage($this->clients, [
                    'type' => 'userDisconnect',
                    'id' => $id
                ]);
                break;
            }
        }
    }
}