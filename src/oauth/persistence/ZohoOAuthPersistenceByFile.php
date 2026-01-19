<?php

namespace zcrmsdk\oauth\persistence;

use zcrmsdk\oauth\exception\ZohoOAuthException;
use zcrmsdk\oauth\utility\ZohoOAuthTokens;
use zcrmsdk\oauth\ZohoOAuth;

class ZohoOAuthPersistenceByFile implements ZohoOAuthPersistenceInterface
{
    private const string DEFAULT_FILENAME = 'zcrm_oauthtokens.txt';

    public function getTokenPersistencePath(): string
    {
        return trim(ZohoOAuth::getConfigValue('token_persistence_path'));
    }

    protected function getTokenFilePath(): string
    {
        return sprintf('%s/%s', self::getTokenPersistencePath(), self::DEFAULT_FILENAME);
    }

    public function saveOAuthData(ZohoOAuthTokens $zohoOAuthTokens): void
    {
        $arr = [];
        self::deleteOAuthTokens($zohoOAuthTokens->getUserEmailId());
        if (file_exists($this->getTokenFilePath())) {
            try {
                $content = file_get_contents($this->getTokenFilePath());
                if ($content) {
                    $arr = @unserialize($content);
                }
            } catch (\Throwable) {}
        }

        $arr[] = $zohoOAuthTokens;
        $serialized = serialize($arr);
        file_put_contents($this->getTokenFilePath(), $serialized);
    }

    /**
     * @throws ZohoOAuthException
     */
    public function getOAuthTokens(?string $userEmailId): ZohoOAuthTokens
    {
        if (empty($userEmailId)) {
            throw new ZohoOAuthException('User email id is not provided.');
        }
        if (!file_exists($this->getTokenFilePath())) {
            throw new ZohoOAuthException('Token file not exists.');
        }
        $serialized = file_get_contents($this->getTokenFilePath());
        if (empty($serialized)) {
            throw new ZohoOAuthException('Token file contains no data.');
        }
        $arr = unserialize($serialized);
        foreach ($arr as $eachObj) {
            if ($userEmailId === $eachObj->getUserEmailId()) {
                return $eachObj;
            }
        }
        throw new ZohoOAuthException('No Tokens exist for the given user-identifier. Please generate and try again.');
    }

    public function deleteOAuthTokens(?string $userEmailId): void
    {
        if (!file_exists($this->getTokenFilePath())) {
            return;
        }
        $serialized = file_get_contents($this->getTokenFilePath());
        if (empty($serialized)) {
            return;
        }
        $arr = unserialize($serialized);
        foreach ($arr as $i => $eachObj) {
            if ($userEmailId !== $eachObj->getUserEmailId()) {
                continue;
            }
            unset($arr[$i]);
            break;
        }
        $arr = array_values(array_filter($arr));
        $serialized = serialize($arr);
        file_put_contents($this->getTokenFilePath(), $serialized);
    }
}
