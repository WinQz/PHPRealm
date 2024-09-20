<?php

namespace App\Network;

use SplObjectStorage;

class MessageSender {
    public static function broadcastMessage(SplObjectStorage $clients, array $message) {
        foreach ($clients as $client) {
            $client->send(json_encode($message));
        }
    }
}