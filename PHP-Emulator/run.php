<?php

require 'vendor/autoload.php';

use App\Database\DatabaseConnection;
use App\Database\User\Set\UserStatusUpdater;
use App\Database\User\Get\GetUserData;
use App\WebSocket\User\UserSessionManager;
use App\WebSocket\Game\GameServer;
use App\Network\ConnectionHandler;
use App\Network\MessageDispatcher;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

use React\EventLoop\Factory;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\Socket\Server as ReactServer;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
    'server' => $_ENV['SERVER'] ?? 'mysql',
    'host' => $_ENV['HOST'] ?? 'localhost',
    'dbname' => $_ENV['DBNAME'] ?? 'phprealm',
    'username' => $_ENV['DB_USER'] ?? 'username',
    'password' => $_ENV['DB_PASSWORD'] ?? 'password',
];

$logger = new Logger('app');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$loop = Factory::create();

$clients = new SplObjectStorage();

$dbConnection = new DatabaseConnection($config, $logger);
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

$logger->info("Server running on port 8080");

$server->run();