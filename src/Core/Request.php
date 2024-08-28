<?php

namespace App\Core;

class Request
{
    private array $queryParams;
    private array $postParams;
    private array $data;
    private string $method;
    private array $headers;
    private array $cookies;

    private function __construct(array $queryParams, array $postParams, array $data, string $method, array $headers, array $cookies)
    {
        $this->queryParams = $queryParams;
        $this->postParams = $postParams;
        $this->data = $data;
        $this->method = $method;
        $this->headers = $headers;
        $this->cookies = $cookies;
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

        // Retrieve headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = str_replace('HTTP_', '', $key);
                $headerKey = str_replace('_', '-', strtolower($headerKey));
                $headers[$headerKey] = $value;
            }
        }

        // Retrieve cookies
        $cookies = $_COOKIE;

        return new self($queryParams, $postParams, $data, $method, $headers, $cookies);
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

    public function getHeader(string $key): ?string
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? null;
    }

    public function getCookie(string $key): ?string
    {
        return $this->cookies[$key] ?? null;
    }

    public function setCookie(string $key, string $value, int $expiry = 0, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false): void
    {
        setcookie($key, $value, $expiry, $path, $domain, $secure, $httponly);
    }

    public function getSession(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
