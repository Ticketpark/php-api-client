<?php

declare(strict_types=1);

namespace Ticketpark\ApiClient\Http;

class Response
{
    public function __construct(
        private int $statusCode,
        private string $content,
        private array $headers
    )
    {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContent(): array
    {
        return json_decode($this->content, true);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isSuccessful(): bool
    {
        return ($this->statusCode >= 200 && $this->statusCode <= 204);
    }
}