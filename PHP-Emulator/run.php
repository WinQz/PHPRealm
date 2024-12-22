<?php

require 'vendor/autoload.php';

use App\Database\DatabaseConnection;
use App\Database\User\Set\UserStatusUpdater;
use App\Database\User\Get\GetUserData;
use App\WebSocket\User\UserSessionManager;
use App\WebSocket\Game\GameServer;
use App\Network\ConnectionHandler;
use App\Network\MessageDispatcher;
use App\Network\Logger;
use Dotenv\Dotenv;

use React\EventLoop\Factory;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\Socket\Server as ReactServer;

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "Loading environment variables...\n";

    $config = [
        'server' => $_ENV['SERVER'] ?? 'mysql',
        'host' => $_ENV['HOST'] ?? 'localhost',
        'dbname' => $_ENV['DBNAME'] ?? 'phprealm',
        'username' => $_ENV['DB_USER'] ?? 'username',
        'password' => $_ENV['DB_PASSWORD'] ?? 'password',
    ];

    $logger = new Logger();
    echo "Logger initialized...\n";

    $loop = Factory::create();
    echo "Event loop created...\n";

    $clients = new SplObjectStorage();
    echo "Clients storage initialized...\n";

    $logger->log("Connecting to the database...");
    $dbConnection = new DatabaseConnection($config, $logger->getLogger());
    $pdo = $dbConnection->getConnection();
    $logger->log("Database connection established.");

    $logger->log("Initializing components...");
    $statusUpdater = new UserStatusUpdater($pdo);
    $userDataFetcher = new GetUserData($pdo);
    $sessionManager = new UserSessionManager($statusUpdater);
    $connectionHandler = new ConnectionHandler($sessionManager, $clients, $logger);
    $messageDispatcher = new MessageDispatcher($userDataFetcher, $sessionManager, $clients, $logger);
    $gameServer = new GameServer($connectionHandler, $messageDispatcher, $logger);
    $logger->log("Components initialized.");

    $logger->log("Setting up WebSocket server...");
    $reactServer = new ReactServer('0.0.0.0:8080', $loop);
    $server = new IoServer(
        new HttpServer(
            new WsServer($gameServer)
        ),
        $reactServer,
        $loop
    );
    $logger->log("WebSocket server setup complete.");

    $logger->log("Server running on port 8080");
    $server->run();
} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    if (isset($logger)) {
        $logger->error("An error occurred: " . $e->getMessage());
    }
}