<?php

namespace App\Network;

use Ratchet\ConnectionInterface;
use SplObjectStorage;
use App\WebSocket\User\UserSessionManager;
use App\Network\MessageSender;

class ConnectionHandler {
    protected $clients;
    protected $sessionManager;

    public function __construct(UserSessionManager $sessionManager, SplObjectStorage $clients) {
        $this->sessionManager = $sessionManager;
        $this->clients = $clients;
        $this->messageSender = new MessageSender();
        echo "Connection Handler Initialized.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        Logger::log("New connection established: Client {$conn->resourceId}");
        Logger::log("Total clients connected: " . $this->getClientCount());
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->handleDisconnection($conn);
        Logger::log("Connection closed: Client {$conn->resourceId}");
        Logger::log("Total clients still connected: " . $this->getClientCount());
    }

    public function getClientCount(): int {
        return count($this->clients);
    }

    public function handleDisconnection(ConnectionInterface $conn) {
        foreach ($this->sessionManager->getAllSessions() as $id => $client) {
            if ($client === $conn) {
                $username = $client->userData['username'] ?? 'Unknown';
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