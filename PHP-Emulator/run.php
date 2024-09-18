<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\Game\GameServer;
use App\WebSocket\User\UserSessionManager;

$sessionManager = new UserSessionManager();
$gameServer = new GameServer($sessionManager);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $gameServer
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
$server->run();