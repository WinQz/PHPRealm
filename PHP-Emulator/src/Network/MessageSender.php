<?php

namespace Emulator\Network;

use SplObjectStorage;

class MessageSender {

    private $sensitiveKeys = ['password', 'email', 'account_created'];

    public function broadcastMessage(SplObjectStorage $clients, array $message) {
        $filteredMessage = $this->filterSensitiveData($message);

        foreach ($clients as $client) {
            $client->send(json_encode($filteredMessage));
        }
    }

    public function filterSensitiveData(array $message): array {
        foreach ($this->sensitiveKeys as $key) {
            if (array_key_exists($key, $message)) {
                unset($message[$key]);
            }
        }
        return $message;
    }

    public function broadcastPlayerPositions(SplObjectStorage $clients, array $playerPositions) {
        $message = [
            'type' => 'updatePlayerPosition',
            'data' => $playerPositions
        ];
        
        $this->broadcastMessage($clients, $message);
    }
}