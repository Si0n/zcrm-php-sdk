<?php

namespace zcrmsdk\crm\api;

use zcrmsdk\crm\api\handler\APIHandler;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\api\response\BulkAPIResponse;
use zcrmsdk\crm\api\response\FileAPIResponse;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\utility\APIConstants;
use zcrmsdk\crm\utility\ZCRMConfigUtil;
use zcrmsdk\crm\utility\ZohoHTTPConnector;

/**
 * This class is to construct the API requests and initiate the request.
 *
 * @author sumanth-3058
 */
class APIRequest
{
    private null|string $url = null;

    private null|array $requestParams = [];

    private null|array $requestHeaders = [];

    private null|array $requestBody = null;

    private null|string $requestMethod = null;

    private null|string $apiKey = null;

    private null|string $response = null;

    private null|array $responseInfo = null;

    private function __construct(APIHandler $apiHandler)
    {
        if (str_contains($apiHandler->getUrlPath(), 'content') || str_contains($apiHandler->getUrlPath(), 'upload') || str_contains($apiHandler->getUrlPath(), 'bulk-write')) {
            $this->setUrl($apiHandler->getUrlPath());
        } else {
            $this->constructAPIUrl($apiHandler);
            $this->setUrl($this->url . $apiHandler->getUrlPath());
            if (!str_starts_with($apiHandler->getUrlPath(), 'http')) {
                $this->setUrl('https://' . $this->url);
            }
        }
        $this->setRequestParams($apiHandler->getRequestParams());
        $this->setRequestHeaders($apiHandler->getRequestHeaders());
        $this->setRequestBody($apiHandler->getRequestBody());
        $this->setRequestMethod($apiHandler->getRequestMethod());
        $this->setApiKey($apiHandler->getApiKey());
    }

    public static function getInstance($apiHandler): self
    {
        return new self($apiHandler);
    }

    /**
     * Method to construct the API Url.
     */
    public function constructAPIUrl($apiHandler): void
    {
        $hitSandbox = ZCRMConfigUtil::getConfigValue('sandbox');
        $baseUrl = 0 == strcasecmp($hitSandbox, 'true') ? str_replace('www', 'sandbox', ZCRMConfigUtil::getAPIBaseUrl()) : ZCRMConfigUtil::getAPIBaseUrl();
        if ($apiHandler->isBulk()) {
            $this->url = $baseUrl . '/crm/bulk/' . ZCRMConfigUtil::getAPIVersion() . '/';
        } else {
            $this->url = $baseUrl . '/crm/' . ZCRMConfigUtil::getAPIVersion() . '/';
        }
        $this->url = str_replace(PHP_EOL, '', $this->url);
    }

    /**
     * @throws ZCRMException
     */
    private function authenticateRequest(): void
    {
        $accessToken = (new ZCRMConfigUtil())->getAccessToken();
        if (str_contains($this->url, 'content') || str_contains($this->url, 'upload') || str_contains($this->url, 'bulk-write')) {
            $this->requestHeaders[APIConstants::AUTHORIZATION] = ' ' . APIConstants::OAUTH_HEADER_PREFIX . $accessToken;
        } else {
            $this->requestHeaders[APIConstants::AUTHORIZATION] = APIConstants::OAUTH_HEADER_PREFIX . $accessToken;
        }
    }

    /**
     * initiate the request and get the API response.
     *
     * @throws ZCRMException
     */
    public function getAPIResponse(): APIResponse
    {
        $connector = ZohoHTTPConnector::getInstance();
        $connector->setUrl($this->url);
        self::authenticateRequest();
        $connector->setRequestHeadersMap($this->requestHeaders);
        $connector->setRequestParamsMap($this->requestParams);
        $connector->setRequestBody($this->requestBody);
        $connector->setRequestType($this->requestMethod);
        $connector->setApiKey($this->apiKey);
        $response = $connector->fireRequest();
        $this->response = $response[0];
        $this->responseInfo = $response[1];

        return new APIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
    }

