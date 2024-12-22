<?php

namespace Emulator\WebSocket\Game;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Emulator\Network\ConnectionHandler;
use Emulator\Network\MessageDispatcher;
use Emulator\Network\Logger;

class GameServer implements MessageComponentInterface {
    protected $connectionHandler;
    protected $messageDispatcher;
    protected $logger;

    public function __construct(ConnectionHandler $connectionHandler, MessageDispatcher $messageDispatcher, Logger $logger) {
        $this->connectionHandler = $connectionHandler;
        $this->messageDispatcher = $messageDispatcher;
        $this->logger = $logger;
        $this->logger->log("GameServer Initialized. Waiting for connections...\n");
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
        $this->logger->log("Error on Client {$conn->resourceId}: {$e->getMessage()}");
        $conn->close();
    }
}