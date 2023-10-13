<?php

declare(strict_types=1);

namespace Ticketpark\ApiClient\Http;

use Ticketpark\ApiClient\TicketparkApiClient;

class Response
{
    public function __construct(
        private readonly int $statusCode,
        private readonly string $content,
        private readonly array $headers
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContent(): array
    {
        return json_decode($this->content, true, 512, JSON_THROW_ON_ERROR);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isSuccessful(): bool
    {
        return ($this->statusCode >= 200 && $this->statusCode <= 204);
    }

    /**
     * After creating a single record with POST, use this method
     * to get the PID of the newly created record.
     */
    public function getGeneratedPid(): ?string
    {
        $lastElement = $this->getLastElementOfLocationHeader();

        if ($lastElement && !str_contains($lastElement, 'batchId')) {
            return $lastElement;
        }

        return null;
    }

    /**
     * After creating multiple records with POST, use this method
     * to get the URL where the newly created elements can be fetched with a new GET request.
     */
    public function getGeneratedListLink(): ?string
    {
        $lastElement = $this->getLastElementOfLocationHeader();

        if ($lastElement && str_contains($lastElement, 'batchId')) {
            return str_replace(TicketparkApiClient::ROOT_URL, '', $this->getLocationHeaderContent());
        }

        return null;
    }

    private function getLastElementOfLocationHeader(): ?string
    {
        $location = $this->getLocationHeaderContent();

        if ($location) {
            return trim(preg_replace('/^.*\//', '', $location));
        }

        return null;
    }

    private function getLocationHeaderContent(): ?string
    {
        foreach($this->getHeaders() as $key => $content) {
            if (strtolower($key) === 'location') {
                return trim($content[0]);
            }
        }

        return null;
    }
}
