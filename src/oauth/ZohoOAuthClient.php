<?php

namespace zcrmsdk\oauth;

use zcrmsdk\crm\utility\LogManager;
use zcrmsdk\oauth\exception\ZohoOAuthException;
use zcrmsdk\oauth\utility\ZohoOAuthConstants;
use zcrmsdk\oauth\utility\ZohoOAuthHTTPConnector;
use zcrmsdk\oauth\utility\ZohoOAuthParams;
use zcrmsdk\oauth\utility\ZohoOAuthTokens;

class ZohoOAuthClient
{
    private static ?ZohoOAuthClient $zohoOAuthClient = null;

    private function __construct(
        private ZohoOAuthParams $zohoOAuthParams
    ) {
    }

    public static function getInstance(ZohoOAuthParams $params): ZohoOAuthClient
    {
        self::$zohoOAuthClient = new ZohoOAuthClient($params);

        return self::$zohoOAuthClient;
    }

    public static function getInstanceWithOutParam(): ZohoOAuthClient
    {
        return self::$zohoOAuthClient;
    }

    /**
     * @throws ZohoOAuthException
     */
    public function getAccessToken(string $userEmailId): ?string
    {
        $persistence = ZohoOAuth::getPersistenceHandlerInstance();
        $tokens = $persistence->getOAuthTokens($userEmailId);

        try {
            return $tokens->getAccessToken();
        } catch (ZohoOAuthException $exception) {
            LogManager::info('Access Token has expired. Hence refreshing.', ['exception' => $exception]);
            $tokens = self::refreshAccessToken($tokens->getRefreshToken(), $userEmailId);

            return $tokens->getAccessToken();
        }
    }

    /**
     * @throws ZohoOAuthException
     */
    public function generateAccessToken(?string $grantToken): ZohoOAuthTokens
    {
        if (null == $grantToken) {
            throw new ZohoOAuthException('Grant Token is not provided.');
        }
        $conn = self::getZohoConnector(ZohoOAuth::getTokenURL());
        $conn->addParam(ZohoOAuthConstants::GRANT_TYPE, ZohoOAuthConstants::GRANT_TYPE_AUTH_CODE);
        $conn->addParam(ZohoOAuthConstants::CODE, $grantToken);
        $resp = $conn->post();
        $responseJSON = self::processResponse($resp);
        if (isset($responseJSON[ZohoOAuthConstants::ACCESS_TOKEN])) {
            $tokens = self::getTokensFromJSON($responseJSON);
            $tokens->setUserEmailId(self::getUserEmailIdFromIAM($tokens->getAccessToken()));
            ZohoOAuth::getPersistenceHandlerInstance()->saveOAuthData($tokens);

            return $tokens;
        }  

        throw new ZohoOAuthException('Exception while fetching access token from grant token - ' . $resp);
    }

    /**
     * @throws ZohoOAuthException
     */
    public function refreshAccessToken(?string $refreshToken, ?string $userEmailId): ZohoOAuthTokens
    {
        if (null === $refreshToken) {
            throw new ZohoOAuthException('Refresh token is not provided.');
        }
        if (null === $userEmailId) {
            throw new ZohoOAuthException('User Email is not provided.');
        }
        $conn = self::getZohoConnector(ZohoOAuth::getRefreshTokenURL());
        $conn->addParam(ZohoOAuthConstants::GRANT_TYPE, ZohoOAuthConstants::GRANT_TYPE_REFRESH);
        $conn->addParam(ZohoOAuthConstants::REFRESH_TOKEN, $refreshToken);
        $response = $conn->post();
        $responseJSON = self::processResponse($response);
        if (isset($responseJSON[ZohoOAuthConstants::ACCESS_TOKEN])) {
            $tokens = self::getTokensFromJSON($responseJSON);
            $tokens->setRefreshToken($refreshToken);
            $tokens->setUserEmailId($userEmailId);
            ZohoOAuth::getPersistenceHandlerInstance()->saveOAuthData($tokens);

            return $tokens;
        }

        throw new ZohoOAuthException('Exception while fetching access token from refresh token - ' . $response);
    }

    private function getZohoConnector(string $url): ZohoOAuthHTTPConnector
    {
        $zohoHttpCon = new ZohoOAuthHTTPConnector();
        $zohoHttpCon->setUrl($url);
        $zohoHttpCon->addParam(ZohoOAuthConstants::CLIENT_ID, $this->zohoOAuthParams->getClientId());
        $zohoHttpCon->addParam(ZohoOAuthConstants::CLIENT_SECRET, $this->zohoOAuthParams->getClientSecret());
        $zohoHttpCon->addParam(ZohoOAuthConstants::REDIRECT_URL, $this->zohoOAuthParams->getRedirectURL());

        return $zohoHttpCon;
    }

    private function getTokensFromJSON(array $responseObj): ZohoOAuthTokens
    {
        $oAuthTokens = new ZohoOAuthTokens();
        $expiresIn = $responseObj[ZohoOAuthConstants::EXPIRES_IN] ?? 36000;
        if (!isset($responseObj[ZohoOAuthConstants::EXPIRES_IN_SEC])) {
            $expiresIn *= 1000;
        }
        $oAuthTokens->setExpiryTime($oAuthTokens->getCurrentTimeInMillis() + $expiresIn);
        $oAuthTokens->setAccessToken($responseObj[ZohoOAuthConstants::ACCESS_TOKEN] ?? null);
        if (array_key_exists(ZohoOAuthConstants::REFRESH_TOKEN, $responseObj)) {
            $oAuthTokens->setRefreshToken($responseObj[ZohoOAuthConstants::REFRESH_TOKEN] ?? null);
        }

        return $oAuthTokens;
    }

    public function getZohoOAuthParams(): ZohoOAuthParams
    {
        return $this->zohoOAuthParams;
    }

    public function setZohoOAuthParams(ZohoOAuthParams $zohoOAuthParams): void
    {
        $this->zohoOAuthParams = $zohoOAuthParams;
    }

    public function getUserEmailIdFromIAM(string $accessToken): string
    {
        $connector = new ZohoOAuthHTTPConnector();
        $connector->setUrl(ZohoOAuth::getUserInfoURL());
        $connector->addHeader(ZohoOAuthConstants::AUTHORIZATION, ZohoOAuthConstants::OAUTH_HEADER_PREFIX . $accessToken);
        $apiResponse = $connector->get();
        $jsonResponse = self::processResponse($apiResponse);
        if (empty($jsonResponse['Email'])) {
            throw new ZohoOAuthException('Exception while fetching UserID from access token, Make sure AAAserver.profile.Read scope is included while generating the Grant token ' . $jsonResponse);
        }

        return $jsonResponse['Email'];
    }

    public function processResponse($apiResponse): mixed
    {
        list($headers, $content) = explode("\r\n\r\n", $apiResponse, 2);

        return json_decode($content, true);
    }
}
