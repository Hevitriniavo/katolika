<?php

namespace App\Core;

use AltoRouter;
use Exception;
use ReflectionException;
use ReflectionMethod;

class Server
{
    private AltoRouter $router;

    /**
     * @throws Exception
     */
    public function __construct(AltoRouter $router, array $routes)
    {
        $this->router = $router;
        session_start();

        foreach ($routes as $route) {
            $this->router->map(
                $route['method'],
                $route['path'],
                $route['controller'],
                $route['name'] ?? null
            );
        }
    }

    public static function start(AltoRouter $router, array $routes): void
    {
        try {
            (new self($router, $routes))->run();
        } catch (ReflectionException|Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @throws ReflectionException
     */
    public function run(): void
    {
        $match = $this->router->match();

        if ($match) {
            $controller = $match['target'];
            $params = $match['params'];

            if (is_array($controller)) {
                list($controllerClass, $method) = $controller;
                $controllerInstance = new $controllerClass();

                $request = Request::createFromGlobals();

                if (method_exists($controllerInstance, $method)) {
                    $reflectionMethod = new ReflectionMethod($controllerInstance, $method);
                    $args = [];
                    foreach ($reflectionMethod->getParameters() as $param) {
                        $paramName = $param->getName();
                        if ($param->getType() && $param->getType()->getName() === Request::class) {
                            $args[] = $request;
                        } elseif (isset($params[$paramName])) {
                            $args[] = $params[$paramName];
                        } else {
                            $args[] = null;
                        }
                    }
                    echo $reflectionMethod->invokeArgs($controllerInstance, $args);
                } else {
                    echo 'Method not found!';
                }
            } else {
                echo 'Controller not found!';
            }
        } else {
            echo 'No matching route found!';
        }
    }
}
