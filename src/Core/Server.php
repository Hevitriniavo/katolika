<?php

namespace App\Core;

use AltoRouter;
use Exception;
use ReflectionException;
use ReflectionMethod;

class Server
{
    private AltoRouter $router;
    private array $routes;

    /**
     * @throws Exception
     */
    public function __construct(AltoRouter $router, array $routes)
    {
        $this->router = $router;
        $this->routes = $routes;
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
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function run(): void
    {
        $match = $this->router->match();

        if ($match) {
            $controller = $match['target'];
            $params = $match['params'];

            $request = Request::createFromGlobals();
            $response = new Response('');

            foreach ($this->routes as $route) {
                $middlewares = $route["middlewares"];
                $this->invokeMiddlewares($middlewares, $controller, $params, $request, $response);
            }

            echo $response;
        } else {
            echo 'No matching route found!';
        }
    }

    private function invokeController($controller, array $params, Request $request, Response $response): Response
    {
        if (is_array($controller)) {
            [$controllerClass, $method] = $controller;

            if (!class_exists($controllerClass)) {
                $response->setContent('Controller class not found!');
                return $response;
            }

            $controllerInstance = new $controllerClass();

            if (!method_exists($controllerInstance, $method)) {
                $response->setContent('Method not found!');
                return $response;
            }

            $reflectionMethod = new ReflectionMethod($controllerInstance, $method);
            $args = [];

            foreach ($reflectionMethod->getParameters() as $param) {
                $paramName = $param->getName();

                if ($param->getType() && $param->getType()->getName() === Request::class) {
                    $args[] = $request;
                } elseif (array_key_exists($paramName, $params)) {
                    $args[] = $params[$paramName];
                } else {
                    $args[] = null;
                }
            }

            try {
                $responseContent = $reflectionMethod->invokeArgs($controllerInstance, $args);
                $response->setContent($responseContent);
            } catch (Exception $e) {
                $response->setContent('Error invoking method: ' . $e->getMessage());
            }
        } else {
            $response->setContent('Invalid controller format!');
        }

        return $response;
    }

    /**
     * @throws Exception
     */
    private function invokeMiddlewares(array $middlewareClasses, $controller, array $params, Request $request, Response $response): void
    {
        $middlewareClasses = array_reverse($middlewareClasses);
        $next = function (Request $request, Response $response) use ($controller, $params) {
            return $this->invokeController($controller, $params, $request, $response);
        };

        foreach ($middlewareClasses as $middlewareClass) {
            if (is_string($middlewareClass)) {
                $middlewareClass = new $middlewareClass();
            }

            if (is_object($middlewareClass) && method_exists($middlewareClass, 'handle')) {
                $currentMiddleware = $middlewareClass;
                $next = function (Request $request, Response $response) use ($currentMiddleware, $next) {
                    return $currentMiddleware->handle($request, $response, $next);
                };
            } else {
                throw new Exception('Invalid middleware class or missing handle method.');
            }
        }

        $finalResponse = $next($request, $response);

        if ($finalResponse instanceof Response) {
            $response->setContent($finalResponse->getContent());
            $response->setStatusCode($finalResponse->getStatusCode());
        } else {
            throw new Exception('Middleware did not return a valid Response object.');
        }
    }

}
