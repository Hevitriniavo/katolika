<?php

namespace App\Core;

class Request
{
    private array $queryParams;
    private array $postParams;
    private array $data;
    private string $method;

    private function __construct(array $queryParams, array $postParams, array $data, string $method)
    {
        $this->queryParams = $queryParams;
        $this->postParams = $postParams;
        $this->data = $data;
        $this->method = $method;
    }

    public static function createFromGlobals(): self
    {
        $queryParams = $_GET;
        $postParams = $_POST;
        $data = $_POST;

        parse_str(file_get_contents("php://input"), $putData);
        $data = array_merge($data, $putData);

        if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $json = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $data = array_merge($data, $json);
            }
        }

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && isset($postParams['__method'])) {
            $method = strtoupper($postParams['__method']);
        }

        return new self($queryParams, $postParams, $data, $method);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPost(string $key): ?string
    {
        return $this->postParams[$key] ?? null;
    }

    public function getQuery(string $key): ?string
    {
        return $this->queryParams[$key] ?? null;
    }

    public function getData(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function getPayload(): array
    {
        return $this->data;
    }
}
