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
        
        var_dump($msg);
    
        if (isset($data['userId'])) {
            $this->handleUserData($from, $data['userId']);
        }
    
        if ($data['type'] === 'playerUpdate' && isset($data['data'])) {
            $this->handlePlayerUpdate($from, $data['data']);
        }
    }

    private function handleUserData(ConnectionInterface $conn, $id) {
        $userData = $this->userDataFetcher->getUserById($id);
        
        if (!$userData) {
            Logger::log("ID {$id} not found.");
            return;
        }
        
        $username = $userData['username'];
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
        
        Logger::log("{$username} has joined the adventure");
    
        $playersData = [];
        foreach ($this->sessionManager->getAllSessions() as $sessionUserId => $sessionConn) {
            $userDataFiltered = $this->messageSender->filterSensitiveData($sessionConn->userData);
            $playersData[$sessionUserId] = $userDataFiltered;
        }
    
        $conn->send(json_encode([
            'type' => 'userUpdate',
            'data' => $playersData
        ]));
    
        $this->messageSender->broadcastMessage($this->clients, [
            'type' => 'userJoined',
            'data' => $this->messageSender->filterSensitiveData($userData)
        ]);
    }

    private function handlePlayerUpdate(ConnectionInterface $conn, $playerData) {
        $userId = $playerData['id'];
    
        $session = $this->sessionManager->getUserSession($userId);
        if (!$session || $session !== $conn) {
            Logger::log("No valid session for user ID: {$userId}.");
            return;
        }
    
        $this->sessionManager->setUserData($userId, [
            'x' => $playerData['x'],
            'y' => $playerData['y']
        ]);
    
        Logger::log("Updated position for user ID {$userId} to ({$playerData['x']}, {$playerData['y']}).");
    
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