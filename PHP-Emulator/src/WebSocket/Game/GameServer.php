<?php

namespace App\WebSocket\Game;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Network\ConnectionHandler;
use App\Network\MessageDispatcher;
use App\Network\Logger;

class GameServer implements MessageComponentInterface {
    protected $connectionHandler;
    protected $messageDispatcher;

    public function __construct(ConnectionHandler $connectionHandler, MessageDispatcher $messageDispatcher) {
        $this->connectionHandler = $connectionHandler;
        $this->messageDispatcher = $messageDispatcher;
        Logger::log("GameServer Initialized. Waiting for connections...\n");
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->connectionHandler->onOpen($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->messageDispatcher->onMessage($from, $msg);
    }

    public function onClose(ConnectionInterface $conn) {
        $this->connectionHandler->onClose($conn);
        $this->messageDispatcher->handleDisconnection($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        Logger::log("Error on Client {$conn->resourceId}: {$e->getMessage()}");
        $conn->close();
    }
}