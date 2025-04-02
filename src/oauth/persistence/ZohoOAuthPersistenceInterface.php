<?php

namespace zcrmsdk\oauth\persistence;

use zcrmsdk\oauth\utility\ZohoOAuthTokens;

interface ZohoOAuthPersistenceInterface
{
    public function saveOAuthData(ZohoOAuthTokens $zohoOAuthTokens): void;

    public function getOAuthTokens(?string $userEmailId): ZohoOAuthTokens;

    public function deleteOAuthTokens(?string $userEmailId): void;
}
