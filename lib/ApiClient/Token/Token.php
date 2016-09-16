<?php

namespace Ticketpark\ApiClient\Token;

abstract class Token
{
    /**
     * Seconds to consider token as expired before it actually expires
     *
     * @const int TIMEOUT_BUFFER
     */
    const TIMEOUT_BUFFER = 10;

    /**
     * @var string $token
     */
    protected $token;

    /**
     * @var \DateTime
     */
    protected $expiration;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     * @param \DateTime|null $expiration
     */
    public function __construct($token = null, \DateTime $expiration = null)
    {
        $this->token = $token;
        $this->expiration = $expiration;
    }

    /**
     * @param string $token
     * @return Token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param \DateTime $expiration
     * @return Token
     */
    public function setExpiration(\DateTime $expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasExpired()
    {
        if (null == $this->getExpiration()) {

            return false;
        }

        return $this->getExpiration()->getTimestamp() < (time() + self::TIMEOUT_BUFFER);
    }
}