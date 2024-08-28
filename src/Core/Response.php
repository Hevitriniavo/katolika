<?php

namespace App\Core;


class Response
{
    private ?string $content;
    private int $statusCode;

    public function __construct(string $content = '', int $statusCode = 200)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setStatusCode(int $statusCode): void
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException('Invalid HTTP status code');
        }
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
