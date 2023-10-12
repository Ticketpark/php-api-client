<?php

declare(strict_types=1);

namespace Ticketpark\ApiClient\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Ticketpark\ApiClient\Exception\HttpRequestException;
use Ticketpark\ApiClient\Exception\HttpTimeOutException;

final class Client implements ClientInterface
{
    private readonly GuzzleClient $guzzle;

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
            $guzzleResponse = $this->doExecute(
                $method,
                $url,
                $headers,
                $content,
                $formData
            );

        } catch (ConnectException $e) {
            if (str_contains($e->getMessage(), 'cURL error 28')) {
                throw new HttpTimeOutException();
            }

        } catch (ClientException $e) {
            /** @var GuzzleResponse $response */
            $guzzleResponse = $e->getResponse();

        } catch (\Exception $e) {
            throw new HttpRequestException($e->getMessage());
        }

        return new Response(
            $guzzleResponse->getStatusCode(),
            (string) $guzzleResponse->getBody(),
            $guzzleResponse->getHeaders()
        );
    }

    private function doExecute(
        string $method,
        string $url,
        array $headers,
        ?string $content,
        array $formData
    ): GuzzleResponse {
        $requestData = [
            'headers' => $headers,
            'timeout' => 30
        ];

        if ($formData) {
            $requestData['form_params'] = $formData;
        } else {
            $requestData['body'] = $content;
        }

        /** @var GuzzleResponse $response */
        $guzzleResponse = $this->guzzle->request(
            $method,
            $url,
            $requestData
        );

        return $guzzleResponse;
    }
}
