<?php

namespace Ticketpark\ApiClient;

use Ticketpark\ApiClient\Exception\TokenGenerationException;
use Ticketpark\ApiClient\Exception\UnexpectedResponseException;
use Ticketpark\ApiClient\Http\Client;
use Ticketpark\ApiClient\Http\ClientInterface;
use Ticketpark\ApiClient\Http\Response;
use Ticketpark\ApiClient\Token\AccessToken;
use Ticketpark\ApiClient\Token\RefreshToken;

class TicketparkApiClient
{
    public const ROOT_URL = 'https://api.ticketpark.ch';
    private const REFRESH_TOKEN_LIFETIME = 30 * 86400;

    private ?ClientInterface $client = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?RefreshToken $refreshToken = null;
    private ?AccessToken $accessToken = null;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret
    ) {
    }

    public function setUserCredentials(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = new AccessToken($accessToken);
    }

    public function getRefreshToken(): ?RefreshToken
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = new RefreshToken($refreshToken);
    }

    public function head(string $path, array $parameters = []): Response
    {
        return $this->getClient()->head(
            $this->getUrl($path, $parameters),
            $this->getHeaders()
        );
    }

    public function get(string $path, array $parameters = []): Response
    {
        return $this->getClient()->get(
            $this->getUrl($path, $parameters),
            $this->getHeaders()
        );
    }

    public function post(string $path, array $data = []): Response
    {
        return $this->getClient()->post(
            $this->getUrl($path),
            json_encode($data, JSON_THROW_ON_ERROR),
            $this->getHeaders()
        );
    }

    public function patch(string $path, array $data = []): Response
    {
        return $this->getClient()->patch(
            $this->getUrl($path),
            json_encode($data, JSON_THROW_ON_ERROR),
            $this->getHeaders()
        );
    }

    public function delete(string $path): Response
    {
        return $this->getClient()->delete(
            $this->getUrl($path),
            $this->getHeaders()
        );
    }

    public function generateTokens(): void
    {
        // Try with refresh token
        $refreshToken = $this->getRefreshToken();
        if ($refreshToken && !$refreshToken->hasExpired()) {
            $data = [
                'refresh_token' => $refreshToken->getToken(),
                'grant_type' => 'refresh_token'
            ];

            if ($this->doGenerateTokens($data)) {
                return;
            }
        }

        // Try with user credentials
        if ($this->username) {
            $data = [
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password'
            ];

            if ($this->doGenerateTokens($data)) {
                return;
            }
        }

        throw new TokenGenerationException('Failed to generate a access tokens. Make sure to provide a valid refresh token or user credentials.');
    }

    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    private function getClient(): ClientInterface
    {
        if (null === $this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    private function getUrl(string $path, array $parameters = []): string
    {
        $params = '';
        if (count($parameters)) {
            $params = '?' . http_build_query($parameters);
        }

        return self::ROOT_URL . $path . $params;
    }

    private function getValidAccessToken(): string
    {
        $accessToken = $this->getAccessToken();

        if (null === $accessToken || $accessToken->hasExpired()) {
            $this->generateTokens();
            $accessToken = $this->getAccessToken();
        }

        return $accessToken->getToken();
    }

    private function doGenerateTokens(array $data): bool
    {
        $headers = [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret)
        ];

        $response = $this->getClient()->postForm(
            $this->getUrl('/oauth/v2/token'),
            $data,
            $headers,
        );

        if (!$response->isSuccessful()) {
            return false;
        }

        $content = $response->getContent();

        if (!isset($content['access_token']) || !isset($content['refresh_token']) || !isset($content['expires_in'])) {
            throw new UnexpectedResponseException('Generating tokens did not receive the expected http response.');
        }

        $this->accessToken = new AccessToken(
            $content['access_token'],
            (new \DateTime())->setTimestamp(time() + $content['expires_in'])
        );

        $this->refreshToken = new RefreshToken(
            $content['refresh_token'],
            (new \DateTime())->setTimestamp(time() + self::REFRESH_TOKEN_LIFETIME)
        );

        return true;
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' .  $this->getValidAccessToken()
        ];
    }
}
