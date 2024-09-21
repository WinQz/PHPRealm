<?php

namespace App\Network;

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
}