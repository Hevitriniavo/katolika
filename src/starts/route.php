<?php


use App\Controller\HomeController;

return [
    [
        "path" => "/",
        "method" => "GET",
        "controller" => [HomeController::class, "index"],
        "name" => "home"
    ]
];
