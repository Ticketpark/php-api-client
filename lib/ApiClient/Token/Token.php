<?php

namespace Ticketpark\ApiClient\Token;

abstract class Token
{
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
     * @return DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param DateTime $expiration
     * @return Token
     */
    public function setExpiration(\DateTime $expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }
}