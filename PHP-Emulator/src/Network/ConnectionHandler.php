<?php

namespace App\Network;

use Ratchet\ConnectionInterface;
use SplObjectStorage;
use App\WebSocket\User\UserSessionManager;
use App\Network\MessageSender;

class ConnectionHandler {
    protected $clients;
    protected $sessionManager;
    protected $logger;

    public function __construct(UserSessionManager $sessionManager, SplObjectStorage $clients, Logger $logger) {
        $this->sessionManager = $sessionManager;
        $this->clients = $clients;
        $this->logger = $logger;
        $this->messageSender = new MessageSender();
        $this->logger->log("Connection Handler Initialized.\n");
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->logger->log("New connection established: Client {$conn->resourceId}");
        $this->logger->log("Total clients connected: " . $this->getClientCount());
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->handleDisconnection($conn);
        $this->logger->log("Connection closed: Client {$conn->resourceId}");
        $this->logger->log("Total clients still connected: " . $this->getClientCount());
    }

    public function getClientCount(): int {
        return count($this->clients);
    }

    public function handleDisconnection(ConnectionInterface $conn) {
        foreach ($this->sessionManager->getAllSessions() as $id => $client) {
            if ($client === $conn) {
                $userData = $this->sessionManager->getUserData($id);
                $username = $userData['username'] ?? 'Unknown';
                $this->sessionManager->removeUserSession($id);
                $this->logger->log("{$username} has left the adventure");

                $this->messageSender->broadcastMessage($this->clients, [
                    'type' => 'userDisconnect',
                    'id' => $id
                ]);
                break;
            }
        }
    }
}