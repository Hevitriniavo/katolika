<?php

namespace App;

use Dotenv\Dotenv;
use AltoRouter;
use App\Core\Server;

class Kernel
{
    private static ?Kernel $instance = null;

    private function __construct()
    {
        $this->loadEnvironmentVariables();
        $this->defineConstants();
    }

    private function __clone() {}

    public static function getInstance(): Kernel
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironmentVariables(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }

    private function defineConstants(): void
    {
        define('CONFIG_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        define('RESOURCE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'resources');
    }

    public function run(): void
    {
        $routes = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'starts' . DIRECTORY_SEPARATOR . 'route.php';
        Server::start(new AltoRouter(), $routes);
    }
}
