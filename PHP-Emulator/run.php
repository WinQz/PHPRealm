<?php

require 'vendor/autoload.php';

use App\Database\DatabaseConnection;
use App\Database\User\Set\UserStatusUpdater;
use App\Database\User\Get\GetUserData;
use App\WebSocket\User\UserSessionManager;
use App\WebSocket\Game\GameServer;
use App\Network\ConnectionHandler;
use App\Network\MessageDispatcher;

use React\EventLoop\Factory;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\Socket\Server as ReactServer;

$loop = Factory::create();

$clients = new SplObjectStorage();

$dbConnection = new DatabaseConnection();
$pdo = $dbConnection->getConnection();

$statusUpdater = new UserStatusUpdater($pdo);
$userDataFetcher = new GetUserData($pdo);

$sessionManager = new UserSessionManager($statusUpdater);

$connectionHandler = new ConnectionHandler($sessionManager, $clients);
$messageDispatcher = new MessageDispatcher($userDataFetcher, $sessionManager, $clients);

$gameServer = new GameServer($connectionHandler, $messageDispatcher);

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