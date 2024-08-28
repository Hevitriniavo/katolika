<?php


use App\Controller\HomeController;
use App\Middleware\AuthMiddleware;

return [
    [
        "path" => "/",
        "method" => "GET",
        "controller" => [HomeController::class, "index"],
        "name" => "home",
        "middlewares" => [AuthMiddleware::class]
    ]
];
