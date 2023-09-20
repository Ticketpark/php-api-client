<?php

namespace Ticketpark\ApiClient\Token;

abstract class Token
{
    private const TIMEOUT_BUFFER = 10;

    public function __construct(
        private readonly string $token,
        private readonly ?\DateTime $expiration = null
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiration(): ?\DateTime
    {
        return $this->expiration;
    }

    public function hasExpired(): bool
    {
        if (null == $this->getExpiration()) {

            return false;
        }

        return $this->getExpiration()->getTimestamp() < (time() + self::TIMEOUT_BUFFER);
    }
}
