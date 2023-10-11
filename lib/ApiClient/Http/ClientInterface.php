<?php

declare(strict_types=1);

namespace Ticketpark\ApiClient\Http;

interface ClientInterface
{
    public function head(string $url, array $headers): Response;

    public function get(string $url, array $headers): Response;

    public function post(string $url, string $content, array $headers): Response;

    public function postForm(string $url, array $formData, array $headers): Response;

    public function patch(string $url, string $content, array $headers): Response;

    public function delete(string $url, array $headers): Response;
}
