<?php

namespace App\Network;

class Logger {
    public static function log(string $message) {
        echo $message . "\n";
    }
}