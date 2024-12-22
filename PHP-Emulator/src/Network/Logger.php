<?php

namespace Emulator\Network;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Logger {
    private $logger;

    public function __construct() {
        $this->logger = new MonologLogger('PHP-Emulator');
        $this->logger->pushHandler(new StreamHandler('php://stdout', MonologLogger::DEBUG));
    }

    public function log(string $message) {
        $this->logger->info($message);
    }

    public function error(string $message) {
        $this->logger->error($message);
    }

    public function getLogger(): MonologLogger {
        return $this->logger;
    }
}