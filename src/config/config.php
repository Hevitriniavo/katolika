<?php

return [
    "database" => [
        "driver" => $_ENV['DATABASE_DRIVER'] ?? null,
        "host" => $_ENV['DATABASE_HOST'] ?? null,
        "username" => $_ENV['DATABASE_USERNAME'] ?? null,
        "password" => $_ENV['DATABASE_PASSWORD'] ?? null,
        "name" => $_ENV['DATABASE_NAME'] ?? null,
        "path" => $_ENV['DATABASE_PATH'] ?? null,
        "dsn" => $_ENV['DATABASE_DSN'] ?? null
    ]
];
