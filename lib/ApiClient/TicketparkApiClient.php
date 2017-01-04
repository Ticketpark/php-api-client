<?php

namespace Ticketpark\ApiClient;

use Buzz\Browser;
use Ticketpark\ApiClient\Exception\TokenGenerationException;
use Ticketpark\ApiClient\Token\AccessToken;
use Ticketpark\ApiClient\Token\RefreshToken;

class TicketparkApiClient
{
    /**
     * @const string ROOT_URL
     */
    const ROOT_URL = 'https://api.ticketpark.ch';

    /**
     * @const int REFRESH_TOKEN_LIFETIME
     */
    const REFRESH_TOKEN_LIFETIME = 30 * 86400;

    /**
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * @var string $apiSecret
     */
    protected $apiSecret;

    /**
     * @var string $username
     */
    protected $username;

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var Browser
     */
    protected $browser;

    /**
     * @var RefreshToken
     */
    protected $refreshToken;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * Constructor
     *
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Set browser
     *
     * @param Browser $browser
     * @return $this
     */
    public function setBrowser(Browser $browser = null)
    {
        $this->browser = $browser;

        return $this;
    }

    /**
     * Get browser
     *
     * @return \Buzz\Browser
     */
    public function getBrowser()
    {
        if (null == $this->browser) {
            $this->browser = new \Buzz\Browser(new \Buzz\Client\Curl());
        }

        return $this->browser;
    }

    /**
     * Set user credentials
     *
     * @param string $username
     * @param string $password
     */
    public function setUserCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    /**
     * Get access token
     *
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set access token object
     *
     * @param AccessToken $accessToken
     * @return $this
     */
    public function setAccessTokenInstance(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set access token
     *
     * @param string $accessToken
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = new AccessToken($accessToken);

        return $this;
    }

    /**
     * Get refresh token
     *
     * @return RefreshToken
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set refresh token object
     *
     * @param RefreshToken $refreshToken
     * @return $this
     */
    public function setRefreshTokenInstance(RefreshToken $refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }


    /**
     * Set refresh token
     *
     * @param RefreshToken $refreshToken
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = new RefreshToken($refreshToken);

        return $this;
    }

    /**
     * GET
     *
     * @param string $path
     * @param array $parameters
     * @param array $headers
     * @return \Buzz\Message\Response
     */
    public function get($path, $parameters = array(), $headers = array())
    {
        $params = '';
        if (count($parameters)) {
            $params = '?' . http_build_query($parameters);
        }

        return $this->getBrowser()->get(self::ROOT_URL . $path . $params, $this->getDefaultHeaders($headers));
    }

    /**
     * POST
     *
     * @param string $path
     * @param string $content
     * @param array $headers
     * @return \Buzz\Message\Response
     */
    public function post($path, $content = '', $headers = array())
    {
        return $this->getBrowser()->post(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), json_encode($content));
    }

    /**
     * HEAD
     *
     * @param string $path
     * @param array $headers
     * @return \Buzz\Message\Response
     */
    public function head($path, $parameters = array(), $headers = array())
    {
        $params = '';
        if (count($parameters)) {
            $params = '?' . http_build_query($parameters);
        }

        return $this->getBrowser()->head(self::ROOT_URL . $path . $params, $this->getDefaultHeaders($headers));
    }

    /**
     * PATCH
     *
     * @param string $path
     * @param string $content
     * @param array $headers
     * @return \Buzz\Message\Response
     */
    public function patch($path, $content = '', $headers = array())
    {
        return $this->getBrowser()->patch(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), json_encode($content));
    }

    /**
     * PUT
     *
     * @param string $path
     * @param string $content
     * @param array $headers
     * @return \Buzz\Message\MessageInterface
     */
    public function put($path, $content = '', $headers = array())
    {
        return $this->getBrowser()->put(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), json_encode($content));
    }

    /**
     * DELETE
     *
     * @param string $path
     * @param array $headers
     * @return \Buzz\Message\Response
     */
    public function delete($path, $headers = array())
    {
        return $this->getBrowser()->delete(self::ROOT_URL . $path, $this->getDefaultHeaders($headers));
    }

    /**
     * Generate new tokens
     *
     * @throws TokenGenerationException
     */
    public function generateTokens()
    {
        // Try with refresh token
        $refreshToken = $this->getRefreshToken();
        if ($refreshToken && !$refreshToken->hasExpired()) {
            $data = array(
                'refresh_token' => $refreshToken->getToken(),
                'grant_type' => 'refresh_token'
            );

            if ($this->doGenerateTokens($data)) {
                return;
            }
        }

        // Try with user credentials
        if (!isset($data) && $this->username) {
            $data = array(
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password'
            );

            if ($this->doGenerateTokens($data)) {
                return;
            }
        }

        throw new TokenGenerationException('Failed to generate a access tokens. Make sure to provide a valid refresh token or user credentials.');
    }

    /**
     * Get headers
     *
     * @return array
     */
    protected function getDefaultHeaders($customHeaders = array())
    {
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' .  $this->getValidAccessToken()
        );

        return array_merge($customHeaders, $headers);
    }

    /**
     * Get a valid access token
     *
     * @return string
     */
    protected function getValidAccessToken()
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken || $accessToken->hasExpired()) {
            $this->generateTokens();
            $accessToken = $this->getAccessToken();
        }

        return $accessToken->getToken();
    }

    /**
     * Actually tries to generate tokens.
     *
     * Returns whether token generation has been successful.
     *
     * @param array $data
     * @return bool
     */
    protected function doGenerateTokens(array $data)
    {
        $headers = array(
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Accept'        => 'application/json',
            'Authorization' => 'Basic '.base64_encode($this->apiKey . ':' . $this->apiSecret)
        );

        $response = $this->getBrowser()->post(self::ROOT_URL . '/oauth/v2/token', $headers, $data);

        if (200 == $response->getStatusCode()) {
            $response = json_decode($response->getContent(), true);

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