    /**
     * initiate the request and get the API response.
     *
     * @throws ZCRMException
     */
    public function getBulkAPIResponse(): BulkAPIResponse
    {
        $connector = ZohoHTTPConnector::getInstance();
        $connector->setUrl($this->url);
        self::authenticateRequest();
        $connector->setRequestHeadersMap($this->requestHeaders);
        $connector->setRequestParamsMap($this->requestParams);
        $connector->setRequestBody($this->requestBody);
        $connector->setRequestType($this->requestMethod);
        $connector->setApiKey($this->apiKey);
        $connector->setBulkRequest(true);
        $response = $connector->fireRequest();
        $this->response = $response[0];
        $this->responseInfo = $response[1];

        return new BulkAPIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
    }

    /**
     * @throws ZCRMException
     */
    public function uploadFile(string $filePath): APIResponse
    {
        $filename = basename($filePath);
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($filePath);
        } else {
            $mime = 'application/octet-stream';
        }
        if (function_exists('curl_file_create')) { // php 5.6+
            $cFile = curl_file_create($filePath, $mime, $filename);
        } else {
            $cFile = '@' . realpath($filePath, $mime, $filename);
        }
        $connector = ZohoHTTPConnector::getInstance();
        $connector->setUrl($this->url);
        self::authenticateRequest();
        $connector->setRequestHeadersMap($this->requestHeaders);
        $connector->setRequestParamsMap($this->requestParams);
        $connector->setRequestBody([
            'file' => $cFile,
        ]);
        $connector->setRequestType($this->requestMethod);
        $connector->setApiKey($this->apiKey);
        $response = $connector->fireRequest();
        $this->response = $response[0];
        $this->responseInfo = $response[1];

        return new APIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
    }

    /**
     * @throws ZCRMException
     */
    public function uploadLinkAsAttachment(string $linkURL): APIResponse
    {
        $post = [
            'attachmentUrl' => $linkURL,
        ];

        $connector = ZohoHTTPConnector::getInstance();
        $connector->setUrl($this->url);
        self::authenticateRequest();
        $connector->setRequestHeadersMap($this->requestHeaders);
        $connector->setRequestBody($post);
        $connector->setRequestType($this->requestMethod);
        $connector->setApiKey($this->apiKey);
        $response = $connector->fireRequest();
        $this->response = $response[0];
        $this->responseInfo = $response[1];

        return new APIResponse($this->response, $this->responseInfo[APIConstants::HTTP_CODE]);
    }

    /**
     * @throws ZCRMException
     */
    public function downloadFile(): FileAPIResponse
    {
        $connector = ZohoHTTPConnector::getInstance();
        $connector->setUrl($this->url);
        self::authenticateRequest();
        $connector->setRequestHeadersMap($this->requestHeaders);
        $connector->setRequestParamsMap($this->requestParams);
        $connector->setRequestType($this->requestMethod);
        $response = $connector->downloadFile();

        return (new FileAPIResponse())->setFileContent($response[0], $response[1][APIConstants::HTTP_CODE]);
    }

    /**
     * Get the request url.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set the request url.
     */
    public function setUrl(null|string $url): void
    {
        $this->url = $url;
    }

    /**
     * Get the request parameters.
     */
    public function getRequestParams(): null|array
    {
        return $this->requestParams;
    }

    /**
     * Set the request parameters.
     */
    public function setRequestParams(null|array $requestParams): void
    {
        $this->requestParams = $requestParams;
    }

    /**
     * Get the request headers.
     */
    public function getRequestHeaders(): null|array
    {
        return $this->requestHeaders;
    }

    /**
     * Set the request headers.
     */
    public function setRequestHeaders(null|array $requestHeaders): void
    {
        $this->requestHeaders = $requestHeaders;
    }

    /**
     * Get the request body.
     */
    public function getRequestBody():null| array
    {
        return $this->requestBody;
    }

    /**
     * Set the request body.
     */
    public function setRequestBody(null|array $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    /**
     * Get the request method.
     */
    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    /**
     * Set the request method.
     */
    public function setRequestMethod(string $requestMethod): void
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * Get the API Key used in the input json data(like 'modules', 'data','layouts',..etc).
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Set the API Key used in the input json data(like 'modules', 'data','layouts',..etc).
     */
    public function setApiKey(null|string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
