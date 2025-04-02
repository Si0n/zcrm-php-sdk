<?php

namespace zcrmsdk\oauth\utility;

use zcrmsdk\oauth\exception\ZohoOAuthException;

class ZohoOAuthTokens
{
    private ?string $refreshToken = null;

    private ?string $accessToken = null;

    private null|float|int $expiryTime = null;

    private ?string $userEmailId = null;

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getAccessToken(): string
    {
        if ($this->isValidAccessToken()) {
            return $this->accessToken;
        }

        throw new ZohoOAuthException('Access token got expired!');
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getExpiryTime(): ?int
    {
        return $this->expiryTime;
    }

    public function setExpiryTime(null|float|int $expiryTime): void
    {
        $this->expiryTime = $expiryTime;
    }

    public function isValidAccessToken(): bool
    {
        return ($this->getExpiryTime() - $this->getCurrentTimeInMillis()) > 1000;
    }

    public function getCurrentTimeInMillis(): int
    {
        return round(microtime(true) * 1000);
    }

    /**
     * userEmailId.
     */
    public function getUserEmailId(): ?string
    {
        return $this->userEmailId;
    }

    /**
     * userEmailId.
     */
    public function setUserEmailId(?string $userEmailId): void
    {
        $this->userEmailId = $userEmailId;
    }
}
