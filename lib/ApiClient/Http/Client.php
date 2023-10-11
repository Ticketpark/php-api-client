<?php

declare(strict_types=1);

namespace Ticketpark\ApiClient\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

final class Client implements ClientInterface
{
    private GuzzleClient $guzzle;

    public function __construct()
    {
        $this->guzzle = new GuzzleClient();
    }

    public function head(string $url, array $headers): Response
    {
        return $this->execute('head', $url, $headers);
    }

    public function get(string $url, array $headers): Response
    {
        return $this->execute('get', $url, $headers);
    }

    public function post(string $url, string $content, array $headers): Response
    {
        return $this->execute('post', $url, $headers, $content);
    }

    public function postForm(string $url, array $formData, array $headers): Response
    {
        return $this->execute('post', $url, $headers, null, $formData);
    }

    public function patch(string $url, string $content, array $headers): Response
    {
        return $this->execute('patch', $url, $headers, $content);
    }

    public function delete(string $url, array $headers): Response
    {
        return $this->execute('delete', $url, $headers);
    }

    private function execute(
        string $method,
        string $url,
        array $headers = [],
        string $content = null,
        array $formData = []
    ): Response {
        try {
            /** @var GuzzleResponse $response */
            $guzzleResponse = $this->guzzle->request(
                $method,
                $url,
                [
                    'headers' => $headers,
                    'body' => $content,
                    'form_params' => $formData
                ]
            );
        } catch (\Exception $e) {
            if (!$e instanceof ClientException) {
                throw new HttpRequestException($e->getMessage());
            }

            /** @var GuzzleResponse $response */
            $guzzleResponse = $e->getResponse();
        }

        return new Response(
            $guzzleResponse->getStatusCode(),
            (string) $guzzleResponse->getBody(),
            $guzzleResponse->getHeaders()
        );
    }
}