<?php

namespace Ticketpark\ApiClient;

use Buzz\Browser;
use Ticketpark\ApiClient\Token\AccessToken;
use Ticketpark\ApiClient\Token\RefreshToken;

class TicketparkApiClient
{
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
     * @const string ROOT_URL
     */
    const ROOT_URL = 'https://api.ticketpark.ch';

    /**
     * @const int REFRESH_TOKEN_LIFETIME
     */
    const REFRESH_TOKEN_LIFETIME = 30 * 86400;

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
     * Set access token
     *
     * @param AccessToken $accessToken
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

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
     * Set refresh token
     *
     * @param RefreshToken $refreshToken
     * @return $this
     */
    public function setRefreshToken(RefreshToken $refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * GET
     *
     * @param string $path
     * @param array $headers
     * @return \Buzz\Message\MessageInterface
     */
    public function get($path, $headers = array())
    {
        return $this->getBrowser()->get(self::ROOT_URL . $path, $this->getDefaultHeaders($headers));
    }

    /**
     * POST
     *
     * @param string $path
     * @param string $content
     * @param array $headers
     * @return \Buzz\Message\MessageInterface
     */
    public function post($path, $content = '', $headers = array())
    {
        return $this->getBrowser()->post(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), $content);
    }

    /**
     * HEAD
     *
     * @param string $path
     * @param array $headers
     * @return \Buzz\Message\MessageInterface
     */
    public function head($path, $headers = array())
    {
        return $this->getBrowser()->head(self::ROOT_URL . $path, $this->getDefaultHeaders($headers));
    }

    /**
     * PATCH
     *
     * @param string $path
     * @param string $content
     * @param array $headers
     * @return \Buzz\Message\MessageInterface
     */
    public function patch($path, $content = '', $headers = array())
    {
        return $this->getBrowser()->patch(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), $content);
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
        return $this->getBrowser()->put(self::ROOT_URL . $path, $this->getDefaultHeaders($headers), $content);
    }

    /**
     * DELETE
     *
     * @param string $path
     * @param array $headers
     * @return \Buzz\Message\MessageInterface
     */
    public function delete($path, $headers = array())
    {
        return $this->getBrowser()->delete(self::ROOT_URL . $path, $this->getDefaultHeaders($headers));
    }

    /**
     * Generate tokens
     *
     * @throws \Exception
     */
    public function generateTokens()
    {
        // Refresh token available?
        if ($refreshToken = $this->getRefreshToken()) {
            if (!$refreshToken->getExpiration() || $refreshToken->getExpiration()->getTimestamp() > (time() + 10)) {
                $data = array(
                    'refresh_token' => $refreshToken->getToken(),
                    'grant_type' => 'refresh_token'
                );
            }
        }

        // If there is no refresh token, use username and password, if available
        if(!isset($data) && $this->username){

            $data = array(
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password'
            );
        }

        if (!isset($data)) {

            throw new \Exception('You must provide either a valid access token, a valid refresh token or user credentials.');
        }

        // Generate access token
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

        } else {

            throw new \Exception('Generating access token failed.');
        }
    }

    /**
     * Get valid access token based on available data
     *
     * @return string
     */
    protected function getValidAccessToken()
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken || ($accessToken->getExpiration()->getTimestamp() < (time() + 10))) {
            $this->generateTokens();
        }

        return $accessToken->getToken();

    }

    /**
     * Get headers
     *
     * @return array
     */
    protected function getDefaultHeaders($customHeaders = array())
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' .  $this->getValidAccessToken()
        );

        return array_merge($headers, $customHeaders);
    }
}