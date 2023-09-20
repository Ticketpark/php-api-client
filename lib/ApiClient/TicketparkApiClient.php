<?php

namespace Ticketpark\ApiClient;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Message\Response;
use Ticketpark\ApiClient\Exception\TokenGenerationException;
use Ticketpark\ApiClient\Token\AccessToken;
use Ticketpark\ApiClient\Token\RefreshToken;

class TicketparkApiClient
{
    private const ROOT_URL = 'https://api.ticketpark.ch';
    private const REFRESH_TOKEN_LIFETIME = 30 * 86400;

    private ?string $username = null;
    private ?string $password = null;
    private ?Browser $browser = null;
    private ?RefreshToken $refreshToken = null;
    private ?AccessToken $accessToken = null;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret
    ) {
    }

    public function setBrowser(Browser $browser = null): void
    {
        $this->browser = $browser;
    }

    public function getBrowser(): Browser
    {
        if (null === $this->browser) {
            $this->browser = new Browser(new Curl());
        }

        return $this->browser;
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

    public function setAccessTokenInstance(AccessToken $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = new AccessToken($accessToken);
    }

    public function getRefreshToken(): ?RefreshToken
    {
        return $this->refreshToken;
    }

    public function setRefreshTokenInstance(RefreshToken $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = new RefreshToken($refreshToken);
    }

    public function get(string $path, array $parameters = [], array $headers = []): Response
    {
        $params = '';
        if (count($parameters)) {
            $params = '?' . http_build_query($parameters);
        }

        return $this->getBrowser()->get(self::ROOT_URL . $path . $params, $this->getDefaultHeaders($headers));
    }

    public function post(string $path, mixed $content = '', array $headers = []): Response
    {
        return $this->getBrowser()->post(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), json_encode($content, JSON_THROW_ON_ERROR));
    }

    public function head($path, array $parameters = [], array $headers = []): Response
    {
        $params = '';
        if (count($parameters)) {
            $params = '?' . http_build_query($parameters);
        }

        return $this->getBrowser()->head(self::ROOT_URL . $path . $params, $this->getDefaultHeaders($headers));
    }

    public function patch(string $path, mixed $content = '', array $headers = []): Response
    {
        return $this->getBrowser()->patch(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), json_encode($content, JSON_THROW_ON_ERROR));
    }

    public function delete(string $path, array $headers = []): Response
    {
        return $this->getBrowser()->delete(self::ROOT_URL . $path, $this->getDefaultHeaders($headers));
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
        if (!isset($data) && $this->username) {
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

    private function getDefaultHeaders(array $customHeaders = []): array
    {
        $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer ' .  $this->getValidAccessToken()];

        return array_merge($customHeaders, $headers);
    }

    private function getValidAccessToken(): string
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken || $accessToken->hasExpired()) {
            $this->generateTokens();
            $accessToken = $this->getAccessToken();
        }

        return $accessToken->getToken();
    }

    protected function doGenerateTokens(array $data): bool
    {
        $headers = [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Accept'        => 'application/json',
            'Authorization' => 'Basic '.base64_encode($this->apiKey . ':' . $this->apiSecret)
        ];

        $response = $this->getBrowser()->post(self::ROOT_URL . '/oauth/v2/token', $headers, $data);

        if (200 == $response->getStatusCode()) {
            $response = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $this->accessToken = new AccessToken(
                $response['access_token'],
                (new \DateTime())->setTimestamp(time() + $response['expires_in'])
            );

            $this->refreshToken = new RefreshToken(
                $response['refresh_token'],
                (new \DateTime())->setTimestamp(time() + self::REFRESH_TOKEN_LIFETIME)
            );

            return true;
        }

        return false;
    }
}
