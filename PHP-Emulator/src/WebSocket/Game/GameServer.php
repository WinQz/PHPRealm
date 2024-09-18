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
        echo "GameServer initialized. Waiting for connections...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection established: Client {$conn->resourceId}\n";
        echo "Total clients connected: " . count($this->clients) . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (isset($data['userData'])) {
            $userData = $data['userData'];
            $userId = $userData['id'];
            $username = $userData['username'];

            echo "Received user data from Client {$from->resourceId}: " . print_r($userData, true) . "\n";

            $from->userData = $userData;

            $this->sessionManager->disconnectPreviousSession($userId, $from);
            $this->sessionManager->setUserSession($userId, $from);

            echo $username . " has joined the adventure\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        // This somehow gives sometimes error when user leaves have to check later then
        foreach ($this->sessionManager->getAllSessions() as $userId => $client) {
            if ($client === $conn) {
                $username = $client->userData['username'] ?? 'Unknown';
                $this->sessionManager->removeUserSession($userId);

                echo $username . " has left the adventure\n";
                break;
            }
        }

        echo "Connection closed: Client {$conn->resourceId}\n";
        echo "Total clients still connected: " . count($this->clients) . "\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred on Client {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }
}