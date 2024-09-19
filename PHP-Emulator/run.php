<?php

require 'vendor/autoload.php';

use App\Database\DatabaseConnection;
use App\Database\User\Set\UserStatusUpdater;
use App\Database\User\Get\GetUserData;
use App\WebSocket\User\UserSessionManager;
use App\WebSocket\Game\GameServer;
use React\EventLoop\Factory;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\Socket\Server as ReactServer;

$loop = Factory::create();

$dbConnection = new DatabaseConnection();
$pdo = $dbConnection->getConnection();

$statusUpdater = new UserStatusUpdater($pdo);
$userDataFetcher = new GetUserData($pdo);
$sessionManager = new UserSessionManager($statusUpdater);

$gameServer = new GameServer($sessionManager, $userDataFetcher);

$reactServer = new ReactServer('0.0.0.0:8080', $loop);

$server = new IoServer(
    new HttpServer(
        new WsServer($gameServer)
    ),
    $reactServer,
    $loop
);

echo "Server running on port 8080\n";
$server->run();