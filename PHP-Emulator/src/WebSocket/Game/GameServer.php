<?php

namespace App\WebSocket\Game;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\WebSocket\User\UserSessionManager;

class GameServer implements MessageComponentInterface {
    protected $clients;
    protected $sessionManager;

    public function __construct(UserSessionManager $sessionManager) {
        $this->clients = new \SplObjectStorage;
        $this->sessionManager = $sessionManager;
        $this->log("GameServer Initialized. \nWaiting for connections...");
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->log("New connection established: Client {$conn->resourceId}");
        $this->log("Total clients connected: " . $this->getClientCount());
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (isset($data['userData'])) {
            $this->handleUserData($from, $data['userData']);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->handleDisconnection($conn);
        $this->log("Connection closed: Client {$conn->resourceId}");
        $this->log("Total clients still connected: " . $this->getClientCount());
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log("Error on Client {$conn->resourceId}: {$e->getMessage()}");
        $conn->close();
    }

    private function handleUserData(ConnectionInterface $conn, array $userData) {
        $userId = $userData['id'];
        $username = $userData['username'];
        
        $conn->userData = $userData;
        $this->sessionManager->disconnectPreviousSession($userId, $conn);
        $this->sessionManager->setUserSession($userId, $conn);
        
        $this->log("{$username} has joined the adventure");
    }

    private function handleDisconnection(ConnectionInterface $conn) {
        foreach ($this->sessionManager->getAllSessions() as $userId => $client) {
            if ($client === $conn) {
                $username = $client->userData['username'] ?? 'Unknown';
                $this->sessionManager->removeUserSession($userId);
                $this->log("{$username} has left the adventure");
                break;
            }
        }
    }

    private function getClientCount(): int {
        return count($this->clients);
    }

    private function log(string $message) {
        echo $message . "\n";
    }
}