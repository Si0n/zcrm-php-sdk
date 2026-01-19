<?php

namespace zcrmsdk\oauth\utility;

class ZohoOAuthParams
{
    private ?string $clientId = null;

    private ?string $clientSecret = null;

    private ?string $redirectUrl = null;

    private ?string $accessType = null;

    private ?string $scopes = null;

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function getRedirectURL(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectURL(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getAccessType(): ?string
    {
        return $this->accessType;
    }

    public function setAccessType(?string $accessType): void
    {
        $this->accessType = $accessType;
    }

    public function getScopes(): ?string
    {
        return $this->scopes;
    }

    public function setScopes(?string $scopes): void
    {
        $this->scopes = $scopes;
    }
}
